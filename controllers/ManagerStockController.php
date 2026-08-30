<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/BranchStock.php';
require_once __DIR__ . '/../models/ProductBranch.php';

/**
 * Branch stock page — real per-branch (and per-variant-branch) stock,
 * scoped to the ONE branch the logged-in staff was assigned. Every
 * adjustment requires a reason and is logged (BranchStock::adjust()) so
 * the Seller can see why the number changed later.
 *
 * Access: Branch Manager (position 'branch_manager') and Inventory Staff
 * (position 'inventory_staff') only — inventory is literally their job,
 * everyone else (Cashier, Order Staff, Customer Service) has no business
 * changing stock levels. Enforced here, not just hidden in the sidebar,
 * so a direct URL visit is blocked too.
 */
class ManagerStockController extends Controller
{
    private BranchStock $branchStock;
    private ProductBranch $productBranches;

    private const ALLOWED_POSITIONS = ['branch_manager', 'inventory_staff'];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->branchStock = new BranchStock();
        $this->productBranches = new ProductBranch();
    }

    // GET /manager/stock
    public function index(): void
    {
        $profile = $this->requireActiveStaff();
        $this->guardPosition($profile);
        $branchId = (int) $profile['branch_id'];

        $this->view('manager/stock', [
            'name' => $_SESSION['user_name'],
            'profile' => $profile,
            'rows' => $this->branchStock->forBranch($branchId),
            // Damage uses the separate report-and-approval workflow, so it
            // cannot be silently deducted from the generic adjustment form.
            'reasons' => array_diff_key(BranchStock::REASONS, ['damaged' => true]),
            'error' => $_GET['error'] ?? null,
            'active' => 'stock',
        ]);
    }

    // POST /manager/stock/adjust
    public function adjust(): void
    {
        $profile = $this->requireActiveStaff();
        $this->guardPosition($profile);
        $branchId = (int) $profile['branch_id'];

        $productId = (int) ($_POST['product_id'] ?? 0);
        $size = trim($_POST['variant_size'] ?? '');
        $color = trim($_POST['variant_color'] ?? '');
        $newStock = (int) ($_POST['stock'] ?? -1);
        $reason = $_POST['reason'] ?? '';
        $note = trim($_POST['note'] ?? '');

        if ($reason === 'damaged') {
            $this->redirect('/manager/stock?error=' . urlencode('Damaged stock must be reported by Inventory Staff and confirmed by the Branch Manager.'));
            return;
        }

        // Defense in depth: this branch must actually carry this product.
        if (!$this->productBranches->belongsToBranch($productId, $branchId)) {
            $this->redirect('/manager/stock?error=' . urlencode('That product is not assigned to your branch.'));
            return;
        }

        // Log the specific position (branch_manager / inventory_staff) rather
        // than a generic 'manager' string, so the Seller's audit trail shows
        // exactly who made the change.
        $result = $this->branchStock->adjust($productId, $size, $color, $branchId, $newStock, (int) $_SESSION['user_id'], $profile['position'], $reason, $note);

        $this->redirect($result['success'] ? '/manager/stock' : '/manager/stock?error=' . urlencode($result['error']));
    }

    private function guardPosition(array $profile): void
    {
        if (!in_array($profile['position'], self::ALLOWED_POSITIONS, true)) {
            http_response_code(403);
            exit('Access Denied: only branch managers and inventory staff may manage branch stock.');
        }
    }
}
