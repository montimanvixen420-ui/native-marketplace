<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/BranchManager.php';
require_once __DIR__ . '/../models/Staff.php';

/**
 * Super Admin view-only org structure: Seller -> Branch -> Branch Manager -> Staff.
 * staff.txt sections 18-21: VIEW ONLY, no CRUD, no operational data (orders/products/inventory).
 */
class OrganizationController extends Controller
{
    private Branch $branches;
    private BranchManager $managers;
    private Staff $staff;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->branches = new Branch();
        $this->managers = new BranchManager();
        $this->staff = new Staff();
    }

    public function index(): void
    {
        $this->requireRole('superadmin');
        $this->view('superadmin/organization', [
            'name' => $_SESSION['user_name'],
            'branches' => $this->branches->allForSuperAdmin(),
            'managers' => $this->managers->allForSuperAdmin(),
            'staffList' => $this->staff->allForSuperAdmin(),
            'positions' => Staff::POSITIONS,
            'active' => 'organization',
        ]);
    }
}