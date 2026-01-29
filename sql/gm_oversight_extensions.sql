-- GM Oversight Schema Extensions

-- System Logs Table (if not exists)
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(100) NOT NULL,
    module VARCHAR(100),
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_action (action_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Audit Trail Table (if not exists)
CREATE TABLE IF NOT EXISTS audit_trail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    module VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    reference_id INT,
    old_value TEXT,
    new_value TEXT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_module (module),
    INDEX idx_reference (reference_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Approval History Table (if not exists)
CREATE TABLE IF NOT EXISTS approval_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module VARCHAR(100) NOT NULL,
    reference_id INT NOT NULL,
    approver_id INT NOT NULL,
    decision ENUM('approved', 'rejected', 'pre_approved', 'won', 'loss') NOT NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (approver_id) REFERENCES users(id),
    INDEX idx_module (module),
    INDEX idx_reference (reference_id),
    INDEX idx_approver (approver_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Add GM approval status columns to existing tables (if not exists)

-- Employees table
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS gm_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS gm_approved_by INT,
ADD COLUMN IF NOT EXISTS gm_approved_at TIMESTAMP NULL,
ADD FOREIGN KEY IF NOT EXISTS (gm_approved_by) REFERENCES users(id);

-- Leave Requests table
ALTER TABLE leave_requests 
ADD COLUMN IF NOT EXISTS gm_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS gm_approved_by INT,
ADD COLUMN IF NOT EXISTS gm_approved_at TIMESTAMP NULL,
ADD FOREIGN KEY IF NOT EXISTS (gm_approved_by) REFERENCES users(id);

-- Budgets table
ALTER TABLE budgets 
ADD COLUMN IF NOT EXISTS status ENUM('draft', 'pending', 'active', 'rejected', 'closed') DEFAULT 'draft',
ADD COLUMN IF NOT EXISTS gm_approved_by INT,
ADD COLUMN IF NOT EXISTS gm_approved_at TIMESTAMP NULL,
ADD FOREIGN KEY IF NOT EXISTS (gm_approved_by) REFERENCES users(id);

-- Material Requests table
ALTER TABLE material_requests 
ADD COLUMN IF NOT EXISTS gm_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS gm_approved_by INT,
ADD COLUMN IF NOT EXISTS gm_approved_at TIMESTAMP NULL,
ADD FOREIGN KEY IF NOT EXISTS (gm_approved_by) REFERENCES users(id);

-- Master Schedules table
ALTER TABLE master_schedules 
ADD COLUMN IF NOT EXISTS gm_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS gm_approved_by INT,
ADD COLUMN IF NOT EXISTS gm_approved_at TIMESTAMP NULL,
ADD FOREIGN KEY IF NOT EXISTS (gm_approved_by) REFERENCES users(id);

-- Projects table (add progress tracking if not exists)
ALTER TABLE projects 
ADD COLUMN IF NOT EXISTS progress_percent DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS risk_score INT DEFAULT 0;
