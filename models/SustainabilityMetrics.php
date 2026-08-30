<?php

require_once __DIR__ . '/../config/database.php';

class SustainabilityMetrics
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    public function get(): array
    {
        $metrics = ['active_listings' => 0, 'rehomed_items' => 0, 'active_sellers' => 0, 'published_branches' => 0];
        try {
            $metrics['active_listings'] = (int) $this->db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
            $metrics['rehomed_items'] = (int) $this->db->query("SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi INNER JOIN orders o ON o.id = oi.order_id WHERE o.status = 'completed'")->fetchColumn();
            $metrics['active_sellers'] = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'approved'")->fetchColumn();
            $metrics['published_branches'] = (int) $this->db->query('SELECT COUNT(*) FROM branches WHERE is_active = 1')->fetchColumn();
        } catch (PDOException) {
            // The page remains available during a partial/older database setup.
        }
        return $metrics;
    }
}
