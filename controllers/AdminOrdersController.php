<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../core/OrderNotificationMailer.php';

class AdminOrdersController extends Controller
{
    private Order $orderModel;
    private Branch $branchModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireApprovedSeller();
        $this->orderModel = new Order();
        $this->branchModel = new Branch();
    }

    // GET /admin/orders?branch=<id|none|''>
    public function index(): void
    {
        $sellerId = (int) $_SESSION['user_id'];
        $branchFilter = (string) ($_GET['branch'] ?? '');
        $orders = $this->orderModel->allBySeller($sellerId, $branchFilter);

        $this->view('admin/orders/index', [
            'name' => $_SESSION['user_name'],
            'orders' => $orders,
            'branches' => $this->branchModel->allBySeller($sellerId),
            'branchFilter' => $branchFilter,
            'pendingOnlineOrders' => $this->orderModel->countPendingOnlineBySeller($sellerId),
            'active' => 'orders',
        ]);
    }

    // GET /admin/orders/{id}
    public function show(int $id): void
    {
        $order = $this->orderModel->findByIdForSeller($id, (int) $_SESSION['user_id']);

        if (!$order) {
            $this->redirect('/admin/orders');
            return;
        }

        $this->view('admin/orders/show', [
            'name' => $_SESSION['user_name'],
            'order' => $order,
            'items' => $this->orderModel->getItems($id),
            'nextStatuses' => $this->orderModel->nextStatuses($order['status']),
            'error' => $_GET['error'] ?? null,
            'active' => 'orders',
        ]);
    }

    // POST /admin/orders/{id}/fulfillment
    public function updateFulfillment(int $id): void
    {
        $result = $this->orderModel->updateFulfillment(
            $id,
            (int) $_SESSION['user_id'],
            $_POST['status'] ?? '',
            trim($_POST['courier'] ?? ''),
            trim($_POST['tracking_number'] ?? '')
        );
        if ($result['success']) (new OrderNotificationMailer())->sendStatus($id, $_POST['status'] ?? '', $this->orderModel);

        $url = '/admin/orders/' . $id;
        if (!$result['success']) {
            $url .= '?error=' . urlencode($result['error']);
        }
        $this->redirect($url);
    }
}