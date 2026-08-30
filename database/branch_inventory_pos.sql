-- branch_stock is now Branch Inventory. This table is the branch's sellable POS balance.
CREATE TABLE IF NOT EXISTS branch_pos_stock (
  branch_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_size VARCHAR(120) NOT NULL DEFAULT '',
  variant_color VARCHAR(120) NOT NULL DEFAULT '',
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (branch_id, product_id, variant_size, variant_color),
  CONSTRAINT fk_branch_pos_stock_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
  CONSTRAINT fk_branch_pos_stock_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS branch_inventory_transfers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_size VARCHAR(120) NOT NULL DEFAULT '',
  variant_color VARCHAR(120) NOT NULL DEFAULT '',
  quantity INT UNSIGNED NOT NULL,
  direction ENUM('inventory_to_pos','pos_to_inventory','inventory_to_seller') NOT NULL,
  performed_by_user_id INT UNSIGNED NOT NULL,
  note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_branch_transfer_branch (branch_id, created_at),
  CONSTRAINT fk_branch_transfer_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
  CONSTRAINT fk_branch_transfer_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_branch_transfer_user FOREIGN KEY (performed_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
