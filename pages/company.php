<?php
/**
 * Company Details Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$companyId = (int)($_GET['id'] ?? 0);
if (!$companyId) {
    header('Location: companies.php');
    exit;
}

$db = Database::getInstance();
$company = $db->fetch("SELECT * FROM companies WHERE id = :id AND is_active = 1", ['id' => $companyId]);

if (!$company) {
    header('Location: companies.php');
    exit;
}

// Get company jobs
$jobs = $db->fetchAll(
    "SELECT * FROM jobs WHERE company_id = :id AND status = 'active' ORDER BY created_at DESC",
    ['id' => $companyId]
);

foreach ($jobs as &$job) {
    $job['skills'] = json_decode($job['skills'] ?? '[]', true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($company['name']) ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <!-- Company Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-start">
                    <div class="company-logo bg-primary text-white rounded d-flex align-items-center justify-content-center me-4" style="width: 80px; height: 80px; font-size: 32px; font-weight: bold;">
                        <?= substr($company['name'], 0, 1) ?>
                    </div>
                    <div class="flex-grow-1">
                        <h1 class="h3 fw-bold mb-1"><?= htmlspecialchars($company['name']) ?></h1>
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($company['industry']) ?></span>
                        <span class="badge bg-secondary"><?= getCompanySizeLabel($company['size']) ?></span>
                        <div class="mt-3 text-muted">
                            <span class="me-4"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($company['headquarters']) ?></span>
                            <?php if ($company['founded_year']): ?>
                            <span class="me-4"><i class="bi bi-calendar me-1"></i>Founded <?= $company['founded_year'] ?></span>
                            <?php endif; ?>
                            <?php if ($company['website']): ?>
                            <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank" class="text-decoration-none"><i class="bi bi-globe me-1"></i>Website</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- About -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">About <?= htmlspecialchars($company['name']) ?></h5>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($company['description'])) ?></p>
                    </div>
                </div>
                
                <!-- Open Positions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Open Positions (<?= count($jobs) ?>)</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($jobs)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-briefcase text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No open positions at the moment</p>
                        </div>
                        <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($jobs as $job): ?>
                            <a href="job.php?id=<?= $job['id'] ?>" class="list-group-item list-group-item-action py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($job['title']) ?></h6>
                                        <small class="text-muted">
                                            <span class="me-3"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?></span>
                                            <span class="me-3"><?= getJobTypeLabel($job['type']) ?></span>
                                            <span><?= getRemoteLabel($job['remote']) ?></span>
                                        </small>
                                    </div>
                                    <span class="text-muted small"><?= timeAgo($job['created_at']) ?></span>
                                </div>
                                <?php if (!empty($job['skills'])): ?>
                                <div class="mt-2">
                                    <?php foreach (array_slice($job['skills'], 0, 4) as $skill): ?>
                                    <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($skill) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Company Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Industry</small>
                            <span><?= htmlspecialchars($company['industry']) ?></span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Company Size</small>
                            <span><?= getCompanySizeLabel($company['size']) ?></span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Headquarters</small>
                            <span><?= htmlspecialchars($company['headquarters']) ?></span>
                        </div>
                        <?php if ($company['founded_year']): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Founded</small>
                            <span><?= $company['founded_year'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($company['email']): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Contact</small>
                            <a href="mailto:<?= htmlspecialchars($company['email']) ?>"><?= htmlspecialchars($company['email']) ?></a>
                        </div>
                        <?php endif; ?>
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
