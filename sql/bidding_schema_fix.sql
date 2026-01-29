-- bidding_schema_fix.sql
-- Fixes the schema mismatch between code (uses bid_id) and database (uses tender_id)
-- Run this to align the database with the current BidManager code

USE wechecha_con;

-- 1. Create the BIDS table that BidManager expects
CREATE TABLE IF NOT EXISTS bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_no VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    client_name VARCHAR(255),
    description TEXT,
    deadline DATETIME,
    status ENUM('DRAFT', 'TECHNICAL_COMPLETED', 'FINANCIAL_COMPLETED', 'GM_PRE_APPROVED', 'FINANCE_FINAL_REVIEW', 'WON', 'LOSS') DEFAULT 'DRAFT',
    submission_mode ENUM('softcopy', 'hardcopy', 'both') DEFAULT 'softcopy',
    bid_file VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create bid_decisions table for GM approval workflow
CREATE TABLE IF NOT EXISTS bid_decisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bid_id INT NOT NULL,
    gm_id INT NOT NULL,
    decision ENUM('PRE_APPROVED', 'FINAL_APPROVED', 'REJECTED', 'QUERY') NOT NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bid_id) REFERENCES bids(id) ON DELETE CASCADE,
    FOREIGN KEY (gm_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Fix financial_bids table - add bid_id column if it doesn't exist
-- First check if bid_id column exists, if not add it
SET @col_exists = (SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'wechecha_con' 
    AND TABLE_NAME = 'financial_bids' 
    AND COLUMN_NAME = 'bid_id');

-- Add bid_id column if it doesn't exist
ALTER TABLE financial_bids 
    ADD COLUMN IF NOT EXISTS bid_id INT NULL AFTER id,
    ADD COLUMN IF NOT EXISTS labor_cost DECIMAL(15,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS material_cost DECIMAL(15,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS equipment_cost DECIMAL(15,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS overhead_cost DECIMAL(15,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS tax DECIMAL(15,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS document_path VARCHAR(255) NULL;

-- 4. Fix technical_bids table - add bid_id column if it doesn't exist
ALTER TABLE technical_bids 
    ADD COLUMN IF NOT EXISTS bid_id INT NULL AFTER id,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 5. Add foreign key constraints for bid_id (only if columns were just added)
-- Note: May fail if constraints already exist, that's OK

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_financial_bids_bid_id ON financial_bids(bid_id);
CREATE INDEX IF NOT EXISTS idx_technical_bids_bid_id ON technical_bids(bid_id);
CREATE INDEX IF NOT EXISTS idx_bids_status ON bids(status);
CREATE INDEX IF NOT EXISTS idx_bids_created_by ON bids(created_by);

-- 6. Update planning_requests to use bid_id
ALTER TABLE planning_requests 
    ADD COLUMN IF NOT EXISTS bid_id INT NULL AFTER id;

-- Show success
SELECT 'Schema fix applied successfully!' as Status;
