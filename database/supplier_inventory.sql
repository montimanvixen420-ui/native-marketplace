CREATE TABLE IF NOT EXISTS supplier_inventory (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supplier_id INT UNSIGNED NOT NULL, item_name VARCHAR(255) NOT NULL, description TEXT NULL, unit VARCHAR(50) NOT NULL DEFAULT 'piece', unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00, quantity_available INT UNSIGNED NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_supplier_inventory_supplier FOREIGN KEY (supplier_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_supplier_inventory_available (supplier_id, is_active, quantity_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE stock_requests ADD COLUMN supply_id INT UNSIGNED NULL AFTER supplier_id;
ALTER TABLE stock_requests ADD CONSTRAINT fk_stock_request_supply FOREIGN KEY (supply_id) REFERENCES supplier_inventory(id) ON DELETE SET NULL;
