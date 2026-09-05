<?php

require_once __DIR__ . '/../config/database.php';

class Report
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    public function targetExists(string $type, int $id): ?array
    {
        $sql = $type === 'product'
            ? 'SELECT id, name AS label FROM products WHERE id = :id LIMIT 1'
            : "SELECT id, name AS label FROM users WHERE id = :id AND role = 'admin' LIMIT 1";
        $stmt = $this->db->prepare($sql); $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
    public function hasOpen(int $reporterId, string $type, int $targetId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM customer_reports WHERE reporter_id=:reporter_id AND target_type=:target_type AND target_id=:target_id AND status='open' LIMIT 1");
        $stmt->execute(['reporter_id' => $reporterId, 'target_type' => $type, 'target_id' => $targetId]);
        return (bool) $stmt->fetchColumn();
    }
    public function create(int $reporterId, string $type, int $targetId, string $reason, string $details): void
    {
        $stmt = $this->db->prepare("INSERT INTO customer_reports (reporter_id,target_type,target_id,reason,details,status,created_at) VALUES (:reporter_id,:target_type,:target_id,:reason,:details,'open',NOW())");
        $stmt->execute(['reporter_id'=>$reporterId,'target_type'=>$type,'target_id'=>$targetId,'reason'=>$reason,'details'=>$details]);
    }
    public function all(): array
    {
        return $this->db->query("SELECT r.*, u.name reporter_name, CASE WHEN r.target_type='product' THEN p.name ELSE s.name END target_label FROM customer_reports r INNER JOIN users u ON u.id=r.reporter_id LEFT JOIN products p ON r.target_type='product' AND p.id=r.target_id LEFT JOIN users s ON r.target_type='seller' AND s.id=r.target_id ORDER BY FIELD(r.status,'open','reviewing','resolved','dismissed'),r.created_at DESC")->fetchAll();
    }
    public function summary(): array { return $this->db->query("SELECT COUNT(*) total, COALESCE(SUM(status='open'),0) open_count, COALESCE(SUM(target_type='product'),0) product_count, COALESCE(SUM(target_type='seller'),0) seller_count FROM customer_reports")->fetch(); }
    public function updateStatus(int $id, string $status, int $reviewerId, string $note): void
    {
        $stmt=$this->db->prepare('UPDATE customer_reports SET status=:status,reviewer_id=:reviewer_id,review_note=:note,reviewed_at=NOW() WHERE id=:id');
        $stmt->execute(['id'=>$id,'status'=>$status,'reviewer_id'=>$reviewerId,'note'=>$note ?: null]);
    }

    // Reports filed BY a specific customer — used for the customer's own
    // "My Reports" monitoring page, with superadmin's status + review note visible.
    public function getByReporterId(int $reporterId): array
    {
        $sql = "SELECT
                    r.id,
                    r.target_type,
                    r.target_id,
                    r.reason,
                    r.details,
                    r.status,
                    r.review_note,
                    r.reviewed_at,
                    r.created_at,
                    CASE WHEN r.target_type = 'product' THEN p.name ELSE s.name END AS target_label
                FROM customer_reports r
                LEFT JOIN products p ON r.target_type = 'product' AND p.id = r.target_id
                LEFT JOIN users s ON r.target_type = 'seller' AND s.id = r.target_id
                WHERE r.reporter_id = :reporter_id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['reporter_id' => $reporterId]);
        return $stmt->fetchAll();
    }

    // Reports filed AGAINST a specific seller — either the seller account
    // itself, or one of their products. View-only: sellers can see what
    // was reported and superadmin's resolution, but never resolve it
    // themselves (conflict of interest — see StaffReturnsController for
    // the same view-vs-act separation applied to branch staff).
    public function allBySeller(int $sellerId): array
    {
        $sql = "SELECT
                    r.id,
                    r.target_type,
                    r.target_id,
                    r.reason,
                    r.details,
                    r.status,
                    r.review_note,
                    r.reviewed_at,
                    r.created_at,
                    CASE WHEN r.target_type = 'product' THEN p.name ELSE s.name END AS target_label
                FROM customer_reports r
                LEFT JOIN products p ON r.target_type = 'product' AND p.id = r.target_id
                LEFT JOIN users s ON r.target_type = 'seller' AND s.id = r.target_id
                WHERE (r.target_type = 'seller' AND r.target_id = :seller_id_a)
                   OR (r.target_type = 'product' AND p.seller_id = :seller_id_b)
                ORDER BY FIELD(r.status,'open','reviewing','resolved','dismissed'), r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['seller_id_a' => $sellerId, 'seller_id_b' => $sellerId]);
        return $stmt->fetchAll();
    }

    // Product reports for products carried by a specific branch — used for
    // Branch Manager awareness only (see StaffReportsController). Seller
    // reports are excluded: they're about the whole account, not one
    // branch, and a product can belong to more than one branch, so this
    // is informational only — resolution still stays with superadmin.
    public function productReportsByBranch(int $branchId): array
    {
        $sql = "SELECT
                    r.id,
                    r.target_type,
                    r.target_id,
                    r.reason,
                    r.details,
                    r.status,
                    r.review_note,
                    r.reviewed_at,
                    r.created_at,
                    p.name AS target_label,
                    reporter.name AS reporter_name,
                    reviewer.name AS reviewed_by_name
                FROM customer_reports r
                INNER JOIN products p ON r.target_type = 'product' AND p.id = r.target_id
                INNER JOIN product_branches pb ON pb.product_id = p.id
                LEFT JOIN users reporter ON reporter.id = r.reporter_id
                LEFT JOIN users reviewer ON reviewer.id = r.reviewer_id
                WHERE pb.branch_id = :branch_id
                GROUP BY r.id
                ORDER BY FIELD(r.status,'open','reviewing','resolved','dismissed'), r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['branch_id' => $branchId]);
        return $stmt->fetchAll();
    }
}
