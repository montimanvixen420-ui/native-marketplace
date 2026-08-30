-- ============================================================
-- 003_orders_branch.sql
-- Adds branch_id to orders so both online checkout and POS sales
-- can be tied to a specific branch. Nullable on purpose: existing
-- orders (placed before this migration) simply have no branch,
-- and orders from sellers with zero branches configured still work
-- exactly as before.
-- ============================================================

ALTER TABLE orders
  ADD COLUMN branch_id INT UNSIGNED NULL AFTER seller_id,
  ADD CONSTRAINT fk_orders_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
  ADD KEY idx_orders_branch (branch_id);
