<?php
/**
 * Registration Page
 * Professional multi-step registration with skill and interest suggestions
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Popular interests and skills (will be supplemented with AJAX)
$popularInterests = ['Technology', 'Healthcare', 'Finance', 'Education', 'Marketing', 
                     'Sales', 'Engineering', 'Design', 'Data Science', 'Consulting',
                     'E-commerce', 'Manufacturing', 'Real Estate', 'Media', 'Legal'];
                     
$popularSkills = ['JavaScript', 'Python', 'Java', 'React', 'Node.js', 'SQL', 'AWS',
                  'HTML/CSS', 'TypeScript', 'Git', 'Docker', 'PHP', 'Excel', 'Communication',
                  'Project Management', 'Leadership', 'Problem Solving', 'Teamwork'];
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
    <style>
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            align-items: center;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid #dee2e6;
            background: #fff;
            color: #6c757d;
            transition: all 0.3s;
        }
        .step.active .step-circle {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
        .step.completed .step-circle {
            background: #198754;
            border-color: #198754;
            color: #fff;
        }
        .step-line {
            width: 80px;
            height: 2px;
            background: #dee2e6;
            margin: 0 0.5rem;
        }
        .step.completed + .step-line,
        .step.active ~ .step-line {
            background: #0d6efd;
        }
        .suggestion-badge {
            cursor: pointer;
            transition: all 0.2s;
        }
        .suggestion-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
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
                
                <div class="card border-0 shadow">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="text-center mb-1">Create Your Account</h4>
                        <p class="text-center text-muted mb-4">Join thousands of job seekers finding their dream careers</p>
                        
                        <!-- Step Indicator -->
                        <div class="step-indicator">
                            <div class="step active" id="step-ind-1">
                                <div class="step-circle">1</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step-ind-2">
                                <div class="step-circle">2</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step-ind-3">
                                <div class="step-circle">3</div>
                            </div>
                        </div>
                        
                        <form id="register-form" onsubmit="register(event)">
                            <!-- Step 1: Basic Info -->
                            <div id="step-1">
                                <h5 class="mb-3 text-primary"><i class="bi bi-person me-2"></i>Basic Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="first_name" name="first_name" required placeholder="John">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="last_name" name="last_name" required placeholder="Doe">
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email" required placeholder="john.doe@example.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control form-control-lg" id="password" name="password" required minlength="6">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Minimum 6 characters</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control form-control-lg" id="phone" name="phone" placeholder="+1 234 567 8900">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control form-control-lg" id="city" name="city" placeholder="New York">
                                    </div>
                                    <div class="col-12">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control form-control-lg" id="country" name="country" placeholder="United States">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep(2)">
                                        Continue <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Skills & Interests -->
                            <div id="step-2" style="display: none;">
                                <h5 class="mb-3 text-primary"><i class="bi bi-stars me-2"></i>Skills & Interests</h5>
                                
                                <div class="mb-4">
                                    <label for="bio" class="form-label">About You</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself, your career goals, and what you're looking for in your next role..."></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Your Skills</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" id="skill-input" placeholder="Type a skill and press Add (or select from suggestions)">
                                        <button type="button" class="btn btn-primary" onclick="addSkill()">
                                            <i class="bi bi-plus-lg me-1"></i>Add Custom
                                        </button>
                                    </div>
                                    <div class="mb-2" id="skill-suggestions-container">
                                        <small class="text-muted">Popular skills: </small>
                                        <span id="skill-suggestions">Loading...</span>
                                    </div>
                                    <div id="skills-list" class="mb-2"></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Industries & Interests</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" id="interest-input" placeholder="Type an interest and press Add (or select from suggestions)">
                                        <button type="button" class="btn btn-secondary" onclick="addInterest()">
                                            <i class="bi bi-plus-lg me-1"></i>Add Custom
                                        </button>
                                    </div>
                                    <div class="mb-2" id="interest-suggestions-container">
                                        <small class="text-muted">Most popular: </small>
                                        <span id="interest-suggestions">Loading...</span>
                                    </div>
                                    <div id="interests-list"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="prevStep(1)">
                                        <i class="bi bi-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep(3)">
                                        Continue <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Experience -->
                            <div id="step-3" style="display: none;">
                                <h5 class="mb-3 text-primary"><i class="bi bi-briefcase me-2"></i>Work Experience</h5>
                                <div id="experience-container">
                                    <div class="text-center py-4 border rounded mb-3 bg-light">
                                        <i class="bi bi-briefcase text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Add your work experience to improve job matching</p>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary mb-4" onclick="addExperience()">
                                    <i class="bi bi-plus-lg me-2"></i>Add Work Experience
                                </button>
                                
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                                    <div>
                                        <strong>Pro tip:</strong> You can also upload your CV after registration for a more complete profile and better job recommendations.
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="prevStep(2)">
                                        <i class="bi bi-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="submit" class="btn btn-success btn-lg px-5">
                                        <i class="bi bi-check-lg me-2"></i>Create Account
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        <p class="text-center mb-0">
                            Already have an account? <a href="login.php" class="fw-medium">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        // Fallback in case app.js hasn't loaded yet
        if (typeof escapeHtml === 'undefined') {
            window.escapeHtml = function(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };
        }
        
        let currentStep = 1;
        let experienceCount = 0;
        let systemSkills = [];
        let systemInterests = [];
        
        // Load skills and interests from API on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSystemSkills();
            loadSystemInterests();
        });
        
        async function loadSystemSkills() {
            try {
                const result = await fetch('../api/auth.php?action=get_system_skills').then(r => r.json());
                if (result.success) {
                    systemSkills = result.skills;
                    renderSkillSuggestions();
                }
            } catch (e) {
                console.error('Failed to load skills');
            }
        }
        
        async function loadSystemInterests() {
            try {
                const result = await fetch('../api/auth.php?action=get_system_interests').then(r => r.json());
                if (result.success) {
                    systemInterests = result.interests;
                    renderInterestSuggestions();
                }
            } catch (e) {
                console.error('Failed to load interests');
            }
        }
        
        function renderSkillSuggestions() {
            const container = document.getElementById('skill-suggestions');
            const topSkills = systemSkills.slice(0, 15);
            container.innerHTML = topSkills.map(skill => 
                `<span class="badge bg-light text-dark suggestion-badge me-1 mb-1" onclick="addSkillFromSuggestion('${escapeHtml(skill.name)}')">${escapeHtml(skill.name)}</span>`
            ).join('');
        }
        
        function renderInterestSuggestions() {
            const container = document.getElementById('interest-suggestions');
            const topInterests = systemInterests.slice(0, 15);
            container.innerHTML = topInterests.map(interest => 
                `<span class="badge bg-light text-dark suggestion-badge me-1 mb-1" onclick="addInterestFromSuggestion('${escapeHtml(interest.name)}')">${interest.icon ? `<i class="bi bi-${escapeHtml(interest.icon)} me-1"></i>` : ''}${escapeHtml(interest.name)}</span>`
            ).join('');
        }
        
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        
        function updateStepIndicator() {
            for (let i = 1; i <= 3; i++) {
                const stepEl = document.getElementById('step-ind-' + i);
                stepEl.classList.remove('active', 'completed');
                if (i < currentStep) {
                    stepEl.classList.add('completed');
                    stepEl.querySelector('.step-circle').innerHTML = '<i class="bi bi-check"></i>';
                } else if (i === currentStep) {
                    stepEl.classList.add('active');
                    stepEl.querySelector('.step-circle').textContent = i;
                } else {
                    stepEl.querySelector('.step-circle').textContent = i;
                }
            }
        }
        
        function nextStep(step) {
            // Validate current step
            if (currentStep === 1) {
                const firstName = document.getElementById('first_name').value;
                const lastName = document.getElementById('last_name').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (!firstName || !lastName || !email) {
                    showToast('Please fill in all required fields', 'danger');
                    return;
                }
                
                if (!email.includes('@')) {
                    showToast('Please enter a valid email address', 'danger');
                    return;
                }
                
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
            currentStep = step;
            updateStepIndicator();
        }
        
        function prevStep(step) {
            document.getElementById('step-' + currentStep).style.display = 'none';
            document.getElementById('step-' + step).style.display = 'block';
            currentStep = step;
            updateStepIndicator();
        }
        
        function addSkillFromSuggestion(skill) {
            addSkillToList(skill);
        }
        
        function addSkillToList(skill) {
            const list = document.getElementById('skills-list');
            // Check if already exists
            if (list.querySelector(`[data-skill="${skill}"]`)) return;
            
            const tag = document.createElement('span');
            tag.className = 'skill-tag badge bg-primary me-2 mb-2';
            tag.setAttribute('data-skill', skill);
            tag.innerHTML = `${escapeHtml(skill)} <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>`;
            list.appendChild(tag);
        }
        
        function addInterestFromSuggestion(interest) {
            addInterestToList(interest);
        }
        
        function addInterestToList(interest) {
            const list = document.getElementById('interests-list');
            // Check if already exists
            if (list.querySelector(`[data-interest="${interest}"]`)) return;
            
            const tag = document.createElement('span');
            tag.className = 'skill-tag badge bg-secondary me-2 mb-2';
            tag.setAttribute('data-interest', interest);
            tag.innerHTML = `${escapeHtml(interest)} <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>`;
            list.appendChild(tag);
        }
        
        function addExperience() {
            experienceCount++;
            const container = document.getElementById('experience-container');
            
            // Remove placeholder if exists
            const placeholder = container.querySelector('.text-center.py-4');
            if (placeholder) placeholder.remove();
            
            const div = document.createElement('div');
            div.className = 'card mb-3';
            div.id = 'experience-' + experienceCount;
            div.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-primary"><i class="bi bi-briefcase me-2"></i>Experience ${experienceCount}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExperience(${experienceCount})">
                            <i class="bi bi-trash"></i>
                        </button>
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
                skills.push(tag.getAttribute('data-skill'));
            });
            formData.append('skills', JSON.stringify(skills));
            
            // Collect interests
            const interests = [];
            document.querySelectorAll('#interests-list .skill-tag').forEach(tag => {
                interests.push(tag.getAttribute('data-interest'));
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
                submitBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Create Account';
            }
        };
    </script>
</body>
</html>
