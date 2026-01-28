-- HR Module Database Schema Extensions

USE wechecha_con;

-- 1. ENHANCE EMPLOYEES
ALTER TABLE employees
ADD COLUMN contract_type ENUM('office', 'site', 'contract', 'temporary') AFTER designation,
ADD COLUMN current_site_id INT AFTER base_salary,
ADD COLUMN gm_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER status,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD CONSTRAINT fk_emp_site FOREIGN KEY (current_site_id) REFERENCES sites(id);

-- 2. LABOR MANAGEMENT
CREATE TABLE labor_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    requested_by INT NOT NULL, -- Foreman
    role_required VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    gm_approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (gm_approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE site_labor_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    site_id INT NOT NULL,
    assigned_by INT NOT NULL, -- HR
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'completed', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 3. LEAVE MANAGEMENT
CREATE TABLE leave_balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type ENUM('annual', 'sick', 'emergency', 'unpaid'),
    total_days INT DEFAULT 0,
    used_days INT DEFAULT 0,
    remaining_days INT DEFAULT 0,
    year INT NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

CREATE TABLE leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type ENUM('annual', 'sick', 'emergency', 'unpaid'),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending_hr', 'pending_gm', 'approved', 'rejected') DEFAULT 'pending_hr',
    hr_approved_by INT,
    gm_approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (hr_approved_by) REFERENCES users(id),
    FOREIGN KEY (gm_approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 4. PAYROLL MANAGEMENT
CREATE TABLE payrolls (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    month INT NOT NULL,
    year INT NOT NULL,
    type ENUM('office', 'site') NOT NULL,
    status ENUM('draft', 'pending_gm', 'approved', 'paid') DEFAULT 'draft',
    total_amount DECIMAL(15, 2),
    generated_by INT,
    approved_by INT, -- GM
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE payroll_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_id INT NOT NULL,
    employee_id INT NOT NULL,
    base_salary DECIMAL(15, 2),
    additions DECIMAL(15, 2) DEFAULT 0, -- Allowances, Overtime
    deductions DECIMAL(15, 2) DEFAULT 0, -- Tax, Loan, Leave
    net_salary DECIMAL(15, 2),
    remarks TEXT,
    FOREIGN KEY (payroll_id) REFERENCES payrolls(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

-- 5. MESSAGING & NOTICES
CREATE TABLE hr_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL, -- HR or GM
    recipient_group ENUM('all', 'office', 'site', 'all_staff'), 
    recipient_user_id INT, -- Optional specific user
    title VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE, -- For individual msgs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (recipient_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 6. AUDIT & APPROVAL LOGS
CREATE TABLE approval_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL, -- 'employee', 'leave', 'payroll', 'labor'
    entity_id INT NOT NULL,
    action ENUM('approve', 'reject', 'request_info'),
    actor_id INT NOT NULL, -- GM or HR
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB;
