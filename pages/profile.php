<?php
/**
 * User Profile Page
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
    <title>Profile - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <!-- Profile Card -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="profile-avatar mx-auto mb-3">
                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                        </div>
                        <h4 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                        <?php if ($user['city']): ?>
                        <p class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($user['city']) ?><?= $user['country'] ? ', ' . htmlspecialchars($user['country']) : '' ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- CV Upload -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">CV / Resume</h5>
                    </div>
                    <div class="card-body">
                        <div id="cv-status" class="mb-3">
                            <?php if ($user['cv_path']): ?>
                            <i class="bi bi-check-circle text-success me-2"></i>CV uploaded
                            <?php else: ?>
                            <i class="bi bi-x-circle text-muted me-2"></i>No CV uploaded
                            <?php endif; ?>
                        </div>
                        <div class="file-upload-area" onclick="document.getElementById('cv-upload').click()">
                            <i class="bi bi-cloud-upload text-primary mb-2" style="font-size: 2rem;"></i>
                            <p class="mb-0">Click to upload CV</p>
                            <small class="text-muted">PDF, DOC, DOCX (max 5MB)</small>
                        </div>
                        <input type="file" id="cv-upload" class="d-none" accept=".pdf,.doc,.docx" onchange="uploadCV(this)">
                    </div>
                </div>
            </div>
            
            <!-- Profile Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0">Edit Profile</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="profile-form" onsubmit="updateProfile(event)">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label for="bio" class="form-label">About</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                </div>
                            </div>
                            
                            <h5 class="mb-3">Skills</h5>
                            <div class="mb-4">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" id="skill-input" placeholder="Add a skill">
                                    <button type="button" class="btn btn-outline-primary" onclick="addSkill()">Add</button>
                                </div>
                                <div id="skills-list">
                                    <?php if (!empty($user['skills'])): ?>
                                        <?php foreach ($user['skills'] as $skill): ?>
                                        <span class="skill-tag badge bg-primary me-2 mb-2">
                                            <?= htmlspecialchars($skill) ?>
                                            <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>
                                        </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <h5 class="mb-3">Interests</h5>
                            <div class="mb-4">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" id="interest-input" placeholder="Add an interest">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addInterest()">Add</button>
                                </div>
                                <div id="interests-list">
                                    <?php if (!empty($user['interests'])): ?>
                                        <?php foreach ($user['interests'] as $interest): ?>
                                        <span class="skill-tag badge bg-secondary me-2 mb-2">
                                            <?= htmlspecialchars($interest) ?>
                                            <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>
                                        </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        // Handle Enter key for skill/interest inputs
        document.getElementById('skill-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); addSkill(); }
        });
        document.getElementById('interest-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); addInterest(); }
        });
    </script>
</body>
</html>
