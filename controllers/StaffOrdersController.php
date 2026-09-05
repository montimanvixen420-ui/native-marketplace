<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../core/OrderNotificationMailer.php';

/**
 * Branch Manager + Staff Orders (staff.txt sections 34-35).
 */
class StaffOrdersController extends Controller
{
    private Order $orderModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->orderModel = new Order();
    }

    // GET /staff/orders
    public function index(): void
    {
        $profile = $this->requireActiveStaff();
        $branchId = (int) $profile['branch_id'];

        $isCashier = $profile['position'] === 'cashier';
        $this->view('staff/orders/index', [
            'name' => $_SESSION['user_name'],
            'profile' => $profile,
            'orders' => $isCashier
                ? $this->orderModel->allByBranchForCashier($branchId, (int) $_SESSION['user_id'])
                : $this->orderModel->allByBranch($branchId),
            'pendingOnlineOrders' => $isCashier ? 0 : $this->orderModel->countPendingOnlineByBranch($branchId),
            'active' => 'orders',
        ]);
    }

    // GET /staff/orders/{id}
    public function show(int $id): void
    {
        $profile = $this->requireActiveStaff();
        $branchId = (int) $profile['branch_id'];

        $order = $profile['position'] === 'cashier'
            ? $this->orderModel->findByIdForBranchCashier($id, $branchId, (int) $_SESSION['user_id'])
            : $this->orderModel->findByIdForBranch($id, $branchId);
        if (!$order) {
            $this->redirect('/staff/orders');
            return;
        }

        $this->view('staff/orders/show', [
            'name' => $_SESSION['user_name'],
            'profile' => $profile,
            'order' => $order,
            'items' => $this->orderModel->getItems($id),
            'nextStatuses' => $this->orderModel->nextStatuses($order['status']),
            'error' => $_GET['error'] ?? null,
            'active' => 'orders',
        ]);
    }

    // POST /staff/orders/{id}/fulfillment
    public function updateFulfillment(int $id): void
    {
        $profile = $this->requireActiveStaff();
        $branchId = (int) $profile['branch_id'];

        if ($profile['position'] !== 'order_staff') {
            $this->redirect('/staff/orders/' . $id . '?error=' . urlencode('Only order staff can process orders. You have view-only access.'));
            return;
        }

        $result = $this->orderModel->updateFulfillmentForBranch(
            $id,
            $branchId,
            $_POST['status'] ?? '',
            trim($_POST['courier'] ?? ''),
            trim($_POST['tracking_number'] ?? '')
        );

        if ($result['success']) (new OrderNotificationMailer())->sendStatus($id, $_POST['status'] ?? '', $this->orderModel);

        $url = '/staff/orders/' . $id;
        if (!$result['success']) {
            $url .= '?error=' . urlencode($result['error']);
        }
        $this->redirect($url);
    }
}
