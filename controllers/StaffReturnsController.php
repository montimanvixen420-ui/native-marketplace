<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PostPurchase.php';

/**
 * Branch-scoped return/refund handling for Customer Service Staff.
 *
 * Mirrors AdminReturnsController but scoped to a single branch_id via
 * staff_profiles, same pattern as StaffOrdersController. Only staff whose
 * position is 'customer_service' may act on requests here — Branch
 * Managers and other positions get redirected with an error, matching
 * how order fulfillment is locked to 'order_staff' only.
 */
class StaffReturnsController extends Controller
{
    private PostPurchase $postPurchase;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->postPurchase = new PostPurchase();
    }

    // GET /staff/returns
    public function index(): void
    {
        $profile = $this->requireActiveStaff();
        $branchId = (int) $profile['branch_id'];

        $this->view('staff/returns/index', [
            'name' => $_SESSION['user_name'],
            'profile' => $profile,
            'requests' => $this->postPurchase->returnsByBranch($branchId),
            'success' => isset($_GET['updated']),
            'error' => $_GET['error'] ?? null,
            'active' => 'returns',
        ]);
    }

    // POST /staff/returns/{id}
    public function update(int $id): void
    {
        $profile = $this->requireActiveStaff();
        $branchId = (int) $profile['branch_id'];

        // Only Customer Service Staff may process returns. Branch Managers
        // and other positions (cashier, inventory staff, order staff) can
        // view branch return requests but not change their status.
        if ($profile['position'] !== 'customer_service') {
            $this->redirect('/staff/returns?error=' . urlencode('Only customer service staff can process returns. You have view-only access.'));
            return;
        }

        $this->postPurchase->updateReturnStatusForBranch($id, $branchId, $_POST['status'] ?? '', (int) $_SESSION['user_id']);
        $this->redirect('/staff/returns?updated=1');
    }
}
