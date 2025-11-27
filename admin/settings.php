<?php
/**
 * Admin Settings - Skills, Interests & Email Templates Management
 * Professional settings interface for platform configuration
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
$currentUser = getCurrentUser();

// Get skill categories
$skillCategories = $db->fetchAll(
    "SELECT DISTINCT category FROM system_skills WHERE is_active = 1 ORDER BY category"
);

// Get interest categories
$interestCategories = $db->fetchAll(
    "SELECT DISTINCT category FROM system_interests WHERE is_active = 1 ORDER BY category"
);

// Get email templates
$emailTemplates = $db->fetchAll("SELECT * FROM email_templates ORDER BY type, name");

// Get meeting settings
$meetingSettings = $db->fetchAll("SELECT * FROM meeting_settings ORDER BY is_default DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?= APP_NAME ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people me-1"></i>Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="applications.php"><i class="bi bi-file-text me-1"></i>Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="companies.php"><i class="bi bi-building me-1"></i>Companies</a></li>
                    <li class="nav-item"><a class="nav-link" href="jobs.php"><i class="bi bi-briefcase me-1"></i>Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php"><i class="bi bi-calendar me-1"></i>Appointments</a></li>
                    <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="bi bi-gear me-1"></i>Settings</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/jobs.php" target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>View Site</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($currentUser['first_name']) ?>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-gear me-2"></i>Platform Settings</h2>
                <p class="text-muted mb-0">Manage skills, interests, email templates and meeting settings</p>
            </div>
        </div>
        
        <!-- Settings Tabs -->
        <ul class="nav nav-tabs mb-4" id="settingsTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#skills-tab">
                    <i class="bi bi-tools me-2"></i>Skills
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#interests-tab">
                    <i class="bi bi-heart me-2"></i>Interests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#templates-tab">
                    <i class="bi bi-envelope me-2"></i>Email Templates
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#meetings-tab">
                    <i class="bi bi-camera-video me-2"></i>Meeting Settings
                </a>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- Skills Tab -->
            <div class="tab-pane fade show active" id="skills-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="bi bi-tools me-2"></i>System Skills</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                            <i class="bi bi-plus-lg me-1"></i>Add Skill
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="skill-search" placeholder="Search skills..." onkeyup="filterSkills()">
                        </div>
                        
                        <div id="skills-container">
                            <?php foreach ($skillCategories as $cat): ?>
                            <div class="mb-4 skill-category" data-category="<?= htmlspecialchars($cat['category']) ?>">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-folder me-2"></i><?= htmlspecialchars($cat['category']) ?>
                                </h6>
                                <div class="d-flex flex-wrap gap-2" id="skills-<?= preg_replace('/[^a-zA-Z0-9]/', '', $cat['category']) ?>">
                                    <!-- Skills loaded via AJAX -->
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Interests Tab -->
            <div class="tab-pane fade" id="interests-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="bi bi-heart me-2"></i>System Interests</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInterestModal">
                            <i class="bi bi-plus-lg me-1"></i>Add Interest
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="interest-search" placeholder="Search interests..." onkeyup="filterInterests()">
                        </div>
                        
                        <div id="interests-container">
                            <?php foreach ($interestCategories as $cat): ?>
                            <div class="mb-4 interest-category" data-category="<?= htmlspecialchars($cat['category']) ?>">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-folder me-2"></i><?= htmlspecialchars($cat['category']) ?>
                                </h6>
                                <div class="d-flex flex-wrap gap-2" id="interests-<?= preg_replace('/[^a-zA-Z0-9]/', '', $cat['category']) ?>">
                                    <!-- Interests loaded via AJAX -->
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Email Templates Tab -->
            <div class="tab-pane fade" id="templates-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Email Templates</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                            <i class="bi bi-plus-lg me-1"></i>Add Template
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Name</th>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($emailTemplates as $template): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <strong><?= htmlspecialchars($template['name']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars(substr($template['subject'], 0, 50)) ?>...</td>
                                        <td>
                                            <span class="badge bg-<?= $template['type'] === 'onboarding' ? 'warning' : ($template['type'] === 'interview' ? 'primary' : ($template['type'] === 'offer' ? 'success' : 'secondary')) ?>">
                                                <?= ucfirst($template['type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($template['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary" onclick="editTemplate(<?= $template['id'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="previewTemplate(<?= $template['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Meeting Settings Tab -->
            <div class="tab-pane fade" id="meetings-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="mb-0"><i class="bi bi-camera-video me-2"></i>Video Meeting Integration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <?php foreach ($meetingSettings as $setting): ?>
                            <div class="col-md-4">
                                <div class="card h-100 <?= $setting['is_default'] ? 'border-primary' : '' ?>">
                                    <div class="card-body text-center">
                                        <?php
                                        $icons = ['zoom' => 'camera-video', 'teams' => 'microsoft', 'google_meet' => 'google'];
                                        ?>
                                        <i class="bi bi-<?= $icons[$setting['provider']] ?? 'camera-video' ?> text-primary" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3"><?= getMeetingProviderName($setting['provider']) ?></h5>
                                        <?php if ($setting['is_default']): ?>
                                        <span class="badge bg-primary">Default</span>
                                        <?php endif; ?>
                                        <p class="text-muted small mt-2 mb-3">
                                            <?= htmlspecialchars(substr($setting['base_url'], 0, 40)) ?>...
                                        </p>
                                        <div class="d-flex justify-content-center gap-2">
                                            <?php if (!$setting['is_default']): ?>
                                            <button class="btn btn-sm btn-outline-primary" onclick="setDefaultProvider(<?= $setting['id'] ?>)">
                                                Set as Default
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editMeetingSettings(<?= $setting['id'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="alert alert-info mt-4 d-flex align-items-start">
                            <i class="bi bi-info-circle-fill me-3 mt-1 fs-4"></i>
                            <div>
                                <strong>How Meeting Links Work</strong>
                                <p class="mb-0 mt-1">When scheduling onboarding calls or interviews, the system automatically generates meeting links using the default provider. You can configure each provider's base URL and settings above.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Skill Modal -->
    <div class="modal fade" id="addSkillModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="add-skill-form">
                        <div class="mb-3">
                            <label for="skill-name" class="form-label">Skill Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="skill-name" required>
                        </div>
                        <div class="mb-3">
                            <label for="skill-category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="skill-category" required>
                                <option value="">Select category</option>
                                <option value="Programming Languages">Programming Languages</option>
                                <option value="Frameworks">Frameworks</option>
                                <option value="Databases">Databases</option>
                                <option value="Cloud & DevOps">Cloud & DevOps</option>
                                <option value="Data & AI">Data & AI</option>
                                <option value="Soft Skills">Soft Skills</option>
                                <option value="Tools">Tools</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addSkill()">Add Skill</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Interest Modal -->
    <div class="modal fade" id="addInterestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Interest</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="add-interest-form">
                        <div class="mb-3">
                            <label for="interest-name" class="form-label">Interest Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="interest-name" required>
                        </div>
                        <div class="mb-3">
                            <label for="interest-category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="interest-category" required>
                                <option value="">Select category</option>
                                <option value="Industry">Industry</option>
                                <option value="Job Field">Job Field</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="interest-icon" class="form-label">Icon (Bootstrap Icon name)</label>
                            <input type="text" class="form-control" id="interest-icon" placeholder="e.g., laptop, heart, briefcase">
                            <small class="text-muted">See <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a> for options</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addInterest()">Add Interest</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        // Load skills and interests on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSkills();
            loadInterests();
        });
        
        async function loadSkills() {
            const result = await apiRequest(API_BASE + '/api/admin.php?action=get_system_skills');
            if (result.success) {
                const skills = result.skills;
                const categories = {};
                
                skills.forEach(skill => {
                    if (!categories[skill.category]) {
                        categories[skill.category] = [];
                    }
                    categories[skill.category].push(skill);
                });
                
                for (const [category, categorySkills] of Object.entries(categories)) {
                    const containerId = 'skills-' + category.replace(/[^a-zA-Z0-9]/g, '');
                    const container = document.getElementById(containerId);
                    if (container) {
                        container.innerHTML = categorySkills.map(skill => `
                            <span class="badge bg-primary bg-opacity-10 text-primary p-2 skill-badge" data-skill="${escapeHtml(skill.name)}">
                                ${escapeHtml(skill.name)}
                                <small class="ms-1 text-muted">(${skill.usage_count})</small>
                                <button class="btn btn-link btn-sm p-0 ms-1 text-danger" onclick="deleteSkill(${skill.id})">
                                    <i class="bi bi-x"></i>
                                </button>
                            </span>
                        `).join('');
                    }
                }
            }
        }
        
        async function loadInterests() {
            const result = await apiRequest(API_BASE + '/api/admin.php?action=get_system_interests');
            if (result.success) {
                const interests = result.interests;
                const categories = {};
                
                interests.forEach(interest => {
                    if (!categories[interest.category]) {
                        categories[interest.category] = [];
                    }
                    categories[interest.category].push(interest);
                });
                
                for (const [category, categoryInterests] of Object.entries(categories)) {
                    const containerId = 'interests-' + category.replace(/[^a-zA-Z0-9]/g, '');
                    const container = document.getElementById(containerId);
                    if (container) {
                        container.innerHTML = categoryInterests.map(interest => `
                            <span class="badge bg-secondary bg-opacity-10 text-secondary p-2 interest-badge" data-interest="${escapeHtml(interest.name)}">
                                ${interest.icon ? `<i class="bi bi-${escapeHtml(interest.icon)} me-1"></i>` : ''}
                                ${escapeHtml(interest.name)}
                                <small class="ms-1 text-muted">(${interest.usage_count})</small>
                                <button class="btn btn-link btn-sm p-0 ms-1 text-danger" onclick="deleteInterest(${interest.id})">
                                    <i class="bi bi-x"></i>
                                </button>
                            </span>
                        `).join('');
                    }
                }
            }
        }
        
        async function addSkill() {
            const name = document.getElementById('skill-name').value.trim();
            const category = document.getElementById('skill-category').value;
            
            if (!name || !category) {
                showToast('Please fill in all required fields', 'danger');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'add_skill');
            formData.append('name', name);
            formData.append('category', category);
            
            const result = await apiRequest(API_BASE + '/api/admin.php', 'POST', formData);
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('addSkillModal')).hide();
                document.getElementById('skill-name').value = '';
                loadSkills();
            }
        }
        
        async function deleteSkill(id) {
            if (!confirm('Are you sure you want to delete this skill?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete_skill');
            formData.append('id', id);
            
            const result = await apiRequest(API_BASE + '/api/admin.php', 'POST', formData);
            if (result.success) {
                loadSkills();
            }
        }
        
        async function addInterest() {
            const name = document.getElementById('interest-name').value.trim();
            const category = document.getElementById('interest-category').value;
            const icon = document.getElementById('interest-icon').value.trim();
            
            if (!name || !category) {
                showToast('Please fill in all required fields', 'danger');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'add_interest');
            formData.append('name', name);
            formData.append('category', category);
            formData.append('icon', icon);
            
            const result = await apiRequest(API_BASE + '/api/admin.php', 'POST', formData);
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('addInterestModal')).hide();
                document.getElementById('interest-name').value = '';
                loadInterests();
            }
        }
        
        async function deleteInterest(id) {
            if (!confirm('Are you sure you want to delete this interest?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete_interest');
            formData.append('id', id);
            
            const result = await apiRequest(API_BASE + '/api/admin.php', 'POST', formData);
            if (result.success) {
                loadInterests();
            }
        }
        
        function filterSkills() {
            const search = document.getElementById('skill-search').value.toLowerCase();
            document.querySelectorAll('.skill-badge').forEach(badge => {
                const skill = badge.getAttribute('data-skill').toLowerCase();
                badge.style.display = skill.includes(search) ? '' : 'none';
            });
        }
        
        function filterInterests() {
            const search = document.getElementById('interest-search').value.toLowerCase();
            document.querySelectorAll('.interest-badge').forEach(badge => {
                const interest = badge.getAttribute('data-interest').toLowerCase();
                badge.style.display = interest.includes(search) ? '' : 'none';
            });
        }
        
        function editTemplate(id) {
            showToast('Template editor coming soon', 'info');
        }
        
        function previewTemplate(id) {
            showToast('Template preview coming soon', 'info');
        }
        
        async function setDefaultProvider(id) {
            const formData = new FormData();
            formData.append('action', 'set_default_meeting_provider');
            formData.append('id', id);
            
            const result = await apiRequest(API_BASE + '/api/admin.php', 'POST', formData);
            if (result.success) {
                location.reload();
            }
        }
        
        function editMeetingSettings(id) {
            showToast('Meeting settings editor coming soon', 'info');
        }
    </script>
</body>
</html>
