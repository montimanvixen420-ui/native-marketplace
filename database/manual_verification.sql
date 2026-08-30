-- Run once after seller_applications.sql.
ALTER TABLE seller_applications
  ADD COLUMN application_role ENUM('admin', 'supplier') NOT NULL DEFAULT 'admin' AFTER user_id,
  ADD COLUMN selfie_path VARCHAR(255) NULL AFTER document_path,
  ADD COLUMN verification_status ENUM('pending_review', 'verified', 'rejected') NOT NULL DEFAULT 'pending_review' AFTER selfie_path,
  ADD COLUMN review_notes TEXT NULL AFTER verification_status,
  ADD COLUMN reviewed_at DATETIME NULL AFTER review_notes;
