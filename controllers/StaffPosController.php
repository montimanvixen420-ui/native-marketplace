<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Branch-scoped POS for Cashier — mirrors PosController (the Seller's own
 * POS), but scoped to the cashier's assigned branch. Every sale is tagged
 * with branch_id automatically (via Order::checkout's $branchId param,
 * already supported there but never used by the Seller's own POS), so
 * branch-level sales are always attributable to the right branch.
 *
 * Only 'cashier' may use this — Order Staff, Customer Service, Inventory
 * Staff, and Branch Manager do not have "sales" in their permission set
 * (see Staff::POSITION_PERMISSIONS).
 */
class StaffPosController extends Controller
{
    private Product $productModel;
    private Order $orderModel;
    private User $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    // GET /staff/pos
    public function index(): void
    {
        $profile = $this->requireActiveStaff();

        if ($profile['position'] !== 'cashier') {
            http_response_code(403);
            exit('Access Denied: only cashiers may use the Point of Sale.');
        }

        $branchId = (int) $profile['branch_id'];
        $sellerId = (int) $profile['seller_id'];

        $allProducts = $this->productModel->activeForBranchPos($sellerId, $branchId);
        $products = array_values(array_filter($allProducts, function ($p) {
            return $p['status'] === 'active' && (int) $p['stock'] > 0;
        }));

        $this->view('staff/pos/index', [
            'products' => $products,
            'categories' => $this->productModel->getSellerCategories($sellerId),
            'profile' => $profile,
            'active' => 'pos',
            'name' => $_SESSION['user_name'] ?? '',
        ]);
    }

    // GET /staff/pos/customers?q=juan
    // AJAX endpoint — returns JSON, does NOT use $this->view()
    public function searchCustomers(): void
    {
        $this->requireActiveStaff();
        $query = trim($_GET['q'] ?? '');

        $results = $query !== ''
            ? $this->userModel->searchUsers($query, User::ROLE_CUSTOMER, User::STATUS_APPROVED)
            : [];

        header('Content-Type: application/json');
        echo json_encode(array_map(function ($u) {
            return [
                'id' => (int) $u['id'],
                'name' => $u['name'],
                'email' => $u['email'],
            ];
        }, $results));
        exit;
    }

    // POST /staff/pos/checkout
    // AJAX endpoint — expects JSON body, returns JSON
    public function checkout(): void
    {
        header('Content-Type: application/json');
        $profile = $this->requireActiveStaff();

        if ($profile['position'] !== 'cashier') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only cashiers may process sales.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid request.']);
            exit;
        }

        $items = $input['items'] ?? [];
        $paymentMethod = $input['payment_method'] ?? 'cash';
        $customerId = !empty($input['customer_id']) ? (int) $input['customer_id'] : null;
        $customerName = trim($input['customer_name'] ?? '');

        if (!$customerId && $customerName === '') {
            $customerName = 'Walk-in customer';
        }

        $result = $this->orderModel->checkout(
            (int) $profile['seller_id'],
            $customerId,
            $customerName,
            $items,
            $paymentMethod,
            'pos',
            'completed',
            0.0,
            0.0,
            null,
            (int) $profile['branch_id'],
            (int) $_SESSION['user_id'],
            'cashier'
        );

        if (!$result['success']) {
            http_response_code(422);
        }

        echo json_encode($result);
        exit;
    }
}
