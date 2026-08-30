<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SellerApplication.php';

class UserManagementController extends Controller
{
    private User $userModel;
    private SellerApplication $sellerApplicationModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
        $this->sellerApplicationModel = new SellerApplication();
    }

    // GET /superadmin/users
    public function index(): void
    {
        $this->requireRole('superadmin');

        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');

        $users = $this->userModel->searchUsers($search, $role, $status);

        $this->view('superadmin/users/index', [
            'name' => $_SESSION['user_name'],
            'users' => $users,
            'filters' => [
                'role' => $role,
                'status' => $status,
                'search' => $search,
            ],
            'active' => 'users',
        ]);
    }

    // GET /superadmin/users/{id}/edit
    public function edit(int $id): void
    {
        $this->requireRole('superadmin');

        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->redirect('/superadmin/users');
            return;
        }

        $this->view('superadmin/users/edit', [
            'name' => $_SESSION['user_name'],
            'user' => $user,
            'error' => null,
            'active' => 'users',
        ]);
    }

    // POST /superadmin/users/{id}/update
    public function update(int $id): void
    {
        $this->requireRole('superadmin');

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';

        $allowedRoles = [
            User::ROLE_SUPERADMIN,
            User::ROLE_ADMIN,
            User::ROLE_SUPPLIER,
            User::ROLE_CUSTOMER,
        ];

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, $allowedRoles, true)) {
            $user = $this->userModel->findById($id);
            $this->view('superadmin/users/edit', [
                'name' => $_SESSION['user_name'],
                'user' => $user,
                'error' => 'Please fill in a valid name, email, and role.',
                'active' => 'users',
            ]);
            return;
        }

        $this->userModel->updateDetails($id, $name, $email, $role);
        $this->redirect('/superadmin/users');
    }

    // POST /superadmin/users/{id}/approve
    public function approve(int $id): void
    {
        $this->requireRole('superadmin');
        $this->userModel->updateStatus($id, User::STATUS_APPROVED);
        $this->sellerApplicationModel->updateVerificationStatus($id, 'verified');
        $this->redirectBack('/superadmin/dashboard');
    }

    // POST /superadmin/users/{id}/reject
    public function reject(int $id): void
    {
        $this->requireRole('superadmin');
        $this->userModel->updateStatus($id, User::STATUS_BANNED);
        $this->sellerApplicationModel->updateVerificationStatus($id, 'rejected');
        $this->redirectBack('/superadmin/dashboard');
    }

    // POST /superadmin/users/{id}/suspend
    public function suspend(int $id): void
    {
        $this->requireRole('superadmin');
        $this->userModel->updateStatus($id, User::STATUS_SUSPENDED);
        $this->redirectBack('/superadmin/users');
    }

    // POST /superadmin/users/{id}/reactivate
    public function reactivate(int $id): void
    {
        $this->requireRole('superadmin');
        $this->userModel->updateStatus($id, User::STATUS_APPROVED);
        $this->redirectBack('/superadmin/users');
    }

    // POST /superadmin/users/{id}/delete
    public function delete(int $id): void
    {
        $this->requireRole('superadmin');

        // Don't let the superadmin delete their own account by accident
        if ($id === (int) $_SESSION['user_id']) {
            $this->redirectBack('/superadmin/users');
            return;
        }

        $this->userModel->deleteUser($id);
        $this->redirectBack('/superadmin/users');
    }

    // GET /superadmin/sellers
    public function sellers(): void
    {
        $this->requireRole('superadmin');

        $status = $_GET['status'] ?? '';

        $sellers = $this->userModel->searchUsers('', User::ROLE_ADMIN, $status);
        $sellerApplications = $this->sellerApplicationModel->getByUserIds(array_column($sellers, 'id'));

        $this->view('superadmin/sellers', [
            'name' => $_SESSION['user_name'],
            'sellers' => $sellers,
            'sellerApplications' => $sellerApplications,
            'statusFilter' => $status,
            'active' => 'sellers',
        ]);
    }

    // GET /superadmin/suppliers
    public function suppliers(): void
    {
        $this->requireRole('superadmin');

        $status = $_GET['status'] ?? '';

        $suppliers = $this->userModel->searchUsers('', User::ROLE_SUPPLIER, $status);
        $sellerApplications = $this->sellerApplicationModel->getByUserIds(array_column($suppliers, 'id'));

        $this->view('superadmin/suppliers', [
            'name' => $_SESSION['user_name'],
            'suppliers' => $suppliers,
            'sellerApplications' => $sellerApplications,
            'statusFilter' => $status,
            'active' => 'suppliers',
        ]);
    }

    // GET /superadmin/applications
    public function reviewApplications(): void
    {
        $this->requireRole('superadmin');

        $role = $_GET['role'] ?? '';

        $pendingSellers = ($role === '' || $role === User::ROLE_ADMIN)
            ? $this->userModel->searchUsers('', User::ROLE_ADMIN, User::STATUS_PENDING)
            : [];
        $pendingSuppliers = ($role === '' || $role === User::ROLE_SUPPLIER)
            ? $this->userModel->searchUsers('', User::ROLE_SUPPLIER, User::STATUS_PENDING)
            : [];

        $applicants = array_merge($pendingSellers, $pendingSuppliers);
        usort($applicants, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

        $sellerApplications = $this->sellerApplicationModel->getByUserIds(array_column($applicants, 'id'));

        $this->view('superadmin/applications', [
            'name' => $_SESSION['user_name'],
            'applicants' => $applicants,
            'sellerApplications' => $sellerApplications,
            'roleFilter' => $role,
            'active' => 'applications',
        ]);
    }

    /**
     * Redirect back to wherever the request came from (e.g. the
     * dashboard's pending-approvals widget), falling back to a
     * default path if there's no referer available.
     */
    private function redirectBack(string $fallback): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        $this->redirect($referer);
    }
}