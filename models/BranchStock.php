<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BranchAllocation.php';

/**
 * True per-branch stock. Variants are identified by (size, color) rather
 * than variant_id, because ProductVariant::replaceForProduct() deletes
 * and re-inserts variant rows on every product edit -- variant_id is not
 * stable, but (product_id, size, color) is (it's the natural unique key
 * product_variants itself uses).
 *
 * Every write goes through adjust(), which logs a
 * branch_stock_adjustments row -- there is no silent stock edit path.
 */
class BranchStock
{
    private PDO $db;
    private BranchAllocation $allocations;

    public const REASONS = [
        'restock' => 'Restock',
        'damaged' => 'Damaged',
        'sale' => 'Sale (Order)',
        'lost' => 'Lost / Missing',
        'physical_count' => 'Physical Count Correction',
        'unlogged_sale' => 'Unlogged Sale',
        'other' => 'Other',
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->allocations = new BranchAllocation();
    }

    public function getStock(int $productId, string $size, string $color, int $branchId): int
    {
        $stmt = $this->db->prepare(
            'SELECT stock FROM branch_stock WHERE product_id = :product_id AND variant_size = :size AND variant_color = :color AND branch_id = :branch_id LIMIT 1'
        );
        $stmt->execute(['product_id' => $productId, 'size' => $size, 'color' => $color, 'branch_id' => $branchId]);
        $stock = $stmt->fetchColumn();
        return $stock === false ? 0 : (int) $stock;
    }

    /** Stock a customer can buy from one specific branch. */
    public function available(int $productId, ?array $variant, int $branchId): int
    {
        $stmt = $this->db->prepare('SELECT stock FROM branch_pos_stock WHERE product_id=:product_id AND variant_size=:size AND variant_color=:color AND branch_id=:branch_id LIMIT 1');
        $stmt->execute(['product_id'=>$productId,'size'=>$variant['size']??'','color'=>$variant['color']??'','branch_id'=>$branchId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Every product/variant this branch carries (via product_branches),
     * with its current branch stock (0 if never set). Used by the Branch
     * Manager's Stock page.
     */
    public function forBranch(int $branchId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id AS product_id, p.name AS product_name, p.status AS product_status,
                    pv.size, pv.color,
                    COALESCE(bs.stock, 0) AS stock
             FROM (SELECT DISTINCT branch_id, product_id FROM branch_stock_allocations) a
             INNER JOIN products p ON p.id = a.product_id
             LEFT JOIN product_variants pv ON pv.product_id = p.id
             LEFT JOIN branch_stock bs ON bs.product_id = p.id
                 AND bs.variant_size = COALESCE(pv.size, '') AND bs.variant_color = COALESCE(pv.color, '')
                 AND bs.branch_id = :branch_id
             WHERE a.branch_id = :branch_id2
             ORDER BY p.name, pv.size, pv.color"
        );
        $stmt->execute(['branch_id' => $branchId, 'branch_id2' => $branchId]);
        return $stmt->fetchAll();
    }

    public function summaryForBranch(int $branchId, int $lowStockThreshold = 5): array
    {
        $rows = $this->forBranch($branchId);
        $products = [];
        $low = 0;
        $out = 0;
        foreach ($rows as $row) {
            $products[$row['product_id']] = true;
            if ((int) $row['stock'] === 0) $out++;
            elseif ((int) $row['stock'] <= $lowStockThreshold) $low++;
        }
        return [
            'product_count' => count($products),
            'low_stock_count' => $low,
            'out_of_stock_count' => $out,
        ];
    }

    public function lowStockForBranch(int $branchId, int $lowStockThreshold = 5): array
    {
        $rows = $this->forBranch($branchId);
        $rows = array_filter($rows, fn ($r) => (int) $r['stock'] <= $lowStockThreshold);
        usort($rows, fn ($a, $b) => (int) $a['stock'] <=> (int) $b['stock']);
        return array_values($rows);
    }

    /**
     * Real restock counts per day for this branch, from the
     * branch_stock_adjustments audit log (reason = 'restock' only).
     * Returns one row per day, oldest first, with 0 for days with no
     * restocks -- always $days entries even if the log is sparse.
     */
    public function dailyRestockCountsForBranch(int $branchId, int $days = 7): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE(created_at) AS day, COUNT(*) AS restock_count
             FROM branch_stock_adjustments
             WHERE branch_id = :branch_id AND reason = 'restock'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days_back DAY)
             GROUP BY DATE(created_at)"
        );
        $stmt->execute(['branch_id' => $branchId, 'days_back' => $days - 1]);
        $byDay = [];
        foreach ($stmt->fetchAll() as $row) {
            $byDay[$row['day']] = (int) $row['restock_count'];
        }

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = ['date' => $date, 'label' => date('D', strtotime($date)), 'count' => $byDay[$date] ?? 0];
        }
        return $result;
    }

    /**
     * The only way branch_stock ever changes. Upserts the stock row and
     * writes an audit log entry in the same transaction.
     *
     * @return array{success:bool, error?:string, previous?:int, new?:int}
     */
    public function adjust(
        int $productId,
        string $size,
        string $color,
        int $branchId,
        int $newStock,
        int $userId,
        string $role,
        string $reason,
        ?string $note
    ): array {
        if ($newStock < 0) {
            return ['success' => false, 'error' => 'Stock cannot be negative.'];
        }
        if (!isset(self::REASONS[$reason])) {
            return ['success' => false, 'error' => 'Please choose a valid reason.'];
        }

        // Damage is a permanent write-off, not a restock adjustment and never
        // returns units to Seller POS. Record it in its own seller-visible log.
        if ($reason === 'damaged') {
            $previous = $this->getStock($productId, $size, $color, $branchId);
            return $this->markDamaged($productId, $size, $color, $branchId, $previous - $newStock, $userId, $role, $note);
        }

        $this->db->beginTransaction();
        try {
            $previous = $this->getStock($productId, $size, $color, $branchId);
            if ($newStock > $previous) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Branch stock cannot be increased here. Ask the Seller to allocate additional stock.'];
            }
            if ($newStock < $previous) $this->allocations->consume($branchId, $productId, $size, $color, $previous - $newStock);

            $this->db->prepare(
                'INSERT INTO branch_stock (product_id, variant_size, variant_color, branch_id, stock)
                 VALUES (:product_id, :size, :color, :branch_id, :stock)
                 ON DUPLICATE KEY UPDATE stock = :stock2'
            )->execute([
                'product_id' => $productId, 'size' => $size, 'color' => $color,
                'branch_id' => $branchId, 'stock' => $newStock, 'stock2' => $newStock,
            ]);

            $this->db->prepare(
                'INSERT INTO branch_stock_adjustments
                    (product_id, variant_size, variant_color, branch_id, adjusted_by_user_id, adjusted_by_role, previous_stock, new_stock, change_amount, reason, note, created_at)
                 VALUES (:product_id, :size, :color, :branch_id, :user_id, :role, :previous, :new, :change, :reason, :note, NOW())'
            )->execute([
                'product_id' => $productId, 'size' => $size, 'color' => $color, 'branch_id' => $branchId,
                'user_id' => $userId, 'role' => $role,
                'previous' => $previous, 'new' => $newStock, 'change' => $newStock - $previous,
                'reason' => $reason, 'note' => ($note !== null && $note !== '') ? $note : null,
            ]);

            $this->db->commit();
            return ['success' => true, 'previous' => $previous, 'new' => $newStock];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('BranchStock::adjust failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Unable to save this stock adjustment. Please try again.'];
        }
    }

    public function markDamaged(int $productId, string $size, string $color, int $branchId, int $quantity, int $userId, string $role, ?string $note): array
    {
        if ($quantity < 1) return ['success' => false, 'error' => 'Damaged quantity must be at least 1.'];
        // BranchDamageReport::approve() opens the outer transaction so the
        // report row and the inventory deduction always commit together.
        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) $this->db->beginTransaction();
        try {
            $stock = $this->db->prepare('SELECT stock FROM branch_stock WHERE product_id=:product_id AND variant_size=:size AND variant_color=:color AND branch_id=:branch_id FOR UPDATE');
            $stock->execute(['product_id'=>$productId,'size'=>$size,'color'=>$color,'branch_id'=>$branchId]);
            $previous = (int) $stock->fetchColumn();
            if ($previous < $quantity) throw new RuntimeException('Damaged quantity exceeds branch stock.');
            $seller = $this->db->prepare('SELECT seller_id FROM products WHERE id=:id'); $seller->execute(['id'=>$productId]); $sellerId=(int)$seller->fetchColumn();
            if (!$sellerId) throw new RuntimeException('Product not found.');
            $new = $previous - $quantity;
            $this->db->prepare('UPDATE branch_stock SET stock=:stock WHERE product_id=:product_id AND variant_size=:size AND variant_color=:color AND branch_id=:branch_id')->execute(['stock'=>$new,'product_id'=>$productId,'size'=>$size,'color'=>$color,'branch_id'=>$branchId]);
            $this->allocations->consume($branchId, $productId, $size, $color, $quantity);
            $this->db->prepare("INSERT INTO branch_stock_adjustments (product_id,variant_size,variant_color,branch_id,adjusted_by_user_id,adjusted_by_role,previous_stock,new_stock,change_amount,reason,note,created_at) VALUES (:product_id,:size,:color,:branch_id,:user_id,:role,:previous,:new,:change,'damaged',:note,NOW())")->execute(['product_id'=>$productId,'size'=>$size,'color'=>$color,'branch_id'=>$branchId,'user_id'=>$userId,'role'=>$role,'previous'=>$previous,'new'=>$new,'change'=>-$quantity,'note'=>$note ?: null]);
            $this->db->prepare('INSERT INTO damaged_products (seller_id,branch_id,product_id,variant_size,variant_color,quantity,reason,note,recorded_by_user_id,recorded_by_role) VALUES (:seller_id,:branch_id,:product_id,:size,:color,:quantity,:reason,:note,:user_id,:role)')->execute(['seller_id'=>$sellerId,'branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color,'quantity'=>$quantity,'reason'=>'Damaged stock','note'=>$note ?: null,'user_id'=>$userId,'role'=>$role]);
            if ($ownsTransaction) $this->db->commit();
            return ['success'=>true,'previous'=>$previous,'new'=>$new];
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) $this->db->rollBack();
            return ['success'=>false,'error'=>$e->getMessage()];
        }
    }

    public function damagesForSeller(int $sellerId): array
    {
        $stmt=$this->db->prepare('SELECT d.*,p.name AS product_name,b.name AS branch_name,u.name AS recorded_by_name FROM damaged_products d INNER JOIN products p ON p.id=d.product_id INNER JOIN branches b ON b.id=d.branch_id LEFT JOIN users u ON u.id=d.recorded_by_user_id WHERE d.seller_id=:seller_id ORDER BY d.created_at DESC');
        $stmt->execute(['seller_id'=>$sellerId]); return $stmt->fetchAll();
    }

    /**
     * Seller-facing audit log. Optional filters narrow to one branch
     * and/or one product.
     */
    public function adjustmentsForSeller(int $sellerId, ?int $branchId, ?int $productId): array
    {
        $sql = "SELECT a.*, p.name AS product_name, b.name AS branch_name,
                    COALESCE(u.name, 'System') AS adjusted_by_name
                FROM branch_stock_adjustments a
                INNER JOIN products p ON p.id = a.product_id
                INNER JOIN branches b ON b.id = a.branch_id
                LEFT JOIN users u ON u.id = a.adjusted_by_user_id
                WHERE p.seller_id = :seller_id";
        $params = ['seller_id' => $sellerId];
        if ($branchId) {
            $sql .= ' AND a.branch_id = :branch_id';
            $params['branch_id'] = $branchId;
        }
        if ($productId) {
            $sql .= ' AND a.product_id = :product_id';
            $params['product_id'] = $productId;
        }
        $sql .= ' ORDER BY a.created_at DESC LIMIT 300';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Populates the Seller's filter dropdowns on the Stock Adjustments page. */
    public function filterOptionsForSeller(int $sellerId): array
    {
        $branches = $this->db->prepare('SELECT id, name FROM branches WHERE seller_id = :seller_id ORDER BY name');
        $branches->execute(['seller_id' => $sellerId]);

        $products = $this->db->prepare('SELECT id, name FROM products WHERE seller_id = :seller_id ORDER BY name');
        $products->execute(['seller_id' => $sellerId]);

        return ['branches' => $branches->fetchAll(), 'products' => $products->fetchAll()];
    }
}