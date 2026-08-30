-- Run this once in phpMyAdmin after the existing schema and product_variants.sql.
-- Adds clothing fit/measurement data, seller vouchers, and back-in-stock subscriptions.

ALTER TABLE products
    ADD COLUMN size_guide TEXT NULL AFTER description,
    ADD COLUMN fit_information VARCHAR(255) NULL AFTER size_guide;

CREATE TABLE IF NOT EXISTS vouchers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id INT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    discount_type ENUM('fixed', 'percent', 'free_shipping') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    minimum_order DECIMAL(10,2) NOT NULL DEFAULT 0,
    maximum_discount DECIMAL(10,2) NULL,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    usage_limit INT UNSIGNED NULL,
    times_used INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_seller_voucher_code (seller_id, code),
    CONSTRAINT fk_vouchers_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS restock_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    notified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_restock_subscription (customer_id, product_id, variant_id),
    CONSTRAINT fk_restock_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_restock_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_restock_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
