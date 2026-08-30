<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PostPurchase.php';

class PostPurchaseController extends Controller
{
    private PostPurchase $postPurchase;
    public function __construct() { if (session_status() === PHP_SESSION_NONE) session_start(); $this->requireRole('customer'); $this->postPurchase = new PostPurchase(); }
    private function item(int $id): ?array { return $this->postPurchase->eligibleItem($id, (int) $_SESSION['user_id']); }
    private function form(string $view, int $id, ?string $error = null): void { $item = $this->item($id); if (!$item) { $this->redirect('/orders?error=' . urlencode('That delivered item was not found.')); return; } $active = $view === 'customer/review' ? 'reviews' : 'returns'; $this->view($view, ['name' => $_SESSION['user_name'], 'item' => $item, 'error' => $error, 'active' => $active]); }
    public function reviewsIndex(): void { $this->view('customer/reviews', ['name' => $_SESSION['user_name'], 'items' => $this->postPurchase->completedItemsByCustomer((int) $_SESSION['user_id']), 'success' => $_GET['success'] ?? null, 'active' => 'reviews']); }
    public function returnsIndex(): void { $this->view('customer/returns', ['name' => $_SESSION['user_name'], 'items' => $this->postPurchase->completedItemsByCustomer((int) $_SESSION['user_id']), 'success' => $_GET['success'] ?? null, 'active' => 'returns']); }
    public function returnForm(int $id): void { $this->form('customer/return-request', $id); }
    public function reviewForm(int $id): void { $this->form('customer/review', $id); }
    public function submitReturn(int $id): void { $result = $this->postPurchase->createReturn($id, (int) $_SESSION['user_id'], $_POST['reason'] ?? '', trim($_POST['details'] ?? '')); if (!$result['success']) { $this->form('customer/return-request', $id, $result['error']); return; } $this->redirect('/orders/returns?success=' . urlencode('Your return/refund request has been submitted.')); }
    public function submitReview(int $id): void { $upload = $this->uploadPhoto(); if (isset($upload['error'])) { $this->form('customer/review', $id, $upload['error']); return; } $result = $this->postPurchase->createReview($id, (int) $_SESSION['user_id'], (int) ($_POST['rating'] ?? 0), $_POST['fit_feedback'] ?? '', trim($_POST['comment'] ?? ''), $upload['path']); if (!$result['success']) { $this->form('customer/review', $id, $result['error']); return; } $this->redirect('/orders/reviews?success=' . urlencode('Thanks! Your verified review has been posted.')); }
    private function uploadPhoto(): array { if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) return ['path' => null]; $file = $_FILES['photo']; if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) return ['error' => 'Upload a photo smaller than 5MB.']; $mime = mime_content_type($file['tmp_name']); if (!in_array($mime, ['image/jpeg', 'image/png'], true)) return ['error' => 'Only JPEG or PNG photos are allowed.']; $extension = $mime === 'image/png' ? 'png' : 'jpg'; $dir = __DIR__ . '/../public/uploads/reviews/'; if (!is_dir($dir)) mkdir($dir, 0755, true); $filename = uniqid('review_', true) . '.' . $extension; if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) return ['error' => 'Unable to save the review photo.']; return ['path' => 'uploads/reviews/' . $filename]; }
}
