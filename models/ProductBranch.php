<?php

require_once __DIR__ . '/../config/database.php';

class ProductBranch
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Wipes and re-saves the set of branches a product is available at. */
    public function replaceForProduct(int $productId, array $branchIds): void
    {
        $stmt = $this->db->prepare('DELETE FROM product_branches WHERE product_id = :product_id');
        $stmt->execute(['product_id' => $productId]);

        if (empty($branchIds)) return;

        $insert = $this->db->prepare('INSERT INTO product_branches (product_id, branch_id) VALUES (:product_id, :branch_id)');
        foreach (array_unique(array_map('intval', $branchIds)) as $branchId) {
            $insert->execute(['product_id' => $productId, 'branch_id' => $branchId]);
        }
    }

    /** Branch ids currently linked to a product — used to pre-check boxes on the edit form. */
    public function branchIdsForProduct(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT branch_id FROM product_branches WHERE product_id = :product_id');
        $stmt->execute(['product_id' => $productId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'branch_id'));
    }
        /** Authorization helper: is this product actually assigned to this branch? Used before letting a Branch Manager or Seller adjust that branch's stock for the product. */
    public function belongsToBranch(int $productId, int $branchId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM product_branches WHERE product_id = :product_id AND branch_id = :branch_id LIMIT 1');
        $stmt->execute(['product_id' => $productId, 'branch_id' => $branchId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Active branch ids for a single product — used at checkout to figure
     * out which branch(es) can fulfill an order (see
     * CheckoutController::resolveBranchOptions()).
     *
     * Returns: array of branch ids (int[])
     */
    public function activeBranchIdsForProduct(int $productId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pb.branch_id
             FROM product_branches pb
             INNER JOIN branches b ON b.id = pb.branch_id
             WHERE pb.product_id = :product_id AND b.is_active = 1'
        );
        $stmt->execute(['product_id' => $productId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'branch_id'));
    }

    /**
     * Active branch details for a set of products, grouped by product_id.
     * Used on the customer storefront — only branches the SELLER chose for
     * THAT product are returned, not every branch the seller owns.
     *
     * Returns: [ product_id => [ ['name'=>..,'address'=>..,'city'=>..,'phone'=>..,'hours'=>..], ... ], ... ]
     */
    public function activeAddressesForProducts(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if (empty($productIds)) return [];

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $sql = "SELECT pb.product_id, b.name, b.address, b.city, b.phone, b.hours
                FROM product_branches pb
                INNER JOIN branches b ON b.id = pb.branch_id
                WHERE pb.product_id IN ($placeholders) AND b.is_active = 1
                ORDER BY b.city, b.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($productIds);

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['product_id']][] = [
                'name' => $row['name'],
                'address' => $row['address'],
                'city' => $row['city'],
                'phone' => $row['phone'],
                'hours' => $row['hours'],
            ];
        }
        return $grouped;
    }

    /** Customer-facing listings: one product card for every branch with stock. */
    public function customerListings(string $search = ''): array
    {
        $sql = "SELECT p.*, u.name AS seller_name, u.status AS seller_status,
                       b.id AS branch_id, b.name AS branch_name, b.address AS branch_address,
                       b.city AS branch_city, b.phone AS branch_phone, b.hours AS branch_hours,
                       CASE WHEN EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id)
                            THEN COALESCE((SELECT SUM(bs2.stock) FROM product_variants pv2
                                           LEFT JOIN branch_pos_stock bs2 ON bs2.product_id = pv2.product_id
                                             AND bs2.variant_size COLLATE utf8mb4_unicode_ci = pv2.size COLLATE utf8mb4_unicode_ci
                                             AND bs2.variant_color COLLATE utf8mb4_unicode_ci = pv2.color COLLATE utf8mb4_unicode_ci
                                             AND bs2.branch_id = b.id WHERE pv2.product_id = p.id), 0)
                            ELSE COALESCE(bs.stock, 0) END AS branch_stock
                FROM (SELECT DISTINCT branch_id, product_id FROM branch_pos_stock WHERE stock > 0) ps
                INNER JOIN products p ON p.id = ps.product_id
                INNER JOIN users u ON u.id = p.seller_id AND u.status = 'approved'
                INNER JOIN branches b ON b.id = ps.branch_id AND b.is_active = 1
                LEFT JOIN branch_pos_stock bs ON bs.product_id = p.id AND bs.variant_size = '' AND bs.variant_color = '' AND bs.branch_id = b.id
                WHERE p.status = 'active'";
        $params = [];
        if ($search !== '') { $sql .= ' AND p.name LIKE :search'; $params['search'] = '%' . $search . '%'; }
        $sql .= ' HAVING branch_stock > 0 ORDER BY p.created_at DESC, b.name ASC';
        $stmt = $this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll();
    }

    /** Variants sellable from one customer-visible branch listing only. */
    public function variantsForBranch(int $productId, int $branchId): array
    {
        $stmt=$this->db->prepare("SELECT pv.*, COALESCE(bs.stock,0) AS branch_stock FROM product_variants pv LEFT JOIN branch_pos_stock bs ON bs.product_id=pv.product_id AND bs.variant_size COLLATE utf8mb4_unicode_ci=pv.size COLLATE utf8mb4_unicode_ci AND bs.variant_color COLLATE utf8mb4_unicode_ci=pv.color COLLATE utf8mb4_unicode_ci AND bs.branch_id=:branch_id WHERE pv.product_id=:product_id HAVING branch_stock > 0 ORDER BY pv.color,pv.size");
        $stmt->execute(['product_id'=>$productId,'branch_id'=>$branchId]); return $stmt->fetchAll();
    }
}
