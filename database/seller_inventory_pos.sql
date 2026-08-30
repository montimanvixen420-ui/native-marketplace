-- products/product_variants.stock is the Seller's master inventory balance.
-- Run this before using the new Seller Inventory and Seller POS transfer pages.
CREATE TABLE IF NOT EXISTS seller_pos_stock (
  seller_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_size VARCHAR(120) NOT NULL DEFAULT '',
  variant_color VARCHAR(120) NOT NULL DEFAULT '',
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (seller_id, product_id, variant_size, variant_color),
  CONSTRAINT fk_seller_pos_stock_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_seller_pos_stock_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seller_inventory_transfers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_size VARCHAR(120) NOT NULL DEFAULT '',
  variant_color VARCHAR(120) NOT NULL DEFAULT '',
  quantity INT UNSIGNED NOT NULL,
  direction ENUM('inventory_to_pos','pos_to_inventory') NOT NULL,
  performed_by_user_id INT UNSIGNED NOT NULL,
  note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_inventory_transfer_seller (seller_id, created_at),
  CONSTRAINT fk_inventory_transfer_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_inventory_transfer_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_inventory_transfer_user FOREIGN KEY (performed_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
