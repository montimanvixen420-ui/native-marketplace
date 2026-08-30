<?php

require_once __DIR__ . '/../config/database.php';

class SystemSettings
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get the settings row (always id = 1). Creates a default row
     * if one doesn't exist yet.
     */
    public function get(): array
    {
        $stmt = $this->db->query("SELECT * FROM system_settings WHERE id = 1 LIMIT 1");
        $settings = $stmt->fetch();

        if (!$settings) {
            $this->db->exec("INSERT INTO system_settings (id) VALUES (1)");
            $stmt = $this->db->query("SELECT * FROM system_settings WHERE id = 1 LIMIT 1");
            $settings = $stmt->fetch();
        }

        return $settings;
    }

    public function update(float $commissionRate, array $paymentMethods, float $shippingFee, ?float $freeShippingThreshold): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE system_settings
             SET commission_rate = :commission_rate,
                 payment_methods = :payment_methods,
                 shipping_fee = :shipping_fee,
                 free_shipping_threshold = :free_shipping_threshold
             WHERE id = 1"
        );

        return $stmt->execute([
            'commission_rate' => $commissionRate,
            'payment_methods' => implode(',', $paymentMethods),
            'shipping_fee' => $shippingFee,
            'free_shipping_threshold' => $freeShippingThreshold,
        ]);
    }
}