<?php

require_once __DIR__ . '/../config/database.php';

class Staff
{
    private PDO $db;
    public const POSITIONS = ['cashier'=>'Cashier','inventory_staff'=>'Inventory Staff','order_staff'=>'Order Staff','customer_service'=>'Customer Service Staff'];
    public const POSITION_PERMISSIONS = ['cashier'=>['dashboard','orders','products','sales'],'inventory_staff'=>['dashboard','products','inventory'],'order_staff'=>['dashboard','orders','customers'],'customer_service'=>['dashboard','customers','orders']];
    public function __construct() { $this->db = Database::getConnection(); }

    /** A manager can see only staff they created for their single branch. */
    public function allByManager(int $managerId, int $branchId): array
    {
        $s=$this->db->prepare('SELECT u.id,u.name,u.email,sp.first_name,sp.last_name,sp.phone,sp.position,sp.status,sp.profile_picture,sp.created_at FROM staff_profiles sp INNER JOIN users u ON u.id=sp.user_id WHERE sp.created_by_manager_id=:manager_id AND sp.branch_id=:branch_id AND sp.is_archived=0 ORDER BY sp.status,u.name');
        $s->execute(['manager_id'=>$managerId,'branch_id'=>$branchId]); return $s->fetchAll();
    }
    public function findForManager(int $id,int $managerId,int $branchId): ?array
    {
        $s=$this->db->prepare('SELECT sp.*,u.name,u.email FROM staff_profiles sp INNER JOIN users u ON u.id=sp.user_id WHERE sp.user_id=:id AND sp.created_by_manager_id=:manager_id AND sp.branch_id=:branch_id AND sp.is_archived=0 LIMIT 1');
        $s->execute(['id'=>$id,'manager_id'=>$managerId,'branch_id'=>$branchId]); return $s->fetch() ?: null;
    }
    public function emailExists(string $email): bool { $s=$this->db->prepare('SELECT 1 FROM users WHERE email=:email'); $s->execute(['email'=>$email]); return (bool)$s->fetchColumn(); }

    /** $branchId comes from branch_managers, never from POST input. */
    public function createForManager(int $managerId,int $sellerId,int $branchId,array $data,string $password): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('INSERT INTO users (name,email,password,role,status,created_at) VALUES (:name,:email,:password,"staff","approved",NOW())')->execute(['name'=>trim($data['first_name'].' '.$data['last_name']),'email'=>$data['email'],'password'=>password_hash($password,PASSWORD_DEFAULT)]);
            $id=(int)$this->db->lastInsertId();
            $this->db->prepare('INSERT INTO staff_profiles (user_id,seller_id,branch_id,created_by_manager_id,first_name,last_name,phone,position,status) VALUES (:user_id,:seller_id,:branch_id,:manager_id,:first_name,:last_name,:phone,:position,"active")')->execute([
                'user_id'=>$id,
                'seller_id'=>$sellerId,
                'branch_id'=>$branchId,
                'manager_id'=>$managerId,
                'first_name'=>$data['first_name'],
                'last_name'=>$data['last_name'],
                'phone'=>$data['phone'],
                'position'=>$data['position'],
            ]);
            $this->db->commit(); return $id;
        } catch(Throwable $e) { if($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }
    public function updateForManager(int $id,int $managerId,int $branchId,array $data): bool
    {
        if(!$this->findForManager($id,$managerId,$branchId)) return false;
        $this->db->prepare('UPDATE users SET name=:name,email=:email WHERE id=:id')->execute(['name'=>trim($data['first_name'].' '.$data['last_name']),'email'=>$data['email'],'id'=>$id]);
        return $this->db->prepare('UPDATE staff_profiles SET first_name=:first_name,last_name=:last_name,phone=:phone,position=:position WHERE user_id=:id AND created_by_manager_id=:manager_id AND branch_id=:branch_id')->execute($data+['id'=>$id,'manager_id'=>$managerId,'branch_id'=>$branchId]);
    }
    public function setStatusForManager(int $id,int $managerId,int $branchId,string $status): bool
    {
        if(!in_array($status,['active','inactive','suspended'],true)) return false;
        return $this->db->prepare('UPDATE staff_profiles SET status=:status WHERE user_id=:id AND created_by_manager_id=:manager_id AND branch_id=:branch_id AND is_archived=0')->execute(['status'=>$status,'id'=>$id,'manager_id'=>$managerId,'branch_id'=>$branchId]);
    }
    /** Archive is a terminal, one-way action (spec section 16) — archived staff drop out of allByManager(). */
    public function archiveForManager(int $id,int $managerId,int $branchId): bool
    {
        return $this->db->prepare('UPDATE staff_profiles SET is_archived=1,status="inactive" WHERE user_id=:id AND created_by_manager_id=:manager_id AND branch_id=:branch_id AND is_archived=0')->execute(['id'=>$id,'manager_id'=>$managerId,'branch_id'=>$branchId]);
    }
    /** Ownership is re-verified before touching users.password so a manager can never reset a staff account outside their own branch. */
    public function resetPasswordForManager(int $id,int $managerId,int $branchId,string $password): bool
    {
        if(!$this->findForManager($id,$managerId,$branchId)) return false;
        return $this->db->prepare('UPDATE users SET password=:password WHERE id=:id')->execute(['password'=>password_hash($password,PASSWORD_DEFAULT),'id'=>$id]);
    }
    public function profileForUser(int $id): ?array
    {
        $s=$this->db->prepare('SELECT sp.*,u.name,u.email,b.name AS branch_name FROM staff_profiles sp INNER JOIN users u ON u.id=sp.user_id INNER JOIN branches b ON b.id=sp.branch_id WHERE sp.user_id=:id AND sp.is_archived=0 LIMIT 1'); $s->execute(['id'=>$id]); return $s->fetch() ?: null;
    }
    public function isAssignedToBranch(int $id,int $branchId): bool { $s=$this->db->prepare('SELECT 1 FROM staff_profiles WHERE user_id=:id AND branch_id=:branch_id AND status="active" AND is_archived=0'); $s->execute(['id'=>$id,'branch_id'=>$branchId]); return (bool)$s->fetchColumn(); }
    public function permissionsForPosition(string $position): array { return self::POSITION_PERMISSIONS[$position] ?? []; }
    
    public function allForSuperAdmin(): array
    {
        $sql = 'SELECT u.id,u.name,u.email,sp.first_name,sp.last_name,sp.position,sp.status,sp.created_at,
                b.name AS branch_name, s.name AS seller_name, mgr.name AS manager_name
            FROM staff_profiles sp
            INNER JOIN users u ON u.id = sp.user_id
            INNER JOIN branches b ON b.id = sp.branch_id
            INNER JOIN users s ON s.id = sp.seller_id
            LEFT JOIN users mgr ON mgr.id = sp.created_by_manager_id
            WHERE sp.position != "branch_manager" AND sp.is_archived = 0
            ORDER BY s.name, b.name, u.name';
        return $this->db->query($sql)->fetchAll();
    }
    public function allForBranchOverview(int $branchId, int $sellerId): array
{
    $s = $this->db->prepare(
        'SELECT sp.first_name, sp.last_name, sp.phone, sp.position, sp.status
         FROM staff_profiles sp
         WHERE sp.branch_id = :branch_id
           AND sp.seller_id = :seller_id
           AND sp.is_archived = 0
           AND sp.position != "branch_manager"
         ORDER BY sp.status, sp.last_name, sp.first_name'
    );
    $s->execute(['branch_id' => $branchId, 'seller_id' => $sellerId]);
    return $s->fetchAll();
}
}
