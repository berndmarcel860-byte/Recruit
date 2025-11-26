<?php
/**
 * Admin API
 * Handles admin operations via AJAX
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require admin authentication
if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = Database::getInstance();

switch ($action) {
    // Dashboard Stats
    case 'dashboard':
        $stats = [
            'total_users' => $db->count('users', "role = 'user'"),
            'active_jobs' => $db->count('jobs', "status = 'active'"),
            'total_applications' => $db->count('applications'),
            'pending_applications' => $db->count('applications', "status = 'pending'"),
            'upcoming_appointments' => $db->count('appointments', "scheduled_at >= NOW() AND status IN ('scheduled', 'confirmed')"),
            'total_companies' => $db->count('companies', "is_active = 1")
        ];
        
        // Recent applications
        $recentApplications = $db->fetchAll(
            "SELECT a.*, u.first_name, u.last_name, u.email, j.title as job_title
             FROM applications a
             JOIN users u ON a.user_id = u.id
             JOIN jobs j ON a.job_id = j.id
             ORDER BY a.created_at DESC
             LIMIT 5"
        );
        
        jsonResponse([
            'success' => true,
            'stats' => $stats,
            'recent_applications' => $recentApplications
        ]);
        break;
        
    // User Management
    case 'get_users':
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 10), 100);
        $search = sanitize($_GET['search'] ?? '');
        $role = sanitize($_GET['role'] ?? '');
        
        $where = "1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)";
            $params['search'] = "%$search%";
        }
        if ($role) {
            $where .= " AND role = :role";
            $params['role'] = $role;
        }
        
        $total = $db->count('users', $where, $params);
        $pagination = paginate($total, $limit, $page);
        
        $sql = "SELECT id, email, first_name, last_name, phone, city, role, is_active, created_at, last_login
                FROM users WHERE $where ORDER BY created_at DESC
                LIMIT {$pagination['offset']}, $limit";
        
        $users = $db->fetchAll($sql, $params);
        
        jsonResponse(['success' => true, 'users' => $users, 'pagination' => $pagination]);
        break;
        
    case 'get_user':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'User ID is required'], 400);
        }
        
        $user = getUserById($id);
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'User not found'], 404);
        }
        
        // Get user's applications
        $applications = $db->fetchAll(
            "SELECT a.*, j.title as job_title, c.name as company_name
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             LEFT JOIN companies c ON j.company_id = c.id
             WHERE a.user_id = :id
             ORDER BY a.created_at DESC",
            ['id' => $id]
        );
        
        // Get user's appointments
        $appointments = $db->fetchAll(
            "SELECT * FROM appointments WHERE user_id = :id ORDER BY scheduled_at DESC",
            ['id' => $id]
        );
        
        $user['applications'] = $applications;
        $user['appointments'] = $appointments;
        
        jsonResponse(['success' => true, 'user' => $user]);
        break;
        
    case 'update_user':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'User ID is required'], 400);
        }
        
        $data = [];
        $allowedFields = ['first_name', 'last_name', 'phone', 'role', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = sanitize($_POST[$field]);
            }
        }
        
        if (empty($data)) {
            jsonResponse(['success' => false, 'message' => 'No data to update'], 400);
        }
        
        $db->update('users', $data, 'id = :id', ['id' => $id]);
        logActivity($_SESSION['user_id'], 'update_user', 'user', $id, $data);
        
        jsonResponse(['success' => true, 'message' => 'User updated successfully']);
        break;
        
    // Application Management
    case 'get_applications':
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 10), 100);
        $status = sanitize($_GET['status'] ?? '');
        $jobId = (int)($_GET['job_id'] ?? 0);
        
        $where = "1=1";
        $params = [];
        
        if ($status) {
            $where .= " AND a.status = :status";
            $params['status'] = $status;
        }
        if ($jobId) {
            $where .= " AND a.job_id = :job_id";
            $params['job_id'] = $jobId;
        }
        
        $total = $db->fetch("SELECT COUNT(*) as count FROM applications a WHERE $where", $params)['count'];
        $pagination = paginate($total, $limit, $page);
        
        $sql = "SELECT a.*, u.first_name, u.last_name, u.email,
                       j.title as job_title, c.name as company_name
                FROM applications a
                JOIN users u ON a.user_id = u.id
                JOIN jobs j ON a.job_id = j.id
                LEFT JOIN companies c ON j.company_id = c.id
                WHERE $where
                ORDER BY a.created_at DESC
                LIMIT {$pagination['offset']}, $limit";
        
        $applications = $db->fetchAll($sql, $params);
        
        jsonResponse(['success' => true, 'applications' => $applications, 'pagination' => $pagination]);
        break;
        
    case 'update_application':
        $id = (int)($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');
        
        if (!$id || !$status) {
            jsonResponse(['success' => false, 'message' => 'Application ID and status are required'], 400);
        }
        
        $db->update('applications', [
            'status' => $status,
            'notes' => $notes,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => $_SESSION['user_id']
        ], 'id = :id', ['id' => $id]);
        
        logActivity($_SESSION['user_id'], 'update_application', 'application', $id, ['status' => $status]);
        
        jsonResponse(['success' => true, 'message' => 'Application updated successfully']);
        break;
        
    // Offer Job to User
    case 'offer_job':
        $userId = (int)($_POST['user_id'] ?? 0);
        $jobId = (int)($_POST['job_id'] ?? 0);
        $coverLetter = sanitize($_POST['message'] ?? 'Job offered by admin');
        
        if (!$userId || !$jobId) {
            jsonResponse(['success' => false, 'message' => 'User ID and Job ID are required'], 400);
        }
        
        // Check if already applied
        $existing = $db->fetch(
            "SELECT id FROM applications WHERE user_id = :user_id AND job_id = :job_id",
            ['user_id' => $userId, 'job_id' => $jobId]
        );
        
        if ($existing) {
            jsonResponse(['success' => false, 'message' => 'User already has an application for this job'], 400);
        }
        
        // Calculate match score
        $user = getUserById($userId);
        $job = $db->fetch("SELECT * FROM jobs WHERE id = :id", ['id' => $jobId]);
        $job['skills'] = json_decode($job['skills'] ?? '[]', true);
        $matchScore = calculateMatchScore($user, $job);
        
        // Create application
        $applicationId = $db->insert('applications', [
            'user_id' => $userId,
            'job_id' => $jobId,
            'cover_letter' => $coverLetter,
            'match_score' => $matchScore,
            'source' => 'admin-offered',
            'status' => 'offered'
        ]);
        
        // Create email notification
        $db->insert('emails', [
            'recipient_id' => $userId,
            'sender_id' => $_SESSION['user_id'],
            'subject' => "Job Opportunity: {$job['title']}",
            'body' => "You have been offered a position for {$job['title']}. Please review and respond.",
            'type' => 'job-offer',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->query("UPDATE jobs SET applications_count = applications_count + 1 WHERE id = :id", ['id' => $jobId]);
        
        logActivity($_SESSION['user_id'], 'offer_job', 'application', $applicationId, [
            'user_id' => $userId,
            'job_id' => $jobId
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Job offered successfully', 'application_id' => $applicationId]);
        break;
        
    // Appointment Management
    case 'get_appointments':
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 10), 100);
        $type = sanitize($_GET['type'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        
        $where = "1=1";
        $params = [];
        
        if ($type) {
            $where .= " AND ap.type = :type";
            $params['type'] = $type;
        }
        if ($status) {
            $where .= " AND ap.status = :status";
            $params['status'] = $status;
        }
        
        $total = $db->fetch("SELECT COUNT(*) as count FROM appointments ap WHERE $where", $params)['count'];
        $pagination = paginate($total, $limit, $page);
        
        $sql = "SELECT ap.*, u.first_name, u.last_name, u.email,
                       j.title as job_title
                FROM appointments ap
                JOIN users u ON ap.user_id = u.id
                LEFT JOIN applications a ON ap.application_id = a.id
                LEFT JOIN jobs j ON a.job_id = j.id
                WHERE $where
                ORDER BY ap.scheduled_at ASC
                LIMIT {$pagination['offset']}, $limit";
        
        $appointments = $db->fetchAll($sql, $params);
        
        jsonResponse(['success' => true, 'appointments' => $appointments, 'pagination' => $pagination]);
        break;
        
    case 'create_appointment':
        $required = ['user_id', 'type', 'title', 'scheduled_at'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                jsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
            }
        }
        
        $appointmentId = $db->insert('appointments', [
            'user_id' => (int)$_POST['user_id'],
            'application_id' => (int)($_POST['application_id'] ?? 0) ?: null,
            'created_by' => $_SESSION['user_id'],
            'type' => sanitize($_POST['type']),
            'title' => sanitize($_POST['title']),
            'description' => sanitize($_POST['description'] ?? ''),
            'scheduled_at' => $_POST['scheduled_at'],
            'duration' => (int)($_POST['duration'] ?? 60),
            'location' => sanitize($_POST['location'] ?? ''),
            'meeting_link' => sanitize($_POST['meeting_link'] ?? ''),
            'status' => 'scheduled'
        ]);
        
        // Create email notification
        $user = getUserById($_POST['user_id']);
        $db->insert('emails', [
            'recipient_id' => $_POST['user_id'],
            'sender_id' => $_SESSION['user_id'],
            'subject' => ucfirst($_POST['type']) . " Scheduled: {$_POST['title']}",
            'body' => "Your {$_POST['type']} has been scheduled for " . formatDate($_POST['scheduled_at'], 'M d, Y H:i') . ".",
            'type' => 'interview-invite',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
        
        logActivity($_SESSION['user_id'], 'create_appointment', 'appointment', $appointmentId);
        
        jsonResponse(['success' => true, 'message' => 'Appointment created successfully', 'appointment_id' => $appointmentId]);
        break;
        
    case 'update_appointment':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Appointment ID is required'], 400);
        }
        
        $data = [];
        $allowedFields = ['type', 'title', 'description', 'scheduled_at', 'duration', 'location', 'meeting_link', 'status', 'notes', 'feedback'];
        
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = sanitize($_POST[$field]);
            }
        }
        
        $db->update('appointments', $data, 'id = :id', ['id' => $id]);
        logActivity($_SESSION['user_id'], 'update_appointment', 'appointment', $id);
        
        jsonResponse(['success' => true, 'message' => 'Appointment updated successfully']);
        break;
        
    case 'delete_appointment':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Appointment ID is required'], 400);
        }
        
        $db->update('appointments', ['status' => 'cancelled'], 'id = :id', ['id' => $id]);
        logActivity($_SESSION['user_id'], 'cancel_appointment', 'appointment', $id);
        
        jsonResponse(['success' => true, 'message' => 'Appointment cancelled successfully']);
        break;
        
    // Email Management
    case 'send_email':
        $recipientId = (int)($_POST['recipient_id'] ?? 0);
        $subject = sanitize($_POST['subject'] ?? '');
        $body = sanitize($_POST['body'] ?? '');
        $type = sanitize($_POST['type'] ?? 'custom');
        
        if (!$recipientId || !$subject || !$body) {
            jsonResponse(['success' => false, 'message' => 'Recipient, subject and body are required'], 400);
        }
        
        $emailId = $db->insert('emails', [
            'recipient_id' => $recipientId,
            'sender_id' => $_SESSION['user_id'],
            'subject' => $subject,
            'body' => $body,
            'type' => $type,
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
        
        logActivity($_SESSION['user_id'], 'send_email', 'email', $emailId);
        
        jsonResponse(['success' => true, 'message' => 'Email sent successfully', 'email_id' => $emailId]);
        break;
        
    case 'get_emails':
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 10), 100);
        $type = sanitize($_GET['type'] ?? '');
        
        $where = "1=1";
        $params = [];
        
        if ($type) {
            $where .= " AND e.type = :type";
            $params['type'] = $type;
        }
        
        $total = $db->fetch("SELECT COUNT(*) as count FROM emails e WHERE $where", $params)['count'];
        $pagination = paginate($total, $limit, $page);
        
        $sql = "SELECT e.*, 
                       r.first_name as recipient_first_name, r.last_name as recipient_last_name, r.email as recipient_email,
                       s.first_name as sender_first_name, s.last_name as sender_last_name
                FROM emails e
                JOIN users r ON e.recipient_id = r.id
                JOIN users s ON e.sender_id = s.id
                WHERE $where
                ORDER BY e.created_at DESC
                LIMIT {$pagination['offset']}, $limit";
        
        $emails = $db->fetchAll($sql, $params);
        
        jsonResponse(['success' => true, 'emails' => $emails, 'pagination' => $pagination]);
        break;
        
    // Job Management
    case 'create_job':
        $required = ['company_id', 'title', 'description'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                jsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
            }
        }
        
        $data = [
            'company_id' => (int)$_POST['company_id'],
            'title' => sanitize($_POST['title']),
            'description' => sanitize($_POST['description']),
            'location' => sanitize($_POST['location'] ?? ''),
            'type' => sanitize($_POST['type'] ?? 'full-time'),
            'remote' => sanitize($_POST['remote'] ?? 'on-site'),
            'salary_min' => (int)($_POST['salary_min'] ?? 0) ?: null,
            'salary_max' => (int)($_POST['salary_max'] ?? 0) ?: null,
            'experience_level' => sanitize($_POST['experience_level'] ?? 'mid'),
            'department' => sanitize($_POST['department'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        // JSON fields
        $jsonFields = ['requirements', 'responsibilities', 'skills', 'benefits'];
        foreach ($jsonFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = is_array($_POST[$field]) ? json_encode($_POST[$field]) : $_POST[$field];
            }
        }
        
        if (!empty($_POST['deadline'])) {
            $data['deadline'] = $_POST['deadline'];
        }
        
        $jobId = $db->insert('jobs', $data);
        logActivity($_SESSION['user_id'], 'create_job', 'job', $jobId);
        
        jsonResponse(['success' => true, 'message' => 'Job created successfully', 'job_id' => $jobId]);
        break;
        
    case 'update_job':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Job ID is required'], 400);
        }
        
        $data = [];
        $allowedFields = ['title', 'description', 'location', 'type', 'remote', 'salary_min', 'salary_max',
                          'experience_level', 'department', 'status', 'deadline'];
        
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = sanitize($_POST[$field]);
            }
        }
        
        // JSON fields
        $jsonFields = ['requirements', 'responsibilities', 'skills', 'benefits'];
        foreach ($jsonFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = is_array($_POST[$field]) ? json_encode($_POST[$field]) : $_POST[$field];
            }
        }
        
        $db->update('jobs', $data, 'id = :id', ['id' => $id]);
        logActivity($_SESSION['user_id'], 'update_job', 'job', $id);
        
        jsonResponse(['success' => true, 'message' => 'Job updated successfully']);
        break;
        
    case 'delete_job':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Job ID is required'], 400);
        }
        
        $db->update('jobs', ['status' => 'closed'], 'id = :id', ['id' => $id]);
        logActivity($_SESSION['user_id'], 'close_job', 'job', $id);
        
        jsonResponse(['success' => true, 'message' => 'Job closed successfully']);
        break;
        
    // Company Management
    case 'get_companies':
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        $search = sanitize($_GET['search'] ?? '');
        $industry = sanitize($_GET['industry'] ?? '');
        $isActive = $_GET['is_active'] ?? '';
        
        $where = "1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (name LIKE :search OR description LIKE :search)";
            $params['search'] = "%$search%";
        }
        if ($industry) {
            $where .= " AND industry = :industry";
            $params['industry'] = $industry;
        }
        if ($isActive !== '') {
            $where .= " AND is_active = :is_active";
            $params['is_active'] = (int)$isActive;
        }
        
        $total = $db->count('companies', $where, $params);
        $pagination = paginate($total, $limit, $page);
        
        $sql = "SELECT * FROM companies WHERE $where ORDER BY name ASC
                LIMIT {$pagination['offset']}, $limit";
        
        $companies = $db->fetchAll($sql, $params);
        
        // Get job counts
        foreach ($companies as &$company) {
            $jobCount = $db->fetch(
                "SELECT COUNT(*) as count FROM jobs WHERE company_id = :id AND status = 'active'",
                ['id' => $company['id']]
            );
            $company['job_count'] = $jobCount['count'];
        }
        
        jsonResponse(['success' => true, 'companies' => $companies, 'pagination' => $pagination]);
        break;
        
    case 'get_company':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Company ID is required'], 400);
        }
        
        $company = $db->fetch("SELECT * FROM companies WHERE id = :id", ['id' => $id]);
        
        if (!$company) {
            jsonResponse(['success' => false, 'message' => 'Company not found'], 404);
        }
        
        jsonResponse(['success' => true, 'company' => $company]);
        break;
        
    case 'create_company':
        $required = ['name', 'industry', 'description'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                jsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
            }
        }
        
        $data = [
            'name' => sanitize($_POST['name']),
            'description' => sanitize($_POST['description']),
            'industry' => sanitize($_POST['industry']),
            'website' => sanitize($_POST['website'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'size' => sanitize($_POST['size'] ?? '1-10'),
            'headquarters' => sanitize($_POST['headquarters'] ?? ''),
            'is_active' => (int)($_POST['is_active'] ?? 1)
        ];
        
        if (!empty($_POST['founded_year'])) {
            $data['founded_year'] = (int)$_POST['founded_year'];
        }
        
        $companyId = $db->insert('companies', $data);
        logActivity($_SESSION['user_id'], 'create_company', 'company', $companyId);
        
        jsonResponse(['success' => true, 'message' => 'Company created successfully', 'company_id' => $companyId]);
        break;
        
    case 'update_company':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Company ID is required'], 400);
        }
        
        $data = [];
        $allowedFields = ['name', 'description', 'industry', 'website', 'email', 'phone', 
                          'size', 'headquarters', 'founded_year', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = sanitize($_POST[$field]);
            }
        }
        
        if (empty($data)) {
            jsonResponse(['success' => false, 'message' => 'No data to update'], 400);
        }
        
        $db->update('companies', $data, 'id = :id', ['id' => $id]);
        logActivity($_SESSION['user_id'], 'update_company', 'company', $id);
        
        jsonResponse(['success' => true, 'message' => 'Company updated successfully']);
        break;
        
    case 'delete_company':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Company ID is required'], 400);
        }
        
        $db->update('companies', ['is_active' => 0], 'id = :id', ['id' => $id]);
        logActivity($_SESSION['user_id'], 'deactivate_company', 'company', $id);
        
        jsonResponse(['success' => true, 'message' => 'Company deactivated successfully']);
        break;
        
    // Popular Interests (Most Used)
    case 'get_popular_interests':
        $interests = $db->fetchAll(
            "SELECT interests FROM users WHERE interests IS NOT NULL AND interests != '[]' AND interests != 'null'"
        );
        
        $interestCounts = [];
        foreach ($interests as $row) {
            $userInterests = json_decode($row['interests'], true);
            if (is_array($userInterests)) {
                foreach ($userInterests as $interest) {
                    $interest = trim($interest);
                    if ($interest) {
                        $interestCounts[$interest] = ($interestCounts[$interest] ?? 0) + 1;
                    }
                }
            }
        }
        
        arsort($interestCounts);
        $popularInterests = array_slice(array_keys($interestCounts), 0, 15);
        
        // Add some defaults if not enough
        $defaults = ['Technology', 'Healthcare', 'Finance', 'Education', 'Marketing', 
                     'Sales', 'Engineering', 'Design', 'Data Science', 'Consulting',
                     'E-commerce', 'Manufacturing', 'Real Estate', 'Media', 'Legal'];
        
        foreach ($defaults as $default) {
            if (!in_array($default, $popularInterests) && count($popularInterests) < 15) {
                $popularInterests[] = $default;
            }
        }
        
        jsonResponse(['success' => true, 'interests' => $popularInterests]);
        break;
        
    // Popular Skills (Most Used)
    case 'get_popular_skills':
        $skills = $db->fetchAll(
            "SELECT skills FROM users WHERE skills IS NOT NULL AND skills != '[]' AND skills != 'null'"
        );
        
        $skillCounts = [];
        foreach ($skills as $row) {
            $userSkills = json_decode($row['skills'], true);
            if (is_array($userSkills)) {
                foreach ($userSkills as $skill) {
                    $skill = trim($skill);
                    if ($skill) {
                        $skillCounts[$skill] = ($skillCounts[$skill] ?? 0) + 1;
                    }
                }
            }
        }
        
        arsort($skillCounts);
        $popularSkills = array_slice(array_keys($skillCounts), 0, 20);
        
        // Add some defaults if not enough
        $defaults = ['JavaScript', 'Python', 'Java', 'React', 'Node.js', 'SQL', 'AWS',
                     'HTML/CSS', 'TypeScript', 'Git', 'Docker', 'C++', 'PHP', 'Vue.js',
                     'MongoDB', 'Kubernetes', 'Go', 'Ruby', 'Swift', 'Machine Learning'];
        
        foreach ($defaults as $default) {
            if (!in_array($default, $popularSkills) && count($popularSkills) < 20) {
                $popularSkills[] = $default;
            }
        }
        
        jsonResponse(['success' => true, 'skills' => $popularSkills]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
