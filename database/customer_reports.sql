CREATE TABLE customer_reports (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, reporter_id INT UNSIGNED NOT NULL, target_type ENUM('product','seller') NOT NULL, target_id INT UNSIGNED NOT NULL, reason VARCHAR(120) NOT NULL, details TEXT NOT NULL, status ENUM('open','reviewing','resolved','dismissed') NOT NULL DEFAULT 'open', reviewer_id INT UNSIGNED NULL, review_note TEXT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, reviewed_at DATETIME NULL,
 KEY idx_customer_reports_status (status), KEY idx_customer_reports_target (target_type,target_id),
 CONSTRAINT fk_customer_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_customer_reports_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
