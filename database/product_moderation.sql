-- Run this once after your existing database scripts.
ALTER TABLE products
    MODIFY COLUMN status ENUM('active', 'inactive', 'pending_review', 'rejected') NOT NULL DEFAULT 'active';

CREATE TABLE IF NOT EXISTS product_moderation_flags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    flag_type ENUM('keyword', 'image') NOT NULL DEFAULT 'keyword',
    matched_keywords TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'superseded') NOT NULL DEFAULT 'pending',
    reviewer_id INT UNSIGNED NULL,
    review_note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    CONSTRAINT fk_moderation_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_moderation_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_moderation_queue (status, flag_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
