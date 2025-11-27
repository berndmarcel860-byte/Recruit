<?php
/**
 * Onboarding Page
 * Users must complete onboarding before accessing job features
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();

$user = getCurrentUser();
$db = Database::getInstance();

// If user is already active, redirect to dashboard
if ($user['account_status'] === 'active') {
    header('Location: dashboard.php');
    exit;
}

// Check if user already has an onboarding appointment
$existingAppointment = $db->fetch(
    "SELECT * FROM appointments WHERE user_id = :id AND type = 'onboarding' AND status IN ('scheduled', 'confirmed') ORDER BY scheduled_at ASC LIMIT 1",
    ['id' => $user['id']]
);

// Get available time slots for next 14 days
$availableSlots = $db->fetchAll(
    "SELECT s.*, u.first_name as admin_name, u.last_name as admin_lastname 
     FROM available_slots s 
     JOIN users u ON s.admin_id = u.id
     WHERE s.is_booked = 0 
     AND s.slot_type IN ('onboarding', 'both')
     AND s.slot_date >= CURDATE()
     AND s.slot_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
     ORDER BY s.slot_date ASC, s.start_time ASC"
);

// Group slots by date
$slotsByDate = [];
foreach ($availableSlots as $slot) {
    $date = $slot['slot_date'];
    if (!isset($slotsByDate[$date])) {
        $slotsByDate[$date] = [];
    }
    $slotsByDate[$date][] = $slot;
}

// Get workflow steps
$steps = [
    ['icon' => 'person-plus', 'title' => 'Register', 'desc' => 'Create your account', 'completed' => true],
    ['icon' => 'calendar-check', 'title' => 'Schedule Onboarding', 'desc' => 'Book your intro call', 'completed' => $user['account_status'] === 'onboarding_scheduled'],
    ['icon' => 'camera-video', 'title' => 'Complete Onboarding', 'desc' => 'Meet with our team', 'completed' => false],
    ['icon' => 'check-circle', 'title' => 'Start Applying', 'desc' => 'Browse & apply for jobs', 'completed' => false]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Onboarding - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .workflow-step {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        .workflow-step.completed {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .workflow-step.current {
            background: linear-gradient(135deg, #cce5ff 0%, #b8daff 100%);
            border: 2px solid #0d6efd;
        }
        .workflow-step.pending {
            background: #f8f9fa;
            opacity: 0.7;
        }
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-right: 1rem;
        }
        .step-icon.completed {
            background: #198754;
            color: white;
        }
        .step-icon.current {
            background: #0d6efd;
            color: white;
        }
        .step-icon.pending {
            background: #dee2e6;
            color: #6c757d;
        }
        .time-slot {
            cursor: pointer;
            transition: all 0.2s;
        }
        .time-slot:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .time-slot.selected {
            background: #0d6efd !important;
            color: white;
            border-color: #0d6efd;
        }
        .date-section {
            margin-bottom: 1.5rem;
        }
        .appointment-confirmed {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Welcome Header -->
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold text-primary">Welcome to <?= APP_NAME ?>! 🎉</h1>
                    <p class="lead text-muted">Complete your onboarding to start your job search journey</p>
                </div>
                
                <div class="row g-4">
                    <!-- Workflow Progress -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent py-3">
                                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Your Journey</h5>
                            </div>
                            <div class="card-body">
                                <?php 
                                $currentStep = $user['account_status'] === 'pending_onboarding' ? 1 : 2;
                                foreach ($steps as $index => $step): 
                                    $stepClass = $step['completed'] ? 'completed' : ($index == $currentStep ? 'current' : 'pending');
                                    $iconClass = $step['completed'] ? 'completed' : ($index == $currentStep ? 'current' : 'pending');
                                ?>
                                <div class="workflow-step <?= $stepClass ?>">
                                    <div class="step-icon <?= $iconClass ?>">
                                        <?php if ($step['completed']): ?>
                                        <i class="bi bi-check-lg"></i>
                                        <?php else: ?>
                                        <i class="bi bi-<?= $step['icon'] ?>"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= $step['title'] ?></h6>
                                        <small class="text-muted"><?= $step['desc'] ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        <?php if ($existingAppointment): ?>
                        <!-- Appointment Confirmed -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="appointment-confirmed">
                                    <i class="bi bi-calendar-check text-success" style="font-size: 4rem;"></i>
                                    <h3 class="mt-3 mb-2">Your Onboarding is Scheduled!</h3>
                                    <p class="text-muted mb-4">We're looking forward to meeting you</p>
                                    
                                    <div class="card bg-white border-0 shadow-sm d-inline-block">
                                        <div class="card-body p-4">
                                            <div class="row text-start g-3">
                                                <div class="col-12">
                                                    <strong><i class="bi bi-calendar3 me-2 text-primary"></i>Date:</strong>
                                                    <span><?= formatDate($existingAppointment['scheduled_at'], 'l, F j, Y') ?></span>
                                                </div>
                                                <div class="col-12">
                                                    <strong><i class="bi bi-clock me-2 text-primary"></i>Time:</strong>
                                                    <span><?= formatDate($existingAppointment['scheduled_at'], 'g:i A') ?></span>
                                                </div>
                                                <div class="col-12">
                                                    <strong><i class="bi bi-hourglass me-2 text-primary"></i>Duration:</strong>
                                                    <span><?= $existingAppointment['duration'] ?> minutes</span>
                                                </div>
                                                <?php if ($existingAppointment['meeting_link']): ?>
                                                <div class="col-12">
                                                    <strong><i class="bi bi-camera-video me-2 text-primary"></i>Meeting:</strong>
                                                    <a href="<?= htmlspecialchars($existingAppointment['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-primary ms-2">
                                                        Join Meeting
                                                    </a>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <h5>What to Expect:</h5>
                                        <ul class="list-unstyled text-start d-inline-block">
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Brief introduction call (<?= $existingAppointment['duration'] ?> mins)</li>
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Overview of our platform and job matching</li>
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Discussion of your career goals</li>
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Q&A session</li>
                                        </ul>
                                    </div>
                                    
                                    <p class="text-muted small mt-3 mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Need to reschedule? <a href="#" onclick="cancelAppointment(<?= $existingAppointment['id'] ?>)">Click here</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php else: ?>
                        <!-- Schedule Appointment -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent py-3">
                                <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Schedule Your Onboarding Call</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="alert alert-info d-flex align-items-start mb-4">
                                    <i class="bi bi-info-circle-fill me-3 mt-1 fs-4"></i>
                                    <div>
                                        <strong>Why do we require onboarding?</strong>
                                        <p class="mb-0 small mt-1">Our brief onboarding call helps us understand your career goals and ensures we can match you with the best opportunities. It typically takes only 15-30 minutes.</p>
                                    </div>
                                </div>
                                
                                <?php if (empty($slotsByDate)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                                    <h5 class="mt-3">No Available Slots</h5>
                                    <p class="text-muted">Please check back later or contact us to schedule directly.</p>
                                    <a href="mailto:<?= SUPPORT_EMAIL ?? 'support@recruit.com' ?>" class="btn btn-primary">Contact Support</a>
                                </div>
                                <?php else: ?>
                                
                                <p class="mb-4">Select a convenient time for your onboarding call:</p>
                                
                                <div id="slots-container">
                                    <?php foreach ($slotsByDate as $date => $slots): ?>
                                    <div class="date-section">
                                        <h6 class="text-primary mb-3">
                                            <i class="bi bi-calendar3 me-2"></i>
                                            <?= formatDate($date, 'l, F j, Y') ?>
                                        </h6>
                                        <div class="row g-2">
                                            <?php foreach ($slots as $slot): ?>
                                            <div class="col-md-4 col-6">
                                                <div class="card time-slot border" data-slot-id="<?= $slot['id'] ?>" onclick="selectSlot(this, <?= $slot['id'] ?>)">
                                                    <div class="card-body text-center py-3">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?= date('g:i A', strtotime($slot['start_time'])) ?>
                                                        <small class="d-block text-muted">with <?= htmlspecialchars($slot['admin_name']) ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <input type="hidden" id="selected-slot" value="">
                                
                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="bookOnboarding()" id="book-btn" disabled>
                                        <i class="bi bi-calendar-check me-2"></i>Book Onboarding Call
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Help Section -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5><i class="bi bi-question-circle me-2"></i>Need Help?</h5>
                                        <p class="text-muted mb-0">Have questions about the onboarding process? Our team is here to help.</p>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <a href="mailto:<?= SUPPORT_EMAIL ?? 'support@recruit.com' ?>" class="btn btn-outline-primary">
                                            <i class="bi bi-envelope me-2"></i>Contact Support
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        let selectedSlotId = null;
        
        function selectSlot(element, slotId) {
            // Remove selection from all slots
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            
            // Select this slot
            element.classList.add('selected');
            selectedSlotId = slotId;
            document.getElementById('selected-slot').value = slotId;
            document.getElementById('book-btn').disabled = false;
        }
        
        async function bookOnboarding() {
            if (!selectedSlotId) {
                showToast('Please select a time slot', 'warning');
                return;
            }
            
            const btn = document.getElementById('book-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking...';
            
            const formData = new FormData();
            formData.append('action', 'book_onboarding');
            formData.append('slot_id', selectedSlotId);
            
            const result = await apiRequest(API_BASE + '/api/appointments.php', 'POST', formData);
            
            if (result.success) {
                showToast('Onboarding scheduled successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-calendar-check me-2"></i>Book Onboarding Call';
            }
        }
        
        async function cancelAppointment(appointmentId) {
            if (!confirm('Are you sure you want to cancel this appointment?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'cancel_appointment');
            formData.append('appointment_id', appointmentId);
            
            const result = await apiRequest(API_BASE + '/api/appointments.php', 'POST', formData);
            
            if (result.success) {
                showToast('Appointment cancelled', 'success');
                setTimeout(() => location.reload(), 1500);
            }
        }
    </script>
</body>
</html>
