-- Run once in phpMyAdmin before using the updated staff orders/returns pages.
-- No foreign keys are added: existing databases can use different signed/unsigned
-- definitions for users.id, while these audit values still work safely as IDs.
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS processed_by_user_id INT NULL AFTER customer_name,
  ADD KEY IF NOT EXISTS idx_orders_processed_by (processed_by_user_id);

ALTER TABLE return_requests
  ADD COLUMN IF NOT EXISTS handled_by_user_id INT NULL AFTER status,
  ADD COLUMN IF NOT EXISTS handled_at DATETIME NULL AFTER handled_by_user_id,
  ADD KEY IF NOT EXISTS idx_return_requests_handled_by (handled_by_user_id);
