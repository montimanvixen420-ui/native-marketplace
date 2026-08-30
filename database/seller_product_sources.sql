-- Run this once in phpMyAdmin after supplier_inventory.sql.
-- Each supplier delivery can be used to create one seller product listing.
ALTER TABLE products
    ADD COLUMN stock_request_id INT UNSIGNED NULL AFTER seller_id,
    ADD UNIQUE KEY uq_products_stock_request (stock_request_id),
    ADD CONSTRAINT fk_products_stock_request
        FOREIGN KEY (stock_request_id) REFERENCES stock_requests(id) ON DELETE SET NULL;
