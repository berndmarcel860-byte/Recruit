# Recruit - Professional Job Recruitment Platform

A complete, professional job recruitment web application with comprehensive workflow management, messaging, email notifications, and video meeting integration. Built with PHP, MySQL, Bootstrap 5, and AJAX.

## Key Features

### 🔄 Professional Workflow System

The platform implements a complete recruitment workflow:

1. **User Registration** → Account status: `pending_onboarding`
2. **Onboarding Scheduling** → User books an onboarding call (via Zoom/Teams/Google Meet)
3. **Onboarding Completion** → Admin/moderator activates account
4. **Job Application** → User applies or receives AI recommendations
5. **Application Review** → Moderator accepts/rejects
6. **Interview Scheduling** → Accepted candidates schedule video interviews
7. **Hiring Decision** → Final offer or rejection

### 📹 Video Meeting Integration

- **Zoom** - Default meeting provider
- **Microsoft Teams** - Full support
- **Google Meet** - Full support
- Automatic meeting link generation for onboarding and interviews
- Configurable default provider in admin settings

### 💬 In-App Messaging System

- Real-time messaging between users and admin/moderators
- Conversation threads with history
- Priority levels (low, normal, high, urgent)
- Unread message indicators
- Message notifications

### 📧 Email Notification System

- **Templated Emails** - Pre-defined templates for all workflow stages
- **Automatic Sending** - Emails sent at key workflow moments
- **Meeting Links** - Zoom/Teams links included in emails
- **Templates Include**:
  - Onboarding scheduled (with meeting link)
  - Interview invitation
  - Interview scheduled (with meeting link)
  - Job offer
  - Application rejection
  - Welcome/activation email
  - AI job recommendations

### 👤 For Job Seekers (Users)

- **Multi-step Registration**
  - Personal information with validation
  - Skills selection from admin-managed system skills
  - Custom skill addition
  - Interests/industry preferences from system list
  - Work experience history
  - CV/Resume upload

- **Onboarding Process**
  - Schedule onboarding call from available slots
  - Receive Zoom/Teams meeting link via email
  - Meet with recruiter to discuss goals
  - Account activation after successful onboarding

- **Job Search & Discovery**
  - Browse jobs with filters (type, location, experience, remote/hybrid/on-site)
  - Advanced search functionality
  - Company profiles with open positions
  
- **AI-Powered Recommendations**
  - Smart job matching based on skills and experience
  - Match score calculation (0-100%)
  - Personalized job suggestions
  - Email notifications for high-match jobs

- **Application Tracking**
  - Real-time status updates
  - Interview scheduling for accepted applications
  - In-app notifications
  - Email notifications

- **Messaging**
  - Message recruitment team directly
  - Receive responses in-app
  - Conversation history

- **Dashboard**
  - Profile completion tracking
  - Application statistics
  - Upcoming appointments with meeting links
  - Quick actions
  - Notification center

### 👨‍💼 For Administrators & Moderators

- **Dashboard Overview**
  - Workflow alerts (pending onboarding, applications, interviews)
  - Key metrics and statistics
  - Today's appointments with quick actions
  - Recent activity

- **User Management**
  - View all users with status filters
  - Complete onboarding calls (pass/fail)
  - Activate/suspend/reject accounts
  - User profile details and history
  - Send messages to users

- **Application Review**
  - Accept applications → Send interview invitations with meeting links
  - Reject applications → Notify candidates via email
  - Add notes and feedback
  - Track application pipeline

- **Interview Management**
  - Schedule interviews with available slots
  - Automatic Zoom/Teams link generation
  - Complete interviews with outcomes
  - Make job offers
  - Track hiring funnel

- **Company & Job Management**
  - Create/edit companies
  - Create/edit job listings
  - Toggle job status (active/paused/closed)
  
- **Appointment System**
  - Create available time slots
  - Schedule onboarding calls
  - Schedule interviews
  - Automatic meeting link generation
  - Track appointment outcomes

- **Settings & Configuration**
  - **Skills Management** - Add/edit/delete system skills
  - **Interests Management** - Add/edit/delete system interests with icons
  - **Email Templates** - View and configure email templates
  - **Meeting Settings** - Configure Zoom/Teams/Google Meet integration

- **Communication**
  - In-app messaging with all users
  - Email communications via templates
  - Automated notifications for workflow events
  - Message priority system

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: Bootstrap 5.3 (latest)
- **JavaScript**: Vanilla JS with AJAX
- **Icons**: Bootstrap Icons
- **Video Meetings**: Zoom, Microsoft Teams, Google Meet

## Installation

### Prerequisites
- PHP 8.0 or higher (PHP 7.4 minimum)
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-repo/recruit.git
   cd recruit
   ```

2. **Create the database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Seed the database with dummy data**
   ```bash
   mysql -u root -p < database/seed.sql
   ```

4. **Configure database connection**
   
   Edit `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'recruit_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

5. **Configure meeting provider (optional)**
   
   Edit `includes/config.php`:
   ```php
   define('MEETING_PROVIDER', 'zoom'); // Options: zoom, teams, google_meet
   define('ZOOM_BASE_URL', 'https://zoom.us/j/');
   define('TEAMS_BASE_URL', 'https://teams.microsoft.com/l/meetup-join/');
   define('GOOGLE_MEET_BASE_URL', 'https://meet.google.com/');
   ```

6. **Configure email settings (optional)**
   
   Edit `includes/config.php`:
   ```php
   define('SMTP_HOST', 'smtp.your-provider.com');
   define('SMTP_PORT', 587);
   define('SMTP_USER', 'your_email@example.com');
   define('SMTP_PASS', 'your_password');
   ```

7. **Set up uploads directory permissions**
   ```bash
   mkdir -p assets/uploads/cv
   chmod 755 assets/uploads/cv
   ```

8. **Configure your web server**
   
   Point your web server document root to the project directory.

9. **Access the application**
   
   Open your configured URL in your browser.

## Demo Credentials

### Admin Account
- **Email**: admin@recruit.com
- **Password**: admin123

### Moderator Accounts
- **Email**: moderator@recruit.com
- **Password**: mod123

- **Email**: hr@recruit.com
- **Password**: mod123

### Active User Accounts (completed onboarding)
- **Email**: john.doe@example.com
- **Password**: password123

- **Email**: jane.smith@example.com
- **Password**: password123

### Pending Onboarding Users (for testing workflow)
- **Email**: pending.user@example.com
- **Password**: password123

- **Email**: scheduled.user@example.com
- **Password**: password123

## Project Structure

```
recruit/
├── admin/                  # Admin panel pages
│   ├── index.php          # Dashboard with workflow alerts
│   ├── users.php          # User management
│   ├── user.php           # User detail & actions
│   ├── applications.php   # Application review
│   ├── companies.php      # Company management
│   ├── jobs.php           # Job management
│   ├── appointments.php   # Appointment management
│   └── settings.php       # Skills, interests & email templates
├── api/                    # API endpoints (AJAX)
│   ├── auth.php           # Authentication & notifications
│   ├── jobs.php           # Jobs operations
│   ├── companies.php      # Companies operations
│   ├── appointments.php   # Appointment booking
│   ├── messages.php       # Messaging system
│   └── admin.php          # Admin/moderator operations
├── assets/
│   ├── css/style.css      # Custom styles
│   ├── js/app.js          # Main JavaScript
│   ├── js/admin.js        # Admin panel JavaScript
│   └── uploads/cv/        # CV uploads directory
├── database/
│   ├── schema.sql         # Database structure
│   └── seed.sql           # Dummy data
├── includes/
│   ├── config.php         # Configuration (DB, email, meetings)
│   ├── database.php       # Database connection
│   ├── auth.php           # Authentication & authorization
│   ├── functions.php      # Helper functions (email, messaging, etc.)
│   ├── navbar.php         # Navigation with notifications
│   └── footer.php         # Footer component
├── pages/                  # User-facing pages
│   ├── login.php          # Login page
│   ├── register.php       # Multi-step registration
│   ├── onboarding.php     # Onboarding scheduling
│   ├── dashboard.php      # User dashboard
│   ├── profile.php        # User profile
│   ├── jobs.php           # Job listings
│   ├── job.php            # Job details
│   ├── companies.php      # Company listings
│   ├── company.php        # Company details
│   ├── recommendations.php # AI recommendations
│   ├── applications.php   # User applications
│   └── messages.php       # Messaging interface
├── index.php               # Homepage
└── README.md
```

## Database Schema

### Tables
- **users** - User accounts with account_status workflow field
- **companies** - Company information
- **jobs** - Job listings
- **applications** - Job applications with extended status workflow
- **appointments** - Onboarding/interview appointments with outcomes
- **notifications** - In-app notifications
- **available_slots** - Available time slots for scheduling
- **emails** - Email communication logs
- **messages** - In-app messaging between users
- **system_skills** - Admin-managed skills list
- **system_interests** - Admin-managed interests list
- **email_templates** - Reusable email templates
- **meeting_settings** - Video meeting provider configuration
- **password_resets** - Password reset tokens
- **activity_log** - User activity audit

### Account Status Values
- `pending_onboarding` - New registration, needs to schedule onboarding
- `onboarding_scheduled` - Onboarding call is booked
- `active` - Account activated, can apply for jobs
- `suspended` - Account suspended by admin
- `rejected` - Application rejected during onboarding

### Application Status Values
- `pending` - New application, awaiting review
- `reviewed` - Reviewed by moderator
- `shortlisted` - Shortlisted for interview
- `interview_scheduled` - Interview is scheduled
- `interview_completed` - Interview done, awaiting decision
- `offered` - Job offer sent
- `offer_accepted` - Offer accepted
- `hired` - Hired
- `rejected` - Not selected
- `withdrawn` - Withdrawn by candidate

## Dummy Data

The seed file includes:
- 1 Admin user
- 2 Moderator users
- 3 Active job seekers (completed onboarding)
- 2 Pending users (for workflow testing)
- 8 Companies across different industries
- 20+ Active job listings
- Sample applications in various stages
- Sample appointments
- Available time slots for onboarding
- 70+ System skills across categories
- 30+ System interests
- 7 Email templates
- 3 Meeting provider configurations
- Sample messages

### Companies Included
- TechCorp Solutions (Technology)
- FinanceFirst Bank (Finance)
- HealthPlus Medical (Healthcare)
- GreenEnergy Co (Energy)
- RetailMax (Retail)
- EduLearn Academy (Education)
- AutoDrive Motors (Automotive)
- MediaStream Networks (Media)

## Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS prevention with output escaping
- Session-based authentication
- Role-based access control (user/moderator/admin)
- Account status validation
- File upload validation
- Activity logging

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is open-source and available under the MIT License.