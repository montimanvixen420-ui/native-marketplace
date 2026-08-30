<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/StockRequest.php';

class SupplierRequestsController extends Controller
{
    private StockRequest $stockRequestModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->requireRole('supplier');
        $this->stockRequestModel = new StockRequest();
    }

    public function index(): void
    {
        $this->view('supplier/requests/index', [
            'name' => $_SESSION['user_name'],
            'requests' => $this->stockRequestModel->allBySupplier((int) $_SESSION['user_id']),
            'error' => $_SESSION['supplier_request_error'] ?? null,
            'active' => 'requests',
        ]);
        unset($_SESSION['supplier_request_error']);
    }

    public function updateStatus(): void
    {
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($requestId > 0 && $status === StockRequest::STATUS_FULFILLED) {
            if (!$this->stockRequestModel->fulfillAndDeductForSupplier($requestId, (int) $_SESSION['user_id'])) {
                $_SESSION['supplier_request_error'] = 'Unable to fulfill this request because the current stock is no longer enough.';
            }
        } elseif ($requestId > 0 && $status === StockRequest::STATUS_REJECTED) {
            $this->stockRequestModel->updateStatusForSupplier($requestId, (int) $_SESSION['user_id'], $status);
        }

        $this->redirect('/supplier/requests');
    }
}
