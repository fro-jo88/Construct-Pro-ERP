-- Bidding Module Schema
-- Financial & Technical Bids, Planning Integration, GM Approval

USE wechecha_con;

-- 1. EXTEND TENDERS (Optional, if needed, mostly covered)
-- ALTER TABLE tenders ADD COLUMN bidding_stage ENUM('open', 'locked_financial', 'locked_technical', 'gm_review') DEFAULT 'open';

-- 2. FINANCIAL BIDS
CREATE TABLE financial_bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_id INT NOT NULL,
    total_amount DECIMAL(15, 2) DEFAULT 0.00,
    profit_margin_percent DECIMAL(5, 2) DEFAULT 0.00,
    status ENUM('draft', 'ready', 'submitted', 'gm_query', 'approved', 'rejected') DEFAULT 'draft',
    gm_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE
);

CREATE TABLE financial_bid_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    financial_bid_id INT NOT NULL,
    item_description VARCHAR(255) NOT NULL,
    unit VARCHAR(50),
    quantity DECIMAL(15, 2) NOT NULL,
    unit_rate DECIMAL(15, 2) NOT NULL,
    total_amount DECIMAL(15, 2) GENERATED ALWAYS AS (quantity * unit_rate) STORED,
    FOREIGN KEY (financial_bid_id) REFERENCES financial_bids(id) ON DELETE CASCADE
);

-- 3. TECHNICAL BIDS
CREATE TABLE technical_bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_id INT NOT NULL,
    compliance_score INT DEFAULT 0, -- 0 to 100
    status ENUM('draft', 'planning_pending', 'ready', 'submitted', 'gm_query', 'approved', 'rejected') DEFAULT 'draft',
    gm_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE
);

CREATE TABLE technical_compliance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    technical_bid_id INT NOT NULL,
    requirement VARCHAR(255) NOT NULL,
    is_compliant ENUM('yes', 'no', 'clarification') DEFAULT 'yes',
    remarks TEXT,
    FOREIGN KEY (technical_bid_id) REFERENCES technical_bids(id) ON DELETE CASCADE
);

-- 4. PLANNING INTEGRATION
CREATE TABLE planning_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_id INT NOT NULL,
    requested_by INT NOT NULL, -- Tech Bid Manager
    request_details TEXT,
    status ENUM('requested', 'in_progress', 'completed') DEFAULT 'requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(id),
    FOREIGN KEY (requested_by) REFERENCES users(id)
);

CREATE TABLE planning_outputs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    output_type ENUM('ms_schedule', 'material_list', 'manpower_plan', 'machinery_list'),
    file_path VARCHAR(255),
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES planning_requests(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- 5. BID SUBMISSIONS (LINKING BOTH) for GM
CREATE TABLE bid_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_id INT NOT NULL,
    financial_bid_id INT NOT NULL,
    technical_bid_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'gm_query', 'approved', 'rejected') DEFAULT 'pending',
    gm_decision_at TIMESTAMP NULL,
    FOREIGN KEY (tender_id) REFERENCES tenders(id),
    FOREIGN KEY (financial_bid_id) REFERENCES financial_bids(id),
    FOREIGN KEY (technical_bid_id) REFERENCES technical_bids(id)
);
