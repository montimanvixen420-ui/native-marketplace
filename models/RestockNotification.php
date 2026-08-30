<?php

require_once __DIR__ . '/../config/database.php';

class RestockNotification
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }
    public function subscribe(int $customerId, int $productId, ?int $variantId): bool {
        $stmt = $this->db->prepare('INSERT INTO restock_notifications (customer_id, product_id, variant_id) VALUES (:customer_id, :product_id, :variant_id) ON DUPLICATE KEY UPDATE notified_at = NULL');
        return $stmt->execute(['customer_id' => $customerId, 'product_id' => $productId, 'variant_id' => $variantId]);
    }
    /** Marks pending subscriptions as notified; an email provider can later send these records. */
    public function markAvailable(int $productId, ?int $variantId): void {
        $sql = 'UPDATE restock_notifications SET notified_at = NOW() WHERE product_id = :product_id AND notified_at IS NULL';
        $params = ['product_id' => $productId];
        if ($variantId !== null) { $sql .= ' AND (variant_id = :variant_id OR variant_id IS NULL)'; $params['variant_id'] = $variantId; }
        $this->db->prepare($sql)->execute($params);
    }

    public function markProductAvailable(int $productId): void {
        $stmt = $this->db->prepare('UPDATE restock_notifications SET notified_at = NOW() WHERE product_id = :product_id AND variant_id IS NULL AND notified_at IS NULL');
        $stmt->execute(['product_id' => $productId]);
    }
}
