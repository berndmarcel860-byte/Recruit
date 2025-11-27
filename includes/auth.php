<?php
/**
 * Authentication Functions
 * Handles user login, registration, and session management
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Register a new user
 */
function registerUser($data) {
    $db = Database::getInstance();
    
    // Check if email already exists
    $existing = $db->fetch("SELECT id FROM users WHERE email = :email", ['email' => $data['email']]);
    if ($existing) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
    
    // Process JSON fields
    $jsonFields = ['skills', 'interests', 'experience', 'education'];
    foreach ($jsonFields as $field) {
        if (isset($data[$field]) && is_array($data[$field])) {
            $data[$field] = json_encode($data[$field]);
        } elseif (!isset($data[$field])) {
            $data[$field] = '[]';
        }
    }
    
    // Set defaults - new users start with pending_onboarding status
    $data['role'] = 'user';
    $data['account_status'] = 'pending_onboarding';
    $data['is_active'] = 1;
    
    try {
        $userId = $db->insert('users', $data);
        $user = getUserById($userId);
        
        // Create welcome notification
        $db->insert('notifications', [
            'user_id' => $userId,
            'type' => 'onboarding',
            'title' => 'Welcome to ' . APP_NAME . '!',
            'message' => 'Please schedule your onboarding appointment to get started with your job search.',
            'link' => 'pages/onboarding.php',
            'priority' => 'high'
        ]);
        
        // Log user in
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['account_status'] = $user['account_status'];
        
        return ['success' => true, 'user' => $user, 'message' => 'Registration successful! Please schedule your onboarding appointment.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Login user
 */
function loginUser($email, $password) {
    $db = Database::getInstance();
    
    $user = $db->fetch("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    if (!$user['is_active']) {
        return ['success' => false, 'message' => 'Account is deactivated'];
    }
    
    if ($user['account_status'] === 'suspended') {
        return ['success' => false, 'message' => 'Your account has been suspended. Please contact support.'];
    }
    
    if ($user['account_status'] === 'rejected') {
        return ['success' => false, 'message' => 'Your application was not approved. Please contact support for more information.'];
    }
    
    // Update last login
    $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['account_status'] = $user['account_status'];
    
    unset($user['password']);
    
    // Determine redirect based on account status
    $redirect = 'pages/dashboard.php';
    if ($user['role'] === 'admin' || $user['role'] === 'moderator') {
        $redirect = 'admin/index.php';
    } elseif ($user['account_status'] === 'pending_onboarding') {
        $redirect = 'pages/onboarding.php';
    }
    
    return ['success' => true, 'user' => $user, 'message' => 'Login successful', 'redirect' => $redirect];
}

/**
 * Logout user
 */
function logoutUser() {
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if current user is moderator or admin
 */
function isModerator() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'moderator']);
}

/**
 * Check if user has completed onboarding
 */
function hasCompletedOnboarding() {
    return isset($_SESSION['account_status']) && $_SESSION['account_status'] === 'active';
}

/**
 * Get user's account status
 */
function getAccountStatus() {
    return $_SESSION['account_status'] ?? 'pending_onboarding';
}

/**
 * Get current logged in user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return getUserById($_SESSION['user_id']);
}

/**
 * Get user by ID
 */
function getUserById($id) {
    $db = Database::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $id]);
    if ($user) {
        unset($user['password']);
        // Parse JSON fields
        $jsonFields = ['skills', 'interests', 'experience', 'education'];
        foreach ($jsonFields as $field) {
            if (isset($user[$field])) {
                $user[$field] = json_decode($user[$field], true) ?? [];
            }
        }
    }
    return $user;
}

/**
 * Update user's account status
 */
function updateAccountStatus($userId, $status, $notes = null, $adminId = null) {
    $db = Database::getInstance();
    
    $data = ['account_status' => $status];
    
    if ($status === 'active') {
        $data['activated_at'] = date('Y-m-d H:i:s');
        $data['activated_by'] = $adminId;
    }
    
    if ($notes) {
        $data['onboarding_notes'] = $notes;
    }
    
    $db->update('users', $data, 'id = :id', ['id' => $userId]);
    
    // Update session if updating current user
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
        $_SESSION['account_status'] = $status;
    }
    
    return true;
}

/**
 * Update user profile
 */
function updateUserProfile($userId, $data) {
    $db = Database::getInstance();
    
    // Process JSON fields
    $jsonFields = ['skills', 'interests', 'experience', 'education'];
    foreach ($jsonFields as $field) {
        if (isset($data[$field]) && is_array($data[$field])) {
            $data[$field] = json_encode($data[$field]);
        }
    }
    
    // Remove fields that shouldn't be updated
    unset($data['id'], $data['email'], $data['password'], $data['role'], $data['created_at']);
    
    try {
        $db->update('users', $data, 'id = :id', ['id' => $userId]);
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
    }
}

/**
 * Change user password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $db = Database::getInstance();
    
    $user = $db->fetch("SELECT password FROM users WHERE id = :id", ['id' => $userId]);
    
    if (!password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
    $db->update('users', ['password' => $hashedPassword], 'id = :id', ['id' => $userId]);
    
    return ['success' => true, 'message' => 'Password changed successfully'];
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/pages/login.php');
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: ' . APP_URL . '/pages/dashboard.php');
        exit;
    }
}

/**
 * Require moderator or admin role
 */
function requireModerator() {
    requireAuth();
    if (!isModerator()) {
        header('Location: ' . APP_URL . '/pages/dashboard.php');
        exit;
    }
}

/**
 * Require completed onboarding for accessing job features
 */
function requireActiveAccount() {
    requireAuth();
    if (!hasCompletedOnboarding()) {
        header('Location: ' . APP_URL . '/pages/onboarding.php');
        exit;
    }
}

/**
 * Create notification for user
 */
function createNotification($userId, $type, $title, $message, $link = null, $priority = 'normal', $relatedId = null, $relatedType = null) {
    $db = Database::getInstance();
    return $db->insert('notifications', [
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'link' => $link,
        'priority' => $priority,
        'related_id' => $relatedId,
        'related_type' => $relatedType
    ]);
}

/**
 * Get unread notification count for user
 */
function getUnreadNotificationCount($userId) {
    $db = Database::getInstance();
    return $db->count('notifications', 'user_id = :id AND is_read = 0', ['id' => $userId]);
}
