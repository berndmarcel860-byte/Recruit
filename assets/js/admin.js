/**
 * Admin Panel JavaScript
 * Handles admin operations via AJAX
 */

// Ensure app.js is loaded before this file
if (typeof BASE_PATH === 'undefined') {
    throw new Error('app.js must be loaded before admin.js');
}

const ADMIN_API = BASE_PATH + 'api/admin.php';
const JOBS_API = BASE_PATH + 'api/jobs.php';
const COMPANIES_API = BASE_PATH + 'api/companies.php';

/**
 * Load dashboard data
 */
async function loadDashboard() {
    const result = await apiRequest(ADMIN_API + '?action=dashboard');
    if (result.success) {
        // Update stats and recent applications if needed
        console.log('Dashboard loaded', result);
    }
}

/**
 * Review application modal
 */
function reviewApplication(id) {
    window.location.href = 'applications.php?review=' + id;
}

/**
 * Update application status
 */
async function updateApplicationStatus(id, status, notes = '') {
    const result = await apiRequest(ADMIN_API, 'POST', {
        action: 'update_application',
        id: id,
        status: status,
        notes: notes
    });
    
    if (result.success) {
        showToast('Application updated successfully', 'success');
        location.reload();
    }
}

/**
 * Load users list
 */
async function loadUsers(page = 1) {
    const search = document.querySelector('#search')?.value || '';
    const role = document.querySelector('#role')?.value || '';
    
    const params = new URLSearchParams({
        action: 'get_users',
        page: page,
        search: search,
        role: role
    });
    
    const container = document.querySelector('#users-container');
    if (!container) return;
    
    container.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    const result = await apiRequest(ADMIN_API + '?' + params.toString());
    
    if (result.success) {
        if (result.users.length === 0) {
            container.innerHTML = '<tr><td colspan="7" class="text-center py-4">No users found</td></tr>';
        } else {
            container.innerHTML = result.users.map(user => `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary text-white me-3">${escapeHtml((user.first_name || '').charAt(0) + (user.last_name || '').charAt(0))}</div>
                            <div>
                                <div class="fw-medium">${escapeHtml(user.first_name + ' ' + user.last_name)}</div>
                                <small class="text-muted">${escapeHtml(user.email)}</small>
                            </div>
                        </div>
                    </td>
                    <td>${escapeHtml(user.phone || '-')}</td>
                    <td>${escapeHtml(user.city || '-')}</td>
                    <td><span class="badge ${user.role === 'admin' ? 'bg-danger' : 'bg-primary'}">${user.role}</span></td>
                    <td><span class="badge ${user.is_active ? 'bg-success' : 'bg-secondary'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>${user.last_login ? formatDate(user.last_login) : 'Never'}</td>
                    <td class="text-end pe-4">
                        <a href="user.php?id=${user.id}" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></a>
                        <button class="btn btn-sm btn-outline-${user.is_active ? 'warning' : 'success'}" onclick="toggleUserStatus(${user.id}, ${!user.is_active})" title="${user.is_active ? 'Deactivate' : 'Activate'}">
                            <i class="bi bi-${user.is_active ? 'pause' : 'play'}"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        renderPagination(result.pagination, 'loadUsers');
    }
}

/**
 * Toggle user status
 */
async function toggleUserStatus(id, isActive) {
    const result = await apiRequest(ADMIN_API, 'POST', {
        action: 'update_user',
        id: id,
        is_active: isActive ? 1 : 0
    });
    
    if (result.success) {
        showToast('User status updated', 'success');
        loadUsers();
    }
}

/**
 * Load applications list
 */
async function loadApplications(page = 1) {
    const status = document.querySelector('#status')?.value || '';
    
    const params = new URLSearchParams({
        action: 'get_applications',
        page: page,
        status: status
    });
    
    const container = document.querySelector('#applications-container');
    if (!container) return;
    
    container.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    const result = await apiRequest(ADMIN_API + '?' + params.toString());
    
    if (result.success) {
        if (result.applications.length === 0) {
            container.innerHTML = '<tr><td colspan="7" class="text-center py-4">No applications found</td></tr>';
        } else {
            container.innerHTML = result.applications.map(app => `
                <tr>
                    <td class="ps-4">
                        <a href="user.php?id=${app.user_id}" class="text-decoration-none">
                            <div class="fw-medium">${escapeHtml(app.first_name + ' ' + app.last_name)}</div>
                        </a>
                        <small class="text-muted">${escapeHtml(app.email)}</small>
                    </td>
                    <td>${escapeHtml(app.job_title)}</td>
                    <td>${escapeHtml(app.company_name)}</td>
                    <td><span class="badge ${getStatusBadgeClass(app.status)}">${escapeHtml(app.status)}</span></td>
                    <td>
                        ${app.match_score ? `
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar ${app.match_score >= 70 ? 'bg-success' : app.match_score >= 40 ? 'bg-warning' : 'bg-danger'}" style="width: ${app.match_score}%"></div>
                                </div>
                                <small>${Math.round(app.match_score)}%</small>
                            </div>
                        ` : '-'}
                    </td>
                    <td>${formatDate(app.created_at)}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary" onclick="showApplicationModal(${app.id})">
                            <i class="bi bi-check2-square me-1"></i>Review
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        renderPagination(result.pagination, 'loadApplications');
    }
}

/**
 * Show application review modal
 */
function showApplicationModal(id) {
    document.getElementById('application-id').value = id;
    const modal = new bootstrap.Modal(document.getElementById('applicationModal'));
    modal.show();
}

/**
 * Submit application review
 */
async function submitApplicationReview() {
    const id = document.getElementById('application-id').value;
    const status = document.getElementById('app-status').value;
    const notes = document.getElementById('app-notes').value;
    
    await updateApplicationStatus(id, status, notes);
}

/**
 * Offer job to user
 */
async function offerJob(userId, jobId, message = '') {
    const result = await apiRequest(ADMIN_API, 'POST', {
        action: 'offer_job',
        user_id: userId,
        job_id: jobId,
        message: message
    });
    
    if (result.success) {
        showToast('Job offered successfully', 'success');
    }
}

/**
 * Load appointments
 */
async function loadAppointments(page = 1) {
    const type = document.querySelector('#type')?.value || '';
    const status = document.querySelector('#status')?.value || '';
    
    const params = new URLSearchParams({
        action: 'get_appointments',
        page: page,
        type: type,
        status: status
    });
    
    const container = document.querySelector('#appointments-container');
    if (!container) return;
    
    container.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    const result = await apiRequest(ADMIN_API + '?' + params.toString());
    
    if (result.success) {
        if (result.appointments.length === 0) {
            container.innerHTML = '<tr><td colspan="7" class="text-center py-4">No appointments found</td></tr>';
        } else {
            container.innerHTML = result.appointments.map(apt => `
                <tr>
                    <td class="ps-4">
                        <a href="user.php?id=${apt.user_id}">${escapeHtml(apt.first_name + ' ' + apt.last_name)}</a>
                    </td>
                    <td>${escapeHtml(apt.title)}</td>
                    <td><span class="badge bg-info">${escapeHtml(apt.type)}</span></td>
                    <td>${formatDateTime(apt.scheduled_at)}</td>
                    <td>${apt.duration} min</td>
                    <td><span class="badge ${getAppointmentStatusClass(apt.status)}">${escapeHtml(apt.status)}</span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editAppointment(${apt.id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(${apt.id})"><i class="bi bi-x"></i></button>
                    </td>
                </tr>
            `).join('');
        }
        
        renderPagination(result.pagination, 'loadAppointments');
    }
}

/**
 * Create appointment
 */
async function createAppointment(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('action', 'create_appointment');
    
    const result = await apiRequest(ADMIN_API, 'POST', formData);
    
    if (result.success) {
        showToast('Appointment created successfully', 'success');
        bootstrap.Modal.getInstance(document.getElementById('appointmentModal')).hide();
        loadAppointments();
    }
}

/**
 * Cancel appointment
 */
async function cancelAppointment(id) {
    if (!confirm('Are you sure you want to cancel this appointment?')) return;
    
    const result = await apiRequest(ADMIN_API, 'POST', {
        action: 'delete_appointment',
        id: id
    });
    
    if (result.success) {
        showToast('Appointment cancelled', 'success');
        loadAppointments();
    }
}

/**
 * Send email to user
 */
async function sendEmail(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('action', 'send_email');
    
    const result = await apiRequest(ADMIN_API, 'POST', formData);
    
    if (result.success) {
        showToast('Email sent successfully', 'success');
        bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
        form.reset();
    }
}

/**
 * Load jobs for admin
 */
async function loadAdminJobs(page = 1) {
    const search = document.querySelector('#search')?.value || '';
    const companyId = document.querySelector('#company_filter')?.value || '';
    const type = document.querySelector('#type_filter')?.value || '';
    const status = document.querySelector('#status_filter')?.value || '';
    
    const params = new URLSearchParams({
        action: 'list',
        page: page,
        limit: 20,
        search: search
    });
    
    if (companyId) params.append('company_id', companyId);
    if (type) params.append('type', type);
    if (status) params.append('status', status);
    
    const container = document.querySelector('#jobs-container');
    if (!container) return;
    
    container.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    const result = await apiRequest(JOBS_API + '?' + params.toString());
    
    if (result.success) {
        if (result.jobs.length === 0) {
            container.innerHTML = '<tr><td colspan="8" class="text-center py-4">No jobs found</td></tr>';
        } else {
            container.innerHTML = result.jobs.map(job => `
                <tr>
                    <td class="ps-4">
                        <div class="fw-medium">${escapeHtml(job.title)}</div>
                        <small class="text-muted">${escapeHtml(job.department || '')}</small>
                    </td>
                    <td>${escapeHtml(job.company_name)}</td>
                    <td>${escapeHtml(job.location || 'N/A')}</td>
                    <td><span class="badge bg-primary-subtle text-primary">${formatJobType(job.type)}</span></td>
                    <td><span class="badge bg-${job.status === 'active' ? 'success' : job.status === 'paused' ? 'warning' : 'secondary'}">${job.status}</span></td>
                    <td><i class="bi bi-eye me-1"></i>${job.views || 0}</td>
                    <td><i class="bi bi-file-text me-1"></i>${job.applications_count || 0}</td>
                    <td class="text-end pe-4">
                        <a href="../pages/job.php?id=${job.id}" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="View"><i class="bi bi-eye"></i></a>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editJob(${job.id})" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-${job.status === 'active' ? 'warning' : 'success'}" onclick="toggleJobStatus(${job.id}, '${job.status === 'active' ? 'paused' : 'active'}')" title="${job.status === 'active' ? 'Pause' : 'Activate'}">
                            <i class="bi bi-${job.status === 'active' ? 'pause' : 'play'}"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        renderPagination(result.pagination, 'loadAdminJobs');
    }
}

/**
 * Save job (create or update)
 */
async function saveJob(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const id = document.getElementById('job-id').value;
    formData.append('action', id ? 'update_job' : 'create_job');
    
    // Collect skills
    const skills = [];
    document.querySelectorAll('#job-skills-list .badge').forEach(tag => {
        skills.push(tag.getAttribute('data-skill'));
    });
    formData.append('skills', JSON.stringify(skills));
    
    // Collect benefits
    const benefits = [];
    document.querySelectorAll('#job-benefits-list .badge').forEach(tag => {
        benefits.push(tag.getAttribute('data-benefit'));
    });
    formData.append('benefits', JSON.stringify(benefits));
    
    const result = await apiRequest(ADMIN_API, 'POST', formData);
    
    if (result.success) {
        showToast(id ? 'Job updated successfully' : 'Job created successfully', 'success');
        bootstrap.Modal.getInstance(document.getElementById('jobModal')).hide();
        loadAdminJobs();
    }
}

/**
 * Toggle job status
 */
async function toggleJobStatus(id, newStatus) {
    const result = await apiRequest(ADMIN_API, 'POST', {
        action: 'update_job',
        id: id,
        status: newStatus
    });
    
    if (result.success) {
        showToast('Job status updated', 'success');
        loadAdminJobs();
    }
}

/**
 * Edit job - load job data into modal
 */
async function editJob(id) {
    const result = await apiRequest(JOBS_API + '?action=get&id=' + id);
    
    if (result.success && result.job) {
        const job = result.job;
        
        document.getElementById('job-id').value = job.id;
        document.getElementById('job-title').value = job.title || '';
        document.getElementById('job-company').value = job.company_id || '';
        document.getElementById('job-description').value = job.description || '';
        document.getElementById('job-location').value = job.location || '';
        document.getElementById('job-type').value = job.type || 'full-time';
        document.getElementById('job-remote').value = job.remote || 'on-site';
        document.getElementById('job-department').value = job.department || '';
        document.getElementById('job-experience').value = job.experience_level || 'mid';
        document.getElementById('job-deadline').value = job.deadline || '';
        document.getElementById('job-salary-min').value = job.salary_min || '';
        document.getElementById('job-salary-max').value = job.salary_max || '';
        document.getElementById('job-status').value = job.status || 'active';
        
        // Load skills
        const skillsList = document.getElementById('job-skills-list');
        skillsList.innerHTML = '';
        const skills = Array.isArray(job.skills) ? job.skills : JSON.parse(job.skills || '[]');
        skills.forEach(skill => {
            const tag = document.createElement('span');
            tag.className = 'badge bg-primary me-2 mb-2';
            tag.setAttribute('data-skill', skill);
            tag.innerHTML = `${escapeHtml(skill)} <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>`;
            skillsList.appendChild(tag);
        });
        
        // Load benefits
        const benefitsList = document.getElementById('job-benefits-list');
        benefitsList.innerHTML = '';
        const benefits = Array.isArray(job.benefits) ? job.benefits : JSON.parse(job.benefits || '[]');
        benefits.forEach(benefit => {
            const tag = document.createElement('span');
            tag.className = 'badge bg-success me-2 mb-2';
            tag.setAttribute('data-benefit', benefit);
            tag.innerHTML = `${escapeHtml(benefit)} <span style="cursor: pointer" onclick="this.parentElement.remove()">×</span>`;
            benefitsList.appendChild(tag);
        });
        
        document.getElementById('jobModalLabel').innerHTML = '<i class="bi bi-briefcase me-2"></i>Edit Job';
        const modal = new bootstrap.Modal(document.getElementById('jobModal'));
        modal.show();
    }
}

/**
 * Load companies for admin
 */
async function loadCompanies(page = 1) {
    const search = document.querySelector('#search')?.value || '';
    const industry = document.querySelector('#industry')?.value || '';
    const status = document.querySelector('#status')?.value || '';
    
    const params = new URLSearchParams({
        action: 'get_companies',
        page: page,
        limit: 20,
        search: search
    });
    
    if (industry) params.append('industry', industry);
    if (status !== '') params.append('is_active', status);
    
    const container = document.querySelector('#companies-container');
    if (!container) return;
    
    container.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    const result = await apiRequest(ADMIN_API + '?' + params.toString());
    
    if (result.success) {
        if (result.companies.length === 0) {
            container.innerHTML = '<tr><td colspan="7" class="text-center py-4">No companies found</td></tr>';
        } else {
            container.innerHTML = result.companies.map(company => `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="company-logo bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                ${escapeHtml((company.name || 'C').charAt(0))}
                            </div>
                            <div>
                                <div class="fw-medium">${escapeHtml(company.name)}</div>
                                <small class="text-muted">${escapeHtml(company.website || '')}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark">${escapeHtml(company.industry || 'N/A')}</span></td>
                    <td>${escapeHtml(company.size || 'N/A')}</td>
                    <td>${escapeHtml(company.headquarters || 'N/A')}</td>
                    <td>${company.job_count || 0} jobs</td>
                    <td><span class="badge ${company.is_active ? 'bg-success' : 'bg-secondary'}">${company.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editCompany(${company.id})" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-${company.is_active ? 'warning' : 'success'}" onclick="toggleCompanyStatus(${company.id}, ${!company.is_active})" title="${company.is_active ? 'Deactivate' : 'Activate'}">
                            <i class="bi bi-${company.is_active ? 'pause' : 'play'}"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        renderPagination(result.pagination, 'loadCompanies');
    }
}

/**
 * Save company (create or update)
 */
async function saveCompany(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const id = document.getElementById('company-id').value;
    formData.append('action', id ? 'update_company' : 'create_company');
    
    const result = await apiRequest(ADMIN_API, 'POST', formData);
    
    if (result.success) {
        showToast(id ? 'Company updated successfully' : 'Company created successfully', 'success');
        bootstrap.Modal.getInstance(document.getElementById('companyModal')).hide();
        loadCompanies();
    }
}

/**
 * Edit company - load company data into modal
 */
async function editCompany(id) {
    const result = await apiRequest(ADMIN_API + '?action=get_company&id=' + id);
    
    if (result.success && result.company) {
        const company = result.company;
        
        document.getElementById('company-id').value = company.id;
        document.getElementById('company-name').value = company.name || '';
        document.getElementById('company-industry').value = company.industry || '';
        document.getElementById('company-description').value = company.description || '';
        document.getElementById('company-website').value = company.website || '';
        document.getElementById('company-email').value = company.email || '';
        document.getElementById('company-size').value = company.size || '1-10';
        document.getElementById('company-headquarters').value = company.headquarters || '';
        document.getElementById('company-founded').value = company.founded_year || '';
        document.getElementById('company-phone').value = company.phone || '';
        document.getElementById('company-status').value = company.is_active ? '1' : '0';
        
        document.getElementById('companyModalLabel').innerHTML = '<i class="bi bi-building me-2"></i>Edit Company';
        const modal = new bootstrap.Modal(document.getElementById('companyModal'));
        modal.show();
    }
}

/**
 * Toggle company status
 */
async function toggleCompanyStatus(id, isActive) {
    const result = await apiRequest(ADMIN_API, 'POST', {
        action: 'update_company',
        id: id,
        is_active: isActive ? 1 : 0
    });
    
    if (result.success) {
        showToast('Company status updated', 'success');
        loadCompanies();
    }
}

// Helper functions
function formatDateTime(dateStr) {
    return new Date(dateStr).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getAppointmentStatusClass(status) {
    const classes = {
        scheduled: 'bg-warning',
        confirmed: 'bg-primary',
        completed: 'bg-success',
        cancelled: 'bg-danger',
        rescheduled: 'bg-info'
    };
    return classes[status] || 'bg-secondary';
}

function formatJobType(type) {
    return type.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}
