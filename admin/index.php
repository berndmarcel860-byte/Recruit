<?php
/**
 * Admin Dashboard
 * Professional admin interface with statistics and quick actions
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireModerator();

$db = Database::getInstance();
$currentUser = getCurrentUser();

$stats = [
    'total_users' => $db->count('users', "role = 'user'"),
    'active_users' => $db->count('users', "role = 'user' AND account_status = 'active'"),
    'pending_onboarding' => $db->count('users', "role = 'user' AND account_status IN ('pending_onboarding', 'onboarding_scheduled')"),
    'active_jobs' => $db->count('jobs', "status = 'active'"),
    'total_applications' => $db->count('applications'),
    'pending_applications' => $db->count('applications', "status = 'pending'"),
    'interviews_scheduled' => $db->count('applications', "status = 'interview_scheduled'"),
    'upcoming_appointments' => $db->count('appointments', "scheduled_at >= NOW() AND status IN ('scheduled', 'confirmed')"),
    'total_companies' => $db->count('companies', "is_active = 1")
];

// Pending onboarding users
$pendingOnboarding = $db->fetchAll(
    "SELECT u.*, 
            (SELECT scheduled_at FROM appointments WHERE user_id = u.id AND type = 'onboarding' AND status = 'scheduled' ORDER BY scheduled_at ASC LIMIT 1) as onboarding_date
     FROM users u
     WHERE u.role = 'user' AND u.account_status IN ('pending_onboarding', 'onboarding_scheduled')
     ORDER BY u.created_at DESC LIMIT 5"
);

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

// Today's appointments
$todayAppointments = $db->fetchAll(
    "SELECT ap.*, u.first_name, u.last_name, u.email, u.account_status
     FROM appointments ap
     JOIN users u ON ap.user_id = u.id
     WHERE DATE(ap.scheduled_at) = CURDATE()
     AND ap.status IN ('scheduled', 'confirmed')
     ORDER BY ap.scheduled_at ASC
     LIMIT 5"
);

// Recent users
$recentUsers = $db->fetchAll(
    "SELECT id, first_name, last_name, email, city, account_status, created_at 
     FROM users WHERE role = 'user' 
     ORDER BY created_at DESC LIMIT 5"
);

// Get account status badge class
function getAccountStatusBadgeClass($status) {
    $classes = [
        'pending_onboarding' => 'bg-warning text-dark',
        'onboarding_scheduled' => 'bg-info',
        'active' => 'bg-success',
        'suspended' => 'bg-danger',
        'rejected' => 'bg-secondary'
    ];
    return $classes[$status] ?? 'bg-secondary';
}
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
        .workflow-alert {
            border-left: 4px solid;
            border-radius: 0 0.5rem 0.5rem 0;
        }
        .workflow-alert.onboarding { border-left-color: #ffc107; }
        .workflow-alert.applications { border-left-color: #0d6efd; }
        .workflow-alert.interviews { border-left-color: #198754; }
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
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($currentUser['first_name']) ?>
                            <span class="badge bg-<?= $currentUser['role'] === 'admin' ? 'danger' : 'info' ?> ms-1"><?= ucfirst($currentUser['role']) ?></span>
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
                <h2 class="mb-1">Welcome back, <?= htmlspecialchars($currentUser['first_name']) ?>!</h2>
                <p class="text-muted mb-0">Here's what's happening with your recruitment platform today.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="jobs.php" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Create Job</a>
                <a href="companies.php" class="btn btn-outline-primary"><i class="bi bi-building me-2"></i>Add Company</a>
            </div>
        </div>
        
        <!-- Workflow Alerts -->
        <?php if ($stats['pending_onboarding'] > 0 || $stats['pending_applications'] > 0 || $stats['interviews_scheduled'] > 0): ?>
        <div class="row g-3 mb-4">
            <?php if ($stats['pending_onboarding'] > 0): ?>
            <div class="col-md-4">
                <div class="alert workflow-alert onboarding bg-warning bg-opacity-10 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-badge text-warning fs-3 me-3"></i>
                        <div class="flex-grow-1">
                            <strong><?= $stats['pending_onboarding'] ?> users</strong> pending onboarding
                            <br><small class="text-muted">Need to complete onboarding calls</small>
                        </div>
                        <a href="users.php?status=pending" class="btn btn-sm btn-warning">View</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($stats['pending_applications'] > 0): ?>
            <div class="col-md-4">
                <div class="alert workflow-alert applications bg-primary bg-opacity-10 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-text text-primary fs-3 me-3"></i>
                        <div class="flex-grow-1">
                            <strong><?= $stats['pending_applications'] ?> applications</strong> pending review
                            <br><small class="text-muted">Awaiting moderator decision</small>
                        </div>
                        <a href="applications.php?status=pending" class="btn btn-sm btn-primary">Review</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($stats['interviews_scheduled'] > 0): ?>
            <div class="col-md-4">
                <div class="alert workflow-alert interviews bg-success bg-opacity-10 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-camera-video text-success fs-3 me-3"></i>
                        <div class="flex-grow-1">
                            <strong><?= $stats['interviews_scheduled'] ?> interviews</strong> scheduled
                            <br><small class="text-muted">Upcoming candidate interviews</small>
                        </div>
                        <a href="appointments.php?type=interview" class="btn btn-sm btn-success">Manage</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?= number_format($stats['total_users']) ?></h3>
                                <small class="text-muted">Total Users</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?= number_format($stats['active_users']) ?></h3>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
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
            <div class="col-6 col-lg-3 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
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
            <div class="col-6 col-lg-3 col-xl">
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
        </div>
        
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Today's Appointments -->
                <?php if (!empty($todayAppointments)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Today's Appointments</h5>
                        <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Time</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayAppointments as $apt): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <strong><?= date('g:i A', strtotime($apt['scheduled_at'])) ?></strong>
                                        </td>
                                        <td>
                                            <a href="user.php?id=<?= $apt['user_id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $apt['type'] === 'onboarding' ? 'warning' : ($apt['type'] === 'interview' ? 'primary' : 'info') ?>">
                                                <?= ucfirst($apt['type']) ?>
                                            </span>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= ucfirst($apt['status']) ?></span></td>
                                        <td class="text-end pe-4">
                                            <?php if ($apt['type'] === 'onboarding'): ?>
                                            <button class="btn btn-sm btn-success" onclick="completeOnboarding(<?= $apt['user_id'] ?>, <?= $apt['id'] ?>)">
                                                <i class="bi bi-check-lg me-1"></i>Complete
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewAppointment(<?= $apt['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Pending Onboarding -->
                <?php if (!empty($pendingOnboarding)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="bi bi-person-badge me-2 text-warning"></i>Pending Onboarding</h5>
                        <a href="users.php?status=pending" class="btn btn-sm btn-outline-warning">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">User</th>
                                        <th>Status</th>
                                        <th>Scheduled</th>
                                        <th>Registered</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingOnboarding as $user): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-warning text-dark me-2">
                                                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <a href="user.php?id=<?= $user['id'] ?>" class="text-decoration-none fw-medium">
                                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                    </a>
                                                    <br><small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= getAccountStatusBadgeClass($user['account_status']) ?>">
                                                <?= str_replace('_', ' ', ucfirst($user['account_status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['onboarding_date']): ?>
                                            <small><?= formatDate($user['onboarding_date'], 'M d, g:i A') ?></small>
                                            <?php else: ?>
                                            <span class="text-muted">Not scheduled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?= timeAgo($user['created_at']) ?></small></td>
                                        <td class="text-end pe-4">
                                            <a href="user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Applications -->
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
                                        <td><span class="badge <?= getStatusBadgeClass($app['status']) ?>"><?= ucfirst(str_replace('_', ' ', $app['status'])) ?></span></td>
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
                                            <?php if ($app['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success me-1" onclick="acceptApplication(<?= $app['id'] ?>)" title="Accept & Invite for Interview">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectApplication(<?= $app['id'] ?>)" title="Reject">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-outline-primary" onclick="reviewApplication(<?= $app['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <?php endif; ?>
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
                                <span class="badge bg-primary"><?= $stats['total_users'] ?></span>
                            </a>
                            <a href="applications.php?status=pending" class="btn btn-outline-warning d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-file-text me-2"></i>Review Applications</span>
                                <span class="badge bg-warning text-dark"><?= $stats['pending_applications'] ?></span>
                            </a>
                            <a href="companies.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-building me-2"></i>Manage Companies</span>
                                <span class="badge bg-primary"><?= $stats['total_companies'] ?></span>
                            </a>
                            <a href="jobs.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-briefcase me-2"></i>Manage Jobs</span>
                                <span class="badge bg-primary"><?= $stats['active_jobs'] ?></span>
                            </a>
                            <a href="appointments.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-calendar me-2"></i>Appointments</span>
                                <span class="badge bg-primary"><?= $stats['upcoming_appointments'] ?></span>
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
                                <div class="avatar-circle bg-<?= $user['account_status'] === 'active' ? 'success' : 'warning' ?> text-white me-3">
                                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="user.php?id=<?= $user['id'] ?>" class="text-decoration-none fw-medium">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </a>
                                    <br>
                                    <span class="badge <?= getAccountStatusBadgeClass($user['account_status']) ?> badge-sm">
                                        <?= str_replace('_', ' ', ucfirst($user['account_status'])) ?>
                                    </span>
                                </div>
                                <small class="text-muted"><?= timeAgo($user['created_at']) ?></small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Workflow Legend -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Workflow Process</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-warning text-dark me-2">1</span>
                            <small>User registers → Pending Onboarding</small>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-info me-2">2</span>
                            <small>User schedules onboarding call</small>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-success me-2">3</span>
                            <small>Admin completes onboarding → Active</small>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-primary me-2">4</span>
                            <small>User applies / Admin offers jobs</small>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-secondary me-2">5</span>
                            <small>Moderator reviews → Accept/Reject</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">6</span>
                            <small>Interview scheduled → Hired</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Complete Onboarding Modal -->
    <div class="modal fade" id="onboardingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Onboarding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="onboarding-user-id">
                    <input type="hidden" id="onboarding-appointment-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Outcome</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="onboarding-outcome" id="outcome-passed" value="passed" checked>
                            <label class="form-check-label text-success" for="outcome-passed">
                                <i class="bi bi-check-circle me-1"></i>Passed - Activate Account
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="onboarding-outcome" id="outcome-failed" value="failed">
                            <label class="form-check-label text-danger" for="outcome-failed">
                                <i class="bi bi-x-circle me-1"></i>Failed - Reject Application
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="onboarding-feedback" class="form-label">Feedback / Notes</label>
                        <textarea class="form-control" id="onboarding-feedback" rows="3" placeholder="Add notes about the onboarding call..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitOnboarding()">Complete Onboarding</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        function completeOnboarding(userId, appointmentId) {
            document.getElementById('onboarding-user-id').value = userId;
            document.getElementById('onboarding-appointment-id').value = appointmentId || '';
            new bootstrap.Modal(document.getElementById('onboardingModal')).show();
        }
        
        async function submitOnboarding() {
            const userId = document.getElementById('onboarding-user-id').value;
            const appointmentId = document.getElementById('onboarding-appointment-id').value;
            const outcome = document.querySelector('input[name="onboarding-outcome"]:checked').value;
            const feedback = document.getElementById('onboarding-feedback').value;
            
            const formData = new FormData();
            formData.append('action', 'complete_onboarding');
            formData.append('user_id', userId);
            formData.append('appointment_id', appointmentId);
            formData.append('outcome', outcome);
            formData.append('feedback', feedback);
            
            const result = await apiRequest(API_BASE + '/api/admin.php', 'POST', formData);
            
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('onboardingModal')).hide();
                setTimeout(() => location.reload(), 1500);
            }
        }
        
        async function acceptApplication(applicationId) {
            if (!confirm('Accept this application and send interview invitation to the candidate?')) return;
            
            const formData = new FormData();
            formData.append('action', 'accept_application');
            formData.append('application_id', applicationId);
            
            const result = await apiRequest(API_BASE + '/api/admin.php', 'POST', formData);
            if (result.success) {
                setTimeout(() => location.reload(), 1500);
            }
        }
        
        async function rejectApplication(applicationId) {
            if (!confirm('Reject this application? The candidate will be notified.')) return;
            
            const formData = new FormData();
            formData.append('action', 'reject_application');
            formData.append('application_id', applicationId);
            
            const result = await apiRequest(API_BASE + '/api/admin.php', 'POST', formData);
            if (result.success) {
                setTimeout(() => location.reload(), 1500);
            }
        }
    </script>
</body>
</html>
