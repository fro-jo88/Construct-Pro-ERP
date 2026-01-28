-- Foreman Module Schema
-- Daily Reports, Incidents, Foreman Requests

USE wechecha_con;

-- 1. DAILY PROGRESS REPORTING
CREATE TABLE daily_site_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    foreman_id INT NOT NULL,
    report_date DATE NOT NULL,
    
    -- Work Progress
    planned_work TEXT, -- From Planning Module (Snapshot)
    actual_work TEXT,  -- Field formerly 'work_completed'
    progress_percent INT DEFAULT 0,
    
    -- Resources
    labor_count INT DEFAULT 0, -- Field formerly 'manpower_count'
    equipment_used TEXT,
    material_used JSON, -- JSON Array: [{"item": "Cement", "qty": 10, "unit": "bags"}]
    
    -- Issues & Safety
    blockers TEXT,
    safety_notes TEXT,
    weather_condition VARCHAR(100),
    
    -- Status Workflow
    status ENUM('submitted', 'reviewed', 'approved') DEFAULT 'submitted',
    
    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (foreman_id) REFERENCES users(id)
);

CREATE TABLE report_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    file_path VARCHAR(255),
    caption VARCHAR(255),
    FOREIGN KEY (report_id) REFERENCES daily_site_reports(id) ON DELETE CASCADE
);

-- 2. INCIDENT REPORTING
CREATE TABLE site_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    reported_by INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    status ENUM('open', 'investigating', 'resolved') DEFAULT 'open',
    incident_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (reported_by) REFERENCES users(id)
);

-- 3. MATERIAL REQUESTS (Specific for Site)
-- Might already exist in procurement, but ensuring structure
CREATE TABLE IF NOT EXISTS material_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    requested_by INT NOT NULL,
    item_name VARCHAR(255), -- Or link to products table
    quantity DECIMAL(10, 2),
    priority ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'ordered', 'delivered') DEFAULT 'pending',
    gm_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (requested_by) REFERENCES users(id)
);

-- 4. WEEKLY PLANS (Read-Only for Foreman)
CREATE TABLE weekly_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    week_start_date DATE,
    week_end_date DATE,
    goals TEXT,
    planned_labor_count INT,
    status ENUM('draft', 'approved') DEFAULT 'draft',
    FOREIGN KEY (site_id) REFERENCES sites(id)
);

-- 5. SAFETY CHECKS (Simple Daily)
CREATE TABLE safety_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    check_date DATE NOT NULL,
    checked_by INT NOT NULL,
    ppe_compliance BOOLEAN DEFAULT TRUE,
    machinery_safe BOOLEAN DEFAULT TRUE,
    notes TEXT,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (checked_by) REFERENCES users(id)
);
