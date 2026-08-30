-- Run after product_variants.sql. Stores the selected clothing variant in each completed order line.
-- Run each ALTER only once in phpMyAdmin. Back up your database before changing an existing table.
ALTER TABLE order_items ADD COLUMN variant_id INT UNSIGNED NULL AFTER product_id;
ALTER TABLE order_items ADD COLUMN variant_label VARCHAR(120) NULL AFTER product_name;

ALTER TABLE order_items
    ADD CONSTRAINT fk_order_items_variant
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
    ON DELETE SET NULL;
