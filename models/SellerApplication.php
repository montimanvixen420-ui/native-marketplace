<?php

require_once __DIR__ . '/../config/database.php';

class SellerApplication
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $userId, array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO seller_applications
                (user_id, business_name, business_description, phone, business_address, logo_path, document_path, created_at)
             VALUES
                (:user_id, :business_name, :business_description, :phone, :business_address, :logo_path, :document_path, NOW())"
        );
        $stmt->execute([
            'user_id' => $userId,
            'business_name' => $data['business_name'],
            'business_description' => $data['business_description'],
            'phone' => $data['phone'],
            'business_address' => $data['business_address'],
            'logo_path' => $data['logo_path'],
            'document_path' => $data['document_path'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createVerificationApplication(int $userId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO seller_applications (user_id, business_name, business_description, phone, business_address, logo_path, document_path, application_role, selfie_path, verification_status, created_at)
             VALUES (:user_id, :business_name, :business_description, :phone, :business_address, :logo_path, :document_path, :application_role, :selfie_path, :verification_status, NOW())'
        );
        $stmt->execute([
            'user_id' => $userId, 'business_name' => $data['business_name'], 'business_description' => $data['business_description'],
            'phone' => $data['phone'], 'business_address' => $data['business_address'], 'logo_path' => '',
            'document_path' => $data['document_path'], 'application_role' => $data['application_role'],
            'selfie_path' => $data['selfie_path'], 'verification_status' => 'pending_review',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByUserIds(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $this->db->prepare("SELECT * FROM seller_applications WHERE user_id IN ({$placeholders})");
        $stmt->execute(array_values($userIds));

        $applications = [];
        foreach ($stmt->fetchAll() as $application) {
            $applications[(int) $application['user_id']] = $application;
        }
        return $applications;
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM seller_applications WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $application = $stmt->fetch();

        return $application ?: null;
    }

    public function updateVerificationStatus(int $userId, string $status, ?string $note = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE seller_applications SET verification_status = :status, review_notes = :note, reviewed_at = NOW() WHERE user_id = :user_id'
        );
        return $stmt->execute(['user_id' => $userId, 'status' => $status, 'note' => $note]);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $sql = "UPDATE seller_applications SET
                    business_name = :business_name,
                    business_description = :business_description,
                    phone = :phone,
                    business_address = :business_address";
        $params = [
            'user_id' => $userId,
            'business_name' => $data['business_name'],
            'business_description' => $data['business_description'],
            'phone' => $data['phone'],
            'business_address' => $data['business_address'],
        ];
        if ($data['logo_path'] !== null) {
            $sql .= ", logo_path = :logo_path";
            $params['logo_path'] = $data['logo_path'];
        }
        $sql .= " WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
