<?php
/**
 * Login Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? '../admin/index.php' : 'dashboard.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <a href="../index.php" class="text-decoration-none">
                        <i class="bi bi-briefcase-fill text-primary" style="font-size: 3rem;"></i>
                        <h2 class="text-primary fw-bold"><?= APP_NAME ?></h2>
                    </a>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="text-center mb-1">Welcome Back</h4>
                        <p class="text-center text-muted mb-4">Sign in to your account</p>
                        
                        <div id="alert-container"></div>
                        
                        <form id="login-form" onsubmit="login(event)">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
                        </form>
                        
                        <p class="text-center mt-4 mb-0">
                            Don't have an account? <a href="register.php">Create one</a>
                        </p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body p-3">
                        <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Demo Credentials</h6>
                        <p class="small text-muted mb-1"><strong>Admin:</strong> admin@recruit.com / admin123</p>
                        <p class="small text-muted mb-0"><strong>User:</strong> john.doe@example.com / password123</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
