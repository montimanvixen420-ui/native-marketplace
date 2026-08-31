<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/User.php';

class BranchManager
{
    private PDO $db;

    public function __construct() { $this->db = Database::getConnection(); }

       public function forUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT bm.*, b.name AS branch_name, b.is_active AS branch_is_active, sp.first_name, sp.last_name
            FROM branch_managers bm
            INNER JOIN branches b ON b.id = bm.branch_id
            LEFT JOIN staff_profiles sp ON sp.user_id = bm.user_id
            WHERE bm.user_id = :user_id AND bm.status = "active" LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function allBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare('SELECT bm.*, u.name, u.email, b.name AS branch_name, sp.phone
            FROM branch_managers bm INNER JOIN users u ON u.id = bm.user_id
            INNER JOIN branches b ON b.id = bm.branch_id
            LEFT JOIN staff_profiles sp ON sp.user_id = bm.user_id AND sp.seller_id = bm.seller_id AND sp.position = "branch_manager"
            WHERE bm.seller_id = :seller_id
            ORDER BY bm.status, u.name');
        $stmt->execute(['seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }

    public function create(int $sellerId, int $branchId, array $data, string $password): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('INSERT INTO users (name,email,password,role,status,created_at)
                VALUES (:name,:email,:password,"manager","approved",NOW())');
            $stmt->execute(['name' => trim($data['first_name'].' '.$data['last_name']), 'email' => $data['email'], 'password' => password_hash($password, PASSWORD_DEFAULT)]);
            $userId = (int) $this->db->lastInsertId();
            $this->db->prepare('INSERT INTO staff_profiles (user_id,seller_id,branch_id,first_name,last_name,phone,position,status)
                VALUES (:user_id,:seller_id,:branch_id,:first_name,:last_name,:phone,"branch_manager","active")')->execute([
                    'user_id' => $userId,
                    'seller_id' => $sellerId,
                    'branch_id' => $branchId,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                ]);
            $this->db->prepare('INSERT INTO branch_managers (seller_id,branch_id,user_id) VALUES (:seller_id,:branch_id,:user_id)')->execute(['seller_id' => $sellerId, 'branch_id' => $branchId, 'user_id' => $userId]);
            $this->db->commit();
            return $userId;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    public function branchBelongsToSeller(int $branchId, int $sellerId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM branches WHERE id = :branch_id AND seller_id = :seller_id AND is_active = 1 LIMIT 1');
        $stmt->execute(['branch_id' => $branchId, 'seller_id' => $sellerId]);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE email = :email';
        $params = ['email' => $email];
        if ($excludeUserId !== null) { $sql .= ' AND id != :exclude_id'; $params['exclude_id'] = $excludeUserId; }
        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function branchHasManager(int $branchId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM branch_managers WHERE branch_id = :branch_id AND status != "archived" LIMIT 1');
        $stmt->execute(['branch_id' => $branchId]);
        return (bool) $stmt->fetchColumn();
    }

    public function setStatus(int $managerUserId, int $sellerId, string $status): bool
    {
        if (!in_array($status, ['active', 'inactive', 'archived'], true)) return false;
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('UPDATE branch_managers SET status = :status WHERE user_id = :user_id AND seller_id = :seller_id');
            $stmt->execute(['status' => $status, 'user_id' => $managerUserId, 'seller_id' => $sellerId]);
            if ($stmt->rowCount() !== 1) { $this->db->rollBack(); return false; }
            $this->db->prepare('UPDATE staff_profiles SET status = :profile_status, is_archived = :archived WHERE user_id = :user_id AND seller_id = :seller_id AND position = "branch_manager"')->execute([
                'profile_status' => $status === 'active' ? 'active' : 'inactive',
                'archived' => $status === 'archived' ? 1 : 0,
                'user_id' => $managerUserId,
                'seller_id' => $sellerId,
            ]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    /** Only a seller can reassign a manager, and both records must belong to that seller. */
    public function changeBranch(int $managerUserId, int $sellerId, int $branchId): bool
    {
        if (!$this->branchBelongsToSeller($branchId, $sellerId)) return false;

        // Confirm the manager row exists BEFORE updating — rowCount() after an UPDATE only
        // counts rows that actually changed value, so reassigning back to the SAME branch
        // (a genuine no-op) would wrongly look like "0 rows affected" and be treated as a failure.
        $exists = $this->db->prepare('SELECT 1 FROM branch_managers WHERE user_id = :user_id AND seller_id = :seller_id LIMIT 1');
        $exists->execute(['user_id' => $managerUserId, 'seller_id' => $sellerId]);
        if (!$exists->fetchColumn()) return false;

        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE branch_managers SET branch_id = :branch_id WHERE user_id = :user_id AND seller_id = :seller_id')
                ->execute(['branch_id' => $branchId, 'user_id' => $managerUserId, 'seller_id' => $sellerId]);
            $this->db->prepare('UPDATE staff_profiles SET branch_id = :branch_id WHERE user_id = :user_id AND seller_id = :seller_id AND position = "branch_manager"')
                ->execute(['branch_id' => $branchId, 'user_id' => $managerUserId, 'seller_id' => $sellerId]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    /** Edit: updates the manager's own profile info (not their branch or status). */
    public function updateProfile(int $managerUserId, int $sellerId, array $data): bool
    {
        if (!$this->forSeller($managerUserId, $sellerId)) return false;
        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id')->execute([
                'name' => trim($data['first_name'] . ' ' . $data['last_name']), 'email' => $data['email'], 'id' => $managerUserId,
            ]);
            $stmt = $this->db->prepare('UPDATE staff_profiles SET first_name = :first_name, last_name = :last_name, phone = :phone
                WHERE user_id = :user_id AND seller_id = :seller_id AND position = "branch_manager"');
            $stmt->execute(['first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'phone' => $data['phone'], 'user_id' => $managerUserId, 'seller_id' => $sellerId]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    /** Ownership is re-verified before touching users.password so a seller can never reset a manager outside their own team. */
    public function resetPassword(int $managerUserId, int $sellerId, string $password): bool
    {
        if (!$this->forSeller($managerUserId, $sellerId)) return false;
        return $this->db->prepare('UPDATE users SET password = :password WHERE id = :id')->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $managerUserId]);
    }

    private function forSeller(int $managerUserId, int $sellerId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM branch_managers WHERE user_id = :user_id AND seller_id = :seller_id LIMIT 1');
        $stmt->execute(['user_id' => $managerUserId, 'seller_id' => $sellerId]);
        return (bool) $stmt->fetchColumn();
    }

    public function allForSuperAdmin(): array
    {
        $sql = 'SELECT bm.*, u.name, u.email, b.name AS branch_name, s.name AS seller_name
            FROM branch_managers bm
            INNER JOIN users u ON u.id = bm.user_id
            INNER JOIN branches b ON b.id = bm.branch_id
            INNER JOIN users s ON s.id = bm.seller_id
            ORDER BY s.name, b.name';
        return $this->db->query($sql)->fetchAll();
    }
}