-- Create the database first in phpMyAdmin, name it: tinda_marketplace
-- (or whatever you changed it to in config/database.php)

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin', 'supplier', 'customer') NOT NULL DEFAULT 'customer',
    status ENUM('pending', 'approved', 'suspended', 'banned') NOT NULL DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_user (user_id),
    INDEX idx_password_resets_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── If you already have an existing `users` table WITHOUT the status column ──
-- (this is why your schema didn't match your User.php model)
-- Run this ONCE in phpMyAdmin/CLI to sync it:
--
-- ALTER TABLE users
--     ADD COLUMN status ENUM('pending', 'approved', 'suspended', 'banned')
--     NOT NULL DEFAULT 'approved' AFTER role;

-- ── Create a superadmin account manually (you, as the developer) ──
-- DO NOT run the INSERT below directly as-is. You first need to
-- generate a hashed password using generate_hash.php (included in
-- this project) — just swap out the PASSWORD in there, then run it
-- in the browser or CLI: php generate_hash.php
--
-- Paste the result into the 'password' column below before running this INSERT:
--
-- INSERT INTO users (name, email, password, role, status) VALUES (
--     'Developer Admin',
--     '[email protected]',
--     'PASTE_HASHED_PASSWORD_DITO',
--     'superadmin',
--     'approved'
-- );
