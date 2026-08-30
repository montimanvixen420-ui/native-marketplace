-- Run this once only because product_moderation.sql was already applied.
ALTER TABLE product_moderation_flags
    ADD COLUMN flag_type ENUM('keyword', 'image') NOT NULL DEFAULT 'keyword' AFTER product_id;

ALTER TABLE product_moderation_flags
    DROP INDEX idx_moderation_queue,
    ADD INDEX idx_moderation_queue (status, flag_type, created_at);
