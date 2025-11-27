<?php
/**
 * Appointments API
 * Handles booking onboarding and interview appointments
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = Database::getInstance();

switch ($action) {
    // Book onboarding appointment (for users)
    case 'book_onboarding':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $slotId = (int)($_POST['slot_id'] ?? 0);
        if (!$slotId) {
            jsonResponse(['success' => false, 'message' => 'Please select a time slot'], 400);
        }
        
        // Get slot details
        $slot = $db->fetch(
            "SELECT * FROM available_slots WHERE id = :id AND is_booked = 0",
            ['id' => $slotId]
        );
        
        if (!$slot) {
            jsonResponse(['success' => false, 'message' => 'This time slot is no longer available'], 400);
        }
        
        $userId = $_SESSION['user_id'];
        
        // Check if user already has a pending onboarding
        $existing = $db->fetch(
            "SELECT id FROM appointments WHERE user_id = :id AND type = 'onboarding' AND status IN ('scheduled', 'confirmed')",
            ['id' => $userId]
        );
        
        if ($existing) {
            jsonResponse(['success' => false, 'message' => 'You already have an onboarding appointment scheduled'], 400);
        }
        
        // Create the appointment
        $scheduledAt = $slot['slot_date'] . ' ' . $slot['start_time'];
        $duration = (strtotime($slot['end_time']) - strtotime($slot['start_time'])) / 60;
        $meetingLink = generateMeetingLink('onboard_' . generateToken(8));
        
        $appointmentId = $db->insert('appointments', [
            'user_id' => $userId,
            'created_by' => $slot['admin_id'],
            'type' => 'onboarding',
            'title' => 'Initial Onboarding Call',
            'description' => 'Welcome call to discuss your career goals and how we can help you find the perfect job.',
            'scheduled_at' => $scheduledAt,
            'duration' => $duration,
            'location' => getMeetingProviderName() . ' Meeting',
            'meeting_link' => $meetingLink,
            'status' => 'scheduled',
            'booked_by_user' => 1
        ]);
        
        // Mark slot as booked
        $db->update('available_slots', [
            'is_booked' => 1,
            'booked_by' => $userId,
            'appointment_id' => $appointmentId
        ], 'id = :id', ['id' => $slotId]);
        
        // Update user status
        updateAccountStatus($userId, 'onboarding_scheduled');
        
        // Create notification
        createNotification(
            $userId,
            'onboarding',
            'Onboarding Scheduled',
            'Your onboarding call has been scheduled for ' . formatDate($scheduledAt, 'M d, Y at g:i A') . '. Join via ' . getMeetingProviderName() . '.',
            'pages/onboarding.php',
            'normal',
            $appointmentId,
            'appointment'
        );
        
        // Send email with meeting link
        $user = getUserById($userId);
        sendTemplatedEmail('onboarding_scheduled', $userId, [
            'appointment_date' => formatDate($scheduledAt, 'l, F j, Y'),
            'appointment_time' => formatDate($scheduledAt, 'g:i A'),
            'duration' => $duration,
            'meeting_link' => $meetingLink,
            'meeting_provider' => getMeetingProviderName()
        ]);
        
        // Also notify admin
        createNotification(
            $slot['admin_id'],
            'system',
            'New Onboarding Booked',
            getUserById($userId)['first_name'] . ' has booked an onboarding call for ' . formatDate($scheduledAt, 'M d, Y at g:i A') . '.',
            'admin/appointments.php',
            'normal',
            $appointmentId,
            'appointment'
        );
        
        logActivity($userId, 'book_onboarding', 'appointment', $appointmentId);
        
        jsonResponse([
            'success' => true, 
            'message' => 'Onboarding scheduled successfully!',
            'appointment_id' => $appointmentId
        ]);
        break;
        
    // Cancel appointment (for users)
    case 'cancel_appointment':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $appointmentId = (int)($_POST['appointment_id'] ?? 0);
        if (!$appointmentId) {
            jsonResponse(['success' => false, 'message' => 'Appointment ID required'], 400);
        }
        
        // Verify ownership
        $appointment = $db->fetch(
            "SELECT * FROM appointments WHERE id = :id AND user_id = :user_id",
            ['id' => $appointmentId, 'user_id' => $_SESSION['user_id']]
        );
        
        if (!$appointment) {
            jsonResponse(['success' => false, 'message' => 'Appointment not found'], 404);
        }
        
        // Cancel the appointment
        $db->update('appointments', ['status' => 'cancelled'], 'id = :id', ['id' => $appointmentId]);
        
        // Free up the slot if it was booked
        $db->query(
            "UPDATE available_slots SET is_booked = 0, booked_by = NULL, appointment_id = NULL WHERE appointment_id = :id",
            ['id' => $appointmentId]
        );
        
        // If this was an onboarding appointment, update user status
        if ($appointment['type'] === 'onboarding') {
            updateAccountStatus($_SESSION['user_id'], 'pending_onboarding');
        }
        
        logActivity($_SESSION['user_id'], 'cancel_appointment', 'appointment', $appointmentId);
        
        jsonResponse(['success' => true, 'message' => 'Appointment cancelled successfully']);
        break;
        
    // Get available slots (for users)
    case 'get_available_slots':
        $type = sanitize($_GET['type'] ?? 'onboarding');
        $days = min((int)($_GET['days'] ?? 14), 30);
        
        $slots = $db->fetchAll(
            "SELECT s.*, u.first_name as admin_name 
             FROM available_slots s 
             JOIN users u ON s.admin_id = u.id
             WHERE s.is_booked = 0 
             AND (s.slot_type = :type OR s.slot_type = 'both')
             AND s.slot_date >= CURDATE()
             AND s.slot_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY s.slot_date ASC, s.start_time ASC",
            ['type' => $type, 'days' => $days]
        );
        
        jsonResponse(['success' => true, 'slots' => $slots]);
        break;
        
    // Get user's appointments
    case 'get_my_appointments':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $status = sanitize($_GET['status'] ?? '');
        $type = sanitize($_GET['type'] ?? '');
        
        $where = "user_id = :user_id";
        $params = ['user_id' => $_SESSION['user_id']];
        
        if ($status) {
            $where .= " AND status = :status";
            $params['status'] = $status;
        }
        if ($type) {
            $where .= " AND type = :type";
            $params['type'] = $type;
        }
        
        $appointments = $db->fetchAll(
            "SELECT a.*, j.title as job_title, c.name as company_name
             FROM appointments a
             LEFT JOIN applications app ON a.application_id = app.id
             LEFT JOIN jobs j ON app.job_id = j.id
             LEFT JOIN companies c ON j.company_id = c.id
             WHERE $where
             ORDER BY a.scheduled_at DESC",
            $params
        );
        
        jsonResponse(['success' => true, 'appointments' => $appointments]);
        break;
        
    // Confirm attendance (for users)
    case 'confirm_appointment':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $appointmentId = (int)($_POST['appointment_id'] ?? 0);
        
        $appointment = $db->fetch(
            "SELECT * FROM appointments WHERE id = :id AND user_id = :user_id",
            ['id' => $appointmentId, 'user_id' => $_SESSION['user_id']]
        );
        
        if (!$appointment) {
            jsonResponse(['success' => false, 'message' => 'Appointment not found'], 404);
        }
        
        $db->update('appointments', ['status' => 'confirmed'], 'id = :id', ['id' => $appointmentId]);
        
        jsonResponse(['success' => true, 'message' => 'Appointment confirmed']);
        break;
        
    // Book interview appointment (for users who received interview invitation)
    case 'book_interview':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $slotId = (int)($_POST['slot_id'] ?? 0);
        $applicationId = (int)($_POST['application_id'] ?? 0);
        
        if (!$slotId || !$applicationId) {
            jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        }
        
        // Verify application belongs to user and is in interview stage
        $application = $db->fetch(
            "SELECT a.*, j.title as job_title, c.name as company_name 
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             LEFT JOIN companies c ON j.company_id = c.id
             WHERE a.id = :id AND a.user_id = :user_id AND a.status IN ('shortlisted', 'interview_scheduled')",
            ['id' => $applicationId, 'user_id' => $_SESSION['user_id']]
        );
        
        if (!$application) {
            jsonResponse(['success' => false, 'message' => 'Application not found or not eligible for interview'], 400);
        }
        
        // Get slot
        $slot = $db->fetch(
            "SELECT * FROM available_slots WHERE id = :id AND is_booked = 0",
            ['id' => $slotId]
        );
        
        if (!$slot) {
            jsonResponse(['success' => false, 'message' => 'Time slot not available'], 400);
        }
        
        // Create interview appointment
        $scheduledAt = $slot['slot_date'] . ' ' . $slot['start_time'];
        $duration = (strtotime($slot['end_time']) - strtotime($slot['start_time'])) / 60;
        $meetingLink = generateMeetingLink('interview_' . generateToken(8));
        
        $appointmentId = $db->insert('appointments', [
            'user_id' => $_SESSION['user_id'],
            'application_id' => $applicationId,
            'created_by' => $slot['admin_id'],
            'type' => 'interview',
            'title' => 'Interview - ' . $application['job_title'],
            'description' => 'Job interview for ' . $application['job_title'] . ' at ' . $application['company_name'],
            'scheduled_at' => $scheduledAt,
            'duration' => $duration,
            'location' => getMeetingProviderName() . ' Meeting',
            'meeting_link' => $meetingLink,
            'status' => 'scheduled',
            'booked_by_user' => 1
        ]);
        
        // Mark slot as booked
        $db->update('available_slots', [
            'is_booked' => 1,
            'booked_by' => $_SESSION['user_id'],
            'appointment_id' => $appointmentId
        ], 'id = :id', ['id' => $slotId]);
        
        // Update application status
        $db->update('applications', [
            'status' => 'interview_scheduled',
            'interview_scheduled_at' => $scheduledAt
        ], 'id = :id', ['id' => $applicationId]);
        
        // Create notification
        createNotification(
            $_SESSION['user_id'],
            'interview',
            'Interview Scheduled',
            'Your interview for ' . $application['job_title'] . ' is scheduled for ' . formatDate($scheduledAt, 'M d at g:i A') . '. Join via ' . getMeetingProviderName() . '.',
            'pages/applications.php',
            'high',
            $applicationId,
            'application'
        );
        
        // Send email with meeting link
        sendTemplatedEmail('interview_scheduled', $_SESSION['user_id'], [
            'job_title' => $application['job_title'],
            'company_name' => $application['company_name'],
            'appointment_date' => formatDate($scheduledAt, 'l, F j, Y'),
            'appointment_time' => formatDate($scheduledAt, 'g:i A'),
            'duration' => $duration,
            'meeting_type' => getMeetingProviderName(),
            'meeting_link' => $meetingLink
        ]);
        
        logActivity($_SESSION['user_id'], 'book_interview', 'appointment', $appointmentId);
        
        jsonResponse([
            'success' => true,
            'message' => 'Interview scheduled successfully!',
            'appointment_id' => $appointmentId
        ]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
