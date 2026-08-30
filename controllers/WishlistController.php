<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Wishlist.php';

class WishlistController extends Controller
{
    private Wishlist $wishlistModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireRole('customer');
        $this->wishlistModel = new Wishlist();
    }

    private function customerId(): int
    {
        return (int) $_SESSION['user_id'];
    }

    private function isAjax(): bool
    {
        return !empty($_POST['ajax']) || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // GET /wishlist
    public function index(): void
    {
        $items = $this->wishlistModel->allByCustomer($this->customerId());

        $this->view('customer/wishlist', [
            'name' => $_SESSION['user_name'],
            'items' => $items,
            'active' => 'wishlist',
        ]);
    }

    // POST /wishlist/add — quick-add from the storefront (default priority, no notes)
    public function add(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId > 0) {
            $this->wishlistModel->add($this->customerId(), $productId);
        }

        if ($this->isAjax()) {
            $this->jsonResponse(['success' => true, 'in_wishlist' => true]);
            return;
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/shop');
    }

    // POST /wishlist/update — edit notes/priority from the wishlist page
    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';

        $this->wishlistModel->update($id, $this->customerId(), $notes, $priority);
        $this->redirect('/wishlist');
    }

    // POST /wishlist/remove — accepts either a wishlist row `id` (used by the
    // wishlist page itself) or a `product_id` (used by the storefront heart toggle)
    public function remove(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId > 0) {
            $this->wishlistModel->removeByProduct($this->customerId(), $productId);
        } else {
            $id = (int) ($_POST['id'] ?? 0);
            $this->wishlistModel->remove($id, $this->customerId());
        }

        if ($this->isAjax()) {
            $this->jsonResponse(['success' => true, 'in_wishlist' => false]);
            return;
        }

        $this->redirect('/wishlist');
    }
}