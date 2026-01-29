-- sql/transport_extensions.sql
USE wechecha_con;

-- 1. TRANSPORT ORDERS (Connecting Material Issues to Drivers)
CREATE TABLE IF NOT EXISTS transport_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_type ENUM('material_request', 'stock_transfer') NOT NULL,
    reference_id INT NOT NULL, -- ID of material_request or stock_transfer
    origin_store_id INT NOT NULL,
    destination_site_id INT, -- NULL if it's a store-to-store transfer
    destination_store_id INT, -- NULL if it's a store-to-site delivery
    
    requested_date DATE NOT NULL,
    priority ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
    
    driver_id INT, -- Assigned Driver
    vehicle_id INT, -- Assigned Vehicle
    
    status ENUM('pending_assignment', 'assigned', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending_assignment',
    
    assigned_at TIMESTAMP NULL,
    dispatched_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    
    load_type VARCHAR(100), -- Description of the load
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (origin_store_id) REFERENCES stores(id),
    FOREIGN KEY (destination_site_id) REFERENCES sites(id),
    FOREIGN KEY (destination_store_id) REFERENCES stores(id),
    FOREIGN KEY (driver_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
) ENGINE=InnoDB;

-- 2. TRIP TRACKING (Live updates from Driver)
CREATE TABLE IF NOT EXISTS transport_status_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transport_order_id INT NOT NULL,
    status ENUM('assigned', 'in_transit', 'delivered', 'problem') NOT NULL,
    location_note VARCHAR(255),
    update_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT NOT NULL,
    FOREIGN KEY (transport_order_id) REFERENCES transport_orders(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 3. ENHANCE VEHICLES TABLE (If not already enhanced)
-- ALTER TABLE vehicles ADD COLUMN current_kms INT DEFAULT 0;
-- ALTER TABLE vehicles ADD COLUMN last_maintenance_date DATE;
