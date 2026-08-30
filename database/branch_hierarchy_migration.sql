-- Seller -> Branch -> Branch Manager -> Staff hierarchy.
-- Back up the database before running this migration in phpMyAdmin.
-- This replaces the legacy many-to-many staff_branches/branch_staff access model.

ALTER TABLE branches
  ADD COLUMN branch_code VARCHAR(50) NULL UNIQUE AFTER name,
  ADD COLUMN email VARCHAR(150) NULL AFTER phone,
  ADD COLUMN operating_hours VARCHAR(150) NULL AFTER hours,
  ADD COLUMN status ENUM('active','inactive','archived') NOT NULL DEFAULT 'active' AFTER is_active;

ALTER TABLE staff_profiles
  ADD COLUMN branch_id INT UNSIGNED NULL AFTER seller_id,
  ADD COLUMN created_by_manager_id INT UNSIGNED NULL AFTER branch_id,
  ADD KEY idx_staff_branch (branch_id),
  ADD KEY idx_staff_creator (created_by_manager_id),
  ADD CONSTRAINT fk_staff_profile_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
  ADD CONSTRAINT fk_staff_profile_manager FOREIGN KEY (created_by_manager_id) REFERENCES users(id) ON DELETE RESTRICT;

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

-- Legacy tables must no longer be used. Migrate their data to one branch per
-- account before removing them; do not run these DROP statements until reviewed.
-- DROP TABLE staff_branches;
-- DROP TABLE branch_staff;
