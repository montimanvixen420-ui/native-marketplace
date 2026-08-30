<?php

require_once __DIR__ . '/../config/database.php';

class Wishlist
{
    private PDO $db;

    public const PRIORITIES = ['low', 'medium', 'high'];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function allByCustomer(int $customerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT w.*, p.name AS product_name, p.price, p.image_url, p.stock, p.status AS product_status,
                    u.name AS seller_name
             FROM wishlist_items w
             INNER JOIN products p ON p.id = w.product_id
             INNER JOIN users u ON u.id = p.seller_id
             WHERE w.customer_id = :customer_id
             ORDER BY FIELD(w.priority, 'high', 'medium', 'low'), w.created_at DESC"
        );
        $stmt->execute(['customer_id' => $customerId]);

        return $stmt->fetchAll();
    }

    public function exists(int $customerId, int $productId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM wishlist_items WHERE customer_id = :customer_id AND product_id = :product_id LIMIT 1"
        );
        $stmt->execute(['customer_id' => $customerId, 'product_id' => $productId]);

        return (bool) $stmt->fetch();
    }

    /** Plain list of product ids a customer has wishlisted — used to pre-fill
     *  the heart icon state on the storefront without an N+1 query. */
    public function productIdsByCustomer(int $customerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT product_id FROM wishlist_items WHERE customer_id = :customer_id"
        );
        $stmt->execute(['customer_id' => $customerId]);

        return array_map('intval', array_column($stmt->fetchAll(), 'product_id'));
    }

    public function add(int $customerId, int $productId, string $notes = '', string $priority = 'medium'): bool
    {
        if (!in_array($priority, self::PRIORITIES, true)) {
            $priority = 'medium';
        }

        // Ignore duplicates gracefully (unique key on customer_id + product_id)
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO wishlist_items (customer_id, product_id, notes, priority, created_at)
             VALUES (:customer_id, :product_id, :notes, :priority, NOW())"
        );

        return $stmt->execute([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'notes' => $notes !== '' ? $notes : null,
            'priority' => $priority,
        ]);
    }

    public function update(int $id, int $customerId, string $notes, string $priority): bool
    {
        if (!in_array($priority, self::PRIORITIES, true)) {
            $priority = 'medium';
        }

        $stmt = $this->db->prepare(
            "UPDATE wishlist_items SET notes = :notes, priority = :priority
             WHERE id = :id AND customer_id = :customer_id"
        );

        return $stmt->execute([
            'notes' => $notes !== '' ? $notes : null,
            'priority' => $priority,
            'id' => $id,
            'customer_id' => $customerId,
        ]);
    }

    public function remove(int $id, int $customerId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM wishlist_items WHERE id = :id AND customer_id = :customer_id"
        );

        return $stmt->execute(['id' => $id, 'customer_id' => $customerId]);
    }

    /** Removes by product_id instead of wishlist row id — used by the
     *  storefront heart toggle, which only knows the product's id. */
    public function removeByProduct(int $customerId, int $productId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM wishlist_items WHERE customer_id = :customer_id AND product_id = :product_id"
        );

        return $stmt->execute(['customer_id' => $customerId, 'product_id' => $productId]);
    }

    public function countByCustomer(int $customerId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM wishlist_items WHERE customer_id = :customer_id"
        );
        $stmt->execute(['customer_id' => $customerId]);

        return (int) $stmt->fetchColumn();
    }
}