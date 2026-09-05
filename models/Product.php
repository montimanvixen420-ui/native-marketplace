<?php

require_once __DIR__ . '/../config/database.php';

class Product
{
    private PDO $db;
    private array $columnCache = [];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function allBySeller(int $sellerId): array
    {
        // Exclude Branch POS listings: those are created by a Branch Manager from
        // Branch Inventory (see BranchPosStock::createListingFromInventory) under
        // this same seller_id, but are only ever registered in product_branches —
        // the Seller's own "Add product" flow never touches that table. They have
        // their own dedicated management page (Branch Manager > Branch POS Products).
        // Archived (soft-deleted) products are excluded too — see archivedBySeller().
        //
        // Stock: a listing created from Seller Inventory has its own products.stock
        // forced to 0 at creation (see SellerPosStock::createListingFromInventory) —
        // the real sellable quantity lives in seller_pos_stock instead. Coalesce with
        // that so the number shown here matches what's actually sellable. Raw Seller
        // Inventory items (no seller_pos_stock row) just keep using products.stock.
        // Raw Seller Inventory items (inventory_source_product_id IS NULL) are excluded
        // too — those are just master stock, not something for sale yet. They only
        // belong here once the Seller actually "adds" them as a product/listing (which
        // is exactly what sets inventory_source_product_id).
        $stmt = $this->db->prepare(
            "SELECT p.*, COALESCE(sps.total_stock, p.stock) AS stock
             FROM products p
             LEFT JOIN (
                 SELECT product_id, SUM(stock) AS total_stock
                 FROM seller_pos_stock
                 WHERE seller_id = :sps_seller_id
                 GROUP BY product_id
             ) sps ON sps.product_id = p.id
             WHERE p.seller_id = :seller_id
               AND p.status <> 'archived'
               AND p.inventory_source_product_id IS NOT NULL
               AND p.id NOT IN (SELECT product_id FROM product_branches)
             ORDER BY p.created_at DESC"
        );
        $stmt->execute(['seller_id' => $sellerId, 'sps_seller_id' => $sellerId]);

        return $stmt->fetchAll();
    }

    /** Products the seller has archived (soft-deleted) — viewable but not editable/sellable. */
    public function archivedBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, COALESCE(sps.total_stock, p.stock) AS stock
             FROM products p
             LEFT JOIN (
                 SELECT product_id, SUM(stock) AS total_stock
                 FROM seller_pos_stock
                 WHERE seller_id = :sps_seller_id
                 GROUP BY product_id
             ) sps ON sps.product_id = p.id
             WHERE p.seller_id = :seller_id
               AND p.status = 'archived'
               AND p.inventory_source_product_id IS NOT NULL
               AND p.id NOT IN (SELECT product_id FROM product_branches)
             ORDER BY p.updated_at DESC"
        );
        $stmt->execute(['seller_id' => $sellerId, 'sps_seller_id' => $sellerId]);

        return $stmt->fetchAll();
    }

    /**
     * Products sellable at a specific branch — either explicitly assigned
     * to that branch via product_branches, or "branch-agnostic" (no
     * branch assignment at all, sellable anywhere). Same rule the
     * customer checkout flow uses (see CheckoutController::resolveBranchOptions).
     * Used for the Cashier POS so a branch only sells what it actually carries.
     */
    public function allByBranch(int $sellerId, int $branchId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.* FROM products p
             WHERE p.seller_id = :seller_id
               AND (
                    EXISTS (SELECT 1 FROM product_branches pb WHERE pb.product_id = p.id AND pb.branch_id = :branch_id)
                    OR NOT EXISTS (SELECT 1 FROM product_branches pb2 WHERE pb2.product_id = p.id)
               )
             ORDER BY p.created_at DESC"
        );
        $stmt->execute(['seller_id' => $sellerId, 'branch_id' => $branchId]);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.name AS seller_name FROM products p
             INNER JOIN users u ON u.id = p.seller_id
             WHERE p.id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function findByIdForSeller(int $id, int $sellerId): ?array
    {
        // Same seller_pos_stock coalesce as allBySeller() — see its comment for why.
        $stmt = $this->db->prepare(
            "SELECT p.*, COALESCE(sps.total_stock, p.stock) AS stock
             FROM products p
             LEFT JOIN (
                 SELECT product_id, SUM(stock) AS total_stock
                 FROM seller_pos_stock
                 WHERE product_id = :sps_id
                 GROUP BY product_id
             ) sps ON sps.product_id = p.id
             WHERE p.id = :id AND p.seller_id = :seller_id LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'sps_id' => $id, 'seller_id' => $sellerId]);
        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function create(int $sellerId, array $data): int
    {
        $fields = ['seller_id', 'stock_request_id'];
        if ($this->hasColumn('inventory_source_product_id')) $fields[] = 'inventory_source_product_id';
        if ($this->hasColumn('inventory_source_variant_size')) $fields[] = 'inventory_source_variant_size';
        if ($this->hasColumn('inventory_source_variant_color')) $fields[] = 'inventory_source_variant_color';
        $fields = array_merge($fields, ['name', 'description']);
        if ($this->hasColumn('size_guide')) $fields[] = 'size_guide';
        if ($this->hasColumn('fit_information')) $fields[] = 'fit_information';
        $fields = array_merge($fields, ['price', 'stock', 'category', 'image_url', 'status']);
        $stmt = $this->db->prepare('INSERT INTO products (' . implode(', ', $fields) . ', created_at) VALUES (:' . implode(', :', $fields) . ', NOW())');

        $params = [
            'seller_id' => $sellerId,
            'stock_request_id' => $data['stock_request_id'] ?? null,
            'inventory_source_product_id' => $data['inventory_source_product_id'] ?? null,
            'inventory_source_variant_size' => $data['inventory_source_variant_size'] ?? null,
            'inventory_source_variant_color' => $data['inventory_source_variant_color'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'],
            'size_guide' => $data['size_guide'] ?? '', 'fit_information' => $data['fit_information'] ?? '',
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category' => $data['category'],
            'image_url' => $data['image_url'],
            'status' => $data['status'],
        ];
        $stmt->execute(array_intersect_key($params, array_flip($fields)));

        return (int) $this->db->lastInsertId();
    }

    /** Frees up a stock request from an old REJECTED product so a new product
     *  can claim it. The rejected product's history/name stays intact —
     *  only its link to the stock request is cleared. */
    public function unlinkRejectedStockRequest(int $stockRequestId, int $sellerId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE products SET stock_request_id = NULL
             WHERE stock_request_id = :stock_request_id AND seller_id = :seller_id AND status = 'rejected'"
        );
        $stmt->execute(['stock_request_id' => $stockRequestId, 'seller_id' => $sellerId]);
    }

    public function addModerationFlag(int $productId, array $keywords): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO product_moderation_flags (product_id, matched_keywords, status, created_at)\n             VALUES (:product_id, :matched_keywords, 'pending', NOW())"
        );
        $stmt->execute([
            'product_id' => $productId,
            'matched_keywords' => implode(', ', $keywords),
        ]);
    }

    public function addImageModerationFlag(int $productId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO product_moderation_flags (product_id, flag_type, matched_keywords, status, created_at)\n             VALUES (:product_id, 'image', 'New product image requires manual review', 'pending', NOW())"
        );
        $stmt->execute(['product_id' => $productId]);
    }

    public function clearPendingModerationFlags(int $productId, string $flagType = 'keyword'): void
    {
        $stmt = $this->db->prepare(
            "UPDATE product_moderation_flags SET status = 'superseded', resolved_at = NOW()\n             WHERE product_id = :product_id AND flag_type = :flag_type AND status = 'pending'"
        );
        $stmt->execute(['product_id' => $productId, 'flag_type' => $flagType]);
    }

    public function hasPendingModerationFlags(int $productId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM product_moderation_flags WHERE product_id = :product_id AND status = 'pending' LIMIT 1");
        $stmt->execute(['product_id' => $productId]);
        return (bool) $stmt->fetchColumn();
    }

    public function pendingModerationQueue(): array
    {
        $stmt = $this->db->query(
            "SELECT p.*, u.name AS seller_name, u.email AS seller_email, u.status AS seller_status, f.id AS flag_id,\n                    f.matched_keywords, f.flag_type, f.created_at AS flagged_at\n             FROM product_moderation_flags f\n             INNER JOIN products p ON p.id = f.product_id\n             INNER JOIN users u ON u.id = p.seller_id\n             WHERE f.status = 'pending' AND p.status = 'pending_review'\n             ORDER BY f.created_at ASC"
        );
        return $stmt->fetchAll();
    }

    public function reviewModerationFlag(int $flagId, string $decision, int $reviewerId, ?string $note): bool
    {
        $this->db->beginTransaction();
        try {
            $flag = $this->db->prepare("SELECT product_id FROM product_moderation_flags WHERE id = :id AND status = 'pending' FOR UPDATE");
            $flag->execute(['id' => $flagId]);
            $row = $flag->fetch();
            if (!$row) {
                $this->db->rollBack();
                return false;
            }

            $updateFlag = $this->db->prepare(
                "UPDATE product_moderation_flags\n                 SET status = :status, reviewer_id = :reviewer_id, review_note = :review_note, resolved_at = NOW()\n                 WHERE id = :id"
            );
            $updateFlag->execute(['status' => $decision, 'reviewer_id' => $reviewerId, 'review_note' => $note, 'id' => $flagId]);

            $remaining = $this->db->prepare("SELECT COUNT(*) FROM product_moderation_flags WHERE product_id = :product_id AND status = 'pending'");
            $remaining->execute(['product_id' => $row['product_id']]);
            $productStatus = $decision === 'rejected' ? 'rejected' : ((int) $remaining->fetchColumn() > 0 ? 'pending_review' : 'active');
            $updateProduct = $this->db->prepare("UPDATE products SET status = :status WHERE id = :id");
            $updateProduct->execute(['status' => $productStatus, 'id' => $row['product_id']]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, int $sellerId, array $data): bool
    {
        $sets = ['name = :name', 'description = :description'];
        if ($this->hasColumn('size_guide')) $sets[] = 'size_guide = :size_guide';
        if ($this->hasColumn('fit_information')) $sets[] = 'fit_information = :fit_information';
        $sets = array_merge($sets, ['price = :price', 'stock = :stock', 'category = :category', 'image_url = :image_url', 'status = :status']);
        $stmt = $this->db->prepare('UPDATE products SET ' . implode(', ', $sets) . ' WHERE id = :id AND seller_id = :seller_id');

        $params = [
            'id' => $id,
            'seller_id' => $sellerId,
            'name' => $data['name'],
            'description' => $data['description'],
            'size_guide' => $data['size_guide'] ?? '', 'fit_information' => $data['fit_information'] ?? '',
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category' => $data['category'],
            'image_url' => $data['image_url'],
            'status' => $data['status'],
        ];
        if (!$this->hasColumn('size_guide')) unset($params['size_guide']);
        if (!$this->hasColumn('fit_information')) unset($params['fit_information']);
        return $stmt->execute($params);
    }

    public function delete(int $id, int $sellerId): bool
    {
        // Soft delete: products are often referenced by historical records (orders,
        // inventory transfers, allocations, damage reports) that must be preserved,
        // and a real DELETE is blocked by foreign key constraints anyway in that case.
        // Archive it instead — it disappears from the active catalog/storefront but
        // stays visible under the "Archived" tab, and can be restored later.
        $stmt = $this->db->prepare(
            "UPDATE products SET status = 'archived' WHERE id = :id AND seller_id = :seller_id"
        );

        return $stmt->execute(['id' => $id, 'seller_id' => $sellerId]) && $stmt->rowCount() > 0;
    }

    /** Brings an archived product back to 'active' so it can be edited/sold again. */
    public function restore(int $id, int $sellerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE products SET status = 'active' WHERE id = :id AND seller_id = :seller_id AND status = 'archived'"
        );

        return $stmt->execute(['id' => $id, 'seller_id' => $sellerId]) && $stmt->rowCount() > 0;
    }

    public function countBySeller(int $sellerId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM products WHERE seller_id = :seller_id AND status = 'active'"
        );
        $stmt->execute(['seller_id' => $sellerId]);

        return (int) $stmt->fetch()['total'];
    }

    private function hasColumn(string $column): bool
    {
        if (isset($this->columnCache[$column])) return $this->columnCache[$column];
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = \'products\' AND column_name = :column');
        $stmt->execute(['column' => $column]);
        return $this->columnCache[$column] = (bool) $stmt->fetchColumn();
    }

    /** Inventory totals use variant stock when a product has variants. */
    public function getInventorySummaryBySeller(int $sellerId, int $lowStockThreshold = 5): array
    {
        // Same seller_pos_stock coalesce as allBySeller() — a listing's own products.stock
        // is forced to 0 at creation, so fall back to its real seller_pos_stock total.
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT p.id) AS product_count,
                COALESCE(SUM(CASE WHEN pv.id IS NULL THEN COALESCE(sps.total_stock, p.stock) ELSE pv.stock END), 0) AS total_units,
                COALESCE(SUM(CASE WHEN (CASE WHEN pv.id IS NULL THEN COALESCE(sps.total_stock, p.stock) ELSE pv.stock END) BETWEEN 1 AND :threshold THEN 1 ELSE 0 END), 0) AS low_stock_count,
                COALESCE(SUM(CASE WHEN (CASE WHEN pv.id IS NULL THEN COALESCE(sps.total_stock, p.stock) ELSE pv.stock END) = 0 THEN 1 ELSE 0 END), 0) AS out_of_stock_count
             FROM products p
             LEFT JOIN product_variants pv ON pv.product_id = p.id
             LEFT JOIN (
                 SELECT product_id, SUM(stock) AS total_stock
                 FROM seller_pos_stock
                 WHERE seller_id = :sps_seller_id
                 GROUP BY product_id
             ) sps ON sps.product_id = p.id
             WHERE p.seller_id = :seller_id
               AND p.status <> 'archived'
               AND p.inventory_source_product_id IS NOT NULL
               AND p.id NOT IN (SELECT product_id FROM product_branches)"
        );
        $stmt->execute(['seller_id' => $sellerId, 'sps_seller_id' => $sellerId, 'threshold' => $lowStockThreshold]);
        return $stmt->fetch() ?: ['product_count' => 0, 'total_units' => 0, 'low_stock_count' => 0, 'out_of_stock_count' => 0];
    }

    public function getLowStockBySeller(int $sellerId, int $lowStockThreshold = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id AS product_id, p.name, p.status, pv.id AS variant_id, pv.size, pv.color,
                    CASE WHEN pv.id IS NULL THEN COALESCE(sps.total_stock, p.stock) ELSE pv.stock END AS stock
             FROM products p
             LEFT JOIN product_variants pv ON pv.product_id = p.id
             LEFT JOIN (
                 SELECT product_id, SUM(stock) AS total_stock
                 FROM seller_pos_stock
                 WHERE seller_id = :sps_seller_id
                 GROUP BY product_id
             ) sps ON sps.product_id = p.id
             WHERE p.seller_id = :seller_id
               AND p.status <> 'archived'
               AND p.inventory_source_product_id IS NOT NULL
               AND p.id NOT IN (SELECT product_id FROM product_branches)
               AND (CASE WHEN pv.id IS NULL THEN COALESCE(sps.total_stock, p.stock) ELSE pv.stock END) <= :threshold
             ORDER BY stock ASC, p.name ASC"
        );
        $stmt->execute(['seller_id' => $sellerId, 'sps_seller_id' => $sellerId, 'threshold' => $lowStockThreshold]);
        return $stmt->fetchAll();
    }

    /** Seller-owned master inventory items that can be turned into a new POS listing. */
    public function inventorySourcesBySeller(int $sellerId): array
    {
        $sql = 'SELECT id, name, stock, stock_request_id FROM products WHERE seller_id = :seller_id AND stock > 0';
        if ($this->hasColumn('inventory_source_product_id')) $sql .= ' AND inventory_source_product_id IS NULL';
        $sql .= ' ORDER BY name ASC';
        $stmt = $this->db->prepare($sql); $stmt->execute(['seller_id' => $sellerId]); return $stmt->fetchAll();
    }

    /**
     * ALL of a seller's raw Seller Inventory (master stock) — including 0-stock rows
     * marked "restock needed" — for the Seller Inventory page. Unlike
     * inventorySourcesBySeller() (used for the "Add product" dropdown), this doesn't
     * hide out-of-stock rows, since the page needs to show those too.
     */
    public function rawInventoryBySeller(int $sellerId): array
    {
        $sql = 'SELECT * FROM products WHERE seller_id = :seller_id AND status <> \'archived\'';
        if ($this->hasColumn('inventory_source_product_id')) $sql .= ' AND inventory_source_product_id IS NULL';
        $sql .= ' ORDER BY updated_at DESC';
        $stmt = $this->db->prepare($sql); $stmt->execute(['seller_id' => $sellerId]); return $stmt->fetchAll();
    }

    public function inventorySourceForSeller(int $id, int $sellerId): ?array
    {
        $sql = 'SELECT id, name, stock FROM products WHERE id = :id AND seller_id = :seller_id AND stock > 0';
        if ($this->hasColumn('inventory_source_product_id')) $sql .= ' AND inventory_source_product_id IS NULL';
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql); $stmt->execute(['id' => $id, 'seller_id' => $sellerId]); return $stmt->fetch() ?: null;
    }
        /**
     * Branch Manager dashboard: inventory summary for only the products
     * carried by THIS branch (via product_branches). Note: stock itself
     * is still seller-wide (products don't have per-branch stock in this
     * schema) -- this scopes WHICH products show up, not a separate
     * branch-only stock count.
     */
    public function getInventorySummaryByBranch(int $branchId, int $lowStockThreshold = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT p.id) AS product_count,
                COALESCE(SUM(CASE WHEN pv.id IS NULL THEN p.stock ELSE pv.stock END), 0) AS total_units,
                COALESCE(SUM(CASE WHEN (CASE WHEN pv.id IS NULL THEN p.stock ELSE pv.stock END) BETWEEN 1 AND :threshold THEN 1 ELSE 0 END), 0) AS low_stock_count,
                COALESCE(SUM(CASE WHEN (CASE WHEN pv.id IS NULL THEN p.stock ELSE pv.stock END) = 0 THEN 1 ELSE 0 END), 0) AS out_of_stock_count
             FROM products p
             INNER JOIN (SELECT DISTINCT product_id, branch_id FROM branch_stock_allocations) pb ON pb.product_id = p.id AND pb.branch_id = :branch_id
             LEFT JOIN product_variants pv ON pv.product_id = p.id"
        );
        $stmt->execute(['branch_id' => $branchId, 'threshold' => $lowStockThreshold]);
        return $stmt->fetch() ?: ['product_count' => 0, 'total_units' => 0, 'low_stock_count' => 0, 'out_of_stock_count' => 0];
    }

    /** Branch Manager dashboard: low/out-of-stock products carried by this branch, worst first. */
    public function getLowStockByBranch(int $branchId, int $lowStockThreshold = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id AS product_id, p.name, p.status, pv.id AS variant_id, pv.size, pv.color,
                    CASE WHEN pv.id IS NULL THEN p.stock ELSE pv.stock END AS stock
             FROM products p
             INNER JOIN product_branches pb ON pb.product_id = p.id AND pb.branch_id = :branch_id
             LEFT JOIN product_variants pv ON pv.product_id = p.id
             WHERE (CASE WHEN pv.id IS NULL THEN p.stock ELSE pv.stock END) <= :threshold
             ORDER BY stock ASC, p.name ASC"
        );
        $stmt->execute(['branch_id' => $branchId, 'threshold' => $lowStockThreshold]);
        return $stmt->fetchAll();
    }

    /**
     * Get all sellable products (active + in stock) across all sellers,
     * for the customer-facing storefront. Optionally filter by search
     * term (matches product name) and/or category.
     */
    public function getAllActive(string $search = '', ?string $category = null): array
    {
        $sql = "SELECT p.*, u.name AS seller_name, u.status AS seller_status
                FROM products p
                INNER JOIN users u ON u.id = p.seller_id
                WHERE p.status = 'active'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND p.name LIKE :search";
            $params['search'] = "%{$search}%";
        }

        if ($category !== null && $category !== '') {
            $sql .= " AND p.category = :category";
            $params['category'] = $category;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Distinct list of categories currently in use, for the storefront
     * filter dropdown.
     */
    public function getActiveCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT category FROM products
             WHERE status = 'active'
               AND (stock > 0 OR EXISTS (
                   SELECT 1 FROM product_variants pv
                   WHERE pv.product_id = products.id AND pv.stock > 0
               ))
               AND category IS NOT NULL
             ORDER BY category ASC"
        );

        return array_column($stmt->fetchAll(), 'category');
    }

    /**
     * Distinct list of a single seller's own categories currently in use,
     * for the POS filter pills.
     */
    public function getSellerCategories(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT category FROM products
             WHERE seller_id = :seller_id AND status = 'active' AND stock > 0 AND category IS NOT NULL
             ORDER BY category ASC"
        );
        $stmt->execute(['seller_id' => $sellerId]);

        return array_column($stmt->fetchAll(), 'category');
    }

    /**
     * Distinct list of ALL of a seller's categories (any status/stock), for the
     * "My products" management page filter pills — so a newly-typed category
     * shows up in the filter as soon as any product uses it.
     */
    public function distinctCategoriesForSeller(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT category FROM products
             WHERE seller_id = :seller_id AND category IS NOT NULL AND category <> ''
             ORDER BY category ASC"
        );
        $stmt->execute(['seller_id' => $sellerId]);

        return array_column($stmt->fetchAll(), 'category');
    }

    /**
     * Distinct categories used by ONE branch's own Branch POS listings only — kept
     * separate from the seller's own categories (distinctCategoriesForSeller) so a
     * new category added by the Seller (or by a different branch) doesn't leak into
     * this branch's "Add product" dropdown.
     */
    public function distinctCategoriesForBranch(int $branchId): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT p.category
             FROM products p
             INNER JOIN product_branches pb ON pb.product_id = p.id
             WHERE pb.branch_id = :branch_id AND p.category IS NOT NULL AND p.category <> ''
             ORDER BY p.category ASC"
        );
        $stmt->execute(['branch_id' => $branchId]);

        return array_column($stmt->fetchAll(), 'category');
    }
        /**
     * Products a Branch Manager/Staff cashier can sell at POS for their
     * ONE branch: only products assigned to that branch (product_branches),
     * with `stock` substituted from branch_stock instead of the shared
     * flat field. POS has no variant picker, so this only surfaces
     * products that DON'T use size/color variants (matching POS's existing
     * limitation for the seller's own POS too).
     */
    public function activeForBranchPos(int $sellerId, int $branchId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, COALESCE(bs.stock, 0) AS stock
             FROM products p
             INNER JOIN branch_pos_stock bs ON bs.product_id = p.id AND bs.variant_size = '' AND bs.variant_color = '' AND bs.branch_id = :branch_id
             WHERE p.seller_id = :seller_id AND p.status = 'active'
               AND NOT EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id)
             HAVING stock > 0
             ORDER BY p.created_at DESC"
        );
        $stmt->execute(['branch_id' => $branchId, 'seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }

    public function categoriesForBranch(int $sellerId, int $branchId): array
    {
        $products = $this->activeForBranchPos($sellerId, $branchId);
        return array_values(array_unique(array_filter(array_column($products, 'category'))));
    }
}