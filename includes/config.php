<?php
/**
 * Database Configuration
 * Job Recruitment Platform
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'recruit_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Recruit');
define('APP_URL', 'http://localhost/recruit');
define('APP_VERSION', '1.0.0');

// Meeting/Video call settings
define('MEETING_PROVIDER', 'zoom'); // Options: zoom, teams, google_meet
define('ZOOM_BASE_URL', 'https://zoom.us/j/');
define('TEAMS_BASE_URL', 'https://teams.microsoft.com/l/meetup-join/');
define('GOOGLE_MEET_BASE_URL', 'https://meet.google.com/');

// Email settings (configure for your SMTP server)
define('MAIL_FROM_NAME', 'Recruit Platform');
define('MAIL_FROM_ADDRESS', 'noreply@recruit.com');
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls');

// Support contact
define('SUPPORT_EMAIL', 'support@recruit.com');
define('SUPPORT_PHONE', '+1 (555) 000-0000');

// Session settings
define('SESSION_LIFETIME', 7200); // 2 hours

// File upload settings
define('MAX_CV_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_CV_TYPES', ['pdf', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');

// Security
define('JWT_SECRET', 'recruit_jwt_secret_key_2024_change_in_production');
define('HASH_COST', 12);

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
