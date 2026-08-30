-- Run once after the existing database/schema migrations.
CREATE TABLE IF NOT EXISTS shipping_addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, customer_id INT UNSIGNED NOT NULL,
    recipient_name VARCHAR(150) NOT NULL, phone VARCHAR(30) NOT NULL, address_line1 VARCHAR(255) NOT NULL, address_line2 VARCHAR(255) NULL,
    barangay VARCHAR(150) NOT NULL, city VARCHAR(150) NOT NULL, province VARCHAR(150) NOT NULL, postal_code VARCHAR(12) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_shipping_addresses_customer (customer_id), CONSTRAINT fk_shipping_addresses_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_address_id INT UNSIGNED NULL AFTER customer_name, ADD COLUMN IF NOT EXISTS shipping_recipient_name VARCHAR(150) NULL AFTER shipping_address_id, ADD COLUMN IF NOT EXISTS shipping_phone VARCHAR(30) NULL AFTER shipping_recipient_name, ADD COLUMN IF NOT EXISTS shipping_address_text TEXT NULL AFTER shipping_phone;
ALTER TABLE orders ADD CONSTRAINT fk_orders_shipping_address FOREIGN KEY (shipping_address_id) REFERENCES shipping_addresses(id) ON DELETE SET NULL;
