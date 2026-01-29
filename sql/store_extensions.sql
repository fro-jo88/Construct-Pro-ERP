-- sql/store_extensions.sql
USE wechecha_con;

-- 1. STOCKS TRANSFERS TABLE
CREATE TABLE IF NOT EXISTS stock_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    from_store_id INT NOT NULL,
    to_store_id INT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    manager_approval ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    transfer_status ENUM('pending', 'in_transit', 'completed', 'cancelled') DEFAULT 'pending',
    requested_by INT NOT NULL, -- Store Keeper
    approved_by INT,           -- Store Manager
    driver_id INT,             -- Logistics link
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES products(id),
    FOREIGN KEY (from_store_id) REFERENCES stores(id),
    FOREIGN KEY (to_store_id) REFERENCES stores(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (driver_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 2. ADD REORDER THRESHOLD TO PRODUCTS
ALTER TABLE products ADD COLUMN min_threshold DECIMAL(15, 2) DEFAULT 0;

-- 3. ENHANCE MATERIAL REQUESTS FOR STORE MANAGER APPROVAL
-- Adding a column for store manager specific approval if not using 'status'
ALTER TABLE material_requests 
ADD COLUMN store_manager_approval ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER hr_review_status,
ADD COLUMN store_manager_id INT AFTER store_manager_approval;
