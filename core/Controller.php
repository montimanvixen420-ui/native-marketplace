<?php

class Controller
{
    /** Ensures a seller account was approved before using seller-only tools. */
    protected function requireApprovedSeller(): void
    {
        $this->requireRole('admin');
        require_once __DIR__ . '/../models/User.php';

        $seller = (new User())->findById((int) $_SESSION['user_id']);
        if (!$seller || $seller['status'] !== User::STATUS_APPROVED) {
            $_SESSION['seller_access_error'] = 'Your seller account must be approved before you can manage products or sales.';
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Ensures the logged-in user is a staff member whose account is
     * currently active (not deactivated/suspended/archived by the seller).
     * Returns the staff profile row so callers don't have to fetch it again.
     */
    protected function requireActiveStaff(): array
    {
        $this->requireRole(['staff', 'manager']);
        require_once __DIR__ . '/../models/Staff.php';

        $profile = (new Staff())->profileForUser((int) $_SESSION['user_id']);
        if (!$profile || $profile['status'] !== 'active') {
            session_destroy();
            $this->redirect('/login');
        }
        return $profile;
    }

    /**
     * Section 5 of the staff spec: the backend must verify branch
     * authorization on EVERY branch-related request. Never trust a
     * branch id coming from the URL/form alone.
     *
     * Call this at the top of any staff-facing action that takes a
     * branch id, right after requireActiveStaff().
     */
    protected function requireBranchAccess(int $branchId): void
    {
        require_once __DIR__ . '/../models/Staff.php';
        $staffId = (int) ($_SESSION['user_id'] ?? 0);

        if (!(new Staff())->isAssignedToBranch($staffId, $branchId)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied — you are not assigned to this branch.',
            ]);
            exit;
        }
    }

    /**
     * Renders a view file, wrapped in the header/footer layout.
     * Example: $this->view('auth/login', ['error' => $error]);
     */
    protected function view(string $viewPath, array $data = []): void
    {
        extract($data); // turns array keys into variables (e.g. $error)

        $viewFile = __DIR__ . "/../views/{$viewPath}.php";

        if (!file_exists($viewFile)) {
            die("View file '{$viewPath}.php' was not found.");
        }

        require __DIR__ . '/../views/layouts/header.php';
        require $viewFile;
        require __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Redirects to another path.
     */
    protected function redirect(string $path): void
    {
        header("Location: {$path}");
        exit;
    }

    /**
     * Verifies that the user is logged in and has the matching role.
     * Used by all controllers with role-restricted pages
     * (superadmin, admin, supplier, etc). Accepts a single role or
     * an array of roles that are all allowed (e.g. staff pages that
     * Branch Managers should also reach).
     */
    protected function requireRole(string|array $role): void
    {
        $allowed = is_array($role) ? $role : [$role];
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], $allowed, true)) {
            $this->redirect('/login');
            exit;
        }
    }
}
