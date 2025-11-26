<?php
/**
 * Job Details Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$jobId = (int)($_GET['id'] ?? 0);
if (!$jobId) {
    header('Location: jobs.php');
    exit;
}

$db = Database::getInstance();
$job = $db->fetch(
    "SELECT j.*, c.name as company_name, c.logo as company_logo, 
            c.industry as company_industry, c.description as company_description,
            c.headquarters, c.size as company_size, c.website as company_website
     FROM jobs j
     LEFT JOIN companies c ON j.company_id = c.id
     WHERE j.id = :id",
    ['id' => $jobId]
);

if (!$job) {
    header('Location: jobs.php');
    exit;
}

// Increment views
$db->query("UPDATE jobs SET views = views + 1 WHERE id = :id", ['id' => $jobId]);

// Parse JSON fields
$job['skills'] = json_decode($job['skills'] ?? '[]', true);
$job['requirements'] = json_decode($job['requirements'] ?? '[]', true);
$job['responsibilities'] = json_decode($job['responsibilities'] ?? '[]', true);
$job['benefits'] = json_decode($job['benefits'] ?? '[]', true);

// Check if user has already applied
$hasApplied = false;
if (isLoggedIn()) {
    $existingApp = $db->fetch(
        "SELECT id FROM applications WHERE user_id = :user_id AND job_id = :job_id",
        ['user_id' => $_SESSION['user_id'], 'job_id' => $jobId]
    );
    $hasApplied = (bool)$existingApp;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-4">
                            <div class="company-logo bg-primary text-white rounded d-flex align-items-center justify-content-center me-4" style="width: 64px; height: 64px; font-size: 24px; font-weight: bold;">
                                <?= substr($job['company_name'] ?? 'C', 0, 1) ?>
                            </div>
                            <div>
                                <a href="company.php?id=<?= $job['company_id'] ?>" class="text-decoration-none text-primary fw-medium"><?= htmlspecialchars($job['company_name']) ?></a>
                                <p class="text-muted mb-0"><?= htmlspecialchars($job['company_industry']) ?></p>
                            </div>
                        </div>
                        <h1 class="h3 fw-bold mb-3"><?= htmlspecialchars($job['title']) ?></h1>
                        <div class="d-flex flex-wrap gap-3 text-muted mb-4">
                            <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?></span>
                            <span><i class="bi bi-briefcase me-1"></i><?= getJobTypeLabel($job['type']) ?></span>
                            <span><?= getRemoteLabel($job['remote']) ?></span>
                            <span><i class="bi bi-bar-chart me-1"></i><?= getExperienceLevelLabel($job['experience_level']) ?></span>
                        </div>
                        
                        <?php if ($hasApplied): ?>
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>Application Submitted
                            </div>
                        <?php elseif (isLoggedIn() && !isAdmin()): ?>
                            <button class="btn btn-primary btn-lg" id="apply-btn" data-bs-toggle="modal" data-bs-target="#applyModal">Apply Now</button>
                        <?php elseif (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-primary btn-lg">Login to Apply</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 pb-2 border-bottom">Job Description</h5>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                    </div>
                </div>
                
                <?php if (!empty($job['responsibilities'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 pb-2 border-bottom">Responsibilities</h5>
                        <ul class="text-muted">
                            <?php foreach ($job['responsibilities'] as $item): ?>
                            <li class="mb-2"><?= htmlspecialchars($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($job['requirements'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 pb-2 border-bottom">Requirements</h5>
                        <ul class="text-muted">
                            <?php foreach ($job['requirements'] as $item): ?>
                            <li class="mb-2"><?= htmlspecialchars($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($job['skills'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 pb-2 border-bottom">Required Skills</h5>
                        <div>
                            <?php foreach ($job['skills'] as $skill): ?>
                            <span class="badge bg-primary me-2 mb-2 py-2 px-3"><?= htmlspecialchars($skill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($job['benefits'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 pb-2 border-bottom">Benefits</h5>
                        <div class="row">
                            <?php foreach ($job['benefits'] as $benefit): ?>
                            <div class="col-md-6 mb-2">
                                <span class="text-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($benefit) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 pb-2 border-bottom">Job Overview</h5>
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-currency-dollar me-2"></i>Salary</span>
                            <span class="fw-medium"><?= formatSalary($job['salary_min'], $job['salary_max']) ?></span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-calendar me-2"></i>Posted</span>
                            <span class="fw-medium"><?= formatDate($job['created_at']) ?></span>
                        </div>
                        <?php if ($job['deadline']): ?>
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-clock me-2"></i>Deadline</span>
                            <span class="fw-medium"><?= formatDate($job['deadline']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-eye me-2"></i>Views</span>
                            <span class="fw-medium"><?= number_format($job['views']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-file-earmark-text me-2"></i>Applications</span>
                            <span class="fw-medium"><?= number_format($job['applications_count']) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 pb-2 border-bottom">About Company</h5>
                        <p class="text-muted small"><?= htmlspecialchars(substr($job['company_description'] ?? '', 0, 200)) ?>...</p>
                        <a href="company.php?id=<?= $job['company_id'] ?>" class="btn btn-outline-primary w-100">View Company Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Apply Modal -->
    <div class="modal fade" id="applyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Apply for <?= htmlspecialchars($job['title']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cover_letter" class="form-label">Cover Letter (Optional)</label>
                        <textarea class="form-control" id="cover_letter" rows="5" placeholder="Tell the employer why you're a great fit for this role..."></textarea>
                    </div>
                    <?php 
                    $user = getCurrentUser();
                    if ($user && $user['cv_path']): 
                    ?>
                    <p class="text-success"><i class="bi bi-check-circle me-2"></i>Your CV will be attached automatically</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="applyForJob(<?= $jobId ?>)">Submit Application</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
