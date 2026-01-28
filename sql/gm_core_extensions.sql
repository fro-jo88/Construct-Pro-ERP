-- sql/gm_core_extensions.sql
USE wechecha_con;

-- 1. Unified Approval History (for immutable tracking)
CREATE TABLE IF NOT EXISTS approval_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module ENUM('HR', 'FINANCE', 'BIDS', 'PROCUREMENT', 'PLANNING', 'INVENTORY') NOT NULL,
    reference_id INT NOT NULL, -- The ID of the employee, budget, bid, etc.
    approver_id INT NOT NULL,
    decision ENUM('approved', 'rejected', 'queried') NOT NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (approver_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 2. Emergency Incidents (for site-level escalation)
CREATE TABLE IF NOT EXISTS site_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    reporter_id INT NOT NULL,
    type ENUM('accident', 'shortage', 'delay', 'other') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    description TEXT,
    gm_acknowledged BOOLEAN DEFAULT FALSE,
    gm_acknowledgment_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (reporter_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 3. Extend Projects for GM oversight
ALTER TABLE projects 
ADD COLUMN risk_score INT DEFAULT 0,
ADD COLUMN status_summary TEXT,
ADD COLUMN progress_percent DECIMAL(5,2) DEFAULT 0.00;

-- 4. Budget & Finance extensions for GM visibility
ALTER TABLE budgets 
ADD COLUMN budget_name VARCHAR(100) AFTER project_id, 
ADD COLUMN status ENUM('pending', 'active', 'rejected') DEFAULT 'pending' AFTER remaining_amount;

-- 5. Initial KPI Mock Data (Optional but good for first view)
-- (Skipping for now to use real aggregations)
