<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';

class ProductModerationController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $this->requireRole('superadmin');
        $this->view('superadmin/product-moderation/index', [
            'name' => $_SESSION['user_name'] ?? '',
            'queue' => $this->productModel->pendingModerationQueue(),
            'active' => 'product-moderation',
        ]);
    }

    public function review(int $flagId): void
    {
        $this->requireRole('superadmin');
        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['approved', 'rejected'], true)) $this->redirect('/superadmin/product-moderation');

        $this->productModel->reviewModerationFlag(
            $flagId,
            $decision,
            (int) $_SESSION['user_id'],
            trim($_POST['review_note'] ?? '') ?: null
        );
        $this->redirect('/superadmin/product-moderation');
    }
}
