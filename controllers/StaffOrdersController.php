<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../core/OrderNotificationMailer.php';

/**
 * Branch Manager + Staff Orders (staff.txt sections 34-35).
 *
 * A separate controller from AdminOrdersController rather than reusing
 * /admin/orders. Branch Managers (role=manager) and Staff (role=staff)
 * are each tied to exactly ONE branch_id via staff_profiles -- so a
 * single branch-scoped query set serves both. Every query here is
 * scoped to that one branch at the database level (Order::allByBranch /
 * findByIdForBranch), never just hidden in the UI.
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

        $this->view('staff/orders/index', [
            'name' => $_SESSION['user_name'],
            'profile' => $profile,
            'orders' => $this->orderModel->allByBranch($branchId),
            'pendingOnlineOrders' => $this->orderModel->countPendingOnlineByBranch($branchId),
            'active' => 'orders',
        ]);
    }

    // GET /staff/orders/{id}
    public function show(int $id): void
    {
        $profile = $this->requireActiveStaff();
        $branchId = (int) $profile['branch_id'];

        $order = $this->orderModel->findByIdForBranch($id, $branchId);
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

        // Only Order Staff may process orders. Branch Managers and other
        // positions (cashier, inventory staff, customer service) can view
        // branch orders but not change their fulfillment status.
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