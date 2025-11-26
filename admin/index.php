<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getInstance();

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
     LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Admin Navbar -->
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
                    <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="applications.php">Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="jobs.php">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
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
        <h2 class="mb-4">Dashboard</h2>
        
        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <h4 class="mb-0"><?= $stats['total_users'] ?></h4>
                                <small class="text-muted">Users</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <div>
                                <h4 class="mb-0"><?= $stats['active_jobs'] ?></h4>
                                <small class="text-muted">Active Jobs</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div>
                                <h4 class="mb-0"><?= $stats['total_applications'] ?></h4>
                                <small class="text-muted">Applications</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div>
                                <h4 class="mb-0"><?= $stats['pending_applications'] ?></h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <h4 class="mb-0"><?= $stats['upcoming_appointments'] ?></h4>
                                <small class="text-muted">Appointments</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <h4 class="mb-0"><?= $stats['total_companies'] ?></h4>
                                <small class="text-muted">Companies</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Applications -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0">Recent Applications</h5>
                        <a href="applications.php" class="text-decoration-none">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Job</th>
                                        <th>Status</th>
                                        <th>Match</th>
                                        <th>Applied</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentApplications as $app): ?>
                                    <tr>
                                        <td>
                                            <a href="user.php?id=<?= $app['user_id'] ?>"><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></a>
                                            <br><small class="text-muted"><?= htmlspecialchars($app['email']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($app['job_title']) ?></td>
                                        <td><span class="badge <?= getStatusBadgeClass($app['status']) ?>"><?= ucfirst($app['status']) ?></span></td>
                                        <td><?= $app['match_score'] ? round($app['match_score']) . '%' : '-' ?></td>
                                        <td><?= timeAgo($app['created_at']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="reviewApplication(<?= $app['id'] ?>)">Review</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="users.php" class="btn btn-outline-primary"><i class="bi bi-people me-2"></i>Manage Users</a>
                            <a href="applications.php" class="btn btn-outline-primary"><i class="bi bi-file-text me-2"></i>Review Applications</a>
                            <a href="jobs.php" class="btn btn-outline-primary"><i class="bi bi-briefcase me-2"></i>Manage Jobs</a>
                            <a href="appointments.php" class="btn btn-outline-primary"><i class="bi bi-calendar me-2"></i>Schedule Appointments</a>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">System Info</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>App Version:</strong> <?= APP_VERSION ?></p>
                        <p class="mb-2"><strong>PHP Version:</strong> <?= phpversion() ?></p>
                        <p class="mb-0"><strong>Server Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
