<?php
/**
 * Admin Appointments Management
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
$users = $db->fetchAll("SELECT id, first_name, last_name, email FROM users WHERE role = 'user' AND is_active = 1 ORDER BY first_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Admin - <?= APP_NAME ?></title>
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
                    <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="applications.php">Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="jobs.php">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link active" href="appointments.php">Appointments</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/jobs.php" target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>View Site</a>
                    </li>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Appointments & Onboarding</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointmentModal">
                <i class="bi bi-plus me-2"></i>Schedule Appointment
            </button>
        </div>
        
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" id="type">
                            <option value="">All Types</option>
                            <option value="interview">Interview</option>
                            <option value="onboarding">Onboarding</option>
                            <option value="follow-up">Follow-up</option>
                            <option value="assessment">Assessment</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="status">
                            <option value="">All Statuses</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100" onclick="loadAppointments()">Filter</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Appointments Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Scheduled</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="appointments-container">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div id="pagination" class="mt-4"></div>
    </div>
    
    <!-- Create Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form onsubmit="createAppointment(event)">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User *</label>
                            <select class="form-select" name="user_id" id="user_id" required>
                                <option value="">Select User</option>
                                <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' (' . $u['email'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
                            <input type="text" class="form-control" name="title" id="title" required placeholder="e.g., Technical Interview">
                        </div>
                        <div class="mb-3">
                            <label for="scheduled_at" class="form-label">Date & Time *</label>
                            <input type="datetime-local" class="form-control" name="scheduled_at" id="scheduled_at" required>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" name="duration" id="duration" value="60" min="15" max="480">
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="location" placeholder="e.g., Office / Zoom">
                        </div>
                        <div class="mb-3">
                            <label for="meeting_link" class="form-label">Meeting Link</label>
                            <input type="url" class="form-control" name="meeting_link" id="meeting_link" placeholder="https://zoom.us/j/...">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="2"></textarea>
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
        document.addEventListener('DOMContentLoaded', loadAppointments);
    </script>
</body>
</html>
