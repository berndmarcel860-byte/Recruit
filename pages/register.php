<?php
/**
 * Registration Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <a href="../index.php" class="text-decoration-none">
                        <i class="bi bi-briefcase-fill text-primary" style="font-size: 3rem;"></i>
                        <h2 class="text-primary fw-bold"><?= APP_NAME ?></h2>
                    </a>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="text-center mb-1">Create Your Account</h4>
                        <p class="text-center text-muted mb-4">Fill in your information to get started</p>
                        
                        <form id="register-form" onsubmit="register(event)">
                            <!-- Step indicator -->
                            <div class="progress mb-4" style="height: 4px;">
                                <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 33%"></div>
                            </div>
                            
                            <!-- Step 1: Basic Info -->
                            <div id="step-1">
                                <h5 class="mb-3"><i class="bi bi-person me-2"></i>Basic Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password *</label>
                                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                        <div class="form-text">Minimum 6 characters</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city">
                                    </div>
                                    <div class="col-12">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="country" name="country">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next <i class="bi bi-arrow-right ms-2"></i></button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Skills & Interests -->
                            <div id="step-2" style="display: none;">
                                <h5 class="mb-3"><i class="bi bi-stars me-2"></i>Skills & Interests</h5>
                                <div class="mb-4">
                                    <label for="bio" class="form-label">About You</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself, your career goals..."></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Skills</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" id="skill-input" placeholder="Add a skill (e.g., JavaScript, Python)">
                                        <button type="button" class="btn btn-outline-primary" onclick="addSkill()">Add</button>
                                    </div>
                                    <div id="skills-list"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Interests (Job Industries)</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" id="interest-input" placeholder="Add an interest (e.g., Technology, Healthcare)">
                                        <button type="button" class="btn btn-outline-secondary" onclick="addInterest()">Add</button>
                                    </div>
                                    <div id="interests-list"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)"><i class="bi bi-arrow-left me-2"></i>Back</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next <i class="bi bi-arrow-right ms-2"></i></button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Experience -->
                            <div id="step-3" style="display: none;">
                                <h5 class="mb-3"><i class="bi bi-briefcase me-2"></i>Experience</h5>
                                <div id="experience-container">
                                    <p class="text-muted">You can add your work experience now or later from your profile.</p>
                                </div>
                                <button type="button" class="btn btn-outline-primary mb-4" onclick="addExperience()">
                                    <i class="bi bi-plus me-2"></i>Add Experience
                                </button>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    You can also upload your CV after registration for a more complete profile.
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)"><i class="bi bi-arrow-left me-2"></i>Back</button>
                                    <button type="submit" class="btn btn-primary px-4">Create Account</button>
                                </div>
                            </div>
                        </form>
                        
                        <p class="text-center mt-4 mb-0">
                            Already have an account? <a href="login.php">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        let currentStep = 1;
        let experienceCount = 0;
        
        function nextStep(step) {
            // Validate current step
            if (currentStep === 1) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                if (password !== confirmPassword) {
                    showToast('Passwords do not match', 'danger');
                    return;
                }
                if (password.length < 6) {
                    showToast('Password must be at least 6 characters', 'danger');
                    return;
                }
            }
            
            document.getElementById('step-' + currentStep).style.display = 'none';
            document.getElementById('step-' + step).style.display = 'block';
            document.getElementById('progress-bar').style.width = (step * 33) + '%';
            currentStep = step;
        }
        
        function prevStep(step) {
            document.getElementById('step-' + currentStep).style.display = 'none';
            document.getElementById('step-' + step).style.display = 'block';
            document.getElementById('progress-bar').style.width = (step * 33) + '%';
            currentStep = step;
        }
        
        function addExperience() {
            experienceCount++;
            const container = document.getElementById('experience-container');
            const div = document.createElement('div');
            div.className = 'card mb-3';
            div.id = 'experience-' + experienceCount;
            div.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Experience ${experienceCount}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExperience(${experienceCount})">Remove</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control exp-company" placeholder="Company name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Title</label>
                            <input type="text" class="form-control exp-title" placeholder="Your role">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control exp-start">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control exp-end">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input exp-current" id="current-${experienceCount}">
                                <label class="form-check-label" for="current-${experienceCount}">Currently working here</label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.querySelector('p')?.remove();
            container.appendChild(div);
        }
        
        function removeExperience(id) {
            document.getElementById('experience-' + id)?.remove();
        }
        
        // Override register to collect experience data
        const originalRegister = register;
        register = async function(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'register');
            
            // Collect skills
            const skills = [];
            document.querySelectorAll('#skills-list .skill-tag').forEach(tag => {
                skills.push(tag.textContent.replace('×', '').trim());
            });
            formData.append('skills', JSON.stringify(skills));
            
            // Collect interests
            const interests = [];
            document.querySelectorAll('#interests-list .skill-tag').forEach(tag => {
                interests.push(tag.textContent.replace('×', '').trim());
            });
            formData.append('interests', JSON.stringify(interests));
            
            // Collect experience
            const experience = [];
            document.querySelectorAll('#experience-container .card').forEach(card => {
                experience.push({
                    company: card.querySelector('.exp-company')?.value || '',
                    title: card.querySelector('.exp-title')?.value || '',
                    startDate: card.querySelector('.exp-start')?.value || '',
                    endDate: card.querySelector('.exp-end')?.value || '',
                    current: card.querySelector('.exp-current')?.checked || false
                });
            });
            formData.append('experience', JSON.stringify(experience));
            
            // Validate passwords
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
            
            if (password !== confirmPassword) {
                showToast('Passwords do not match', 'danger');
                return;
            }
            
            if (password.length < 6) {
                showToast('Password must be at least 6 characters', 'danger');
                return;
            }
            
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating account...';
            
            const result = await apiRequest('../api/auth.php', 'POST', formData);
            
            if (result.success) {
                showToast('Registration successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Create Account';
            }
        };
    </script>
</body>
</html>
