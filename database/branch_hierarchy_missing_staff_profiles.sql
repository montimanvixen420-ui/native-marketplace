-- HOTFIX for databases where branch_hierarchy_migration.sql stopped because
-- staff_profiles did not already exist. Run this ONCE after the failed import.
-- Do NOT re-run branch_hierarchy_migration.sql: its branches changes succeeded.

ALTER TABLE users
  MODIFY role ENUM('superadmin','admin','supplier','customer','staff') NOT NULL;

CREATE TABLE IF NOT EXISTS staff_profiles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  seller_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NULL,
  created_by_manager_id INT UNSIGNED NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  position ENUM('cashier','inventory_staff','order_staff','customer_service','branch_manager') NOT NULL DEFAULT 'cashier',
  profile_picture VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  is_archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_staff_user (user_id),
  KEY idx_staff_seller (seller_id),
  KEY idx_staff_branch (branch_id),
  KEY idx_staff_creator (created_by_manager_id),
  CONSTRAINT fk_staff_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_staff_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_staff_profile_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
  CONSTRAINT fk_staff_profile_manager FOREIGN KEY (created_by_manager_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS branch_managers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  status ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_manager_user (user_id),
  UNIQUE KEY uniq_branch_manager (branch_id),
  KEY idx_manager_seller (seller_id),
  CONSTRAINT fk_manager_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_manager_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
  CONSTRAINT fk_manager_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
