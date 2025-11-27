<?php
/**
 * Admin User Detail Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    header('Location: users.php');
    exit;
}

$user = getUserById($userId);
if (!$user) {
    header('Location: users.php');
    exit;
}

$db = Database::getInstance();

// Get user's applications
$applications = $db->fetchAll(
    "SELECT a.*, j.title as job_title, c.name as company_name
     FROM applications a
     JOIN jobs j ON a.job_id = j.id
     LEFT JOIN companies c ON j.company_id = c.id
     WHERE a.user_id = :id
     ORDER BY a.created_at DESC",
    ['id' => $userId]
);

// Get user's appointments
$appointments = $db->fetchAll(
    "SELECT * FROM appointments WHERE user_id = :id ORDER BY scheduled_at DESC",
    ['id' => $userId]
);

// Get available jobs for offering
$availableJobs = $db->fetchAll(
    "SELECT j.id, j.title, c.name as company_name 
     FROM jobs j 
     LEFT JOIN companies c ON j.company_id = c.id 
     WHERE j.status = 'active' 
     AND j.id NOT IN (SELECT job_id FROM applications WHERE user_id = :id)
     ORDER BY j.created_at DESC LIMIT 20",
    ['id' => $userId]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User: <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> - Admin - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-briefcase-fill me-2"></i><?= APP_NAME ?> Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="applications.php">Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="jobs.php">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars(getCurrentUser()['first_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="logout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="users.php" class="text-decoration-none"><i class="bi bi-arrow-left me-2"></i>Back to Users</a>
        </div>
        
        <div class="row">
            <!-- User Profile -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <div class="profile-avatar mx-auto mb-3">
                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                        </div>
                        <h4 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                        <p class="text-muted mb-2"><?= htmlspecialchars($user['email']) ?></p>
                        <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></span>
                        
                        <hr>
                        
                        <div class="text-start">
                            <?php if ($user['phone']): ?>
                            <p class="mb-2"><i class="bi bi-phone me-2"></i><?= htmlspecialchars($user['phone']) ?></p>
                            <?php endif; ?>
                            <?php if ($user['city']): ?>
                            <p class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($user['city']) ?><?= $user['country'] ? ', ' . htmlspecialchars($user['country']) : '' ?></p>
                            <?php endif; ?>
                            <?php if ($user['cv_path']): ?>
                            <p class="mb-2"><i class="bi bi-file-earmark-text me-2"></i><a href="../<?= htmlspecialchars($user['cv_path']) ?>" target="_blank">View CV</a></p>
                            <?php endif; ?>
                            <p class="mb-0 text-muted small">Member since: <?= formatDate($user['created_at']) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Skills -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Skills</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($user['skills'])): ?>
                            <?php foreach ($user['skills'] as $skill): ?>
                            <span class="badge bg-primary me-1 mb-1"><?= htmlspecialchars($skill) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <p class="text-muted mb-0">No skills listed</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#offerJobModal">
                                <i class="bi bi-briefcase me-2"></i>Offer Job
                            </button>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#emailModal">
                                <i class="bi bi-envelope me-2"></i>Send Email
                            </button>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#appointmentModal">
                                <i class="bi bi-calendar me-2"></i>Schedule Appointment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Applications & Appointments -->
            <div class="col-lg-8">
                <!-- Applications -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Applications (<?= count($applications) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($applications)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No applications yet</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th>Match</th>
                                        <th>Applied</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['job_title']) ?></td>
                                        <td><?= htmlspecialchars($app['company_name']) ?></td>
                                        <td><span class="badge <?= getStatusBadgeClass($app['status']) ?>"><?= ucfirst($app['status']) ?></span></td>
                                        <td><?= $app['match_score'] ? round($app['match_score']) . '%' : '-' ?></td>
                                        <td><?= formatDate($app['created_at']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Appointments -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Appointments (<?= count($appointments) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($appointments)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No appointments scheduled</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Scheduled</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $apt): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($apt['title']) ?></td>
                                        <td><span class="badge bg-info"><?= ucfirst($apt['type']) ?></span></td>
                                        <td><?= formatDate($apt['scheduled_at'], 'M d, Y H:i') ?></td>
                                        <td><span class="badge bg-<?= $apt['status'] === 'completed' ? 'success' : ($apt['status'] === 'cancelled' ? 'danger' : 'warning') ?>"><?= ucfirst($apt['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Offer Job Modal -->
    <div class="modal fade" id="offerJobModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Offer Job to <?= htmlspecialchars($user['first_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="job_id" class="form-label">Select Job</label>
                        <select class="form-select" id="job_id" required>
                            <option value="">Choose a job...</option>
                            <?php foreach ($availableJobs as $job): ?>
                            <option value="<?= $job['id'] ?>"><?= htmlspecialchars($job['title'] . ' - ' . $job['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="offer_message" class="form-label">Message (Optional)</label>
                        <textarea class="form-control" id="offer_message" rows="3" placeholder="Add a personalized message..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitJobOffer()">Send Offer</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Email to <?= htmlspecialchars($user['first_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form onsubmit="sendEmail(event)">
                    <input type="hidden" name="recipient_id" value="<?= $userId ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" name="subject" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="body" class="form-label">Message *</label>
                            <textarea class="form-control" name="body" id="body" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form onsubmit="createAppointment(event)">
                    <input type="hidden" name="user_id" value="<?= $userId ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="apt_type" class="form-label">Type *</label>
                            <select class="form-select" name="type" id="apt_type" required>
                                <option value="interview">Interview</option>
                                <option value="onboarding">Onboarding</option>
                                <option value="follow-up">Follow-up</option>
                                <option value="assessment">Assessment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="scheduled_at" class="form-label">Date & Time *</label>
                            <input type="datetime-local" class="form-control" name="scheduled_at" id="scheduled_at" required>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" name="duration" id="duration" value="60" min="15">
                        </div>
                        <div class="mb-3">
                            <label for="meeting_link" class="form-label">Meeting Link</label>
                            <input type="url" class="form-control" name="meeting_link" id="meeting_link">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        async function submitJobOffer() {
            const jobId = document.getElementById('job_id').value;
            const message = document.getElementById('offer_message').value;
            
            if (!jobId) {
                showToast('Please select a job', 'danger');
                return;
            }
            
            await offerJob(<?= $userId ?>, jobId, message);
            bootstrap.Modal.getInstance(document.getElementById('offerJobModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
    </script>
</body>
</html>
