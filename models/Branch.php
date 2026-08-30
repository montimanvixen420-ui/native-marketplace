<?php

require_once __DIR__ . '/../config/database.php';

class Branch
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function activeForLocator(): array
    {
        $sql = 'SELECT b.id, b.name, b.address, b.city, b.phone, b.hours, b.latitude, b.longitude, u.name AS seller_name
                FROM branches b INNER JOIN users u ON u.id = b.seller_id
                WHERE b.is_active = 1 AND u.status = "approved"
                ORDER BY b.city, b.name';
        return $this->db->query($sql)->fetchAll();
    }

    /** Published branches shown to customers for one approved seller. */
    public function activeForSeller(int $sellerId): array
    {
        $stmt = $this->db->prepare('SELECT b.id, b.name, b.address, b.city, b.phone, b.hours, b.latitude, b.longitude, u.name AS seller_name
            FROM branches b INNER JOIN users u ON u.id = b.seller_id
            WHERE b.seller_id = :seller_id AND b.is_active = 1 AND u.status = "approved"
            ORDER BY b.city, b.name');
        $stmt->execute(['seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }

    /** Seller's own active branches, for the "which branches sell this?" checklist on the product form. */
    public function activeSimpleForSeller(int $sellerId): array
    {
        $stmt = $this->db->prepare('SELECT id, name, address, city FROM branches WHERE seller_id = :seller_id AND is_active = 1 ORDER BY name');
        $stmt->execute(['seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }

    public function approvedSeller(int $sellerId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name FROM users WHERE id = :id AND role = "admin" AND status = "approved" LIMIT 1');
        $stmt->execute(['id' => $sellerId]);
        return $stmt->fetch() ?: null;
    }

    /** Staff count comes from the real hierarchy (branch_managers + staff_profiles), not the legacy branch_staff table. */
    public function allBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare("SELECT b.*,
                (SELECT COUNT(*) FROM staff_profiles sp WHERE sp.branch_id = b.id AND sp.is_archived = 0) AS staff_count
            FROM branches b
            WHERE b.seller_id = :seller_id
            ORDER BY FIELD(b.status,'active','inactive','archived'), b.name");
        $stmt->execute(['seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }

    public function findForSeller(int $id, int $sellerId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM branches WHERE id = :id AND seller_id = :seller_id LIMIT 1');
        $stmt->execute(['id' => $id, 'seller_id' => $sellerId]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $sellerId, array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO branches (seller_id, name, address, city, phone, hours, latitude, longitude)
            VALUES (:seller_id, :name, :address, :city, :phone, :hours, :latitude, :longitude)');
        $stmt->execute($data + ['seller_id' => $sellerId]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $sellerId, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE branches SET name = :name, address = :address, city = :city, phone = :phone,
            hours = :hours, latitude = :latitude, longitude = :longitude WHERE id = :id AND seller_id = :seller_id');
        return $stmt->execute($data + ['id' => $id, 'seller_id' => $sellerId]);
    }

    /** Active <-> Inactive only. Archived branches must go through restore() first (spec: Activate/Deactivate/Archive are distinct actions). */
    public function toggle(int $id, int $sellerId): bool
    {
        $stmt = $this->db->prepare("UPDATE branches SET is_active = 1 - is_active,
                status = IF(is_active = 1, 'active', 'inactive')
            WHERE id = :id AND seller_id = :seller_id AND status != 'archived'");
        return $stmt->execute(['id' => $id, 'seller_id' => $sellerId]);
    }

    /** Soft-delete: branch drops out of Branch Manager assignment and storefront, but history (orders, staff) is preserved. */
    public function archive(int $id, int $sellerId): bool
    {
        $stmt = $this->db->prepare("UPDATE branches SET status = 'archived', is_active = 0 WHERE id = :id AND seller_id = :seller_id");
        return $stmt->execute(['id' => $id, 'seller_id' => $sellerId]);
    }

    /** Brings an archived branch back as Inactive — seller must explicitly Activate it afterward. */
    public function restore(int $id, int $sellerId): bool
    {
        $stmt = $this->db->prepare("UPDATE branches SET status = 'inactive', is_active = 0 WHERE id = :id AND seller_id = :seller_id AND status = 'archived'");
        return $stmt->execute(['id' => $id, 'seller_id' => $sellerId]);
    }

     public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, seller_id FROM branches WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
        /** Details for a set of branch ids, in no particular order — used to build the checkout page's branch picker. */
    public function findManyByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, name, city, address FROM branches WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }
    
    public function allForSuperAdmin(): array
    {
        $sql = 'SELECT b.*, s.name AS seller_name, bm_user.name AS branch_manager_name,
                (SELECT COUNT(*) FROM staff_profiles sp WHERE sp.branch_id = b.id AND sp.position != "branch_manager" AND sp.is_archived = 0) AS staff_count
            FROM branches b
            INNER JOIN users s ON s.id = b.seller_id
            LEFT JOIN branch_managers bm ON bm.branch_id = b.id AND bm.status != "archived"
            LEFT JOIN users bm_user ON bm_user.id = bm.user_id
            ORDER BY s.name, b.name';
        return $this->db->query($sql)->fetchAll();
    }

}