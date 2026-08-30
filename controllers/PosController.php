<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../models/SellerPosStock.php';

class PosController extends Controller
{
    private Product $productModel;
    private Order $orderModel;
    private User $userModel;
    private Staff $staffModel;
    private SellerPosStock $sellerPosStock;

    /** Set by guard(): null when the Seller is running POS directly, or the staff_profiles row when a branch Cashier is. */
    private ?array $staffProfile = null;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->staffModel = new Staff();
        $this->sellerPosStock = new SellerPosStock();
        $this->guard();
    }

    /**
     * POS is usable by the Seller directly, OR by a branch Staff/Manager
     * whose position grants the 'sales' permission (Cashier). Everything
     * else -- inventory_staff, order_staff, customer_service -- is
     * refused here rather than just hidden in the UI.
     */
    private function guard(): void
    {
        $this->requireRole(['admin', 'staff', 'manager']);

        if ($_SESSION['user_role'] === 'admin') {
            $seller = $this->userModel->findById((int) $_SESSION['user_id']);
            if (!$seller || $seller['status'] !== User::STATUS_APPROVED) {
                $_SESSION['seller_access_error'] = 'Your seller account must be approved before you can manage products or sales.';
                $this->redirect('/admin/dashboard');
            }
            return;
        }

        $profile = $this->staffModel->profileForUser((int) $_SESSION['user_id']);
        if (!$profile || $profile['status'] !== 'active') {
            session_destroy();
            $this->redirect('/login');
        }
        if (!in_array('sales', $this->staffModel->permissionsForPosition($profile['position']), true)) {
            http_response_code(403);
            exit('Access Denied: your staff position does not include Point of Sale access.');
        }
        $this->staffProfile = $profile;
    }

    private function sellerId(): int
    {
        return $this->staffProfile ? (int) $this->staffProfile['seller_id'] : (int) $_SESSION['user_id'];
    }

    private function branchId(): ?int
    {
        return $this->staffProfile ? (int) $this->staffProfile['branch_id'] : null;
    }

    // GET /pos
    public function index(): void
    {
        $branchId = $this->branchId();

        if ($branchId !== null) {
            $products = $this->productModel->activeForBranchPos($this->sellerId(), $branchId);
            $categories = $this->productModel->categoriesForBranch($this->sellerId(), $branchId);
        } else {
            $products = $this->sellerPosStock->posProducts($this->sellerId());
            $categories = $this->productModel->getSellerCategories($this->sellerId());
        }

        $this->view('admin/pos/index', [
            'products' => $products,
            'categories' => $categories,
            'active' => 'pos',
            'name' => $_SESSION['user_name'] ?? '',
            'branchName' => $this->staffProfile['branch_name'] ?? null,
        ]);
    }

    // GET /pos/customers?q=juan
    // AJAX endpoint — returns JSON, does NOT use $this->view()
    public function searchCustomers(): void
    {
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

    // POST /pos/checkout
    // AJAX endpoint — expects JSON body, returns JSON
    public function checkout(): void
    {
        header('Content-Type: application/json');

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
            $this->sellerId(),
            $customerId,
            $customerName,
            $items,
            $paymentMethod,
            'pos',
            'completed',
            0.0,
            0.0,
            null,
            $this->branchId(),
            (int) $_SESSION['user_id'],
            $_SESSION['user_role']
        );

        if (!$result['success']) {
            http_response_code(422);
        }

        echo json_encode($result);
        exit;
    }
}
