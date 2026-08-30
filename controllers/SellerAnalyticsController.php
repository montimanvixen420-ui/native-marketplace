<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Branch.php';

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

        $branches = $branchModel->allBySeller($sellerId);

        // Only trust a branch_id that actually belongs to this seller.
        $selectedBranchId = null;
        if (!empty($_GET['branch_id']) && ctype_digit((string) $_GET['branch_id'])) {
            $requested = (int) $_GET['branch_id'];
            foreach ($branches as $branch) {
                if ((int) $branch['id'] === $requested) {
                    $selectedBranchId = $requested;
                    break;
                }
            }
        }

        $branchComparison = ($selectedBranchId === null && count($branches) > 1)
            ? $orderModel->getSalesByBranchesForSeller($sellerId, $salesTrendDays)
            : [];

        $this->view('admin/analytics', [
            'name' => $_SESSION['user_name'],
            'threshold' => $threshold,
            'salesTrendDays' => $salesTrendDays,
            'inventory' => $productModel->getInventorySummaryBySeller($sellerId, $threshold),
            'lowStockItems' => $productModel->getLowStockBySeller($sellerId, $threshold),
            'sales' => $orderModel->getSalesAnalyticsBySeller($sellerId, $selectedBranchId),
            'dailySales' => $orderModel->getDailySalesBySeller($sellerId, $salesTrendDays, $selectedBranchId),
            'topProducts' => $orderModel->getTopProductsBySeller($sellerId, 5, $selectedBranchId),
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'branchComparison' => $branchComparison,
            'active' => 'analytics',
        ]);
    }
}