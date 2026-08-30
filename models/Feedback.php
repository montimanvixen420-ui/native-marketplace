<?php

require_once __DIR__ . '/../config/database.php';

class Feedback
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(?int $userId, string $subject, string $message): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO feedback (user_id, subject, message, status, created_at)
             VALUES (:user_id, :subject, :message, 'new', NOW())"
        );
        $stmt->execute([
            'user_id' => $userId,
            'subject' => $subject,
            'message' => $message,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            "SELECT f.*, u.name AS sender_name, u.email AS sender_email, u.role AS sender_role
             FROM feedback f
             LEFT JOIN users u ON u.id = f.user_id
             ORDER BY f.created_at DESC"
        );

        return $stmt->fetchAll();
    }

    public function markReviewed(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE feedback SET status = 'reviewed' WHERE id = :id");

        return $stmt->execute(['id' => $id]);
    }

    public function countNew(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM feedback WHERE status = 'new'");

        return (int) $stmt->fetchColumn();
    }
}