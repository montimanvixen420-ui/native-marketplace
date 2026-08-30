<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/BranchManager.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/BranchStock.php';

/**
 * Branch Manager's OWN dashboard — separate from the generic Staff
 * dashboard. Lets a manager monitor the one branch their Seller assigned
 * to them: staff headcount, today's orders/sales, pending orders, TRUE
 * per-branch stock levels, and recent activity. Everything here is
 * scoped to $manager['branch_id'] only.
 */
class ManagerDashboardController extends Controller
{
    private BranchManager $managers;
    private Staff $staff;
    private Order $orderModel;
    private BranchStock $branchStock;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->managers = new BranchManager();
        $this->staff = new Staff();
        $this->orderModel = new Order();
        $this->branchStock = new BranchStock();
    }

    public function index(): void
    {
        $this->requireRole('manager');
        $manager = $this->managers->forUser((int) $_SESSION['user_id']);
        if (!$manager) {
            http_response_code(403);
            exit('Access Denied: only an active branch manager may view this dashboard.');
        }

        $branchId = (int) $manager['branch_id'];
        $staffList = $this->staff->allByManager((int) $_SESSION['user_id'], $branchId);
        $activeStaffCount = count(array_filter($staffList, fn ($s) => $s['status'] === 'active'));

        $recentOrders = array_slice($this->orderModel->allByBranch($branchId), 0, 5);
        $inventorySummary = $this->branchStock->summaryForBranch($branchId);
        $lowStockItems = array_slice($this->branchStock->lowStockForBranch($branchId), 0, 8);
        $salesTrendDays = 14;
        $dailySales = $this->orderModel->getDailySalesByBranch($branchId, $salesTrendDays);
        $topProducts = $this->orderModel->getTopProductsByBranch($branchId);

        $this->view('manager/dashboard', [
            'name' => $_SESSION['user_name'],
            'manager' => $manager,
            'staffCount' => count($staffList),
            'activeStaffCount' => $activeStaffCount,
            'todaysOrders' => $this->orderModel->countTodayByBranch($branchId),
            'pendingOrders' => $this->orderModel->countPendingOnlineByBranch($branchId),
            'todaysRevenue' => $this->orderModel->todaysRevenueByBranch($branchId),
            'recentOrders' => $recentOrders,
            'inventorySummary' => $inventorySummary,
            'lowStockItems' => $lowStockItems,
            'salesTrendDays' => $salesTrendDays,
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
            'active' => 'dashboard',
        ]);
    }
}