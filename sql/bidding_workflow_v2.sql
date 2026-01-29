-- bidding_workflow_v2.sql
-- Implements Parallel Workflow Logic: HR -> (Tech & Fin) -> GM

USE wechecha_con;

-- 1. Create Bid Assignments table for parallel tracking
CREATE TABLE IF NOT EXISTS bid_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bid_id INT NOT NULL,
    assignment_role ENUM('TECHNICAL', 'FINANCIAL') NOT NULL,
    status ENUM('pending', 'submitted') DEFAULT 'pending',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP NULL,
    FOREIGN KEY (bid_id) REFERENCES bids(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Standardize Bids Status for the new workflow
ALTER TABLE bids 
    MODIFY COLUMN status ENUM('under_review', 'ready_for_gm', 'approved', 'rejected', 'cancelled') DEFAULT 'under_review';

-- 3. Ensure financial_bids and technical_bids are ready for the data storage
-- (These already exist from previous fixes, but we ensure they link properly)
CREATE INDEX IF NOT EXISTS idx_assignments_bid_status ON bid_assignments(bid_id, status);
