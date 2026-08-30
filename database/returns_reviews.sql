-- Run once in phpMyAdmin after orders and order_items already exist.
CREATE TABLE IF NOT EXISTS return_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    reason VARCHAR(100) NOT NULL,
    details TEXT NULL,
    status ENUM('requested', 'approved', 'rejected', 'refunded') NOT NULL DEFAULT 'requested',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_return_per_item (order_item_id),
    CONSTRAINT fk_return_order_item FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_return_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    fit_feedback ENUM('too_small', 'true_to_size', 'too_large') NOT NULL,
    comment TEXT NULL,
    photo_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review_per_item (order_item_id),
    CONSTRAINT fk_review_order_item FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
