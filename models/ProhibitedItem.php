<?php

require_once __DIR__ . '/../config/database.php';

class ProhibitedItem
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM prohibited_items ORDER BY item_name ASC");

        return $stmt->fetchAll();
    }

    /** Return prohibited list entries found in a product's name or description. */
    public function findMatches(string $text): array
    {
        $matches = [];
        foreach ($this->getAll() as $item) {
            $keyword = trim((string) $item['item_name']);
            // Match whole words/phrases only: "gun" should not flag "Gundam".
            $pattern = '/(?<![\\pL\\pN])' . preg_quote($keyword, '/') . '(?![\\pL\\pN])/iu';
            if ($keyword !== '' && preg_match($pattern, $text) === 1) {
                $matches[] = $keyword;
            }
        }

        return array_values(array_unique($matches));
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM prohibited_items WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    public function create(string $itemName, ?string $description): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO prohibited_items (item_name, description, created_at)
             VALUES (:item_name, :description, NOW())"
        );

        $stmt->execute([
            'item_name' => $itemName,
            'description' => $description,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $itemName, ?string $description): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE prohibited_items SET item_name = :item_name, description = :description WHERE id = :id"
        );

        return $stmt->execute([
            'item_name' => $itemName,
            'description' => $description,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM prohibited_items WHERE id = :id");

        return $stmt->execute(['id' => $id]);
    }
}
