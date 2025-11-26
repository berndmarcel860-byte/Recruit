# Recruit - Professional Job Recruitment Platform

A complete job recruitment web application with database, frontend, and backend built with PHP, MySQL, Bootstrap 5, and AJAX.

## Features

### For Job Seekers (Users)
- **User Registration & Profile**
  - Complete profile with personal information
  - Skills and interests management
  - Work experience and education history
  - CV/Resume upload support
  
- **Job Search & Discovery**
  - Browse jobs with filters (type, location, experience level, remote/on-site)
  - Search functionality
  - Company profiles with open positions
  
- **AI-Powered Recommendations**
  - Smart job matching based on skills and experience
  - Match score calculation
  - Personalized job suggestions

- **Application Management**
  - One-click job applications
  - Cover letter submission
  - Application status tracking
  - Interview scheduling notifications

### For Administrators
- **Dashboard Overview**
  - Key metrics and statistics
  - Recent applications
  - Upcoming appointments
  
- **User Management**
  - View all registered users
  - User profile details
  - Activate/deactivate accounts
  
- **Application Review**
  - Review and process applications
  - Update application status
  - Add notes and feedback
  
- **Job Offers**
  - Offer jobs directly to candidates
  - Send personalized job offers
  
- **Appointments & Onboarding**
  - Schedule interviews
  - Manage onboarding appointments
  - Meeting link integration
  
- **Email Communication**
  - Send emails to users
  - Job offer notifications
  - Interview invitations

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5.3
- **JavaScript**: Vanilla JS with AJAX
- **Icons**: Bootstrap Icons

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
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

5. **Set up uploads directory permissions**
   ```bash
   chmod 755 assets/uploads/cv
   ```

6. **Configure your web server**
   
   Point your web server document root to the project directory.

7. **Access the application**
   
   Open `http://localhost/recruit` in your browser.

## Demo Credentials

### Admin Account
- **Email**: admin@recruit.com
- **Password**: admin123

### User Accounts
- **Email**: john.doe@example.com
- **Password**: password123

- **Email**: jane.smith@example.com
- **Password**: password123

## Project Structure

```
recruit/
├── admin/                  # Admin panel pages
│   ├── index.php          # Admin dashboard
│   ├── users.php          # User management
│   ├── applications.php   # Application management
│   ├── jobs.php           # Job management
│   ├── appointments.php   # Appointment scheduling
│   └── user.php           # User detail view
├── api/                    # API endpoints (AJAX)
│   ├── auth.php           # Authentication
│   ├── jobs.php           # Jobs operations
│   ├── companies.php      # Companies operations
│   └── admin.php          # Admin operations
├── assets/
│   ├── css/
│   │   └── style.css      # Custom styles
│   ├── js/
│   │   ├── app.js         # Main JavaScript
│   │   └── admin.js       # Admin panel JavaScript
│   └── uploads/
│       └── cv/            # CV uploads directory
├── database/
│   ├── schema.sql         # Database structure
│   └── seed.sql           # Dummy data
├── includes/
│   ├── config.php         # Configuration
│   ├── database.php       # Database connection
│   ├── auth.php           # Authentication functions
│   ├── functions.php      # Helper functions
│   ├── navbar.php         # Navigation component
│   └── footer.php         # Footer component
├── pages/                  # Public pages
│   ├── login.php          # Login page
│   ├── register.php       # Registration page
│   ├── dashboard.php      # User dashboard
│   ├── profile.php        # User profile
│   ├── jobs.php           # Job listings
│   ├── job.php            # Job details
│   ├── companies.php      # Company listings
│   ├── company.php        # Company details
│   ├── recommendations.php # AI recommendations
│   └── applications.php   # User applications
├── index.php               # Homepage
└── README.md
```

## Database Schema

### Tables
- **users** - User accounts and profiles
- **companies** - Company information
- **jobs** - Job listings
- **applications** - Job applications
- **appointments** - Interview/onboarding appointments
- **emails** - Sent email records
- **password_resets** - Password reset tokens
- **activity_log** - User activity audit

## Dummy Data

The seed file includes:
- 1 Admin user
- 3 Sample job seekers
- 8 Companies across different industries
- 20+ Active job listings
- Sample applications and appointments

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
- CSRF protection on forms
- File upload validation

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is open-source and available under the MIT License.