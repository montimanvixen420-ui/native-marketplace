CREATE TABLE IF NOT EXISTS branch_damage_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_size VARCHAR(120) NOT NULL DEFAULT '',
  variant_color VARCHAR(120) NOT NULL DEFAULT '',
  quantity INT UNSIGNED NOT NULL,
  note TEXT NULL,
  reported_by_user_id INT UNSIGNED NOT NULL,
  status ENUM('reported','approved','rejected') NOT NULL DEFAULT 'reported',
  reviewed_by_user_id INT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_damage_report_branch_status (branch_id, status),
  CONSTRAINT fk_damage_report_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
  CONSTRAINT fk_damage_report_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_damage_report_reporter FOREIGN KEY (reported_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_damage_report_reviewer FOREIGN KEY (reviewed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
