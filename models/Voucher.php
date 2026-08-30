<?php

require_once __DIR__ . '/../config/database.php';

class Voucher
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    /** Returns the discount and shipping amount after validating a seller voucher. */
    public function apply(string $code, int $sellerId, float $subtotal, float $shipping = 50.0): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') return ['valid' => true, 'code' => '', 'discount' => 0.0, 'shipping' => $shipping, 'message' => null];
        $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE seller_id = :seller_id AND code = :code AND is_active = 1 LIMIT 1");
        $stmt->execute(['seller_id' => $sellerId, 'code' => $code]);
        $voucher = $stmt->fetch();
        if (!$voucher || ($voucher['starts_at'] && strtotime($voucher['starts_at']) > time()) || ($voucher['ends_at'] && strtotime($voucher['ends_at']) < time()) || ($voucher['usage_limit'] !== null && (int) $voucher['times_used'] >= (int) $voucher['usage_limit'])) return ['valid' => false, 'message' => 'Voucher code is invalid or expired.'];
        if ($subtotal < (float) $voucher['minimum_order']) return ['valid' => false, 'message' => 'This voucher requires a minimum order of ₱' . number_format((float) $voucher['minimum_order'], 2) . '.'];
        $discount = 0.0;
        if ($voucher['discount_type'] === 'free_shipping') $shipping = 0.0;
        elseif ($voucher['discount_type'] === 'percent') $discount = $subtotal * ((float) $voucher['discount_value'] / 100);
        else $discount = (float) $voucher['discount_value'];
        if ($voucher['maximum_discount'] !== null) $discount = min($discount, (float) $voucher['maximum_discount']);
        return ['valid' => true, 'code' => $code, 'voucher' => $voucher, 'discount' => min($discount, $subtotal), 'shipping' => $shipping, 'message' => null];
    }

    public function recordUse(int $voucherId): void
    {
        $stmt = $this->db->prepare('UPDATE vouchers SET times_used = times_used + 1 WHERE id = :id AND is_active = 1');
        $stmt->execute(['id' => $voucherId]);
    }
}
