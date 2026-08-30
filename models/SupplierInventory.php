<?php

require_once __DIR__ . '/../config/database.php';

class SupplierInventory
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }
    public function allBySupplier(int $supplierId): array { $stmt = $this->db->prepare('SELECT * FROM supplier_inventory WHERE supplier_id = :supplier_id ORDER BY is_active DESC, item_name ASC'); $stmt->execute(['supplier_id' => $supplierId]); return $stmt->fetchAll(); }
    public function availableForSellers(): array { $stmt = $this->db->query("SELECT si.*, u.name AS supplier_name FROM supplier_inventory si JOIN users u ON u.id = si.supplier_id WHERE si.is_active = 1 AND si.quantity_available > 0 AND u.role = 'supplier' AND u.status = 'approved' ORDER BY u.name ASC, si.item_name ASC"); return $stmt->fetchAll(); }
    public function findAvailableForSupplier(int $id, int $supplierId): ?array { $stmt = $this->db->prepare('SELECT * FROM supplier_inventory WHERE id = :id AND supplier_id = :supplier_id AND is_active = 1 AND quantity_available > 0 LIMIT 1'); $stmt->execute(['id' => $id, 'supplier_id' => $supplierId]); return $stmt->fetch() ?: null; }
    public function create(int $supplierId, string $itemName, ?string $description, string $unit, float $unitPrice, int $quantity): int { $stmt = $this->db->prepare('INSERT INTO supplier_inventory (supplier_id, item_name, description, unit, unit_price, quantity_available) VALUES (:supplier_id, :item_name, :description, :unit, :unit_price, :quantity)'); $stmt->execute(['supplier_id'=>$supplierId,'item_name'=>$itemName,'description'=>$description,'unit'=>$unit,'unit_price'=>$unitPrice,'quantity'=>$quantity]); return (int)$this->db->lastInsertId(); }
    public function updateForSupplier(int $id, int $supplierId, string $itemName, ?string $description, string $unit, float $unitPrice, int $quantity, bool $isActive): bool { $stmt = $this->db->prepare('UPDATE supplier_inventory SET item_name=:item_name, description=:description, unit=:unit, unit_price=:unit_price, quantity_available=:quantity, is_active=:is_active WHERE id=:id AND supplier_id=:supplier_id'); return $stmt->execute(['id'=>$id,'supplier_id'=>$supplierId,'item_name'=>$itemName,'description'=>$description,'unit'=>$unit,'unit_price'=>$unitPrice,'quantity'=>$quantity,'is_active'=>$isActive?1:0]); }
}
