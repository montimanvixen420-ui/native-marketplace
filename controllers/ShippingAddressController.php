<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ShippingAddress.php';

class ShippingAddressController extends Controller
{
    private ShippingAddress $addressModel;
    public function __construct() { if (session_status() === PHP_SESSION_NONE) session_start(); $this->requireRole('customer'); $this->addressModel = new ShippingAddress(); }
    public function index(): void { $this->view('customer/addresses', ['name' => $_SESSION['user_name'], 'addresses' => $this->addressModel->allByCustomer((int) $_SESSION['user_id']), 'error' => $_GET['error'] ?? null, 'success' => $_GET['success'] ?? null, 'active' => 'addresses']); }
    public function store(): void { $result = $this->addressModel->create((int) $_SESSION['user_id'], $_POST); $this->redirect('/addresses?' . ($result['success'] ? 'success=' . urlencode('Shipping address saved.') : 'error=' . urlencode($result['error']))); }
}
