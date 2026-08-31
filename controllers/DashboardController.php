<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/StockRequest.php';
require_once __DIR__ . '/../models/Content.php';

class DashboardController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

      public function superadmin(): void
    {
        $this->requireRole('superadmin');

        $userModel = new User();
        $orderModel = new Order();

        $this->view('superadmin/dashboard', [
            'name' => $_SESSION['user_name'],
            'stats' => $userModel->getDashboardStats(),
            'pendingApprovals' => $userModel->getPendingApprovals(),
            'salesSummary' => $orderModel->getPlatformSalesSummary(),
            'dailySales' => $orderModel->getPlatformDailySales(14),
            'topSellers' => $orderModel->getTopSellersByRevenue(5, 30),
            'active' => 'overview',
        ]);
    }

    public function admin(): void
{
    $this->requireRole('admin');

    $sellerId = (int) $_SESSION['user_id'];

    $seller = (new User())->findById($sellerId);
    $productModel = new Product();
    $orderModel = new Order();

    $activeProducts = $productModel->countBySeller($sellerId);
    $todaysOrders = $orderModel->countTodayBySeller($sellerId);
    $todaysRevenue = $orderModel->todaysRevenueBySeller($sellerId);
    $pendingOnlineOrders = $orderModel->countPendingOnlineBySeller($sellerId);
    $inventory = $productModel->getInventorySummaryBySeller($sellerId);
    $lowStockItems = array_slice($productModel->getLowStockBySeller($sellerId), 0, 5);

    // BAGO: totoong 7-day sales trend, kapareho ng ginagamit sa Analytics page
    $salesTrendDays = 7;
    $dailySales = $orderModel->getDailySalesBySeller($sellerId, $salesTrendDays);

    $this->view('admin/dashboard', [
        'name' => $_SESSION['user_name'],
        'activeProducts' => $activeProducts,
        'todaysOrders' => $todaysOrders,
        'todaysRevenue' => $todaysRevenue,
        'pendingOnlineOrders' => $pendingOnlineOrders,
        'sellerStatus' => $seller['status'] ?? User::STATUS_PENDING,
        'inventory' => $inventory,
        'lowStockItems' => $lowStockItems,
        'salesTrendDays' => $salesTrendDays,   // BAGO
        'dailySales' => $dailySales,           // BAGO
        'active' => 'overview',
    ]);
}

    public function supplier(): void
    {
        $this->requireRole('supplier');

        $supplierId = (int) $_SESSION['user_id'];
        $stockRequestModel = new StockRequest();

        $this->view('supplier/dashboard', [
            'name' => $_SESSION['user_name'],
            'stats' => $stockRequestModel->getSupplierStats($supplierId),
            'recentRequests' => array_slice($stockRequestModel->allBySupplier($supplierId), 0, 5),
            'active' => 'overview',
        ]);
    }

    public function customer(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            exit;
        }
        $this->view('customer/dashboard', [
            'name' => $_SESSION['user_name'],
            'banners' => (new Content())->getActive(Content::TYPE_BANNER),
            'announcements' => (new Content())->getActive(Content::TYPE_ANNOUNCEMENT),
            'siteTexts' => (new Content())->getActive(Content::TYPE_SITE_TEXT),
            'active' => 'overview',
        ]);
    }
}
