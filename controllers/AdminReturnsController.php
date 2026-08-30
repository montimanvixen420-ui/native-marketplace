<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PostPurchase.php';
class AdminReturnsController extends Controller
{
    private PostPurchase $postPurchase;
    public function __construct() { if (session_status() === PHP_SESSION_NONE) session_start(); $this->requireApprovedSeller(); $this->postPurchase = new PostPurchase(); }
    public function index(): void { $this->view('admin/returns/index', ['name' => $_SESSION['user_name'], 'requests' => $this->postPurchase->returnsBySeller((int) $_SESSION['user_id']), 'success' => isset($_GET['updated']), 'active' => 'returns']); }
    public function update(int $id): void { $this->postPurchase->updateReturnStatus($id, (int) $_SESSION['user_id'], $_POST['status'] ?? ''); $this->redirect('/admin/returns?updated=1'); }
}
