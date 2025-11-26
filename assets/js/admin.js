/**
 * Admin Panel JavaScript
 * Handles admin operations via AJAX
 */

const ADMIN_API = '../api/admin.php';

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
                    <td>${escapeHtml(user.first_name + ' ' + user.last_name)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>${escapeHtml(user.phone || '-')}</td>
                    <td><span class="badge ${user.role === 'admin' ? 'bg-danger' : 'bg-primary'}">${user.role}</span></td>
                    <td><span class="badge ${user.is_active ? 'bg-success' : 'bg-secondary'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>${user.last_login ? formatDate(user.last_login) : 'Never'}</td>
                    <td>
                        <a href="user.php?id=${user.id}" class="btn btn-sm btn-outline-primary">View</a>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleUserStatus(${user.id}, ${!user.is_active})">
                            ${user.is_active ? 'Deactivate' : 'Activate'}
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
                    <td>
                        <a href="user.php?id=${app.user_id}">${escapeHtml(app.first_name + ' ' + app.last_name)}</a>
                        <br><small class="text-muted">${escapeHtml(app.email)}</small>
                    </td>
                    <td>${escapeHtml(app.job_title)}</td>
                    <td>${escapeHtml(app.company_name)}</td>
                    <td><span class="badge ${getStatusBadgeClass(app.status)}">${escapeHtml(app.status)}</span></td>
                    <td>${app.match_score ? Math.round(app.match_score) + '%' : '-'}</td>
                    <td>${formatDate(app.created_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="showApplicationModal(${app.id})">Review</button>
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
                    <td>
                        <a href="user.php?id=${apt.user_id}">${escapeHtml(apt.first_name + ' ' + apt.last_name)}</a>
                    </td>
                    <td>${escapeHtml(apt.title)}</td>
                    <td><span class="badge bg-info">${escapeHtml(apt.type)}</span></td>
                    <td>${formatDateTime(apt.scheduled_at)}</td>
                    <td>${apt.duration} min</td>
                    <td><span class="badge ${getAppointmentStatusClass(apt.status)}">${escapeHtml(apt.status)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editAppointment(${apt.id})">Edit</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(${apt.id})">Cancel</button>
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
    const params = new URLSearchParams({
        action: 'list',
        page: page,
        limit: 20
    });
    
    const container = document.querySelector('#jobs-container');
    if (!container) return;
    
    container.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    const result = await apiRequest('../api/jobs.php?' + params.toString());
    
    if (result.success) {
        if (result.jobs.length === 0) {
            container.innerHTML = '<tr><td colspan="7" class="text-center py-4">No jobs found</td></tr>';
        } else {
            container.innerHTML = result.jobs.map(job => `
                <tr>
                    <td>${escapeHtml(job.title)}</td>
                    <td>${escapeHtml(job.company_name)}</td>
                    <td>${escapeHtml(job.location)}</td>
                    <td><span class="badge bg-${job.status === 'active' ? 'success' : 'secondary'}">${job.status}</span></td>
                    <td>${job.views}</td>
                    <td>${job.applications_count}</td>
                    <td>
                        <a href="../pages/job.php?id=${job.id}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                        <button class="btn btn-sm btn-outline-secondary" onclick="editJob(${job.id})">Edit</button>
                    </td>
                </tr>
            `).join('');
        }
        
        renderPagination(result.pagination, 'loadAdminJobs');
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
