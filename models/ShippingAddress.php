<?php

require_once __DIR__ . '/../config/database.php';

class ShippingAddress
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function allByCustomer(int $customerId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM shipping_addresses WHERE customer_id = :customer_id ORDER BY is_default DESC, created_at DESC');
        $stmt->execute(['customer_id' => $customerId]);
        return $stmt->fetchAll();
    }

    public function findForCustomer(int $id, int $customerId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM shipping_addresses WHERE id = :id AND customer_id = :customer_id LIMIT 1');
        $stmt->execute(['id' => $id, 'customer_id' => $customerId]);
        $address = $stmt->fetch();
        return $address ?: null;
    }

    public function create(int $customerId, array $data): array
    {
        $fields = ['recipient_name', 'phone', 'address_line1', 'barangay', 'city', 'province', 'postal_code'];
        foreach ($fields as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') return ['success' => false, 'error' => 'Please complete all shipping-address fields.'];
        }
        if (!preg_match('/^[0-9+() -]{7,30}$/', (string) $data['phone'])) return ['success' => false, 'error' => 'Please enter a valid phone number.'];
        if (!preg_match('/^[A-Za-z0-9 -]{3,12}$/', (string) $data['postal_code'])) return ['success' => false, 'error' => 'Please enter a valid postal code.'];

        $count = $this->db->prepare('SELECT COUNT(*) FROM shipping_addresses WHERE customer_id = :customer_id');
        $count->execute(['customer_id' => $customerId]);
        $isDefault = (int) $count->fetchColumn() === 0 ? 1 : 0;
        $stmt = $this->db->prepare('INSERT INTO shipping_addresses (customer_id, recipient_name, phone, address_line1, address_line2, barangay, city, province, postal_code, is_default) VALUES (:customer_id, :recipient_name, :phone, :address_line1, :address_line2, :barangay, :city, :province, :postal_code, :is_default)');
        $stmt->execute([
            'customer_id' => $customerId, 'recipient_name' => trim($data['recipient_name']), 'phone' => trim($data['phone']),
            'address_line1' => trim($data['address_line1']), 'address_line2' => trim($data['address_line2'] ?? ''),
            'barangay' => trim($data['barangay']), 'city' => trim($data['city']), 'province' => trim($data['province']),
            'postal_code' => trim($data['postal_code']), 'is_default' => $isDefault,
        ]);
        return ['success' => true, 'id' => (int) $this->db->lastInsertId(), 'error' => null];
    }
}
