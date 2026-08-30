<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Order.php';

class OrdersController extends Controller
{
    private Order $orderModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireRole('customer');
        $this->orderModel = new Order();
    }

    // GET /orders
    public function index(): void
    {
        $orders = $this->orderModel->allByCustomer((int) $_SESSION['user_id']);

        $this->view('customer/orders', [
            'name' => $_SESSION['user_name'],
            'orders' => $orders,
            'justPlaced' => isset($_GET['placed']),
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null,
            'active' => 'my-orders',
        ]);
    }

    // POST /orders/{id}/cancel
    public function cancel(int $id): void
    {
        $result = $this->orderModel->cancelByCustomer($id, (int) $_SESSION['user_id']);
        $message = urlencode($result['success'] ? 'Order cancelled and stock returned.' : $result['error']);
        $this->redirect('/orders?' . ($result['success'] ? 'success=' : 'error=') . $message);
    }
}
