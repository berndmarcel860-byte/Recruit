<?php
/**
 * User Dashboard
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
    'offers' => $db->count('applications', "user_id = :id AND status = 'offered'", ['id' => $user['id']])
];

// Recent applications
$recentApplications = $db->fetchAll(
    "SELECT a.*, j.title as job_title, c.name as company_name
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
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <!-- Welcome Banner -->
        <div class="card border-0 shadow-sm bg-primary text-white mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Welcome back, <?= htmlspecialchars($user['first_name']) ?>!</h4>
                        <p class="mb-0 opacity-75">Here's an overview of your job search journey</p>
                    </div>
                    <a href="recommendations.php" class="btn btn-light">
                        <i class="bi bi-robot me-2"></i>Get AI Recommendations
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['applications'] ?></h3>
                            <p class="text-muted mb-0">Applications</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['interviews'] ?></h3>
                            <p class="text-muted mb-0">Upcoming Interviews</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['offers'] ?></h3>
                            <p class="text-muted mb-0">Job Offers</p>
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
                        <?php if (empty($recentApplications)): ?>
                        <div class="empty-state py-5">
                            <div class="empty-state-icon"><i class="bi bi-file-earmark-text"></i></div>
                            <h6>No applications yet</h6>
                            <p class="text-muted">Start applying for jobs to track them here</p>
                            <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th>Applied</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentApplications as $app): ?>
                                    <tr>
                                        <td><a href="job.php?id=<?= $app['job_id'] ?>"><?= htmlspecialchars($app['job_title']) ?></a></td>
                                        <td><?= htmlspecialchars($app['company_name']) ?></td>
                                        <td><span class="badge <?= getStatusBadgeClass($app['status']) ?>"><?= ucfirst($app['status']) ?></span></td>
                                        <td><?= timeAgo($app['created_at']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Appointments -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Upcoming Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcomingAppointments)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No upcoming appointments</p>
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
                                <small class="text-muted"><?= date('H:i', strtotime($apt['scheduled_at'])) ?> • <?= $apt['duration'] ?> min</small>
                                <?php if ($apt['meeting_link']): ?>
                                <br><a href="<?= htmlspecialchars($apt['meeting_link']) ?>" target="_blank" class="small">Join Meeting</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="jobs.php" class="btn btn-outline-primary"><i class="bi bi-search me-2"></i>Search Jobs</a>
                            <a href="recommendations.php" class="btn btn-outline-primary"><i class="bi bi-robot me-2"></i>AI Recommendations</a>
                            <a href="profile.php" class="btn btn-outline-primary"><i class="bi bi-person me-2"></i>Update Profile</a>
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
