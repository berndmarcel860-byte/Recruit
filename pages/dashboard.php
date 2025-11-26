<?php
/**
 * User Dashboard
 * Professional user dashboard with job recommendations and application tracking
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();

$user = getCurrentUser();
$db = Database::getInstance();

// Get user stats
$stats = [
    'applications' => $db->count('applications', 'user_id = :id', ['id' => $user['id']]),
    'interviews' => $db->count('appointments', "user_id = :id AND type = 'interview' AND status IN ('scheduled', 'confirmed')", ['id' => $user['id']]),
    'offers' => $db->count('applications', "user_id = :id AND status = 'offered'", ['id' => $user['id']]),
    'profile_complete' => calculateProfileCompletion($user)
];

// Recent applications
$recentApplications = $db->fetchAll(
    "SELECT a.*, j.title as job_title, c.name as company_name, j.location
     FROM applications a
     JOIN jobs j ON a.job_id = j.id
     LEFT JOIN companies c ON j.company_id = c.id
     WHERE a.user_id = :id
     ORDER BY a.created_at DESC LIMIT 5",
    ['id' => $user['id']]
);

// Upcoming appointments
$upcomingAppointments = $db->fetchAll(
    "SELECT * FROM appointments WHERE user_id = :id AND scheduled_at >= NOW() AND status IN ('scheduled', 'confirmed') ORDER BY scheduled_at ASC LIMIT 3",
    ['id' => $user['id']]
);

// Skills
$userSkills = json_decode($user['skills'] ?? '[]', true);

function calculateProfileCompletion($user) {
    $fields = ['first_name', 'last_name', 'email', 'phone', 'city', 'country', 'bio', 'skills', 'interests'];
    $filled = 0;
    foreach ($fields as $field) {
        if (!empty($user[$field])) {
            $filled++;
        }
    }
    return round(($filled / count($fields)) * 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <!-- Welcome Banner -->
        <div class="card border-0 shadow-sm bg-gradient mb-4" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
            <div class="card-body p-4 text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">Welcome back, <?= htmlspecialchars($user['first_name']) ?>! 👋</h3>
                        <p class="mb-3 opacity-75">Your job search journey continues. Let's find your dream career today.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="recommendations.php" class="btn btn-light">
                                <i class="bi bi-robot me-2"></i>Get AI Recommendations
                            </a>
                            <a href="jobs.php" class="btn btn-outline-light">
                                <i class="bi bi-search me-2"></i>Browse Jobs
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="profile-avatar mx-auto mx-md-0 ms-md-auto" style="width: 80px; height: 80px;">
                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Completion Alert -->
        <?php if ($stats['profile_complete'] < 80): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div class="flex-grow-1">
                <strong>Complete your profile</strong> to get better job recommendations. Your profile is <?= $stats['profile_complete'] ?>% complete.
            </div>
            <a href="profile.php" class="btn btn-warning btn-sm">Complete Profile</a>
        </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['applications'] ?></h3>
                            <p class="text-muted mb-0 small">Applications</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['interviews'] ?></h3>
                            <p class="text-muted mb-0 small">Interviews</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['offers'] ?></h3>
                            <p class="text-muted mb-0 small">Job Offers</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['profile_complete'] ?>%</h3>
                            <p class="text-muted mb-0 small">Profile Complete</p>
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
                        <?php if (empty($recentApplications)): ?>
                        <div class="empty-state py-5">
                            <div class="empty-state-icon"><i class="bi bi-file-earmark-text"></i></div>
                            <h6>No applications yet</h6>
                            <p class="text-muted mb-3">Start applying for jobs to track them here</p>
                            <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Job</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th>Match</th>
                                        <th>Applied</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentApplications as $app): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <a href="job.php?id=<?= $app['job_id'] ?>" class="text-decoration-none fw-medium"><?= htmlspecialchars($app['job_title']) ?></a>
                                            <br><small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($app['location'] ?? 'Remote') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($app['company_name'] ?? 'N/A') ?></td>
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
                                        <td><small class="text-muted"><?= timeAgo($app['created_at']) ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Skills Section -->
                <?php if (!empty($userSkills)): ?>
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="bi bi-stars me-2"></i>Your Skills</h5>
                        <a href="profile.php" class="btn btn-sm btn-outline-primary">Edit Skills</a>
                    </div>
                    <div class="card-body">
                        <?php foreach ($userSkills as $skill): ?>
                        <span class="badge bg-primary me-2 mb-2"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Upcoming Appointments -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcomingAppointments)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No upcoming appointments</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($upcomingAppointments as $apt): ?>
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <div class="me-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded p-2 text-center" style="min-width: 50px;">
                                    <small class="d-block"><?= date('M', strtotime($apt['scheduled_at'])) ?></small>
                                    <strong><?= date('d', strtotime($apt['scheduled_at'])) ?></strong>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($apt['title']) ?></h6>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($apt['scheduled_at'])) ?> • <?= $apt['duration'] ?> min
                                </small>
                                <?php if ($apt['meeting_link']): ?>
                                <br><a href="<?= htmlspecialchars($apt['meeting_link']) ?>" target="_blank" class="small"><i class="bi bi-camera-video me-1"></i>Join Meeting</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="jobs.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-search me-2"></i>Search Jobs</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="recommendations.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-robot me-2"></i>AI Recommendations</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="companies.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-building me-2"></i>Browse Companies</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="profile.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-person me-2"></i>Update Profile</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
