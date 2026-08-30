-- Run after product_branches.sql / orders_branch.sql. Back up the database first.
-- Seller POS stock stays in products/product_variants. Allocating stock moves it
-- out of that balance and into branch_stock exactly once.

CREATE TABLE IF NOT EXISTS branch_stock_allocations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_size VARCHAR(120) NOT NULL DEFAULT '',
  variant_color VARCHAR(120) NOT NULL DEFAULT '',
  quantity_allocated INT UNSIGNED NOT NULL,
  quantity_used INT UNSIGNED NOT NULL DEFAULT 0,
  quantity_remaining INT UNSIGNED NOT NULL,
  allocated_by_user_id INT UNSIGNED NOT NULL,
  note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_allocation_branch_stock (branch_id, product_id, variant_size, variant_color),
  KEY idx_allocation_seller (seller_id, created_at),
  CONSTRAINT fk_allocation_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_allocation_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
  CONSTRAINT fk_allocation_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_allocation_user FOREIGN KEY (allocated_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS damaged_products (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_size VARCHAR(120) NOT NULL DEFAULT '',
  variant_color VARCHAR(120) NOT NULL DEFAULT '',
  quantity INT UNSIGNED NOT NULL,
  reason VARCHAR(100) NOT NULL,
  note TEXT NULL,
  recorded_by_user_id INT UNSIGNED NOT NULL,
  recorded_by_role VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_damage_seller_created (seller_id, created_at),
  KEY idx_damage_branch_created (branch_id, created_at),
  CONSTRAINT fk_damage_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_damage_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
  CONSTRAINT fk_damage_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_damage_user FOREIGN KEY (recorded_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
