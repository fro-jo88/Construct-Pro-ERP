-- Final HR Module Extensions
USE wechecha_con;

-- 1. Ensure Recruitment Tables are complete
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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

-- 2. Site Staff Assignments (Link Foreman/Store Keeper)
CREATE TABLE IF NOT EXISTS site_staff_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    user_id INT NOT NULL,
    role_type ENUM('foreman', 'store_keeper', 'engineer') NOT NULL,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'removed/transferred') DEFAULT 'active',
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 3. Attendance Link to Payroll (Site-wise)
CREATE TABLE IF NOT EXISTS site_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    employee_id INT NOT NULL,
    work_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'on_leave') DEFAULT 'present',
    overtime_hours DECIMAL(5, 2) DEFAULT 0,
    foreman_submitted_by INT,
    hr_reviewed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (foreman_submitted_by) REFERENCES users(id),
    FOREIGN KEY (hr_reviewed_by) REFERENCES users(id),
    UNIQUE KEY unique_attendance (employee_id, work_date)
) ENGINE=InnoDB;

-- 4. Material Request Forwarding (Extension to material_requests)
ALTER TABLE material_requests 
ADD COLUMN hr_review_status ENUM('pending', 'validated', 'rejected') DEFAULT 'pending' AFTER gm_approval_status,
ADD COLUMN hr_reviewed_by INT AFTER hr_review_status,
ADD COLUMN store_forwarded_at TIMESTAMP NULL AFTER hr_reviewed_by;

-- 5. Messaging Extension (Announcements)
CREATE TABLE IF NOT EXISTS hr_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT,
    target_group ENUM('all', 'office', 'site') DEFAULT 'all',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 6. Ensure Employees has GM Approval fields (already in hr_schema_update.sql but being safe)
-- ALTER TABLE employees ADD COLUMN gm_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending';
-- ALTER TABLE employees ADD COLUMN employee_id_card VARCHAR(100);

-- 7. Audit Logging (Action History)
CREATE TABLE IF NOT EXISTS hr_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(100), -- 'create_tender', 'assign_role', 'approve_payroll'
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
