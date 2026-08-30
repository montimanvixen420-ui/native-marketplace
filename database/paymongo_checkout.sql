-- Run this once in phpMyAdmin before accepting PayMongo payments.
-- The app uses the existing order status as the fulfilment status; it only creates
-- an order after PayMongo confirms that the checkout session has been paid.
ALTER TABLE orders MODIFY payment_method ENUM('cash','gcash','card','paymongo','other') NOT NULL DEFAULT 'cash';
ALTER TABLE system_settings MODIFY payment_methods VARCHAR(255) NOT NULL DEFAULT 'cash,gcash,card,paymongo,other';
