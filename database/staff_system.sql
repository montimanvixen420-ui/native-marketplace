-- ============================================================
-- 002_staff_system.sql
-- Adds the Seller -> Branch -> Staff system on top of the
-- existing users / branches tables.
-- Run this in phpMyAdmin (or `mysql -u root tinda_marketplace < 002_staff_system.sql`)
-- ============================================================

-- 1. Allow 'staff' as a role value on the existing users table.
--    NOTE: only needed if `role` is an ENUM. If it's a VARCHAR,
--    delete this ALTER statement -- it already accepts any string.
ALTER TABLE users
  MODIFY role ENUM('superadmin','admin','supplier','customer','staff') NOT NULL;

-- 2. Staff-specific profile data (1-to-1 with users.id where role = 'staff')
CREATE TABLE IF NOT EXISTS staff_profiles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  seller_id INT UNSIGNED NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  position ENUM('cashier','inventory_staff','order_staff','branch_manager') NOT NULL DEFAULT 'cashier',
  profile_picture VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  is_archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_staff_user (user_id),
  KEY idx_staff_seller (seller_id),
  CONSTRAINT fk_staff_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_staff_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Many-to-many: which branches each staff member can access.
--    (Separate from the existing `branch_staff` table, which stays
--    untouched -- this is a distinct concept with its own rules.)
CREATE TABLE IF NOT EXISTS staff_branches (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_user_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_staff_branch (staff_user_id, branch_id),
  KEY idx_sb_branch (branch_id),
  CONSTRAINT fk_sb_user FOREIGN KEY (staff_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sb_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Lightweight audit log (spec section 22)
CREATE TABLE IF NOT EXISTS staff_audit_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id INT UNSIGNED NOT NULL,
  action VARCHAR(100) NOT NULL,
  module VARCHAR(50) NOT NULL,
  branch_id INT UNSIGNED DEFAULT NULL,
  description VARCHAR(255) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_actor (actor_user_id),
  KEY idx_audit_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
