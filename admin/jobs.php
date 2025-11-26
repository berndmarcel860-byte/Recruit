<?php
/**
 * Admin Jobs Management
 * Professional interface for managing job listings
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getInstance();

// Get statistics
$stats = [
    'total' => $db->count('jobs'),
    'active' => $db->count('jobs', "status = 'active'"),
    'paused' => $db->count('jobs', "status = 'paused'"),
    'closed' => $db->count('jobs', "status = 'closed'")
];

// Get companies for dropdown
$companies = $db->fetchAll("SELECT id, name FROM companies WHERE is_active = 1 ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs - Admin - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
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
                    <li class="nav-item"><a class="nav-link active" href="jobs.php"><i class="bi bi-briefcase me-1"></i>Jobs</a></li>
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
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-briefcase me-2"></i>Jobs Management</h2>
                <p class="text-muted mb-0">Create, edit and manage job listings</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#jobModal" onclick="resetJobForm()">
                <i class="bi bi-plus-lg me-2"></i>Create Job
            </button>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-briefcase"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['total'] ?></h3>
                            <p class="text-muted mb-0">Total Jobs</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['active'] ?></h3>
                            <p class="text-muted mb-0">Active Jobs</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-pause-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['paused'] ?></h3>
                            <p class="text-muted mb-0">Paused</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['closed'] ?></h3>
                            <p class="text-muted mb-0">Closed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="search" placeholder="Job title..." onkeyup="loadAdminJobs()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="company_filter" class="form-label">Company</label>
                        <select class="form-select" id="company_filter" onchange="loadAdminJobs()">
                            <option value="">All Companies</option>
                            <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type_filter" class="form-label">Type</label>
                        <select class="form-select" id="type_filter" onchange="loadAdminJobs()">
                            <option value="">All Types</option>
                            <option value="full-time">Full Time</option>
                            <option value="part-time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="freelance">Freelance</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_filter" class="form-label">Status</label>
                        <select class="form-select" id="status_filter" onchange="loadAdminJobs()">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="closed">Closed</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" onclick="resetJobFilters()">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Jobs Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Job Title</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Apps</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="jobs-container">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div id="pagination" class="mt-4"></div>
    </div>
    
    <!-- Add/Edit Job Modal -->
    <div class="modal fade" id="jobModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="job-form" onsubmit="saveJob(event)">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title" id="jobModalLabel">
                            <i class="bi bi-briefcase me-2"></i>Create New Job
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="job-id" name="id">
                        
                        <!-- Basic Info -->
                        <h6 class="text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="job-title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="job-title" name="title" required placeholder="e.g., Senior Software Engineer">
                            </div>
                            <div class="col-md-6">
                                <label for="job-company" class="form-label">Company <span class="text-danger">*</span></label>
                                <select class="form-select" id="job-company" name="company_id" required>
                                    <option value="">Select Company</option>
                                    <?php foreach ($companies as $company): ?>
                                    <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="job-description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="job-description" name="description" rows="4" required placeholder="Describe the role, responsibilities, and what makes this opportunity exciting..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Location & Type -->
                        <h6 class="text-primary mb-3"><i class="bi bi-geo-alt me-2"></i>Location & Type</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="job-location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="job-location" name="location" placeholder="e.g., New York, NY">
                            </div>
                            <div class="col-md-4">
                                <label for="job-type" class="form-label">Employment Type</label>
                                <select class="form-select" id="job-type" name="type">
                                    <option value="full-time">Full Time</option>
                                    <option value="part-time">Part Time</option>
                                    <option value="contract">Contract</option>
                                    <option value="freelance">Freelance</option>
                                    <option value="internship">Internship</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="job-remote" class="form-label">Work Arrangement</label>
                                <select class="form-select" id="job-remote" name="remote">
                                    <option value="on-site">On-site</option>
                                    <option value="remote">Remote</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="job-department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="job-department" name="department" placeholder="e.g., Engineering">
                            </div>
                            <div class="col-md-4">
                                <label for="job-experience" class="form-label">Experience Level</label>
                                <select class="form-select" id="job-experience" name="experience_level">
                                    <option value="entry">Entry Level</option>
                                    <option value="junior">Junior</option>
                                    <option value="mid" selected>Mid Level</option>
                                    <option value="senior">Senior</option>
                                    <option value="lead">Lead</option>
                                    <option value="executive">Executive</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="job-deadline" class="form-label">Application Deadline</label>
                                <input type="date" class="form-control" id="job-deadline" name="deadline">
                            </div>
                        </div>
                        
                        <!-- Salary -->
                        <h6 class="text-primary mb-3"><i class="bi bi-currency-dollar me-2"></i>Compensation</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="job-salary-min" class="form-label">Minimum Salary (USD)</label>
                                <input type="number" class="form-control" id="job-salary-min" name="salary_min" placeholder="e.g., 60000">
                            </div>
                            <div class="col-md-4">
                                <label for="job-salary-max" class="form-label">Maximum Salary (USD)</label>
                                <input type="number" class="form-control" id="job-salary-max" name="salary_max" placeholder="e.g., 90000">
                            </div>
                            <div class="col-md-4">
                                <label for="job-status" class="form-label">Job Status</label>
                                <select class="form-select" id="job-status" name="status">
                                    <option value="active">Active</option>
                                    <option value="paused">Paused</option>
                                    <option value="draft">Draft</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Skills -->
                        <h6 class="text-primary mb-3"><i class="bi bi-stars me-2"></i>Required Skills</h6>
                        <div class="mb-4">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="job-skill-input" placeholder="Add a skill (e.g., JavaScript, Python)">
                                <button type="button" class="btn btn-outline-primary" onclick="addJobSkill()">Add</button>
                            </div>
                            <div class="popular-skills mb-2">
                                <small class="text-muted">Popular: </small>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addSkillFromSuggestion('JavaScript')">JavaScript</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addSkillFromSuggestion('Python')">Python</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addSkillFromSuggestion('React')">React</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addSkillFromSuggestion('Node.js')">Node.js</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addSkillFromSuggestion('SQL')">SQL</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addSkillFromSuggestion('AWS')">AWS</span>
                            </div>
                            <div id="job-skills-list"></div>
                        </div>
                        
                        <!-- Benefits -->
                        <h6 class="text-primary mb-3"><i class="bi bi-gift me-2"></i>Benefits</h6>
                        <div class="mb-4">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="job-benefit-input" placeholder="Add a benefit (e.g., Health Insurance)">
                                <button type="button" class="btn btn-outline-secondary" onclick="addJobBenefit()">Add</button>
                            </div>
                            <div class="popular-benefits mb-2">
                                <small class="text-muted">Common: </small>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addBenefitFromSuggestion('Health Insurance')">Health Insurance</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addBenefitFromSuggestion('401(k)')">401(k)</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addBenefitFromSuggestion('Remote Work')">Remote Work</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addBenefitFromSuggestion('Flexible Hours')">Flexible Hours</span>
                                <span class="badge bg-light text-dark skill-suggestion" onclick="addBenefitFromSuggestion('Stock Options')">Stock Options</span>
                            </div>
                            <div id="job-benefits-list"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', loadAdminJobs);
        
        function resetJobFilters() {
            document.getElementById('search').value = '';
            document.getElementById('company_filter').value = '';
            document.getElementById('type_filter').value = '';
            document.getElementById('status_filter').value = '';
            loadAdminJobs();
        }
        
        function resetJobForm() {
            document.getElementById('job-form').reset();
            document.getElementById('job-id').value = '';
            document.getElementById('job-skills-list').innerHTML = '';
            document.getElementById('job-benefits-list').innerHTML = '';
            document.getElementById('jobModalLabel').innerHTML = '<i class="bi bi-briefcase me-2"></i>Create New Job';
        }
        
        function addJobSkill() {
            const input = document.getElementById('job-skill-input');
            const skill = input.value.trim();
            if (skill) {
                addSkillToJobList(skill);
                input.value = '';
            }
        }
        
        function addSkillFromSuggestion(skill) {
            addSkillToJobList(skill);
        }
        
        function addSkillToJobList(skill) {
            const list = document.getElementById('job-skills-list');
            // Check if already exists
            if (list.querySelector(`[data-skill="${skill}"]`)) return;
            
            const tag = document.createElement('span');
            tag.className = 'badge bg-primary me-2 mb-2';
            tag.setAttribute('data-skill', skill);
            tag.innerHTML = `${escapeHtml(skill)} <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>`;
            list.appendChild(tag);
        }
        
        function addJobBenefit() {
            const input = document.getElementById('job-benefit-input');
            const benefit = input.value.trim();
            if (benefit) {
                addBenefitToJobList(benefit);
                input.value = '';
            }
        }
        
        function addBenefitFromSuggestion(benefit) {
            addBenefitToJobList(benefit);
        }
        
        function addBenefitToJobList(benefit) {
            const list = document.getElementById('job-benefits-list');
            // Check if already exists
            if (list.querySelector(`[data-benefit="${benefit}"]`)) return;
            
            const tag = document.createElement('span');
            tag.className = 'badge bg-success me-2 mb-2';
            tag.setAttribute('data-benefit', benefit);
            tag.innerHTML = `${escapeHtml(benefit)} <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>`;
            list.appendChild(tag);
        }
    </script>
</body>
</html>
