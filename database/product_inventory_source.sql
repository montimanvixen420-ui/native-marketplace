-- Marks products created as a separate POS listing from a Seller Inventory item.
ALTER TABLE products
  ADD COLUMN inventory_source_product_id INT UNSIGNED NULL AFTER stock_request_id,
  ADD KEY idx_products_inventory_source (inventory_source_product_id),
  ADD CONSTRAINT fk_products_inventory_source FOREIGN KEY (inventory_source_product_id) REFERENCES products(id) ON DELETE SET NULL;
