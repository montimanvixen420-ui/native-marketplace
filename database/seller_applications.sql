-- Run this once in phpMyAdmin after your existing TINDA tables are created.
CREATE TABLE IF NOT EXISTS seller_applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    business_name VARCHAR(150) NOT NULL,
    business_description TEXT NOT NULL,
    phone VARCHAR(30) NOT NULL,
    business_address VARCHAR(255) NOT NULL,
    logo_path VARCHAR(255) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_seller_applications_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
