<?php

require_once __DIR__ . '/../config/database.php';

class ProductVariant
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }
    public function allByProduct(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = :product_id ORDER BY color, size");
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchAll();
    }
    public function hasForProduct(int $productId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM product_variants WHERE product_id = :product_id LIMIT 1");
        $stmt->execute(['product_id' => $productId]);
        return (bool) $stmt->fetchColumn();
    }
    public function findForProduct(int $variantId, int $productId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE id = :id AND product_id = :product_id LIMIT 1");
        $stmt->execute(['id' => $variantId, 'product_id' => $productId]);
        $variant = $stmt->fetch();
        return $variant ?: null;
    }
    public function replaceForProduct(int $productId, array $variants): void {
        $this->db->prepare("DELETE FROM product_variants WHERE product_id = :product_id")->execute(['product_id' => $productId]);
        $stmt = $this->db->prepare("INSERT INTO product_variants (product_id, size, color, sku, stock, created_at) VALUES (:product_id, :size, :color, :sku, :stock, NOW())");
        foreach ($variants as $variant) $stmt->execute(['product_id' => $productId, 'size' => $variant['size'], 'color' => $variant['color'], 'sku' => $variant['sku'] ?: null, 'stock' => $variant['stock']]);
    }
}
