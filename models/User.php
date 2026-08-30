<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $db;

    // Valid roles in the system
    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';       // seller
    public const ROLE_SUPPLIER = 'supplier';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_STAFF = 'staff';
    public const ROLE_MANAGER = 'manager';

    // Valid statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_BANNED = 'banned';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(string $name, string $email, string $password, string $role): int
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Sellers (admin) and suppliers need approval first;
        // customers are approved right away.
        $status = in_array($role, [self::ROLE_ADMIN, self::ROLE_SUPPLIER], true)
            ? self::STATUS_PENDING
            : self::STATUS_APPROVED;

        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role, status, created_at)
             VALUES (:name, :email, :password, :role, :status, NOW())"
        );

        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'status' => $status,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function createPasswordReset(int $userId, string $token): void
    {
        $this->db->prepare('DELETE FROM password_resets WHERE user_id = :user_id')->execute(['user_id' => $userId]);
        $stmt = $this->db->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 15 MINUTE))'
        );
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $token),
        ]);
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $tokenHash = hash('sha256', $token);
        $this->db->beginTransaction();
        try {
            $find = $this->db->prepare('SELECT id, user_id FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1 FOR UPDATE');
            $find->execute(['token_hash' => $tokenHash]);
            $reset = $find->fetch();
            if (!$reset) {
                $this->db->rollBack();
                return false;
            }
            $updatePassword = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
            $updatePassword->execute(['password' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $reset['user_id']]);
            $useToken = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
            $useToken->execute(['id' => $reset['id']]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    // ── Methods for the Superadmin dashboard ──────────────

    /**
     * Get all users (optionally filtered by role).
     */
    public function getAllUsers(?string $role = null): array
    {
        if ($role !== null) {
            $stmt = $this->db->prepare(
                "SELECT id, name, email, role, status, created_at
                 FROM users WHERE role = :role ORDER BY created_at DESC"
            );
            $stmt->execute(['role' => $role]);
        } else {
            $stmt = $this->db->query(
                "SELECT id, name, email, role, status, created_at
                 FROM users ORDER BY created_at DESC"
            );
        }

        return $stmt->fetchAll();
    }

    /**
     * Search/filter users for the Users management page.
     * Any of the filters can be left empty to skip that condition.
     */
    public function searchUsers(string $search = '', ?string $role = null, ?string $status = null): array
    {
        $sql = "SELECT id, name, email, role, status, created_at FROM users WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (name LIKE :search OR email LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($role !== null && $role !== '') {
            $sql .= " AND role = :role";
            $params['role'] = $role;
        }

        if ($status !== null && $status !== '') {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get all admin/supplier accounts that are still pending.
     */
    public function getPendingApprovals(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, role, created_at
             FROM users
             WHERE role IN (:admin, :supplier) AND status = :pending
             ORDER BY created_at ASC"
        );
        $stmt->execute([
            'admin' => self::ROLE_ADMIN,
            'supplier' => self::ROLE_SUPPLIER,
            'pending' => self::STATUS_PENDING,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Update a user's status (approve, reject/ban, suspend, reactivate).
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET status = :status WHERE id = :id"
        );

        return $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }

    public function updateRoleAndStatus(int $id, string $role, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET role = :role, status = :status WHERE id = :id');
        return $stmt->execute(['id' => $id, 'role' => $role, 'status' => $status]);
    }

    /**
     * Update a user's basic details (name, email, role) from the
     * Superadmin's edit-user form.
     */
    public function updateDetails(int $id, string $name, string $email, string $role): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id"
        );

        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'id' => $id,
        ]);
    }

    public function updateProfile(int $id, string $name, string $email): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET name = :name, email = :email WHERE id = :id"
        );

        return $stmt->execute(['id' => $id, 'name' => $name, 'email' => $email]);
    }

    /**
     * Permanently delete a user account.
     */
    public function deleteUser(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");

        return $stmt->execute(['id' => $id]);
    }

    /**
     * Monthly sign-up counts for the last N months, including months
     * with zero sign-ups (so the chart doesn't have gaps).
     */
    public function getSignupsByMonth(int $monthsBack = 6): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
             FROM users
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY month
             ORDER BY month ASC"
        );
        $stmt->bindValue(':months', $monthsBack, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Reindex by month for easy lookup
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['month']] = (int) $row['total'];
        }

        // Build a complete series, filling in zero for months with no sign-ups
        $series = [];
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-{$i} months"));
            $series[] = [
                'month' => $monthKey,
                'label' => date('M Y', strtotime($monthKey . '-01')),
                'count' => $counts[$monthKey] ?? 0,
            ];
        }

        return $series;
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = :role");
        $stmt->execute(['role' => $role]);

        return (int) $stmt->fetchColumn();
    }

    public function countPending(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM users
             WHERE role IN (:admin, :supplier) AND status = :pending"
        );
        $stmt->execute([
            'admin' => self::ROLE_ADMIN,
            'supplier' => self::ROLE_SUPPLIER,
            'pending' => self::STATUS_PENDING,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * All the stats needed by the superadmin dashboard cards, in one
     * call so we don't repeat the same queries over and over.
     */
    public function getDashboardStats(): array
    {
        return [
            'total' => $this->countByRole(self::ROLE_ADMIN)
                + $this->countByRole(self::ROLE_SUPPLIER)
                + $this->countByRole(self::ROLE_CUSTOMER)
                + $this->countByRole(self::ROLE_SUPERADMIN),
            'sellers' => $this->countByRole(self::ROLE_ADMIN),
            'suppliers' => $this->countByRole(self::ROLE_SUPPLIER),
            'customers' => $this->countByRole(self::ROLE_CUSTOMER),
            'pending' => $this->countPending(),
        ];
    }
}
