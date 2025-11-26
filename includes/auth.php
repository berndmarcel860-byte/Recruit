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
    
    // Set defaults
    $data['role'] = 'user';
    $data['is_active'] = 1;
    
    try {
        $userId = $db->insert('users', $data);
        $user = getUserById($userId);
        
        // Log user in
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $user['role'];
        
        return ['success' => true, 'user' => $user, 'message' => 'Registration successful'];
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
    
    // Update last login
    $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    
    unset($user['password']);
    
    return ['success' => true, 'user' => $user, 'message' => 'Login successful'];
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
