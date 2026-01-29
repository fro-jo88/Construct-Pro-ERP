-- core_logging_schema.sql
-- Schema for centralized logging system

-- System Logs Table (replaces scattered echo/debug)
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    level ENUM('DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL') DEFAULT 'INFO',
    module VARCHAR(50) NOT NULL,
    action VARCHAR(255) NOT NULL,
    context JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_level (level),
    INDEX idx_module (module),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Trail Table (for compliance and oversight)
CREATE TABLE IF NOT EXISTS audit_trail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(100) NULL,
    action VARCHAR(100) NOT NULL,
    table_affected VARCHAR(100) NULL,
    record_id VARCHAR(50) NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_affected (table_affected),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions Table (for granular access control)
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_name VARCHAR(100) NOT NULL,
    granted TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_role_permission (role_id, permission_name),
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some default permissions for key roles
INSERT IGNORE INTO role_permissions (role_id, permission_name, granted) 
SELECT id, 'final_approval', 1 FROM roles WHERE role_code IN ('GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN');

INSERT IGNORE INTO role_permissions (role_id, permission_name, granted) 
SELECT id, 'approve_employee', 1 FROM roles WHERE role_code IN ('GM', 'HR_MANAGER', 'SUPER_ADMIN', 'SYSTEM_ADMIN');

INSERT IGNORE INTO role_permissions (role_id, permission_name, granted) 
SELECT id, 'approve_expense', 1 FROM roles WHERE role_code IN ('GM', 'FINANCE_HEAD', 'SUPER_ADMIN', 'SYSTEM_ADMIN');

INSERT IGNORE INTO role_permissions (role_id, permission_name, granted) 
SELECT id, 'view_audit_logs', 1 FROM roles WHERE role_code IN ('GM', 'CONSTRUCTION_AUDIT', 'AUDIT_TEAM', 'FINANCE_HEAD', 'SUPER_ADMIN', 'SYSTEM_ADMIN');

INSERT IGNORE INTO role_permissions (role_id, permission_name, granted) 
SELECT id, 'manage_users', 1 FROM roles WHERE role_code IN ('SYSTEM_ADMIN', 'SUPER_ADMIN');
