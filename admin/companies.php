<?php
/**
 * Admin Companies Management
 * Professional interface for managing companies
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getInstance();

// Get statistics
$stats = [
    'total' => $db->count('companies'),
    'active' => $db->count('companies', "is_active = 1"),
    'inactive' => $db->count('companies', "is_active = 0")
];

// Get industries for dropdown
$industries = $db->fetchAll("SELECT DISTINCT industry FROM companies WHERE industry IS NOT NULL ORDER BY industry");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies - Admin - <?= APP_NAME ?></title>
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
                    <li class="nav-item"><a class="nav-link active" href="companies.php"><i class="bi bi-building me-1"></i>Companies</a></li>
                    <li class="nav-item"><a class="nav-link" href="jobs.php"><i class="bi bi-briefcase me-1"></i>Jobs</a></li>
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
                <h2 class="mb-1"><i class="bi bi-building me-2"></i>Companies Management</h2>
                <p class="text-muted mb-0">Manage partner companies and their profiles</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#companyModal" onclick="resetCompanyForm()">
                <i class="bi bi-plus-lg me-2"></i>Add Company
            </button>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['total'] ?></h3>
                            <p class="text-muted mb-0">Total Companies</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['active'] ?></h3>
                            <p class="text-muted mb-0">Active Companies</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                            <i class="bi bi-pause-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['inactive'] ?></h3>
                            <p class="text-muted mb-0">Inactive Companies</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="search" placeholder="Company name..." onkeyup="loadCompanies()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="industry" class="form-label">Industry</label>
                        <select class="form-select" id="industry" onchange="loadCompanies()">
                            <option value="">All Industries</option>
                            <?php foreach ($industries as $ind): ?>
                            <option value="<?= htmlspecialchars($ind['industry']) ?>"><?= htmlspecialchars($ind['industry']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" onchange="loadCompanies()">
                            <option value="">All Statuses</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Companies Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Company</th>
                                <th>Industry</th>
                                <th>Size</th>
                                <th>Location</th>
                                <th>Jobs</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="companies-container">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div id="pagination" class="mt-4"></div>
    </div>
    
    <!-- Add/Edit Company Modal -->
    <div class="modal fade" id="companyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="company-form" onsubmit="saveCompany(event)">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title" id="companyModalLabel">
                            <i class="bi bi-building me-2"></i>Add New Company
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="company-id" name="id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="company-name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="company-name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="company-industry" class="form-label">Industry <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="company-industry" name="industry" list="industry-list" required>
                                <datalist id="industry-list">
                                    <option value="Technology">
                                    <option value="Healthcare">
                                    <option value="Finance">
                                    <option value="E-commerce">
                                    <option value="Education">
                                    <option value="Manufacturing">
                                    <option value="Retail">
                                    <option value="Consulting">
                                    <option value="Media">
                                    <option value="Transportation">
                                </datalist>
                            </div>
                            <div class="col-12">
                                <label for="company-description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="company-description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="company-website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="company-website" name="website" placeholder="https://example.com">
                            </div>
                            <div class="col-md-6">
                                <label for="company-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="company-email" name="email">
                            </div>
                            <div class="col-md-4">
                                <label for="company-size" class="form-label">Company Size</label>
                                <select class="form-select" id="company-size" name="size">
                                    <option value="1-10">1-10 employees</option>
                                    <option value="11-50">11-50 employees</option>
                                    <option value="51-200">51-200 employees</option>
                                    <option value="201-500">201-500 employees</option>
                                    <option value="501-1000">501-1000 employees</option>
                                    <option value="1000+">1000+ employees</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="company-headquarters" class="form-label">Headquarters</label>
                                <input type="text" class="form-control" id="company-headquarters" name="headquarters" placeholder="City, Country">
                            </div>
                            <div class="col-md-4">
                                <label for="company-founded" class="form-label">Founded Year</label>
                                <input type="number" class="form-control" id="company-founded" name="founded_year" min="1800" max="<?= date('Y') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="company-phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="company-phone" name="phone">
                            </div>
                            <div class="col-md-6">
                                <label for="company-status" class="form-label">Status</label>
                                <select class="form-select" id="company-status" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Company
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
        document.addEventListener('DOMContentLoaded', loadCompanies);
        
        function resetFilters() {
            document.getElementById('search').value = '';
            document.getElementById('industry').value = '';
            document.getElementById('status').value = '';
            loadCompanies();
        }
        
        function resetCompanyForm() {
            document.getElementById('company-form').reset();
            document.getElementById('company-id').value = '';
            document.getElementById('companyModalLabel').innerHTML = '<i class="bi bi-building me-2"></i>Add New Company';
        }
    </script>
</body>
</html>
