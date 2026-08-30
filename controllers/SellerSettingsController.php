<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SellerApplication.php';

class SellerSettingsController extends Controller
{
    private User $userModel;
    private SellerApplication $sellerApplicationModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireApprovedSeller();
        $this->userModel = new User();
        $this->sellerApplicationModel = new SellerApplication();
    }

    public function index(): void
    {
        $this->render(null, false);
    }

    public function update(): void
    {
        $userId = (int) $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        $application = $this->sellerApplicationModel->findByUserId($userId);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $businessName = trim($_POST['business_name'] ?? '');
        $businessDescription = trim($_POST['business_description'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $businessAddress = trim($_POST['business_address'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $businessName === '' || $businessDescription === '' || $phone === '' || $businessAddress === '') {
            $this->render('Please complete all required fields.', false, array_merge($_POST, ['id' => $userId]));
            return;
        }
        $existingByEmail = $this->userModel->findByEmail($email);
        if ($existingByEmail && (int) $existingByEmail['id'] !== $userId) {
            $this->render('That email address is already in use.', false, array_merge($_POST, ['id' => $userId]));
            return;
        }
        $logoPath = null;
        if (isset($_FILES['business_logo']) && $_FILES['business_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = $this->uploadLogo();
            if (isset($upload['error'])) {
                $this->render($upload['error'], false, array_merge($_POST, ['id' => $userId]));
                return;
            }
            $logoPath = $upload['path'];
        }

        if (!$application) {
            if ($logoPath === null) {
                $this->render('Please upload a business logo to complete your shop profile.', false, array_merge($_POST, ['id' => $userId]));
                return;
            }
            $document = $this->uploadDocument();
            if (isset($document['error'])) {
                $this->render($document['error'], false, array_merge($_POST, ['id' => $userId]));
                return;
            }
            $this->userModel->updateProfile($userId, $name, $email);
            $this->sellerApplicationModel->create($userId, [
                'business_name' => $businessName,
                'business_description' => $businessDescription,
                'phone' => $phone,
                'business_address' => $businessAddress,
                'logo_path' => $logoPath,
                'document_path' => $document['path'],
            ]);
            $_SESSION['user_name'] = $name;
            $this->render(null, true);
            return;
        }

        $this->userModel->updateProfile($userId, $name, $email);
        $this->sellerApplicationModel->updateProfile($userId, [
            'business_name' => $businessName,
            'business_description' => $businessDescription,
            'phone' => $phone,
            'business_address' => $businessAddress,
            'logo_path' => $logoPath,
        ]);
        $_SESSION['user_name'] = $name;
        $this->render(null, true);
    }

    private function render(?string $error, bool $success, ?array $old = null): void
    {
        $userId = (int) $_SESSION['user_id'];
        $this->view('admin/settings', [
            'name' => $_SESSION['user_name'],
            'user' => $old ?? $this->userModel->findById($userId),
            'application' => $this->sellerApplicationModel->findByUserId($userId),
            'isInitialSetup' => $this->sellerApplicationModel->findByUserId($userId) === null,
            'error' => $error,
            'success' => $success,
            'active' => 'settings',
        ]);
    }

    private function uploadLogo(): array
    {
        $file = $_FILES['business_logo'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
            return ['error' => 'Logo must be valid and 5MB or smaller.'];
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($extension, ['png', 'jpg', 'jpeg'], true) || !in_array($mimeType, ['image/png', 'image/jpeg'], true)) {
            return ['error' => 'Business logo must be a PNG or JPEG image.'];
        }
        $directory = __DIR__ . '/../public/uploads/seller-applications/logos/';
        if (!is_dir($directory)) mkdir($directory, 0755, true);
        $filename = uniqid('logo_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) return ['error' => 'Unable to save the new logo.'];
        return ['path' => 'uploads/seller-applications/logos/' . $filename];
    }

    private function uploadDocument(): array
    {
        if (!isset($_FILES['business_document']) || $_FILES['business_document']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['error' => 'Please upload your business registration or valid proof.'];
        }
        $file = $_FILES['business_document'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
            return ['error' => 'Business proof must be valid and 5MB or smaller.'];
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($extension, ['pdf', 'png', 'jpg', 'jpeg'], true) || !in_array($mimeType, ['application/pdf', 'image/png', 'image/jpeg'], true)) {
            return ['error' => 'Business proof must be a PDF, PNG, or JPEG file.'];
        }
        $directory = __DIR__ . '/../public/uploads/seller-applications/documents/';
        if (!is_dir($directory)) mkdir($directory, 0755, true);
        $filename = uniqid('document_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) return ['error' => 'Unable to save the business proof.'];
        return ['path' => 'uploads/seller-applications/documents/' . $filename];
    }
}
