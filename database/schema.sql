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
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
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
    status ENUM('pending', 'reviewed', 'shortlisted', 'interview', 'offered', 'hired', 'rejected') DEFAULT 'pending',
    cover_letter TEXT,
    cv_path VARCHAR(500) COMMENT 'CV used for this application',
    notes TEXT COMMENT 'Admin notes on the application',
    match_score DECIMAL(5,2) COMMENT 'AI-calculated match percentage',
    source ENUM('manual', 'ai-recommended', 'admin-offered') DEFAULT 'manual',
    reviewed_at DATETIME,
    reviewed_by INT,
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
    created_by INT NOT NULL,
    type ENUM('interview', 'onboarding', 'follow-up', 'assessment') DEFAULT 'interview',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    scheduled_at DATETIME NOT NULL,
    duration INT DEFAULT 60 COMMENT 'Duration in minutes',
    location VARCHAR(255),
    meeting_link VARCHAR(500),
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    notes TEXT,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
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
