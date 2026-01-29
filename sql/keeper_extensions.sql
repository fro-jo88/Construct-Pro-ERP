-- sql/keeper_extensions.sql
USE wechecha_con;

-- 1. LINK STORE KEEPER TO STORE
ALTER TABLE stores ADD COLUMN keeper_id INT AFTER manager_id;
ALTER TABLE stores ADD FOREIGN KEY (keeper_id) REFERENCES users(id);

-- 2. INVENTORY MOVEMENT LOG
CREATE TABLE IF NOT EXISTS inventory_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL, -- Positive for in, Negative for out
    movement_type ENUM('issue', 'return', 'adjustment', 'transfer_in', 'transfer_out') NOT NULL,
    reference_id INT, -- ID of the related material_request or stock_transfer
    performed_by INT NOT NULL, -- Store Keeper
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES stores(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 3. ADD STORE_ID TO MATERIAL_REQUESTS IF MISSING
-- (Previously material_requests were site-based, but for fulfillment we need to know WHICH store is issuing)
-- If the project/site has a default store, we use that.
-- For now, we'll assume the Store Manager assigns the store or it's linked to the site.
-- I'll add fulfilling_store_id to material_requests.
ALTER TABLE material_requests ADD COLUMN fulfilling_store_id INT AFTER site_id;
ALTER TABLE material_requests ADD FOREIGN KEY (fulfilling_store_id) REFERENCES stores(id);
