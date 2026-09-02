<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/StockRequest.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SupplierInventory.php';

class StockRequestController extends Controller
{
    private StockRequest $stockRequestModel;
    private User $userModel;
    private SupplierInventory $inventoryModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireApprovedSeller();
        $this->stockRequestModel = new StockRequest();
        $this->userModel = new User();
        $this->inventoryModel = new SupplierInventory();
    }

    // GET /stock-requests
    public function index(): void
    {
        $requests = $this->stockRequestModel->allBySeller((int) $_SESSION['user_id']);

        $this->view('admin/stock-requests/index', [
            'name' => $_SESSION['user_name'],
            'requests' => $requests,
            'active' => 'stock',
            'error' => $_GET['error'] ?? null,
            'success' => $_GET['success'] ?? null,
        ]);
    }

    // GET /stock-requests/create
    public function create(): void
    {
        $this->view('admin/stock-requests/create', [
            'name' => $_SESSION['user_name'],
            'supplies' => $this->inventoryModel->availableForSellers(),
            'error' => null,
            'active' => 'stock',
        ]);
    }

    // POST /stock-requests/store
    public function store(): void
    {
        $supplierId = (int) ($_POST['supplier_id'] ?? 0);
        $supplyId = (int) ($_POST['supply_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $note = trim($_POST['note'] ?? '');

        $error = null;
        $supply = $supplyId > 0 ? $this->inventoryModel->findAvailableForSupplier($supplyId, $supplierId) : null;
        if (!$supply) {
            $error = 'Choose an available supply from the selected supplier.';
        } elseif ($quantity <= 0) {
            $error = 'Please enter a valid quantity.';
        } elseif ($quantity > (int) $supply['quantity_available']) {
            $error = 'Requested quantity is higher than the supplier\'s available stock.';
        }

        if ($error !== null) {
            $this->view('admin/stock-requests/create', [
                'name' => $_SESSION['user_name'],
                'supplies' => $this->inventoryModel->availableForSellers(),
                'error' => $error,
                'active' => 'stock',
            ]);
            return;
        }

        $this->stockRequestModel->create(
            (int) $_SESSION['user_id'],
            (int) $supply['supplier_id'],
            $supply['item_name'],
            $quantity,
            $note !== '' ? $note : null,
            $supplyId
        );

        $this->redirect('/stock-requests');
    }

    // POST /stock-requests/receive
    public function receive(): void
    {
        $requestId = (int) ($_POST['id'] ?? 0);
        $result = $this->stockRequestModel->receiveForSeller($requestId, (int) $_SESSION['user_id']);

        $this->redirect($result['success']
            ? '/stock-requests?success=' . urlencode('Stock received into your inventory.')
            : '/stock-requests?error=' . urlencode($result['error']));
    }
}