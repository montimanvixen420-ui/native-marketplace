<?php

require_once __DIR__ . '/../config/database.php';

class Content
{
    private PDO $db;

    public const TYPE_BANNER = 'banner';
    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_SITE_TEXT = 'site_text';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get all content items, optionally filtered by type.
     */
    public function getAll(?string $type = null): array
    {
        if ($type !== null && $type !== '') {
            $stmt = $this->db->prepare(
                "SELECT * FROM site_content WHERE type = :type ORDER BY created_at DESC"
            );
            $stmt->execute(['type' => $type]);
        } else {
            $stmt = $this->db->query("SELECT * FROM site_content ORDER BY created_at DESC");
        }

        return $stmt->fetchAll();
    }

    /** Customer-facing content: only records made visible by Superadmin. */
    public function getActive(?string $type = null): array
    {
        $sql = 'SELECT * FROM site_content WHERE is_active = 1';
        $params = [];
        if ($type !== null && $type !== '') {
            $sql .= ' AND type = :type';
            $params['type'] = $type;
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM site_content WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    public function create(string $type, string $title, ?string $body, ?string $imageUrl, bool $isActive): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO site_content (type, title, body, image_url, is_active, created_at)
             VALUES (:type, :title, :body, :image_url, :is_active, NOW())"
        );

        $stmt->execute([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'image_url' => $imageUrl,
            'is_active' => $isActive ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $type, string $title, ?string $body, ?string $imageUrl, bool $isActive): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE site_content
             SET type = :type, title = :title, body = :body, image_url = :image_url, is_active = :is_active
             WHERE id = :id"
        );

        return $stmt->execute([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'image_url' => $imageUrl,
            'is_active' => $isActive ? 1 : 0,
            'id' => $id,
        ]);
    }

    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE site_content SET is_active = NOT is_active WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM site_content WHERE id = :id");

        return $stmt->execute(['id' => $id]);
    }
}
