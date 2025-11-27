-- =====================================================
-- Job Recruitment Platform - Seed Data
-- Dummy companies and job listings
-- =====================================================

USE recruit_db;

-- =====================================================
-- Insert Admin User (password: admin123)
-- =====================================================
INSERT INTO users (email, password, first_name, last_name, phone, city, country, role, account_status, is_active) VALUES
('admin@recruit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '+1 (555) 000-0000', 'San Francisco', 'USA', 'admin', 'active', 1);

-- =====================================================
-- Insert Moderator User (password: mod123)
-- =====================================================
INSERT INTO users (email, password, first_name, last_name, phone, city, country, role, account_status, is_active) VALUES
('moderator@recruit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Moderator', '+1 (555) 000-0001', 'San Francisco', 'USA', 'moderator', 'active', 1),
('hr@recruit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR', 'Manager', '+1 (555) 000-0002', 'New York', 'USA', 'moderator', 'active', 1);

-- =====================================================
-- Insert Sample Users (password: password123)
-- Active users (completed onboarding)
-- =====================================================
INSERT INTO users (email, password, first_name, last_name, phone, city, country, bio, skills, interests, experience, education, role, account_status, is_active, activated_at, activated_by) VALUES
('john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+1 (555) 111-1111', 'New York', 'USA', 
 'Experienced software developer with 5 years of experience in full-stack development.',
 '["JavaScript", "React", "Node.js", "Python", "SQL"]',
 '["Technology", "AI", "Web Development"]',
 '[{"company": "Tech Startup", "title": "Senior Developer", "startDate": "2020-01-01", "current": true}, {"company": "Web Agency", "title": "Developer", "startDate": "2017-06-01", "endDate": "2019-12-31"}]',
 '[{"school": "MIT", "degree": "BS Computer Science", "year": 2017}]',
 'user', 'active', 1, NOW(), 1),

('jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '+1 (555) 222-2222', 'San Francisco', 'USA',
 'Data scientist passionate about machine learning and AI.',
 '["Python", "Machine Learning", "TensorFlow", "SQL", "Statistics"]',
 '["Data Science", "AI", "Healthcare"]',
 '[{"company": "AI Research Lab", "title": "Data Scientist", "startDate": "2019-03-01", "current": true}]',
 '[{"school": "Stanford", "degree": "MS Data Science", "year": 2019}]',
 'user', 'active', 1, NOW(), 1),

('mike.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Johnson', '+1 (555) 333-3333', 'Austin', 'USA',
 'Project manager with experience in renewable energy sector.',
 '["Project Management", "Leadership", "Budgeting", "Solar Technology"]',
 '["Energy", "Sustainability", "Management"]',
 '[{"company": "Solar Company", "title": "Project Manager", "startDate": "2018-01-01", "current": true}]',
 '[{"school": "Texas A&M", "degree": "MBA", "year": 2017}]',
 'user', 'active', 1, NOW(), 1);

-- =====================================================
-- Insert Users with Pending Onboarding (for demo)
-- =====================================================
INSERT INTO users (email, password, first_name, last_name, phone, city, country, bio, skills, interests, role, account_status, is_active) VALUES
('pending.user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pending', 'User', '+1 (555) 444-4444', 'Chicago', 'USA',
 'New user waiting for onboarding.',
 '["JavaScript", "HTML", "CSS"]',
 '["Technology", "Web Development"]',
 'user', 'pending_onboarding', 1),

('scheduled.user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Scheduled', 'User', '+1 (555) 555-5555', 'Miami', 'USA',
 'User with scheduled onboarding appointment.',
 '["Python", "Data Analysis"]',
 '["Data Science", "Finance"]',
 'user', 'onboarding_scheduled', 1);

-- =====================================================
-- Insert Companies
-- =====================================================
INSERT INTO companies (name, description, industry, website, size, headquarters, founded_year, email, phone) VALUES
('TechCorp Solutions', 'Leading technology company specializing in cloud solutions and AI development. We build cutting-edge products that transform businesses worldwide.', 'Technology', 'https://techcorp.example.com', '201-500', 'San Francisco, CA', 2010, 'careers@techcorp.example.com', '+1 (555) 100-1000'),

('FinanceFirst Bank', 'A modern digital bank offering innovative financial services to millions of customers. We combine traditional banking with fintech innovation.', 'Finance', 'https://financefirst.example.com', '1000+', 'New York, NY', 1995, 'jobs@financefirst.example.com', '+1 (555) 200-2000'),

('HealthPlus Medical', 'Healthcare technology company improving patient outcomes through digital health solutions and medical devices.', 'Healthcare', 'https://healthplus.example.com', '51-200', 'Boston, MA', 2015, 'careers@healthplus.example.com', '+1 (555) 300-3000'),

('GreenEnergy Co', 'Renewable energy company focused on solar and wind power solutions. Making sustainable energy accessible to everyone.', 'Energy', 'https://greenenergy.example.com', '201-500', 'Austin, TX', 2012, 'join@greenenergy.example.com', '+1 (555) 400-4000'),

('RetailMax', 'E-commerce and retail technology company revolutionizing how people shop online and in stores.', 'Retail', 'https://retailmax.example.com', '501-1000', 'Seattle, WA', 2008, 'talent@retailmax.example.com', '+1 (555) 500-5000'),

('EduLearn Academy', 'Online education platform providing courses and certifications to learners worldwide. Democratizing education through technology.', 'Education', 'https://edulearn.example.com', '51-200', 'Los Angeles, CA', 2016, 'hr@edulearn.example.com', '+1 (555) 600-6000'),

('AutoDrive Motors', 'Autonomous vehicle technology company developing self-driving solutions for the future of transportation.', 'Automotive', 'https://autodrive.example.com', '201-500', 'Detroit, MI', 2018, 'careers@autodrive.example.com', '+1 (555) 700-7000'),

('MediaStream Networks', 'Digital media and streaming platform delivering content to millions of users globally.', 'Media', 'https://mediastream.example.com', '501-1000', 'Los Angeles, CA', 2014, 'jobs@mediastream.example.com', '+1 (555) 800-8000');

-- =====================================================
-- Insert Jobs - Technology
-- =====================================================
INSERT INTO jobs (company_id, title, description, requirements, responsibilities, skills, location, type, remote, salary_min, salary_max, experience_level, department, benefits, deadline, status) VALUES
(1, 'Senior Software Engineer', 'We are looking for an experienced software engineer to join our team and help build scalable applications.',
 '["5+ years of software development experience", "Strong knowledge of JavaScript/TypeScript", "Experience with React or Vue.js", "Familiarity with cloud services (AWS/GCP/Azure)"]',
 '["Design and implement new features", "Code review and mentoring junior developers", "Collaborate with product and design teams", "Maintain and improve existing codebase"]',
 '["JavaScript", "TypeScript", "React", "Node.js", "AWS", "Git"]',
 'San Francisco, CA', 'full-time', 'hybrid', 150000, 200000, 'senior', 'Engineering',
 '["Health Insurance", "401k Match", "Unlimited PTO", "Remote Work Options", "Stock Options"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(1, 'DevOps Engineer', 'Join our DevOps team to build and maintain our cloud infrastructure and CI/CD pipelines.',
 '["3+ years DevOps experience", "Strong Linux administration skills", "Experience with Kubernetes and Docker", "Knowledge of Infrastructure as Code"]',
 '["Manage cloud infrastructure", "Implement CI/CD pipelines", "Monitor system performance", "Automate deployment processes"]',
 '["Kubernetes", "Docker", "AWS", "Terraform", "Python", "Linux"]',
 'San Francisco, CA', 'full-time', 'remote', 130000, 170000, 'mid', 'Infrastructure',
 '["Health Insurance", "401k Match", "Remote Work", "Learning Budget"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(1, 'Junior Frontend Developer', 'Great opportunity for a junior developer to join our frontend team and grow their skills.',
 '["1+ years of web development", "Knowledge of HTML, CSS, JavaScript", "Familiarity with React or similar frameworks", "Eager to learn"]',
 '["Develop UI components", "Fix bugs and issues", "Write unit tests", "Participate in code reviews"]',
 '["JavaScript", "React", "HTML", "CSS", "Git"]',
 'San Francisco, CA', 'full-time', 'on-site', 70000, 90000, 'junior', 'Engineering',
 '["Health Insurance", "Learning Budget", "Mentorship Program"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(1, 'Data Scientist', 'Looking for a data scientist to derive insights from our large datasets and build ML models.',
 '["MS in Computer Science, Statistics, or related field", "3+ years of experience in data science", "Proficiency in Python and SQL", "Experience with ML frameworks"]',
 '["Analyze large datasets", "Build and deploy ML models", "Present findings to stakeholders", "Collaborate with engineering team"]',
 '["Python", "SQL", "Machine Learning", "TensorFlow", "Statistics", "Data Visualization"]',
 'San Francisco, CA', 'full-time', 'hybrid', 140000, 180000, 'mid', 'Data Science',
 '["Health Insurance", "401k Match", "Conference Budget", "Stock Options"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active');

-- =====================================================
-- Insert Jobs - Finance
-- =====================================================
INSERT INTO jobs (company_id, title, description, requirements, responsibilities, skills, location, type, remote, salary_min, salary_max, experience_level, department, benefits, deadline, status) VALUES
(2, 'Financial Analyst', 'Seeking a financial analyst to support our investment decisions and financial planning.',
 '["Bachelor''s degree in Finance or Accounting", "3+ years of financial analysis experience", "Strong Excel and financial modeling skills", "CFA preferred"]',
 '["Prepare financial reports and forecasts", "Analyze market trends", "Support budgeting processes", "Present to senior management"]',
 '["Financial Modeling", "Excel", "SQL", "Bloomberg", "PowerPoint"]',
 'New York, NY', 'full-time', 'on-site', 80000, 120000, 'mid', 'Finance',
 '["Health Insurance", "401k Match", "Performance Bonus", "Professional Development"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(2, 'Risk Manager', 'Join our risk management team to identify and mitigate financial risks across the organization.',
 '["5+ years in risk management", "Strong understanding of financial regulations", "Experience with risk modeling", "FRM certification preferred"]',
 '["Develop risk assessment frameworks", "Monitor market and credit risks", "Report to regulatory bodies", "Implement risk controls"]',
 '["Risk Management", "Regulatory Compliance", "Python", "SQL", "Statistical Analysis"]',
 'New York, NY', 'full-time', 'hybrid', 120000, 160000, 'senior', 'Risk Management',
 '["Health Insurance", "401k Match", "Bonus", "Education Assistance"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(2, 'Software Developer - FinTech', 'Build next-generation financial technology applications for our digital banking platform.',
 '["3+ years software development", "Experience with Java or Python", "Knowledge of financial systems", "Understanding of security best practices"]',
 '["Develop banking applications", "Ensure system security", "Integrate with payment systems", "Write clean, maintainable code"]',
 '["Java", "Python", "Spring Boot", "Microservices", "PostgreSQL", "Security"]',
 'New York, NY', 'full-time', 'hybrid', 130000, 170000, 'mid', 'Technology',
 '["Health Insurance", "401k Match", "Stock Purchase Plan", "Flexible Hours"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active');

-- =====================================================
-- Insert Jobs - Healthcare
-- =====================================================
INSERT INTO jobs (company_id, title, description, requirements, responsibilities, skills, location, type, remote, salary_min, salary_max, experience_level, department, benefits, deadline, status) VALUES
(3, 'Healthcare Data Analyst', 'Analyze healthcare data to improve patient outcomes and operational efficiency.',
 '["Bachelor''s in Healthcare, Statistics, or related field", "2+ years healthcare analytics experience", "SQL and Python proficiency", "Knowledge of HIPAA regulations"]',
 '["Analyze patient data", "Create dashboards and reports", "Identify trends and patterns", "Support clinical decision making"]',
 '["SQL", "Python", "Tableau", "Healthcare Analytics", "HIPAA"]',
 'Boston, MA', 'full-time', 'hybrid', 75000, 95000, 'junior', 'Analytics',
 '["Health Insurance", "401k Match", "Wellness Program"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(3, 'Medical Device Engineer', 'Design and develop innovative medical devices that improve patient care.',
 '["BS in Biomedical Engineering or related field", "5+ years medical device experience", "Knowledge of FDA regulations", "Experience with embedded systems"]',
 '["Design medical devices", "Conduct testing and validation", "Ensure regulatory compliance", "Collaborate with clinical teams"]',
 '["Biomedical Engineering", "Embedded Systems", "FDA Regulations", "CAD", "Quality Systems"]',
 'Boston, MA', 'full-time', 'on-site', 110000, 140000, 'senior', 'Engineering',
 '["Health Insurance", "401k Match", "Research Budget", "Patent Bonus"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active');

-- =====================================================
-- Insert Jobs - Energy
-- =====================================================
INSERT INTO jobs (company_id, title, description, requirements, responsibilities, skills, location, type, remote, salary_min, salary_max, experience_level, department, benefits, deadline, status) VALUES
(4, 'Solar Project Manager', 'Manage solar installation projects from planning to completion.',
 '["5+ years project management experience", "Knowledge of solar technology", "PMP certification preferred", "Strong leadership skills"]',
 '["Oversee project timelines", "Manage project budgets", "Coordinate with stakeholders", "Ensure safety compliance"]',
 '["Project Management", "Solar Technology", "Budgeting", "Leadership", "Safety"]',
 'Austin, TX', 'full-time', 'on-site', 90000, 120000, 'senior', 'Operations',
 '["Health Insurance", "401k Match", "Company Vehicle", "Performance Bonus"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(4, 'Renewable Energy Engineer', 'Design and optimize renewable energy systems for residential and commercial clients.',
 '["BS in Electrical Engineering or related field", "3+ years in renewable energy", "Knowledge of power systems", "Experience with energy modeling software"]',
 '["Design energy systems", "Perform energy audits", "Optimize system performance", "Prepare technical documentation"]',
 '["Electrical Engineering", "Solar Design", "Energy Modeling", "AutoCAD", "Python"]',
 'Austin, TX', 'full-time', 'hybrid', 80000, 110000, 'mid', 'Engineering',
 '["Health Insurance", "401k Match", "Green Commute Benefit", "Professional Development"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active');

-- =====================================================
-- Insert Jobs - Retail
-- =====================================================
INSERT INTO jobs (company_id, title, description, requirements, responsibilities, skills, location, type, remote, salary_min, salary_max, experience_level, department, benefits, deadline, status) VALUES
(5, 'E-commerce Product Manager', 'Lead product development for our e-commerce platform, driving growth and user experience.',
 '["5+ years product management experience", "E-commerce background", "Data-driven decision making", "Strong communication skills"]',
 '["Define product roadmap", "Analyze user behavior", "Work with engineering team", "Drive KPI improvements"]',
 '["Product Management", "E-commerce", "Analytics", "Agile", "User Research"]',
 'Seattle, WA', 'full-time', 'hybrid', 130000, 170000, 'senior', 'Product',
 '["Health Insurance", "401k Match", "Employee Discount", "Stock Options"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(5, 'Full Stack Developer', 'Build and maintain our retail technology stack serving millions of customers.',
 '["4+ years full stack experience", "JavaScript/TypeScript expertise", "Experience with high-traffic systems", "Database design knowledge"]',
 '["Develop web applications", "Optimize performance", "Implement new features", "Maintain system reliability"]',
 '["JavaScript", "TypeScript", "React", "Node.js", "PostgreSQL", "Redis"]',
 'Seattle, WA', 'full-time', 'remote', 120000, 160000, 'mid', 'Engineering',
 '["Health Insurance", "401k Match", "Remote Work", "Learning Budget"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active');

-- =====================================================
-- Insert Jobs - Education
-- =====================================================
INSERT INTO jobs (company_id, title, description, requirements, responsibilities, skills, location, type, remote, salary_min, salary_max, experience_level, department, benefits, deadline, status) VALUES
(6, 'Instructional Designer', 'Create engaging online courses and learning experiences for our platform.',
 '["Master''s in Education or Instructional Design", "3+ years course development experience", "Knowledge of learning management systems", "Strong writing skills"]',
 '["Design course content", "Create assessments", "Work with subject matter experts", "Analyze learning outcomes"]',
 '["Instructional Design", "E-learning", "LMS", "Content Development", "Video Production"]',
 'Los Angeles, CA', 'full-time', 'remote', 70000, 90000, 'mid', 'Content',
 '["Health Insurance", "401k Match", "Free Courses", "Remote Work"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(6, 'EdTech Software Engineer', 'Build the technology powering our online learning platform.',
 '["3+ years software development", "Experience with React or Angular", "Backend development skills", "Interest in education technology"]',
 '["Develop platform features", "Improve user experience", "Ensure platform scalability", "Implement learning tools"]',
 '["JavaScript", "React", "Python", "Django", "PostgreSQL", "AWS"]',
 'Los Angeles, CA', 'full-time', 'hybrid', 100000, 140000, 'mid', 'Engineering',
 '["Health Insurance", "401k Match", "Free Courses", "Professional Development"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active');

-- =====================================================
-- Insert Jobs - Automotive
-- =====================================================
INSERT INTO jobs (company_id, title, description, requirements, responsibilities, skills, location, type, remote, salary_min, salary_max, experience_level, department, benefits, deadline, status) VALUES
(7, 'Autonomous Vehicle Engineer', 'Develop software for our self-driving vehicle platform.',
 '["MS in Computer Science or Robotics", "4+ years experience in autonomous systems", "Strong C++ and Python skills", "Knowledge of sensor fusion"]',
 '["Develop AV algorithms", "Test and validate systems", "Collaborate with hardware team", "Ensure safety standards"]',
 '["C++", "Python", "ROS", "Computer Vision", "Machine Learning", "Sensor Fusion"]',
 'Detroit, MI', 'full-time', 'on-site', 150000, 200000, 'senior', 'Engineering',
 '["Health Insurance", "401k Match", "Stock Options", "Relocation Assistance"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(7, 'Embedded Systems Engineer', 'Work on embedded systems for our autonomous vehicle platform.',
 '["BS in Electrical or Computer Engineering", "3+ years embedded systems experience", "Strong C programming", "Experience with RTOS"]',
 '["Design embedded systems", "Write firmware code", "Debug hardware issues", "Optimize system performance"]',
 '["C", "Embedded Systems", "RTOS", "CAN Bus", "Linux", "Hardware Debugging"]',
 'Detroit, MI', 'full-time', 'on-site', 110000, 150000, 'mid', 'Engineering',
 '["Health Insurance", "401k Match", "Patent Bonus", "Training Budget"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active');

-- =====================================================
-- Insert Jobs - Media
-- =====================================================
INSERT INTO jobs (company_id, title, description, requirements, responsibilities, skills, location, type, remote, salary_min, salary_max, experience_level, department, benefits, deadline, status) VALUES
(8, 'Video Streaming Engineer', 'Build and optimize our video streaming infrastructure serving millions of users.',
 '["4+ years experience in video/streaming", "Knowledge of video codecs and protocols", "Experience with CDNs", "Strong problem-solving skills"]',
 '["Optimize streaming quality", "Reduce latency", "Scale infrastructure", "Monitor system health"]',
 '["Video Streaming", "FFmpeg", "HLS", "CDN", "Python", "AWS"]',
 'Los Angeles, CA', 'full-time', 'hybrid', 130000, 170000, 'senior', 'Engineering',
 '["Health Insurance", "401k Match", "Free Subscription", "Flexible Hours"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),

(8, 'UX Designer', 'Design user experiences for our streaming platform across web and mobile.',
 '["3+ years UX design experience", "Strong portfolio", "Proficiency in design tools", "Understanding of user research"]',
 '["Create wireframes and prototypes", "Conduct user research", "Collaborate with developers", "Improve user experience"]',
 '["UX Design", "Figma", "User Research", "Prototyping", "Mobile Design"]',
 'Los Angeles, CA', 'full-time', 'hybrid', 100000, 140000, 'mid', 'Design',
 '["Health Insurance", "401k Match", "Design Conferences", "Equipment Budget"]',
 DATE_ADD(NOW(), INTERVAL 60 DAY), 'active');

-- =====================================================
-- Insert Sample Applications (for active users)
-- =====================================================
INSERT INTO applications (user_id, job_id, status, cover_letter, source, match_score) VALUES
(4, 1, 'pending', 'I am very interested in this position and believe my skills align well with the requirements.', 'manual', 85.00),
(5, 4, 'interview_scheduled', 'I would love to join your team and contribute to your innovative projects.', 'ai-recommended', 92.00),
(6, 13, 'shortlisted', 'My project management experience makes me an ideal fit for this solar role.', 'manual', 88.00);

-- =====================================================
-- Insert Sample Appointments (onboarding and interviews)
-- =====================================================
INSERT INTO appointments (user_id, application_id, created_by, type, title, description, scheduled_at, duration, location, meeting_link, status, outcome, booked_by_user) VALUES
-- Onboarding appointment for scheduled user
(8, NULL, 1, 'onboarding', 'Initial Onboarding Call', 'Welcome call to discuss the platform and your job search goals', DATE_ADD(NOW(), INTERVAL 3 DAY), 30, 'Virtual', 'https://zoom.us/j/onboarding123', 'scheduled', 'pending', 1),
-- Interview for Jane Smith
(5, 2, 1, 'interview', 'Technical Interview - Data Scientist', 'First round technical interview with the data science team', DATE_ADD(NOW(), INTERVAL 7 DAY), 60, 'Zoom Meeting', 'https://zoom.us/j/interview456', 'scheduled', 'pending', 0);

-- =====================================================
-- Insert Available Time Slots for Onboarding
-- =====================================================
INSERT INTO available_slots (admin_id, slot_date, start_time, end_time, slot_type, is_booked) VALUES
-- Next 7 days of available slots
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '09:30:00', 'onboarding', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '10:30:00', 'onboarding', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '14:30:00', 'onboarding', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '09:30:00', 'onboarding', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '11:30:00', 'both', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '15:30:00', 'both', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '09:30:00', 'onboarding', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:30:00', '11:00:00', 'onboarding', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '14:00:00', '14:30:00', 'both', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '10:00:00', '10:30:00', 'interview', 0),
(1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '11:00:00', '11:30:00', 'interview', 0),
(2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', '13:30:00', 'onboarding', 0),
(2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:30:00', '10:00:00', 'onboarding', 0),
(2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '14:30:00', 'both', 0);

-- =====================================================
-- Insert Sample Notifications
-- =====================================================
INSERT INTO notifications (user_id, type, title, message, link, is_read, priority) VALUES
(7, 'onboarding', 'Welcome to Recruit!', 'Please schedule your onboarding appointment to get started.', 'pages/onboarding.php', 0, 'high'),
(8, 'onboarding', 'Onboarding Scheduled', 'Your onboarding call has been scheduled. See you soon!', 'pages/dashboard.php', 0, 'normal'),
(5, 'interview', 'Interview Scheduled', 'Your technical interview for Data Scientist position has been scheduled.', 'pages/applications.php', 0, 'high'),
(4, 'application', 'Application Received', 'Your application for Senior Software Engineer has been received and is under review.', 'pages/applications.php', 1, 'normal');

-- =====================================================
-- Insert System Skills (Admin-managed)
-- =====================================================
INSERT INTO system_skills (name, category, is_active, usage_count, created_by) VALUES
-- Technical Skills - Programming Languages
('JavaScript', 'Programming Languages', 1, 45, 1),
('Python', 'Programming Languages', 1, 42, 1),
('Java', 'Programming Languages', 1, 35, 1),
('TypeScript', 'Programming Languages', 1, 28, 1),
('C++', 'Programming Languages', 1, 22, 1),
('C#', 'Programming Languages', 1, 20, 1),
('Go', 'Programming Languages', 1, 18, 1),
('Ruby', 'Programming Languages', 1, 15, 1),
('PHP', 'Programming Languages', 1, 14, 1),
('Swift', 'Programming Languages', 1, 12, 1),
('Kotlin', 'Programming Languages', 1, 10, 1),
('Rust', 'Programming Languages', 1, 8, 1),

-- Technical Skills - Frameworks
('React', 'Frameworks', 1, 40, 1),
('Node.js', 'Frameworks', 1, 38, 1),
('Angular', 'Frameworks', 1, 25, 1),
('Vue.js', 'Frameworks', 1, 22, 1),
('Django', 'Frameworks', 1, 18, 1),
('Spring Boot', 'Frameworks', 1, 16, 1),
('Express.js', 'Frameworks', 1, 20, 1),
('Next.js', 'Frameworks', 1, 15, 1),
('.NET', 'Frameworks', 1, 18, 1),
('Laravel', 'Frameworks', 1, 12, 1),
('Flutter', 'Frameworks', 1, 10, 1),
('React Native', 'Frameworks', 1, 14, 1),

-- Technical Skills - Databases
('SQL', 'Databases', 1, 50, 1),
('PostgreSQL', 'Databases', 1, 30, 1),
('MongoDB', 'Databases', 1, 28, 1),
('MySQL', 'Databases', 1, 25, 1),
('Redis', 'Databases', 1, 18, 1),
('Elasticsearch', 'Databases', 1, 12, 1),
('Oracle', 'Databases', 1, 10, 1),

-- Technical Skills - Cloud & DevOps
('AWS', 'Cloud & DevOps', 1, 35, 1),
('Docker', 'Cloud & DevOps', 1, 32, 1),
('Kubernetes', 'Cloud & DevOps', 1, 22, 1),
('Azure', 'Cloud & DevOps', 1, 20, 1),
('Google Cloud', 'Cloud & DevOps', 1, 18, 1),
('CI/CD', 'Cloud & DevOps', 1, 25, 1),
('Terraform', 'Cloud & DevOps', 1, 14, 1),
('Jenkins', 'Cloud & DevOps', 1, 12, 1),
('Git', 'Cloud & DevOps', 1, 55, 1),
('Linux', 'Cloud & DevOps', 1, 30, 1),

-- Technical Skills - Data & AI
('Machine Learning', 'Data & AI', 1, 25, 1),
('Data Analysis', 'Data & AI', 1, 28, 1),
('TensorFlow', 'Data & AI', 1, 15, 1),
('PyTorch', 'Data & AI', 1, 14, 1),
('Data Visualization', 'Data & AI', 1, 20, 1),
('Pandas', 'Data & AI', 1, 22, 1),
('Scikit-learn', 'Data & AI', 1, 12, 1),
('Deep Learning', 'Data & AI', 1, 10, 1),
('NLP', 'Data & AI', 1, 8, 1),
('Computer Vision', 'Data & AI', 1, 8, 1),

-- Soft Skills
('Communication', 'Soft Skills', 1, 60, 1),
('Leadership', 'Soft Skills', 1, 45, 1),
('Problem Solving', 'Soft Skills', 1, 55, 1),
('Teamwork', 'Soft Skills', 1, 50, 1),
('Project Management', 'Soft Skills', 1, 40, 1),
('Critical Thinking', 'Soft Skills', 1, 35, 1),
('Time Management', 'Soft Skills', 1, 38, 1),
('Adaptability', 'Soft Skills', 1, 32, 1),
('Creativity', 'Soft Skills', 1, 28, 1),
('Presentation Skills', 'Soft Skills', 1, 25, 1),
('Negotiation', 'Soft Skills', 1, 20, 1),
('Mentoring', 'Soft Skills', 1, 18, 1),

-- Tools
('Jira', 'Tools', 1, 30, 1),
('Figma', 'Tools', 1, 25, 1),
('Slack', 'Tools', 1, 40, 1),
('Microsoft Office', 'Tools', 1, 55, 1),
('Excel', 'Tools', 1, 45, 1),
('Photoshop', 'Tools', 1, 18, 1),
('Salesforce', 'Tools', 1, 15, 1),
('Tableau', 'Tools', 1, 12, 1),
('Power BI', 'Tools', 1, 14, 1),
('Google Analytics', 'Tools', 1, 20, 1);

-- =====================================================
-- Insert System Interests (Admin-managed)
-- =====================================================
INSERT INTO system_interests (name, category, icon, is_active, usage_count, created_by) VALUES
-- Industries
('Technology', 'Industry', 'laptop', 1, 85, 1),
('Healthcare', 'Industry', 'heart-pulse', 1, 45, 1),
('Finance', 'Industry', 'cash-stack', 1, 50, 1),
('Education', 'Industry', 'mortarboard', 1, 35, 1),
('E-commerce', 'Industry', 'cart3', 1, 40, 1),
('Manufacturing', 'Industry', 'gear', 1, 20, 1),
('Energy', 'Industry', 'lightning', 1, 18, 1),
('Real Estate', 'Industry', 'house', 1, 22, 1),
('Media & Entertainment', 'Industry', 'film', 1, 30, 1),
('Automotive', 'Industry', 'car-front', 1, 25, 1),
('Aerospace', 'Industry', 'airplane', 1, 12, 1),
('Hospitality', 'Industry', 'building', 1, 18, 1),
('Legal', 'Industry', 'bank', 1, 15, 1),
('Non-profit', 'Industry', 'people', 1, 12, 1),
('Government', 'Industry', 'flag', 1, 10, 1),
('Consulting', 'Industry', 'diagram-3', 1, 28, 1),

-- Job Fields
('Software Development', 'Job Field', 'code-slash', 1, 70, 1),
('Data Science', 'Job Field', 'graph-up', 1, 45, 1),
('Product Management', 'Job Field', 'kanban', 1, 35, 1),
('UX/UI Design', 'Job Field', 'palette', 1, 38, 1),
('Marketing', 'Job Field', 'megaphone', 1, 42, 1),
('Sales', 'Job Field', 'person-lines-fill', 1, 40, 1),
('Human Resources', 'Job Field', 'people-fill', 1, 25, 1),
('Operations', 'Job Field', 'sliders', 1, 22, 1),
('Customer Support', 'Job Field', 'headset', 1, 20, 1),
('DevOps', 'Job Field', 'server', 1, 30, 1),
('Cybersecurity', 'Job Field', 'shield-lock', 1, 28, 1),
('AI & Machine Learning', 'Job Field', 'robot', 1, 40, 1),
('Cloud Computing', 'Job Field', 'cloud', 1, 32, 1),
('Mobile Development', 'Job Field', 'phone', 1, 28, 1),
('Quality Assurance', 'Job Field', 'check2-circle', 1, 22, 1),
('Business Analysis', 'Job Field', 'clipboard-data', 1, 25, 1);

-- =====================================================
-- Insert Email Templates
-- =====================================================
INSERT INTO email_templates (name, subject, body, type, variables, is_active, created_by) VALUES
('onboarding_scheduled', 'Your Onboarding Call is Scheduled - {app_name}', 
'Hello {user_name},

Great news! Your onboarding call has been scheduled.

📅 Date: {appointment_date}
⏰ Time: {appointment_time}
⏱️ Duration: {duration} minutes
🔗 Meeting Link: {meeting_link}

What to expect:
• Brief introduction to our platform
• Discussion about your career goals
• Overview of our job matching process
• Q&A session

Please click the meeting link at the scheduled time to join. We recommend joining 5 minutes early.

If you need to reschedule, please log in to your account and manage your appointment.

Best regards,
The {app_name} Team', 
'onboarding', '["user_name", "appointment_date", "appointment_time", "duration", "meeting_link", "app_name"]', 1, 1),

('interview_invitation', 'Interview Invitation: {job_title} at {company_name}', 
'Hello {user_name},

Congratulations! 🎉

We are pleased to inform you that your application for {job_title} at {company_name} has been shortlisted, and we would like to invite you for an interview.

Please log in to your account to schedule your interview from the available time slots.

Position: {job_title}
Company: {company_name}
Match Score: {match_score}%

We look forward to speaking with you!

Best regards,
The {app_name} Recruitment Team', 
'interview', '["user_name", "job_title", "company_name", "match_score", "app_name"]', 1, 1),

('interview_scheduled', 'Interview Confirmed: {job_title}', 
'Hello {user_name},

Your interview has been confirmed!

📋 Position: {job_title}
🏢 Company: {company_name}
📅 Date: {appointment_date}
⏰ Time: {appointment_time}
⏱️ Duration: {duration} minutes
📍 Type: {meeting_type}
🔗 Meeting Link: {meeting_link}

Tips for your interview:
• Test your video and audio before the call
• Find a quiet, well-lit location
• Have your resume ready
• Prepare questions about the role

Click the meeting link at the scheduled time to join. Good luck!

Best regards,
The {app_name} Team', 
'interview', '["user_name", "job_title", "company_name", "appointment_date", "appointment_time", "duration", "meeting_type", "meeting_link", "app_name"]', 1, 1),

('job_offer', 'Job Offer: {job_title} at {company_name}', 
'Hello {user_name},

We are thrilled to extend an offer for the position of {job_title} at {company_name}!

This offer is a testament to your skills, experience, and the great impression you made during the interview process.

Please log in to your account to review the offer details and respond.

We hope you will join our team!

Best regards,
The {app_name} Recruitment Team',
'offer', '["user_name", "job_title", "company_name", "app_name"]', 1, 1),

('application_rejected', 'Application Update: {job_title}', 
'Hello {user_name},

Thank you for your interest in the {job_title} position at {company_name} and for taking the time to apply.

After careful consideration, we have decided to move forward with other candidates whose experience more closely matches our current needs.

We encourage you to continue exploring other opportunities on our platform. Your profile remains active, and you may be matched with other suitable positions.

We appreciate your interest and wish you the best in your job search.

Best regards,
The {app_name} Team', 
'rejection', '["user_name", "job_title", "company_name", "app_name"]', 1, 1),

('welcome_activated', 'Welcome to {app_name} - Your Account is Now Active!', 
'Hello {user_name},

Congratulations! 🎉 Your account has been activated following your successful onboarding.

You now have full access to:
✅ Browse all available job listings
✅ Apply for positions that match your skills
✅ Receive AI-powered job recommendations
✅ Track your applications in real-time
✅ Message our recruitment team

Start exploring opportunities now: {dashboard_link}

If you have any questions, feel free to message us through the platform.

Welcome aboard!

Best regards,
The {app_name} Team', 
'welcome', '["user_name", "dashboard_link", "app_name"]', 1, 1),

('ai_job_recommendation', 'New Job Match: {job_title} ({match_score}% Match)', 
'Hello {user_name},

Our AI has found a great match for you!

🎯 Match Score: {match_score}%
📋 Position: {job_title}
🏢 Company: {company_name}
📍 Location: {location}
💼 Type: {job_type}
💰 Salary: {salary_range}

Based on your skills and interests, this position is an excellent fit for your profile.

View this job and apply now: {job_link}

Don''t miss this opportunity!

Best regards,
The {app_name} AI Matching Team', 
'custom', '["user_name", "job_title", "company_name", "match_score", "location", "job_type", "salary_range", "job_link", "app_name"]', 1, 1);

-- =====================================================
-- Insert Meeting Settings
-- =====================================================
INSERT INTO meeting_settings (provider, is_default, base_url, settings, is_active) VALUES
('zoom', 1, 'https://zoom.us/j/', '{"auto_recording": false, "waiting_room": true}', 1),
('teams', 0, 'https://teams.microsoft.com/l/meetup-join/', '{"allow_guests": true}', 1),
('google_meet', 0, 'https://meet.google.com/', '{}', 1);

-- =====================================================
-- Insert Sample Messages
-- =====================================================
INSERT INTO messages (sender_id, recipient_id, subject, body, is_read, priority) VALUES
(1, 4, 'Welcome to Recruit Platform!', 'Hello John,\n\nWelcome to our recruitment platform! We''re excited to help you find your next opportunity.\n\nIf you have any questions, feel free to reply to this message.\n\nBest regards,\nThe Admin Team', 1, 'normal'),
(1, 5, 'Your Interview is Coming Up!', 'Hi Jane,\n\nJust a reminder that your technical interview is scheduled for next week. Make sure to:\n- Test your camera and microphone\n- Have a stable internet connection\n- Prepare questions about the role\n\nGood luck!\n\nBest,\nRecruitment Team', 0, 'high'),
(4, 1, 'Question about job application', 'Hi,\n\nI applied for the Senior Software Engineer position and wanted to ask about the timeline for the next steps.\n\nThank you!', 1, 'normal'),
(1, 4, 'Re: Question about job application', 'Hi John,\n\nThank you for your application! Our team is currently reviewing all applications. You can expect to hear back from us within 5-7 business days.\n\nBest regards,\nRecruitment Team', 0, 'normal');

-- =====================================================
-- Display Summary
-- =====================================================
SELECT 'Database seeded successfully!' AS message;
SELECT 
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM companies) AS total_companies,
    (SELECT COUNT(*) FROM jobs) AS total_jobs,
    (SELECT COUNT(*) FROM applications) AS total_applications,
    (SELECT COUNT(*) FROM appointments) AS total_appointments,
    (SELECT COUNT(*) FROM system_skills) AS total_skills,
    (SELECT COUNT(*) FROM system_interests) AS total_interests,
    (SELECT COUNT(*) FROM email_templates) AS total_templates,
    (SELECT COUNT(*) FROM messages) AS total_messages;
