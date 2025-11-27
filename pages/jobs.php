<?php
/**
 * Jobs Listing Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Jobs - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="text-center mb-5">
            <h1 class="fw-bold">Find Your Perfect Job</h1>
            <p class="text-muted" id="jobs-count">Discover opportunities from top companies</p>
        </div>
        
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="search" placeholder="Search jobs...">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="type">
                            <option value="">All Types</option>
                            <option value="full-time">Full Time</option>
                            <option value="part-time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="freelance">Freelance</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="remote">
                            <option value="">All Locations</option>
                            <option value="on-site">On-site</option>
                            <option value="remote">Remote</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="experience_level">
                            <option value="">All Levels</option>
                            <option value="entry">Entry Level</option>
                            <option value="junior">Junior</option>
                            <option value="mid">Mid Level</option>
                            <option value="senior">Senior</option>
                            <option value="lead">Lead</option>
                            <option value="executive">Executive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="loadJobs()">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Jobs Grid -->
        <div class="row g-4" id="jobs-container">
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
        
        <!-- Pagination -->
        <div id="pagination" class="mt-4"></div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadJobs();
            
            // Search on Enter
            document.getElementById('search').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') loadJobs();
            });
        });
    </script>
</body>
</html>
