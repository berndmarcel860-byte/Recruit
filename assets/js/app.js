/**
 * Job Recruitment Platform - Main JavaScript
 * Handles AJAX calls and UI interactions
 */

// API Base URL - Calculate base path dynamically
const getBasePath = () => {
    const path = window.location.pathname;
    // Find the base directory by looking for the last occurrence of known subdirectories
    const subdirs = ['/pages/', '/admin/'];
    let latestIdx = -1;
    for (const subdir of subdirs) {
        const idx = path.lastIndexOf(subdir);
        if (idx > latestIdx) {
            latestIdx = idx;
        }
    }
    if (latestIdx !== -1) {
        return path.substring(0, latestIdx) + '/';
    }
    // If at root level, use current directory
    const lastSlash = path.lastIndexOf('/');
    return path.substring(0, lastSlash + 1);
};

/**
 * Build API URL from endpoint
 * @param {string} endpoint - The API endpoint
 * @returns {string} - Full URL for the API request
 */
const buildApiUrl = (endpoint) => {
    // Return as-is if endpoint is absolute URL, starts with /, or already has base path
    if (endpoint.startsWith('http') || endpoint.startsWith('/') || endpoint.startsWith(BASE_PATH)) {
        return endpoint;
    }
    return API_URL + endpoint;
};

const BASE_PATH = getBasePath();
const API_URL = BASE_PATH + 'api/';

// Toast notification container
let toastContainer;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Create toast container
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
});

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast show align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
    
    // Close button
    toast.querySelector('.btn-close').addEventListener('click', () => {
        toast.remove();
    });
}

/**
 * AJAX request wrapper
 */
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {}
    };
    
    if (data) {
        if (data instanceof FormData) {
            options.body = data;
        } else {
            options.headers['Content-Type'] = 'application/x-www-form-urlencoded';
            options.body = new URLSearchParams(data).toString();
        }
    }
    
    try {
        const url = buildApiUrl(endpoint);
        const response = await fetch(url, options);
        const result = await response.json();
        
        if (!result.success && result.message) {
            showToast(result.message, 'danger');
        }
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        showToast('An error occurred. Please try again.', 'danger');
        return { success: false, message: error.message };
    }
}

/**
 * Login function
 */
async function login(event) {
    event.preventDefault();
    
    const form = event.target;
    const email = form.querySelector('[name="email"]').value;
    const password = form.querySelector('[name="password"]').value;
    const submitBtn = form.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
    
    const result = await apiRequest('auth.php', 'POST', {
        action: 'login',
        email: email,
        password: password
    });
    
    if (result.success) {
        showToast('Login successful! Redirecting...', 'success');
        setTimeout(() => {
            window.location.href = result.user.role === 'admin' ? 'admin/index.php' : 'dashboard.php';
        }, 1000);
    } else {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Sign In';
    }
}

/**
 * Register function
 */
async function register(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('action', 'register');
    
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
    
    const result = await apiRequest('auth.php', 'POST', formData);
    
    if (result.success) {
        showToast('Registration successful! Redirecting...', 'success');
        setTimeout(() => {
            window.location.href = 'dashboard.php';
        }, 1000);
    } else {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Create Account';
    }
}

/**
 * Logout function
 */
async function logout() {
    const result = await apiRequest('auth.php', 'POST', { action: 'logout' });
    
    if (result.success) {
        window.location.href = '../index.php';
    }
}

/**
 * Load jobs with filters
 */
async function loadJobs(page = 1) {
    const search = document.querySelector('#search')?.value || '';
    const type = document.querySelector('#type')?.value || '';
    const remote = document.querySelector('#remote')?.value || '';
    const experienceLevel = document.querySelector('#experience_level')?.value || '';
    const location = document.querySelector('#location')?.value || '';
    
    const params = new URLSearchParams({
        action: 'list',
        page: page,
        search: search,
        type: type,
        remote: remote,
        experience_level: experienceLevel,
        location: location
    });
    
    const container = document.querySelector('#jobs-container');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    const result = await apiRequest('jobs.php?' + params.toString());
    
    if (result.success) {
        if (result.jobs.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-search"></i></div>
                        <h5>No jobs found</h5>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = result.jobs.map(job => renderJobCard(job)).join('');
        }
        
        renderPagination(result.pagination, 'loadJobs');
    }
}

/**
 * Render job card
 */
function renderJobCard(job) {
    const skills = Array.isArray(job.skills) ? job.skills : JSON.parse(job.skills || '[]');
    const skillsHtml = skills.slice(0, 3).map(s => `<span class="badge bg-light text-dark me-1">${escapeHtml(s)}</span>`).join('');
    const moreSkills = skills.length > 3 ? `<span class="badge bg-light text-dark">+${skills.length - 3}</span>` : '';
    
    const matchScoreHtml = job.match_score ? `
        <span class="match-score ${job.match_score >= 70 ? 'high' : job.match_score >= 40 ? 'medium' : 'low'}">
            ${Math.round(job.match_score)}% Match
        </span>
    ` : '';
    
    return `
        <div class="col-md-6 col-lg-4 fade-in">
            <div class="card h-100 border-0 shadow-sm job-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-start">
                            <div class="company-logo bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; font-size: 20px; font-weight: bold;">
                                ${escapeHtml((job.company_name || 'C').charAt(0))}
                            </div>
                            <div>
                                <a href="company.php?id=${job.company_id}" class="text-decoration-none text-dark fw-medium">${escapeHtml(job.company_name)}</a>
                                <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(job.location)}</p>
                            </div>
                        </div>
                        ${matchScoreHtml}
                    </div>
                    <h5 class="card-title">
                        <a href="job.php?id=${job.id}" class="text-decoration-none text-dark">${escapeHtml(job.title)}</a>
                    </h5>
                    <p class="card-text text-muted small">${escapeHtml(job.description.substring(0, 100))}...</p>
                    <div class="mb-3">
                        <span class="badge bg-primary-subtle text-primary me-1">${escapeHtml(formatJobType(job.type))}</span>
                        <span class="badge bg-secondary-subtle text-secondary me-1">${escapeHtml(formatExperienceLevel(job.experience_level))}</span>
                        <span class="badge bg-secondary-subtle text-secondary">${escapeHtml(formatRemote(job.remote))}</span>
                    </div>
                    <div class="mb-3">${skillsHtml}${moreSkills}</div>
                </div>
                <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                    <span class="fw-medium"><i class="bi bi-currency-dollar me-1"></i>${formatSalary(job.salary_min, job.salary_max)}</span>
                    <a href="job.php?id=${job.id}" class="btn btn-primary btn-sm">View Details</a>
                </div>
            </div>
        </div>
    `;
}

/**
 * Load companies
 */
async function loadCompanies(page = 1) {
    const search = document.querySelector('#search')?.value || '';
    const industry = document.querySelector('#industry')?.value || '';
    
    const params = new URLSearchParams({
        action: 'list',
        page: page,
        search: search,
        industry: industry
    });
    
    const container = document.querySelector('#companies-container');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    const result = await apiRequest('companies.php?' + params.toString());
    
    if (result.success) {
        if (result.companies.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-building"></i></div>
                        <h5>No companies found</h5>
                        <p class="text-muted">Try adjusting your search criteria</p>
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = result.companies.map(company => renderCompanyCard(company)).join('');
        }
        
        renderPagination(result.pagination, 'loadCompanies');
    }
}

/**
 * Render company card
 */
function renderCompanyCard(company) {
    return `
        <div class="col-md-6 col-lg-4 fade-in">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="company-logo bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                            ${escapeHtml(company.name.charAt(0))}
                        </div>
                        <div>
                            <h5 class="card-title mb-1">
                                <a href="company.php?id=${company.id}" class="text-decoration-none text-dark">${escapeHtml(company.name)}</a>
                            </h5>
                            <span class="text-primary">${escapeHtml(company.industry)}</span>
                        </div>
                    </div>
                    <p class="card-text text-muted small">${escapeHtml(company.description.substring(0, 120))}...</p>
                    <div class="text-muted small mb-3">
                        <span class="me-3"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(company.headquarters)}</span>
                        <span><i class="bi bi-people me-1"></i>${escapeHtml(company.size)} employees</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">${company.job_count || 0} open positions</span>
                    <a href="company.php?id=${company.id}" class="btn btn-outline-primary btn-sm">View Jobs</a>
                </div>
            </div>
        </div>
    `;
}

/**
 * Load AI recommendations
 */
async function loadRecommendations() {
    const container = document.querySelector('#recommendations-container');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3">AI is finding the best matches for you...</p></div>';
    
    const result = await apiRequest('jobs.php?action=recommendations&limit=10');
    
    if (result.success) {
        if (result.recommendations.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-robot"></i></div>
                        <h5>No recommendations yet</h5>
                        <p class="text-muted">Complete your profile with skills and interests to get personalized job recommendations</p>
                        <a href="profile.php" class="btn btn-primary">Complete Profile</a>
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = result.recommendations.map(job => renderJobCard(job)).join('');
        }
    }
}

/**
 * Apply for a job
 */
async function applyForJob(jobId) {
    const coverLetter = document.querySelector('#cover_letter')?.value || '';
    
    const result = await apiRequest('jobs.php', 'POST', {
        action: 'apply',
        job_id: jobId,
        cover_letter: coverLetter,
        source: 'manual'
    });
    
    if (result.success) {
        showToast('Application submitted successfully!', 'success');
        // Close modal if open
        const modal = bootstrap.Modal.getInstance(document.querySelector('#applyModal'));
        if (modal) modal.hide();
        // Update UI
        const applyBtn = document.querySelector('#apply-btn');
        if (applyBtn) {
            applyBtn.outerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Application Submitted</div>';
        }
    }
}

/**
 * Load user applications
 */
async function loadApplications() {
    const container = document.querySelector('#applications-container');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    const result = await apiRequest('jobs.php?action=my_applications');
    
    if (result.success) {
        if (result.applications.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <h5>No applications yet</h5>
                    <p class="text-muted">Start applying for jobs to track your applications here</p>
                    <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Job</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Match Score</th>
                                <th>Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${result.applications.map(app => `
                                <tr>
                                    <td><a href="job.php?id=${app.job_id}">${escapeHtml(app.job_title)}</a></td>
                                    <td>${escapeHtml(app.company_name)}</td>
                                    <td><span class="badge ${getStatusBadgeClass(app.status)}">${escapeHtml(app.status)}</span></td>
                                    <td>${app.match_score ? Math.round(app.match_score) + '%' : '-'}</td>
                                    <td>${formatDate(app.created_at)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
    }
}

/**
 * Render pagination
 */
function renderPagination(pagination, callback) {
    const container = document.querySelector('#pagination');
    if (!container || pagination.total_pages <= 1) {
        if (container) container.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    html += `
        <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="${callback}(${pagination.current_page - 1}); return false;">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            html += `
                <li class="page-item ${pagination.current_page === i ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="${callback}(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="${callback}(${pagination.current_page + 1}); return false;">Next</a>
        </li>
    `;
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

/**
 * Update profile
 */
async function updateProfile(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('action', 'update_profile');
    
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
    
    const result = await apiRequest('auth.php', 'POST', formData);
    
    if (result.success) {
        showToast('Profile updated successfully!', 'success');
    }
}

/**
 * Add skill tag
 */
function addSkill() {
    const input = document.querySelector('#skill-input');
    const skill = input.value.trim();
    
    if (skill) {
        const list = document.querySelector('#skills-list');
        const tag = document.createElement('span');
        tag.className = 'skill-tag badge bg-primary me-2 mb-2';
        tag.innerHTML = `${escapeHtml(skill)} <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>`;
        list.appendChild(tag);
        input.value = '';
    }
}

/**
 * Add interest tag
 */
function addInterest() {
    const input = document.querySelector('#interest-input');
    const interest = input.value.trim();
    
    if (interest) {
        const list = document.querySelector('#interests-list');
        const tag = document.createElement('span');
        tag.className = 'skill-tag badge bg-secondary me-2 mb-2';
        tag.innerHTML = `${escapeHtml(interest)} <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>`;
        list.appendChild(tag);
        input.value = '';
    }
}

/**
 * Upload CV
 */
async function uploadCV(input) {
    const file = input.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('action', 'upload_cv');
    formData.append('cv', file);
    
    const result = await apiRequest('auth.php', 'POST', formData);
    
    if (result.success) {
        showToast('CV uploaded successfully!', 'success');
        document.querySelector('#cv-status').innerHTML = `
            <i class="bi bi-check-circle text-success me-2"></i>
            CV uploaded: ${escapeHtml(file.name)}
        `;
    }
}

// Helper functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatSalary(min, max) {
    if (!min && !max) return 'Competitive';
    const format = (n) => '$' + n.toLocaleString();
    if (min && max) return format(min) + ' - ' + format(max);
    return min ? 'From ' + format(min) : 'Up to ' + format(max);
}

function formatJobType(type) {
    return type.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

function formatExperienceLevel(level) {
    const labels = {
        entry: 'Entry Level',
        junior: 'Junior',
        mid: 'Mid Level',
        senior: 'Senior',
        lead: 'Lead',
        executive: 'Executive'
    };
    return labels[level] || level;
}

function formatRemote(remote) {
    const labels = {
        'on-site': '🏢 On-site',
        'remote': '🏠 Remote',
        'hybrid': '🔄 Hybrid'
    };
    return labels[remote] || remote;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function getStatusBadgeClass(status) {
    const classes = {
        pending: 'bg-warning',
        reviewed: 'bg-info',
        shortlisted: 'bg-primary',
        interview: 'bg-primary',
        offered: 'bg-success',
        hired: 'bg-success',
        rejected: 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
}
