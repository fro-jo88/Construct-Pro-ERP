-- HR Module Schema
-- Leave, Payroll, Messaging, Recruitment, Site Assignments

USE wechecha_con;

-- 1. LEAVE MANAGEMENT
CREATE TABLE IF NOT EXISTS leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type ENUM('sick', 'annual', 'unpaid', 'maternity', 'paternity', 'other'),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by INT, -- HR User
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- 2. PAYROLL MANAGEMENT
CREATE TABLE IF NOT EXISTS payroll (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    month_year VARCHAR(7), -- Format: YYYY-MM
    base_salary DECIMAL(15, 2),
    attendance_days INT DEFAULT 0,
    deductions DECIMAL(15, 2) DEFAULT 0.00,
    bonuses DECIMAL(15, 2) DEFAULT 0.00,
    tax DECIMAL(15, 2) DEFAULT 0.00,
    net_pay DECIMAL(15, 2),
    status ENUM('draft', 'approved', 'paid') DEFAULT 'draft',
    generated_by INT, -- HR User
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (generated_by) REFERENCES users(id)
);

-- 3. HR INTERNAL MESSAGING
CREATE TABLE IF NOT EXISTS hr_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL, -- Can be User ID or 0 for All Employees (Announcement)
    subject VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id)
    -- receiver_id not strictly monitored constraint for broadcast capability
);

-- 4. SITE ASSIGNMENTS (History Tracking)
CREATE TABLE IF NOT EXISTS site_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    user_id INT NOT NULL, -- Foreman or Store Keeper
    role_type ENUM('foreman', 'store_keeper', 'engineer') NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT, -- HR User
    status ENUM('active', 'transferred', 'removed') DEFAULT 'active',
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

-- 5. RECRUITMENT
CREATE TABLE IF NOT EXISTS job_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    department VARCHAR(50),
    description TEXT,
    requirements TEXT,
    status ENUM('open', 'closed', 'on_hold') DEFAULT 'open',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100),
    phone VARCHAR(20),
    resume_path VARCHAR(255),
    status ENUM('applied', 'shortlisted', 'interviewed', 'hired', 'rejected') DEFAULT 'applied',
    interview_date DATETIME,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job_requests(id)
);
