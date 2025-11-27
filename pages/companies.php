<?php
/**
 * Companies Listing Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="text-center mb-5">
            <h1 class="fw-bold">Explore Companies</h1>
            <p class="text-muted">Discover companies hiring now</p>
        </div>
        
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="search" placeholder="Search companies...">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="industry">
                            <option value="">All Industries</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="loadCompanies()">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Companies Grid -->
        <div class="row g-4" id="companies-container">
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
        document.addEventListener('DOMContentLoaded', async () => {
            // Load industries for filter
            const result = await apiRequest('../api/companies.php?action=industries');
            if (result.success) {
                const select = document.getElementById('industry');
                result.industries.forEach(ind => {
                    const option = document.createElement('option');
                    option.value = ind;
                    option.textContent = ind;
                    select.appendChild(option);
                });
            }
            
            loadCompanies();
            
            // Search on Enter
            document.getElementById('search').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') loadCompanies();
            });
        });
    </script>
</body>
</html>
