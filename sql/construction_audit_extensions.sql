-- Construction Audit Dashboard Schema Extensions

-- Audit Findings Table
CREATE TABLE IF NOT EXISTS audit_findings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auditor_id INT NOT NULL,
    project_id INT,
    site_id INT,
    audit_date DATE NOT NULL,
    finding_category ENUM('planning_mismatch', 'material_variance', 'reporting_inconsistency', 'work_quality', 'safety_issue') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    description TEXT NOT NULL,
    planned_value DECIMAL(15,2),
    actual_value DECIMAL(15,2),
    variance DECIMAL(15,2),
    status ENUM('draft', 'submitted', 'acknowledged') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auditor_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    INDEX idx_audit_date (audit_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Audit Reports Table (Consolidated submission)
CREATE TABLE IF NOT EXISTS audit_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auditor_id INT NOT NULL,
    project_id INT,
    site_id INT,
    report_period_start DATE NOT NULL,
    report_period_end DATE NOT NULL,
    work_category VARCHAR(100),
    total_findings INT DEFAULT 0,
    critical_findings INT DEFAULT 0,
    material_variance_total DECIMAL(15,2) DEFAULT 0,
    planning_variance_pct DECIMAL(5,2) DEFAULT 0,
    summary TEXT,
    recommendations TEXT,
    status ENUM('draft', 'submitted', 'reviewed') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auditor_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_period (report_period_start, report_period_end),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Material Audit Trail (Links planning, store, and usage)
CREATE TABLE IF NOT EXISTS material_audit_trail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_report_id INT,
    project_id INT,
    site_id INT,
    material_id INT NOT NULL,
    planned_qty DECIMAL(15,2) DEFAULT 0,
    issued_qty DECIMAL(15,2) DEFAULT 0,
    used_qty DECIMAL(15,2) DEFAULT 0,
    variance DECIMAL(15,2) DEFAULT 0,
    variance_pct DECIMAL(5,2) DEFAULT 0,
    flag ENUM('normal', 'overuse', 'underuse', 'missing_record') DEFAULT 'normal',
    audit_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (audit_report_id) REFERENCES audit_reports(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (material_id) REFERENCES products(id),
    INDEX idx_audit_date (audit_date),
    INDEX idx_flag (flag)
) ENGINE=InnoDB;

-- Work Progress Audit (Planning vs Actual)
CREATE TABLE IF NOT EXISTS work_progress_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_report_id INT,
    project_id INT,
    site_id INT,
    work_item VARCHAR(255) NOT NULL,
    planned_progress DECIMAL(5,2) DEFAULT 0,
    actual_progress DECIMAL(5,2) DEFAULT 0,
    variance DECIMAL(5,2) DEFAULT 0,
    flag ENUM('on_track', 'partial', 'delayed', 'over_reported') DEFAULT 'on_track',
    audit_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (audit_report_id) REFERENCES audit_reports(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    INDEX idx_audit_date (audit_date),
    INDEX idx_flag (flag)
) ENGINE=InnoDB;
