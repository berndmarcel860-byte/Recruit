-- =====================================================
-- Job Recruitment Platform Database Schema
-- MySQL Database Structure
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS recruit_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recruit_db;

-- =====================================================
-- USERS TABLE
-- Stores all user information including job seekers and admins
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    bio TEXT,
    skills JSON COMMENT 'Array of skills',
    interests JSON COMMENT 'Array of job interests/industries',
    experience JSON COMMENT 'Array of work experience objects',
    education JSON COMMENT 'Array of education objects',
    cv_path VARCHAR(500) COMMENT 'Path to uploaded CV file',
    profile_picture VARCHAR(500),
    role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
    account_status ENUM('pending_onboarding', 'onboarding_scheduled', 'active', 'suspended', 'rejected') DEFAULT 'pending_onboarding' COMMENT 'Account workflow status',
    is_active TINYINT(1) DEFAULT 1,
    onboarding_notes TEXT COMMENT 'Notes from onboarding appointment',
    activated_at DATETIME COMMENT 'When account was activated after onboarding',
    activated_by INT COMMENT 'Admin who activated the account',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active),
    INDEX idx_account_status (account_status)
) ENGINE=InnoDB;

-- =====================================================
-- COMPANIES TABLE
-- Stores company information
-- =====================================================
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    industry VARCHAR(100),
    website VARCHAR(255),
    logo VARCHAR(500),
    size ENUM('1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'),
    headquarters VARCHAR(255),
    founded_year INT,
    email VARCHAR(255),
    phone VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_industry (industry),
    INDEX idx_is_active (is_active),
    FULLTEXT idx_search (name, description)
) ENGINE=InnoDB;

-- =====================================================
-- JOBS TABLE
-- Stores job listings
-- =====================================================
CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements JSON COMMENT 'Array of requirement strings',
    responsibilities JSON COMMENT 'Array of responsibility strings',
    skills JSON COMMENT 'Array of required skills',
    location VARCHAR(255),
    type ENUM('full-time', 'part-time', 'contract', 'freelance', 'internship') DEFAULT 'full-time',
    remote ENUM('on-site', 'remote', 'hybrid') DEFAULT 'on-site',
    salary_min INT,
    salary_max INT,
    salary_currency VARCHAR(10) DEFAULT 'USD',
    experience_level ENUM('entry', 'junior', 'mid', 'senior', 'lead', 'executive') DEFAULT 'mid',
    department VARCHAR(100),
    benefits JSON COMMENT 'Array of benefits',
    deadline DATE,
    status ENUM('draft', 'active', 'paused', 'closed') DEFAULT 'active',
    views INT DEFAULT 0,
    applications_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_remote (remote),
    INDEX idx_experience (experience_level),
    INDEX idx_location (location),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB;

-- =====================================================
-- APPLICATIONS TABLE
-- Stores job applications from users
-- =====================================================
CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    status ENUM('pending', 'reviewed', 'shortlisted', 'interview_scheduled', 'interview_completed', 'offered', 'offer_accepted', 'hired', 'rejected', 'withdrawn') DEFAULT 'pending',
    cover_letter TEXT,
    cv_path VARCHAR(500) COMMENT 'CV used for this application',
    notes TEXT COMMENT 'Admin notes on the application',
    match_score DECIMAL(5,2) COMMENT 'AI-calculated match percentage',
    source ENUM('manual', 'ai-recommended', 'admin-offered') DEFAULT 'manual',
    reviewed_at DATETIME,
    reviewed_by INT,
    interview_scheduled_at DATETIME COMMENT 'When interview was scheduled',
    interview_feedback TEXT COMMENT 'Feedback from interview',
    offer_sent_at DATETIME COMMENT 'When job offer was sent',
    offer_response_at DATETIME COMMENT 'When user responded to offer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_application (user_id, job_id),
    INDEX idx_user (user_id),
    INDEX idx_job (job_id),
    INDEX idx_status (status),
    INDEX idx_source (source)
) ENGINE=InnoDB;

-- =====================================================
-- APPOINTMENTS TABLE
-- Stores interviews and onboarding appointments
-- =====================================================
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    application_id INT,
    created_by INT,
    type ENUM('interview', 'onboarding', 'follow-up', 'assessment', 'initial_call') DEFAULT 'interview',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    scheduled_at DATETIME NOT NULL,
    duration INT DEFAULT 60 COMMENT 'Duration in minutes',
    location VARCHAR(255),
    meeting_link VARCHAR(500),
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'rescheduled', 'no_show', 'passed', 'failed') DEFAULT 'scheduled',
    notes TEXT,
    feedback TEXT,
    outcome ENUM('pending', 'passed', 'failed') DEFAULT 'pending' COMMENT 'Outcome of the appointment',
    booked_by_user TINYINT(1) DEFAULT 0 COMMENT 'Whether user self-booked this appointment',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_application (application_id),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- =====================================================
-- EMAILS TABLE
-- Stores sent emails for tracking
-- =====================================================
CREATE TABLE emails (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_id INT NOT NULL,
    sender_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    type ENUM('job-offer', 'interview-invite', 'application-update', 'welcome', 'reminder', 'custom') DEFAULT 'custom',
    status ENUM('draft', 'sent', 'failed') DEFAULT 'sent',
    sent_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recipient (recipient_id),
    INDEX idx_sender (sender_id),
    INDEX idx_type (type),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- PASSWORD_RESETS TABLE
-- Stores password reset tokens
-- =====================================================
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB;

-- =====================================================
-- NOTIFICATIONS TABLE
-- In-app notifications for users
-- =====================================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('onboarding', 'application', 'interview', 'offer', 'message', 'system', 'reminder') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500) COMMENT 'Link to relevant page',
    is_read TINYINT(1) DEFAULT 0,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    related_id INT COMMENT 'ID of related entity (application, appointment, etc)',
    related_type VARCHAR(50) COMMENT 'Type of related entity',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- AVAILABLE_SLOTS TABLE
-- Available time slots for onboarding/interview appointments
-- =====================================================
CREATE TABLE available_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_type ENUM('onboarding', 'interview', 'both') DEFAULT 'both',
    is_booked TINYINT(1) DEFAULT 0,
    booked_by INT COMMENT 'User who booked this slot',
    appointment_id INT COMMENT 'Related appointment',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booked_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    INDEX idx_admin (admin_id),
    INDEX idx_date (slot_date),
    INDEX idx_is_booked (is_booked)
) ENGINE=InnoDB;

-- =====================================================
-- ACTIVITY_LOG TABLE
-- Stores user activity for audit purposes
-- =====================================================
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- MESSAGES TABLE
-- In-app messaging system between users and admins
-- =====================================================
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    parent_id INT COMMENT 'For reply threads',
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME,
    is_archived_sender TINYINT(1) DEFAULT 0,
    is_archived_recipient TINYINT(1) DEFAULT 0,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES messages(id) ON DELETE SET NULL,
    INDEX idx_sender (sender_id),
    INDEX idx_recipient (recipient_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- SYSTEM_SKILLS TABLE
-- Admin-managed skills that users can select
-- =====================================================
CREATE TABLE system_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(100) COMMENT 'Technical, Soft Skills, Tools, etc.',
    is_active TINYINT(1) DEFAULT 1,
    usage_count INT DEFAULT 0 COMMENT 'How many users have this skill',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_usage (usage_count DESC)
) ENGINE=InnoDB;

-- =====================================================
-- SYSTEM_INTERESTS TABLE
-- Admin-managed interests/industries that users can select
-- =====================================================
CREATE TABLE system_interests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(100) COMMENT 'Industry, Field, etc.',
    icon VARCHAR(50) COMMENT 'Bootstrap icon name',
    is_active TINYINT(1) DEFAULT 1,
    usage_count INT DEFAULT 0 COMMENT 'How many users have this interest',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_usage (usage_count DESC)
) ENGINE=InnoDB;

-- =====================================================
-- EMAIL_TEMPLATES TABLE
-- Reusable email templates for common notifications
-- =====================================================
CREATE TABLE email_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    type ENUM('onboarding', 'interview', 'offer', 'rejection', 'reminder', 'welcome', 'custom') NOT NULL,
    variables JSON COMMENT 'Available placeholders like {user_name}, {meeting_link}',
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB;

-- =====================================================
-- MEETING_SETTINGS TABLE
-- Settings for video meeting integrations (Teams/Zoom)
-- =====================================================
CREATE TABLE meeting_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    provider ENUM('teams', 'zoom', 'google_meet', 'custom') NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    base_url VARCHAR(500) COMMENT 'Base meeting URL pattern',
    api_key VARCHAR(500) COMMENT 'API key if needed (encrypted)',
    settings JSON COMMENT 'Additional provider-specific settings',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
