<?php
/**
 * AI Recommendations Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Recommendations - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="text-center mb-5">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                <i class="bi bi-robot"></i>
            </div>
            <h1 class="fw-bold">AI Job Recommendations</h1>
            <p class="text-muted">Jobs matched to your skills, experience, and interests</p>
        </div>
        
        <?php if (empty($user['skills']) && empty($user['interests'])): ?>
        <div class="alert alert-warning text-center">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Complete your profile with skills and interests for better recommendations.
            <a href="profile.php" class="ms-2">Update Profile</a>
        </div>
        <?php endif; ?>
        
        <!-- Profile Summary -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="mb-3">Your Profile Match Criteria</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong class="text-muted small">Skills</strong>
                        <div class="mt-1">
                            <?php if (!empty($user['skills'])): ?>
                                <?php foreach ($user['skills'] as $skill): ?>
                                <span class="badge bg-primary me-1 mb-1"><?= htmlspecialchars($skill) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <span class="text-muted">No skills added</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted small">Interests</strong>
                        <div class="mt-1">
                            <?php if (!empty($user['interests'])): ?>
                                <?php foreach ($user['interests'] as $interest): ?>
                                <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($interest) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <span class="text-muted">No interests added</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recommendations -->
        <div class="row g-4" id="recommendations-container">
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-muted">AI is finding the best matches for you...</p>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', loadRecommendations);
    </script>
</body>
</html>
