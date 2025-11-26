<?php
/**
 * Home Page
 * Job Recruitment Platform Landing Page
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();

// Get stats
$stats = [
    'jobs' => $db->count('jobs', "status = 'active'"),
    'companies' => $db->count('companies', "is_active = 1"),
    'users' => $db->count('users', "role = 'user'")
];

// Get featured jobs
$featuredJobs = $db->fetchAll(
    "SELECT j.*, c.name as company_name, c.logo as company_logo, c.industry as company_industry
     FROM jobs j
     LEFT JOIN companies c ON j.company_id = c.id
     WHERE j.status = 'active'
     ORDER BY j.created_at DESC
     LIMIT 6"
);

foreach ($featuredJobs as &$job) {
    $job['skills'] = json_decode($job['skills'] ?? '[]', true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Find Your Dream Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="bi bi-briefcase-fill me-2"></i><?= APP_NAME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="pages/jobs.php">Find Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/companies.php">Companies</a>
                    </li>
                    <?php if (isLoggedIn() && !isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/recommendations.php">AI Recommendations</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/index.php">Admin Panel</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/applications.php">My Applications</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars(getCurrentUser()['first_name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="pages/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="logout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="pages/register.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">Find Your <span class="text-warning">Dream Job</span> Today</h1>
                    <p class="lead mb-4">Connect with top companies, discover opportunities that match your skills, and let our AI help you find the perfect career path.</p>
                    <div class="d-flex justify-content-center gap-3 mb-5">
                        <a href="pages/jobs.php" class="btn btn-light btn-lg px-4">Browse Jobs</a>
                        <a href="pages/register.php" class="btn btn-outline-light btn-lg px-4">Create Account</a>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <div class="text-center px-4">
                                <h2 class="display-5 fw-bold mb-0"><?= $stats['jobs'] ?>+</h2>
                                <small>Active Jobs</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="text-center px-4">
                                <h2 class="display-5 fw-bold mb-0"><?= $stats['companies'] ?>+</h2>
                                <small>Companies</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="text-center px-4">
                                <h2 class="display-5 fw-bold mb-0"><?= $stats['users'] ?>+</h2>
                                <small>Job Seekers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container py-4">
            <h2 class="text-center fw-bold mb-5">Why Choose <?= APP_NAME ?>?</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="card-body">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; line-height: 80px; font-size: 32px;">
                                <i class="bi bi-robot"></i>
                            </div>
                            <h5 class="card-title">AI-Powered Matching</h5>
                            <p class="card-text text-muted">Our intelligent algorithm finds the best job matches for your skills and experience.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="card-body">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; line-height: 80px; font-size: 32px;">
                                <i class="bi bi-building"></i>
                            </div>
                            <h5 class="card-title">Top Companies</h5>
                            <p class="card-text text-muted">Connect with leading companies across various industries.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="card-body">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; line-height: 80px; font-size: 32px;">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <h5 class="card-title">Easy Applications</h5>
                            <p class="card-text text-muted">Apply to multiple jobs with one click. Upload your CV once.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="card-body">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; line-height: 80px; font-size: 32px;">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h5 class="card-title">Track Progress</h5>
                            <p class="card-text text-muted">Monitor your application status and receive updates.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Jobs Section -->
    <section class="py-5">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Featured Jobs</h2>
                <a href="pages/jobs.php" class="text-decoration-none">View All Jobs <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-4" id="featured-jobs">
                <?php foreach ($featuredJobs as $job): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm job-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="company-logo bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; font-size: 20px; font-weight: bold;">
                                    <?= substr($job['company_name'] ?? 'C', 0, 1) ?>
                                </div>
                                <div>
                                    <a href="pages/company.php?id=<?= $job['company_id'] ?>" class="text-decoration-none text-dark fw-medium"><?= htmlspecialchars($job['company_name']) ?></a>
                                    <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?></p>
                                </div>
                            </div>
                            <h5 class="card-title">
                                <a href="pages/job.php?id=<?= $job['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($job['title']) ?></a>
                            </h5>
                            <p class="card-text text-muted small"><?= htmlspecialchars(substr($job['description'], 0, 100)) ?>...</p>
                            <div class="mb-3">
                                <span class="badge bg-primary-subtle text-primary me-1"><?= getJobTypeLabel($job['type']) ?></span>
                                <span class="badge bg-secondary-subtle text-secondary me-1"><?= getExperienceLevelLabel($job['experience_level']) ?></span>
                                <span class="badge bg-secondary-subtle text-secondary"><?= getRemoteLabel($job['remote']) ?></span>
                            </div>
                            <?php if (!empty($job['skills'])): ?>
                            <div class="mb-3">
                                <?php foreach (array_slice($job['skills'], 0, 3) as $skill): ?>
                                <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($skill) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($job['skills']) > 3): ?>
                                <span class="badge bg-light text-dark">+<?= count($job['skills']) - 3 ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                            <span class="fw-medium"><i class="bi bi-currency-dollar me-1"></i><?= formatSalary($job['salary_min'], $job['salary_max']) ?></span>
                            <a href="pages/job.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container py-4 text-center">
            <h2 class="fw-bold mb-3">Ready to Start Your Career Journey?</h2>
            <p class="lead mb-4">Create your profile, upload your CV, and let our AI find the perfect opportunities for you.</p>
            <a href="pages/register.php" class="btn btn-light btn-lg px-4">Get Started Free</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-briefcase-fill me-2"></i>
                    <span class="fw-bold"><?= APP_NAME ?></span>
                </div>
                <p class="mb-0 text-muted">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Professional Job Recruitment Platform.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
