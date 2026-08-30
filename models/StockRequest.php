<?php

require_once __DIR__ . '/../config/database.php';

class StockRequest
{
    private PDO $db;

    public const STATUS_PENDING = 'pending';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_REJECTED = 'rejected';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $sellerId, int $supplierId, string $itemName, int $quantity, ?string $note, ?int $supplyId = null): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO stock_requests (seller_id, supplier_id, supply_id, item_name, quantity_requested, note, status, created_at)
             VALUES (:seller_id, :supplier_id, :supply_id, :item_name, :quantity, :note, 'pending', NOW())"
        );

        $stmt->execute([
            'seller_id' => $sellerId,
            'supplier_id' => $supplierId,
            'supply_id' => $supplyId,
            'item_name' => $itemName,
            'quantity' => $quantity,
            'note' => $note,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * All requests a seller has sent, with the supplier's name joined in.
     */
    public function allBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT sr.*, u.name AS supplier_name
             FROM stock_requests sr
             JOIN users u ON u.id = sr.supplier_id
             WHERE sr.seller_id = :seller_id
             ORDER BY sr.created_at DESC"
        );
        $stmt->execute(['seller_id' => $sellerId]);

        return $stmt->fetchAll();
    }

    /** Delivered supplier stock that has not yet been turned into a seller product.
     *  A stock request tied only to a REJECTED product still counts as available,
     *  so the seller can try listing it again. */
    public function availableForProductBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT sr.id, sr.item_name, sr.quantity_requested, u.name AS supplier_name
             FROM stock_requests sr
             JOIN users u ON u.id = sr.supplier_id
             LEFT JOIN products p ON p.stock_request_id = sr.id AND p.status <> 'rejected'
             WHERE sr.seller_id = :seller_id AND sr.status = 'fulfilled' AND p.id IS NULL
             ORDER BY sr.created_at DESC"
        );
        $stmt->execute(['seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }

    public function findAvailableForProductBySeller(int $requestId, int $sellerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT sr.id, sr.item_name, sr.quantity_requested
             FROM stock_requests sr
             LEFT JOIN products p ON p.stock_request_id = sr.id AND p.status <> 'rejected'
             WHERE sr.id = :id AND sr.seller_id = :seller_id AND sr.status = 'fulfilled' AND p.id IS NULL
             LIMIT 1"
        );
        $stmt->execute(['id' => $requestId, 'seller_id' => $sellerId]);
        return $stmt->fetch() ?: null;
    }

    /** A seller's fulfilled delivery, including one already linked to its product. */
    public function findFulfilledForSeller(int $requestId, int $sellerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, item_name, quantity_requested
             FROM stock_requests
             WHERE id = :id AND seller_id = :seller_id AND status = 'fulfilled'
             LIMIT 1"
        );
        $stmt->execute(['id' => $requestId, 'seller_id' => $sellerId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * All requests sent to a supplier, with the seller's name joined in.
     * (For the supplier-side response UI, built later.)
     */
    public function allBySupplier(int $supplierId): array
    {
        $stmt = $this->db->prepare(
            "SELECT sr.*, u.name AS seller_name
             FROM stock_requests sr
             JOIN users u ON u.id = sr.seller_id
             WHERE sr.supplier_id = :supplier_id
             ORDER BY sr.created_at DESC"
        );
        $stmt->execute(['supplier_id' => $supplierId]);

        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE stock_requests SET status = :status WHERE id = :id");

        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /** Update a request only when it belongs to the signed-in supplier. */
    public function updateStatusForSupplier(int $id, int $supplierId, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE stock_requests
             SET status = :status
             WHERE id = :id AND supplier_id = :supplier_id AND status = 'pending'"
        );

        return $stmt->execute([
            'id' => $id,
            'supplier_id' => $supplierId,
            'status' => $status,
        ]);
    }

    /** Fulfill a linked request and deduct stock in one database transaction. */
    public function fulfillAndDeductForSupplier(int $id, int $supplierId): bool
    {
        $this->db->beginTransaction();
        try {
            $requestStmt = $this->db->prepare(
                "SELECT id, supply_id, quantity_requested FROM stock_requests
                 WHERE id = :id AND supplier_id = :supplier_id AND status = 'pending' FOR UPDATE"
            );
            $requestStmt->execute(['id' => $id, 'supplier_id' => $supplierId]);
            $request = $requestStmt->fetch();
            if (!$request) { $this->db->rollBack(); return false; }

            // Old requests may not have been created from the supplier catalog.
            if ($request['supply_id'] !== null) {
                $deduct = $this->db->prepare(
                    'UPDATE supplier_inventory SET quantity_available = quantity_available - :deduct_quantity
                     WHERE id = :supply_id AND supplier_id = :supplier_id AND quantity_available >= :minimum_quantity'
                );
                $deduct->execute([
                    'deduct_quantity' => (int) $request['quantity_requested'],
                    'minimum_quantity' => (int) $request['quantity_requested'],
                    'supply_id' => (int) $request['supply_id'],
                    'supplier_id' => $supplierId,
                ]);
                if ($deduct->rowCount() !== 1) { $this->db->rollBack(); return false; }
            }

            $this->db->prepare("UPDATE stock_requests SET status = 'fulfilled' WHERE id = :id")->execute(['id' => $id]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function countPendingBySeller(int $sellerId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM stock_requests WHERE seller_id = :seller_id AND status = 'pending'"
        );
        $stmt->execute(['seller_id' => $sellerId]);

        return (int) $stmt->fetchColumn();
    }

    public function getSupplierStats(int $supplierId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_requests,
                SUM(status = 'pending') AS pending_requests,
                SUM(status = 'fulfilled') AS fulfilled_requests
             FROM stock_requests
             WHERE supplier_id = :supplier_id"
        );
        $stmt->execute(['supplier_id' => $supplierId]);
        $stats = $stmt->fetch() ?: [];

        return [
            'totalRequests' => (int) ($stats['total_requests'] ?? 0),
            'pendingRequests' => (int) ($stats['pending_requests'] ?? 0),
            'fulfilledRequests' => (int) ($stats['fulfilled_requests'] ?? 0),
        ];
    }
}