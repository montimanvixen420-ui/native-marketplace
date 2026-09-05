<?php
require_once __DIR__ . '/../config/database.php';

class PostPurchase
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    public function completedItemsByCustomer(int $customerId): array
    {
        $stmt = $this->db->prepare("SELECT oi.*, o.id AS order_number, o.delivered_at, p.image_url,
            rr.status AS return_status, pr.id AS review_id
            FROM order_items oi INNER JOIN orders o ON o.id = oi.order_id
            LEFT JOIN products p ON p.id = oi.product_id
            LEFT JOIN return_requests rr ON rr.order_item_id = oi.id
            LEFT JOIN product_reviews pr ON pr.order_item_id = oi.id
            WHERE o.customer_id = :customer_id AND o.status = 'completed' AND o.order_type = 'online'
            ORDER BY o.delivered_at DESC, oi.id DESC");
        $stmt->execute(['customer_id' => $customerId]);
        return $stmt->fetchAll();
    }

    public function eligibleItem(int $itemId, int $customerId): ?array
    {
        $stmt = $this->db->prepare("SELECT oi.*, o.id AS order_number, p.image_url,
            rr.status AS return_status, pr.id AS review_id
            FROM order_items oi INNER JOIN orders o ON o.id = oi.order_id
            LEFT JOIN products p ON p.id = oi.product_id
            LEFT JOIN return_requests rr ON rr.order_item_id = oi.id
            LEFT JOIN product_reviews pr ON pr.order_item_id = oi.id
            WHERE oi.id = :item_id AND o.customer_id = :customer_id
              AND o.status = 'completed' AND o.order_type = 'online' LIMIT 1");
        $stmt->execute(['item_id' => $itemId, 'customer_id' => $customerId]);
        return $stmt->fetch() ?: null;
    }

    public function createReturn(int $itemId, int $customerId, string $reason, string $details): array
    {
        if (!$this->eligibleItem($itemId, $customerId)) return ['success' => false, 'error' => 'This item is not eligible for a return request.'];
        if (!in_array($reason, ['wrong_size', 'damaged', 'wrong_item', 'not_as_described', 'other'], true)) return ['success' => false, 'error' => 'Please choose a valid reason.'];
        try {
            $stmt = $this->db->prepare("INSERT INTO return_requests (order_item_id, customer_id, reason, details) VALUES (:item_id, :customer_id, :reason, :details)");
            $stmt->execute(['item_id' => $itemId, 'customer_id' => $customerId, 'reason' => $reason, 'details' => $details ?: null]);
            return ['success' => true, 'error' => null];
        } catch (PDOException $e) { return ['success' => false, 'error' => 'A return request already exists for this item.']; }
    }

    public function createReview(int $itemId, int $customerId, int $rating, string $fit, string $comment, ?string $photoPath): array
    {
        $item = $this->eligibleItem($itemId, $customerId);
        if (!$item) return ['success' => false, 'error' => 'This item is not eligible for a review.'];
        if ($rating < 1 || $rating > 5 || !in_array($fit, ['too_small', 'true_to_size', 'too_large'], true)) return ['success' => false, 'error' => 'Please complete your rating and fit feedback.'];
        try {
            $stmt = $this->db->prepare("INSERT INTO product_reviews (order_item_id, product_id, customer_id, rating, fit_feedback, comment, photo_path) VALUES (:item_id, :product_id, :customer_id, :rating, :fit, :comment, :photo_path)");
            $stmt->execute(['item_id' => $itemId, 'product_id' => $item['product_id'], 'customer_id' => $customerId, 'rating' => $rating, 'fit' => $fit, 'comment' => $comment ?: null, 'photo_path' => $photoPath]);
            return ['success' => true, 'error' => null];
        } catch (PDOException $e) { return ['success' => false, 'error' => 'You have already reviewed this item.']; }
    }

    public function returnsBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare("SELECT rr.*, oi.product_name, oi.variant_label, oi.quantity, o.id AS order_number, u.name AS customer_name
            FROM return_requests rr INNER JOIN order_items oi ON oi.id = rr.order_item_id
            INNER JOIN orders o ON o.id = oi.order_id INNER JOIN users u ON u.id = rr.customer_id
            WHERE o.seller_id = :seller_id ORDER BY rr.created_at DESC");
        $stmt->execute(['seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }

    public function updateReturnStatus(int $requestId, int $sellerId, string $status): bool
    {
        if (!in_array($status, ['approved', 'rejected', 'refunded'], true)) return false;
        $stmt = $this->db->prepare("UPDATE return_requests rr INNER JOIN order_items oi ON oi.id = rr.order_item_id
            INNER JOIN orders o ON o.id = oi.order_id SET rr.status = :status
            WHERE rr.id = :id AND o.seller_id = :seller_id AND rr.status IN ('requested', 'approved')");
        return $stmt->execute(['status' => $status, 'id' => $requestId, 'seller_id' => $sellerId]);
    }

    /**
     * Return requests for orders placed under a specific branch — used by
     * Customer Service Staff, who only handle returns for their own branch.
     */
    public function returnsByBranch(int $branchId): array
    {
        $stmt = $this->db->prepare("SELECT rr.*, oi.product_name, oi.variant_label, oi.quantity, o.id AS order_number,
            u.name AS customer_name, handler.name AS handled_by_name
            FROM return_requests rr INNER JOIN order_items oi ON oi.id = rr.order_item_id
            INNER JOIN orders o ON o.id = oi.order_id INNER JOIN users u ON u.id = rr.customer_id
            LEFT JOIN users handler ON handler.id = rr.handled_by_user_id
            WHERE o.branch_id = :branch_id ORDER BY rr.created_at DESC");
        $stmt->execute(['branch_id' => $branchId]);
        return $stmt->fetchAll();
    }

    public function updateReturnStatusForBranch(int $requestId, int $branchId, string $status, int $handledByUserId): bool
    {
        if (!in_array($status, ['approved', 'rejected', 'refunded'], true)) return false;
        $stmt = $this->db->prepare("UPDATE return_requests rr INNER JOIN order_items oi ON oi.id = rr.order_item_id
            INNER JOIN orders o ON o.id = oi.order_id
            SET rr.status = :status, rr.handled_by_user_id = :handled_by_user_id, rr.handled_at = NOW()
            WHERE rr.id = :id AND o.branch_id = :branch_id AND rr.status IN ('requested', 'approved')");
        return $stmt->execute(['status' => $status, 'handled_by_user_id' => $handledByUserId, 'id' => $requestId, 'branch_id' => $branchId]);
    }
}
