-- Run once in phpMyAdmin after the existing orders table has been created.
-- Adds fulfillment data without deleting existing orders.
ALTER TABLE orders
    MODIFY COLUMN status ENUM('pending', 'packed', 'shipped', 'completed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    ADD COLUMN courier VARCHAR(100) NULL AFTER status,
    ADD COLUMN tracking_number VARCHAR(120) NULL AFTER courier,
    ADD COLUMN packed_at DATETIME NULL AFTER tracking_number,
    ADD COLUMN shipped_at DATETIME NULL AFTER packed_at,
    ADD COLUMN delivered_at DATETIME NULL AFTER shipped_at,
    ADD COLUMN cancelled_at DATETIME NULL AFTER delivered_at;
