<?php

require_once __DIR__ . '/../config/database.php';

/** Atomic seller-POS to branch stock transfers and their immutable audit trail. */
class BranchAllocation
{
    private PDO $db;

    public function __construct() { $this->db = Database::getConnection(); }

    public function allocate(int $sellerId, int $branchId, int $productId, string $size, string $color, int $quantity, int $userId, ?string $note): array
    {
        if ($quantity < 1) return ['success' => false, 'error' => 'Allocation quantity must be at least 1.'];
        $this->db->beginTransaction();
        try {
            $branch = $this->db->prepare('SELECT id FROM branches WHERE id = :id AND seller_id = :seller_id AND is_active = 1 FOR UPDATE');
            $branch->execute(['id' => $branchId, 'seller_id' => $sellerId]);
            if (!$branch->fetch()) throw new RuntimeException('That branch is not available.');
            $product = $this->db->prepare('SELECT id, stock FROM products WHERE id = :id AND seller_id = :seller_id FOR UPDATE');
            $product->execute(['id' => $productId, 'seller_id' => $sellerId]); $product = $product->fetch();
            if (!$product) throw new RuntimeException('Product not found.');
            $variant = null;
            if ($size !== '' || $color !== '') {
                $variantStmt = $this->db->prepare('SELECT id, stock FROM product_variants WHERE product_id = :product_id AND size = :size AND color = :color FOR UPDATE');
                $variantStmt->execute(['product_id' => $productId, 'size' => $size, 'color' => $color]); $variant = $variantStmt->fetch();
                if (!$variant) throw new RuntimeException('Product variant not found.');
            }
            $available = (int) ($variant['stock'] ?? $product['stock']);
            if ($available < $quantity) throw new RuntimeException('Only ' . $available . ' units remain in Seller POS stock.');
            $this->db->prepare('INSERT INTO branch_stock_allocations (seller_id, branch_id, product_id, variant_size, variant_color, quantity_allocated, quantity_remaining, allocated_by_user_id, note) VALUES (:seller_id,:branch_id,:product_id,:size,:color,:quantity,:quantity2,:user_id,:note)')->execute(['seller_id'=>$sellerId,'branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color,'quantity'=>$quantity,'quantity2'=>$quantity,'user_id'=>$userId,'note'=>$note ?: null]);
            $this->db->prepare('INSERT INTO product_branches (product_id, branch_id) VALUES (:product_id,:branch_id) ON DUPLICATE KEY UPDATE product_id = product_id')->execute(['product_id'=>$productId,'branch_id'=>$branchId]);
            $this->db->prepare('INSERT INTO branch_stock (product_id, variant_size, variant_color, branch_id, stock) VALUES (:product_id,:size,:color,:branch_id,:quantity) ON DUPLICATE KEY UPDATE stock = stock + :quantity2')->execute(['product_id'=>$productId,'size'=>$size,'color'=>$color,'branch_id'=>$branchId,'quantity'=>$quantity,'quantity2'=>$quantity]);
            $this->db->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :id')->execute(['quantity'=>$quantity,'id'=>$productId]);
            if ($variant) $this->db->prepare('UPDATE product_variants SET stock = stock - :quantity WHERE id = :id')->execute(['quantity'=>$quantity,'id'=>$variant['id']]);
            $this->db->prepare("INSERT INTO branch_stock_adjustments (product_id, variant_size, variant_color, branch_id, adjusted_by_user_id, adjusted_by_role, previous_stock, new_stock, change_amount, reason, note, created_at) VALUES (:product_id,:size,:color,:branch_id,:user_id,'seller',:previous,:new,:change,'restock',:note,NOW())")->execute(['product_id'=>$productId,'size'=>$size,'color'=>$color,'branch_id'=>$branchId,'user_id'=>$userId,'previous'=>0,'new'=>$quantity,'change'=>$quantity,'note'=>$note ?: 'Seller allocation']);
            $this->db->commit(); return ['success'=>true];
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); return ['success'=>false,'error'=>$e->getMessage()]; }
    }

    public function availableForSeller(int $sellerId): array
    {
        $stmt = $this->db->prepare("SELECT p.id AS product_id,p.name,p.stock,p.price,pv.size,pv.color,pv.stock AS variant_stock FROM products p LEFT JOIN product_variants pv ON pv.product_id=p.id WHERE p.seller_id=:seller_id AND p.status IN ('active','pending_review') ORDER BY p.name,pv.size,pv.color");
        $stmt->execute(['seller_id'=>$sellerId]); return $stmt->fetchAll();
    }

    /** Uses allocation rows FIFO. Caller may already own the transaction. */
    public function consume(int $branchId, int $productId, string $size, string $color, int $quantity): void
    {
        $rows=$this->db->prepare('SELECT id, quantity_remaining FROM branch_stock_allocations WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color AND quantity_remaining > 0 ORDER BY created_at,id FOR UPDATE');
        $rows->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);
        $left=$quantity;
        foreach($rows->fetchAll() as $row){
            if($left===0) break;
            $take=min($left,(int)$row['quantity_remaining']);
            $this->db->prepare('UPDATE branch_stock_allocations SET quantity_used=quantity_used+:quantity, quantity_remaining=quantity_remaining-:quantity2 WHERE id=:id')->execute(['quantity'=>$take,'quantity2'=>$take,'id'=>$row['id']]);
            $left-=$take;
        }
        if($left>0) throw new RuntimeException('Allocated stock is no longer available for this branch.');
    }

    /** Restores a cancelled branch sale to the most recent matching allocation. */
    public function restore(int $branchId, int $productId, string $size, string $color, int $quantity): void
    {
        $rows=$this->db->prepare('SELECT id, quantity_used FROM branch_stock_allocations WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color AND quantity_used > 0 ORDER BY created_at DESC,id DESC FOR UPDATE');
        $rows->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);
        $left=$quantity;
        foreach($rows->fetchAll() as $row){
            if($left===0) break;
            $give=min($left,(int)$row['quantity_used']);
            $this->db->prepare('UPDATE branch_stock_allocations SET quantity_used=quantity_used-:quantity, quantity_remaining=quantity_remaining+:quantity2 WHERE id=:id')->execute(['quantity'=>$give,'quantity2'=>$give,'id'=>$row['id']]);
            $left-=$give;
        }
        if($left>0) throw new RuntimeException('Allocation history could not be restored.');
    }
}
