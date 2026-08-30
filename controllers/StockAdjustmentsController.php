<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/BranchStock.php';
require_once __DIR__ . '/../models/ProductBranch.php';

/**
 * Seller-facing "Stock Adjustments" page — the audit trail of every
 * branch stock change (who, when, why) across all of the seller's
 * branches. The Seller can also make their own adjustments here, which
 * get logged the same way as a manager's.
 */
class StockAdjustmentsController extends Controller
{
    private BranchStock $branchStock;
    private ProductBranch $productBranches;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->requireApprovedSeller();
        $this->branchStock = new BranchStock();
        $this->productBranches = new ProductBranch();
    }

    // GET /admin/stock-adjustments?branch=&product=
    public function index(): void
    {
        $sellerId = (int) $_SESSION['user_id'];
        $branchId = (int) ($_GET['branch'] ?? 0) ?: null;
        $productId = (int) ($_GET['product'] ?? 0) ?: null;

        $filters = $this->branchStock->filterOptionsForSeller($sellerId);

        $this->view('admin/stock-adjustments/index', [
            'name' => $_SESSION['user_name'],
            'adjustments' => $this->branchStock->adjustmentsForSeller($sellerId, $branchId, $productId),
            'branches' => $filters['branches'],
            'products' => $filters['products'],
            'branchFilter' => $branchId,
            'productFilter' => $productId,
            'reasons' => BranchStock::REASONS,
            'error' => $_GET['error'] ?? null,
            'active' => 'stock-adjustments',
        ]);
    }

    // POST /admin/stock-adjustments/adjust
    public function adjust(): void
    {
        $sellerId = (int) $_SESSION['user_id'];
        $productId = (int) ($_POST['product_id'] ?? 0);
        $branchId = (int) ($_POST['branch_id'] ?? 0);
        $size = trim($_POST['variant_size'] ?? '');
        $color = trim($_POST['variant_color'] ?? '');
        $newStock = (int) ($_POST['stock'] ?? -1);
        $reason = $_POST['reason'] ?? '';
        $note = trim($_POST['note'] ?? '');

        require_once __DIR__ . '/../models/Product.php';
        require_once __DIR__ . '/../models/Branch.php';
        $product = (new Product())->findByIdForSeller($productId, $sellerId);
        $branch = (new Branch())->findForSeller($branchId, $sellerId);
        if (!$product || !$branch || !$this->productBranches->belongsToBranch($productId, $branchId)) {
            $this->redirect('/admin/stock-adjustments?error=' . urlencode('That product is not assigned to that branch.'));
            return;
        }

        $result = $this->branchStock->adjust($productId, $size, $color, $branchId, $newStock, $sellerId, 'admin', $reason, $note);

        $this->redirect($result['success'] ? '/admin/stock-adjustments' : '/admin/stock-adjustments?error=' . urlencode($result['error']));
    }
}