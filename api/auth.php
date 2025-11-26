<?php
/**
 * Authentication API
 * Handles login, register, logout via AJAX
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Email and password are required'], 400);
        }
        
        $result = loginUser($email, $password);
        jsonResponse($result, $result['success'] ? 200 : 401);
        break;
        
    case 'register':
        $required = ['email', 'password', 'first_name', 'last_name'];
        $data = [];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                jsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
            }
            $data[$field] = $field === 'password' ? $_POST[$field] : sanitize($_POST[$field]);
        }
        
        // Optional fields
        $optional = ['phone', 'address', 'city', 'country', 'bio'];
        foreach ($optional as $field) {
            if (!empty($_POST[$field])) {
                $data[$field] = sanitize($_POST[$field]);
            }
        }
        
        // JSON fields
        $jsonFields = ['skills', 'interests', 'experience', 'education'];
        foreach ($jsonFields as $field) {
            if (!empty($_POST[$field])) {
                $data[$field] = is_array($_POST[$field]) ? $_POST[$field] : json_decode($_POST[$field], true);
            }
        }
        
        if (strlen($data['password']) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
        }
        
        $result = registerUser($data);
        jsonResponse($result, $result['success'] ? 201 : 400);
        break;
        
    case 'logout':
        $result = logoutUser();
        jsonResponse($result);
        break;
        
    case 'profile':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        $user = getCurrentUser();
        jsonResponse(['success' => true, 'user' => $user]);
        break;
        
    case 'update_profile':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $data = [];
        $allowedFields = ['first_name', 'last_name', 'phone', 'address', 'city', 'country', 'bio'];
        
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = sanitize($_POST[$field]);
            }
        }
        
        // JSON fields
        $jsonFields = ['skills', 'interests', 'experience', 'education'];
        foreach ($jsonFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = is_array($_POST[$field]) ? $_POST[$field] : json_decode($_POST[$field], true);
            }
        }
        
        $result = updateUserProfile($_SESSION['user_id'], $data);
        jsonResponse($result);
        break;
        
    case 'upload_cv':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        if (!isset($_FILES['cv'])) {
            jsonResponse(['success' => false, 'message' => 'No file uploaded'], 400);
        }
        
        $result = uploadCV($_FILES['cv'], $_SESSION['user_id']);
        
        if ($result['valid']) {
            $db = Database::getInstance();
            $db->update('users', ['cv_path' => $result['path']], 'id = :id', ['id' => $_SESSION['user_id']]);
            jsonResponse(['success' => true, 'message' => 'CV uploaded successfully', 'path' => $result['path']]);
        } else {
            jsonResponse(['success' => false, 'message' => $result['message']], 400);
        }
        break;
        
    case 'change_password':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            jsonResponse(['success' => false, 'message' => 'Both passwords are required'], 400);
        }
        
        if (strlen($newPassword) < 6) {
            jsonResponse(['success' => false, 'message' => 'New password must be at least 6 characters'], 400);
        }
        
        $result = changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
        jsonResponse($result);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
