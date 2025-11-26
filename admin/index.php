<?php
/**
 * Admin Dashboard
 * Professional admin interface with statistics and quick actions
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
    "SELECT a.*, u.first_name, u.last_name, u.email, j.title as job_title, c.name as company_name
     FROM applications a
     JOIN users u ON a.user_id = u.id
     JOIN jobs j ON a.job_id = j.id
     LEFT JOIN companies c ON j.company_id = c.id
     ORDER BY a.created_at DESC
     LIMIT 10"
);

// Recent users
$recentUsers = $db->fetchAll(
    "SELECT id, first_name, last_name, email, city, created_at 
     FROM users WHERE role = 'user' 
     ORDER BY created_at DESC LIMIT 5"
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
    <style>
        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-briefcase-fill me-2"></i><?= APP_NAME ?> Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people me-1"></i>Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="applications.php"><i class="bi bi-file-text me-1"></i>Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="companies.php"><i class="bi bi-building me-1"></i>Companies</a></li>
                    <li class="nav-item"><a class="nav-link" href="jobs.php"><i class="bi bi-briefcase me-1"></i>Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php"><i class="bi bi-calendar me-1"></i>Appointments</a></li>
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
        <!-- Welcome Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Welcome back, <?= htmlspecialchars(getCurrentUser()['first_name']) ?>!</h2>
                <p class="text-muted mb-0">Here's what's happening with your recruitment platform today.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="jobs.php" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Create Job</a>
                <a href="companies.php" class="btn btn-outline-primary"><i class="bi bi-building me-2"></i>Add Company</a>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?= number_format($stats['total_users']) ?></h3>
                                <small class="text-muted">Users</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?= number_format($stats['active_jobs']) ?></h3>
                                <small class="text-muted">Active Jobs</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?= number_format($stats['total_applications']) ?></h3>
                                <small class="text-muted">Applications</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?= number_format($stats['pending_applications']) ?></h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?= number_format($stats['upcoming_appointments']) ?></h3>
                                <small class="text-muted">Appointments</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?= number_format($stats['total_companies']) ?></h3>
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
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Recent Applications</h5>
                        <a href="applications.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Applicant</th>
                                        <th>Job</th>
                                        <th>Status</th>
                                        <th>Match</th>
                                        <th>Applied</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentApplications)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No applications yet</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($recentApplications as $app): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2">
                                                    <?= strtoupper(substr($app['first_name'], 0, 1) . substr($app['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <a href="user.php?id=<?= $app['user_id'] ?>" class="text-decoration-none fw-medium">
                                                        <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
                                                    </a>
                                                    <br><small class="text-muted"><?= htmlspecialchars($app['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium"><?= htmlspecialchars($app['job_title']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($app['company_name'] ?? '') ?></small>
                                        </td>
                                        <td><span class="badge <?= getStatusBadgeClass($app['status']) ?>"><?= ucfirst($app['status']) ?></span></td>
                                        <td>
                                            <?php if ($app['match_score']): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 50px;">
                                                    <div class="progress-bar <?= $app['match_score'] >= 70 ? 'bg-success' : ($app['match_score'] >= 40 ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= $app['match_score'] ?>%"></div>
                                                </div>
                                                <small><?= round($app['match_score']) ?>%</small>
                                            </div>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?= timeAgo($app['created_at']) ?></small></td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary" onclick="reviewApplication(<?= $app['id'] ?>)">
                                                <i class="bi bi-check2-square"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="users.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-people me-2"></i>Manage Users</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="applications.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-file-text me-2"></i>Review Applications</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="companies.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-building me-2"></i>Manage Companies</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="jobs.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-briefcase me-2"></i>Manage Jobs</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="appointments.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-calendar me-2"></i>Schedule Appointments</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>New Users</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentUsers as $user): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="avatar-circle bg-success text-white me-3">
                                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="user.php?id=<?= $user['id'] ?>" class="text-decoration-none fw-medium">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </a>
                                    <br><small class="text-muted"><?= htmlspecialchars($user['city'] ?? 'Location not set') ?></small>
                                </div>
                                <small class="text-muted"><?= timeAgo($user['created_at']) ?></small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- System Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>System Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">App Version</span>
                            <span class="fw-medium"><?= APP_VERSION ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">PHP Version</span>
                            <span class="fw-medium"><?= phpversion() ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Server Time</span>
                            <span class="fw-medium"><?= date('Y-m-d H:i') ?></span>
                        </div>
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
