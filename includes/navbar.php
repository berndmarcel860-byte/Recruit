<?php
/**
 * Navigation Bar Include
 */

// Get unread counts if logged in
$unreadMessages = 0;
$unreadNotifications = 0;
if (isLoggedIn()) {
    $unreadMessages = getUnreadMessageCount($_SESSION['user_id']);
    $unreadNotifications = getUnreadNotificationCount($_SESSION['user_id']);
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="<?= isLoggedIn() ? (isAdmin() ? '../admin/index.php' : 'dashboard.php') : '../index.php' ?>">
            <i class="bi bi-briefcase-fill me-2"></i><?= APP_NAME ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="jobs.php"><i class="bi bi-briefcase me-1"></i>Find Jobs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="companies.php"><i class="bi bi-building me-1"></i>Companies</a>
                </li>
                <?php if (isLoggedIn() && !isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="recommendations.php"><i class="bi bi-stars me-1"></i>AI Recommendations</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <!-- Messages -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="messages.php" title="Messages">
                            <i class="bi bi-chat-dots fs-5"></i>
                            <?php if ($unreadMessages > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $unreadMessages > 9 ? '9+' : $unreadMessages ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" title="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($unreadNotifications > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $unreadNotifications > 9 ? '9+' : $unreadNotifications ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span><strong>Notifications</strong></span>
                                <?php if ($unreadNotifications > 0): ?>
                                <a href="#" class="small text-primary" onclick="markAllNotificationsRead()">Mark all read</a>
                                <?php endif; ?>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li id="notifications-container" class="px-2">
                                <div class="text-center text-muted py-3">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Loading...
                                </div>
                            </li>
                        </ul>
                    </li>
                    
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/index.php"><i class="bi bi-speedometer2 me-1"></i>Admin</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-grid me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="applications.php"><i class="bi bi-file-text me-1"></i>Applications</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars(getCurrentUser()['first_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="messages.php"><i class="bi bi-chat-dots me-2"></i>Messages <?php if ($unreadMessages > 0): ?><span class="badge bg-danger"><?= $unreadMessages ?></span><?php endif; ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="logout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="register.php">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (isLoggedIn()): ?>
<script>
// Load notifications when dropdown is opened
document.addEventListener('DOMContentLoaded', function() {
    const notificationDropdown = document.querySelector('[data-bs-toggle="dropdown"][title="Notifications"]');
    if (notificationDropdown) {
        notificationDropdown.addEventListener('show.bs.dropdown', loadNotifications);
    }
});

async function loadNotifications() {
    const container = document.getElementById('notifications-container');
    
    try {
        const result = await apiRequest(API_BASE + '/api/auth.php?action=get_notifications');
        
        if (result.success && result.notifications.length > 0) {
            container.innerHTML = result.notifications.slice(0, 5).map(n => `
                <div class="p-2 border-bottom ${n.is_read ? '' : 'bg-light'}">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-${n.type === 'message' ? 'chat' : (n.type === 'interview' ? 'calendar' : 'bell')} text-${n.priority === 'urgent' ? 'danger' : 'primary'} me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="small fw-medium">${escapeHtml(n.title)}</div>
                            <div class="small text-muted">${escapeHtml(n.message.substring(0, 60))}...</div>
                            <div class="small text-muted">${timeAgo(n.created_at)}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-bell-slash"></i> No notifications</div>';
        }
    } catch (error) {
        container.innerHTML = '<div class="text-center text-muted py-3">Unable to load notifications</div>';
    }
}

async function markAllNotificationsRead() {
    const formData = new FormData();
    formData.append('action', 'mark_notifications_read');
    
    await apiRequest(API_BASE + '/api/auth.php', 'POST', formData);
    location.reload();
}
</script>
<?php endif; ?>
