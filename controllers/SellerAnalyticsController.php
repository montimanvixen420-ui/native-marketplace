<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/BranchPosStock.php';

class SellerAnalyticsController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireApprovedSeller();
    }

    public function index(): void
    {
        $sellerId = (int) $_SESSION['user_id'];
        $threshold = 5;
        $salesTrendDays = 14;
        $productModel = new Product();
        $orderModel = new Order();
        $branchModel = new Branch();
        $branchPosStock = new BranchPosStock();

        $branches = $branchModel->allBySeller($sellerId);

        // Default to the Seller's own POS + online orders (no branch attached),
        // same convention as the Orders page. ?branch_id=none / '' / <id>.
        $branchFilter = (string) ($_GET['branch_id'] ?? 'none');
        $ownOnly = false;
        $selectedBranchId = null;
        if ($branchFilter === 'none') {
            $ownOnly = true;
        } elseif ($branchFilter !== '' && ctype_digit($branchFilter)) {
            $requested = (int) $branchFilter;
            foreach ($branches as $branch) {
                if ((int) $branch['id'] === $requested) {
                    $selectedBranchId = $requested;
                    break;
                }
            }
        }
        // else '' => "All branches" combined view: $selectedBranchId stays null, $ownOnly stays false.

        $branchComparison = ($selectedBranchId === null && count($branches) > 1)
            ? $orderModel->getSalesByBranchesForSeller($sellerId, $salesTrendDays)
            : [];

        // Inventory: a specific branch shows that branch's own POS stock (what
        // actually goes down on any order for it, online or POS); otherwise it's
        // the Seller's own product catalog.
        $inventory = $selectedBranchId !== null
            ? $branchPosStock->getInventorySummaryForBranch($selectedBranchId, $threshold)
            : $productModel->getInventorySummaryBySeller($sellerId, $threshold);
        $lowStockItems = $selectedBranchId !== null
            ? $branchPosStock->getLowStockForBranch($selectedBranchId, $threshold)
            : $productModel->getLowStockBySeller($sellerId, $threshold);

        $this->view('admin/analytics', [
            'name' => $_SESSION['user_name'],
            'threshold' => $threshold,
            'salesTrendDays' => $salesTrendDays,
            'inventory' => $inventory,
            'lowStockItems' => $lowStockItems,
            'sales' => $orderModel->getSalesAnalyticsBySeller($sellerId, $selectedBranchId, $ownOnly),
            'dailySales' => $orderModel->getDailySalesBySeller($sellerId, $salesTrendDays, $selectedBranchId, $ownOnly),
            'topProducts' => $orderModel->getTopProductsBySeller($sellerId, 5, $selectedBranchId, $ownOnly),
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'branchFilter' => $branchFilter,
            'branchComparison' => $branchComparison,
            'active' => 'analytics',
        ]);
    }
}