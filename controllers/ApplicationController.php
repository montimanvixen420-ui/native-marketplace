<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SellerApplication.php';

class ApplicationController extends Controller
{
    private User $userModel;
    private SellerApplication $applicationModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->userModel = new User();
        $this->applicationModel = new SellerApplication();
    }

    public function create(): void
    {
        $this->requireRole('customer');
        $this->view('customer/apply', ['name' => $_SESSION['user_name'], 'error' => null, 'active' => 'overview']);
    }

    public function store(): void
    {
        $this->requireRole('customer');
        $role = $_POST['application_role'] ?? '';
        $data = [
            'application_role' => $role,
            'business_name' => trim($_POST['business_name'] ?? ''),
            'business_description' => trim($_POST['business_description'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'business_address' => trim($_POST['business_address'] ?? ''),
        ];
        if (!in_array($role, [User::ROLE_ADMIN, User::ROLE_SUPPLIER], true) || in_array('', [$data['business_name'], $data['business_description'], $data['phone'], $data['business_address']], true) || !isset($_POST['consent'])) {
            $this->renderError('Complete all fields, choose a role, and confirm your consent.');
            return;
        }
        $id = $this->upload('government_id', ['application/pdf', 'image/png', 'image/jpeg'], ['pdf', 'png', 'jpg', 'jpeg']);
        $selfie = $this->upload('selfie', ['image/png', 'image/jpeg'], ['png', 'jpg', 'jpeg']);
        if (isset($id['error']) || isset($selfie['error'])) { $this->renderError($id['error'] ?? $selfie['error']); return; }
        $data['document_path'] = $id['path'];
        $data['selfie_path'] = $selfie['path'];
        $userId = (int) $_SESSION['user_id'];
        $this->applicationModel->createVerificationApplication($userId, $data);
        $this->userModel->updateRoleAndStatus($userId, $role, User::STATUS_PENDING);
        $_SESSION = [];
        session_destroy();
        $this->view('auth/login', ['error' => 'Application submitted. Your ID and selfie will be reviewed before your account is approved.']);
    }

    public function downloadVerification(int $userId, string $type): void
    {
        $this->requireRole('superadmin');
        $application = $this->applicationModel->findByUserId($userId);
        $field = $type === 'id' ? 'document_path' : ($type === 'selfie' ? 'selfie_path' : '');
        if ($field === '' || !$application || empty($application[$field])) { http_response_code(404); exit('Verification file not found.'); }
        $storedPath = (string) $application[$field];
        $path = str_starts_with($storedPath, 'uploads/seller-applications/')
            ? __DIR__ . '/../public/' . $storedPath
            : __DIR__ . '/../storage/verification/' . basename($storedPath);
        if (!is_file($path)) { http_response_code(404); exit('Verification file not found.'); }
        $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = finfo_file($finfo, $path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="verification-' . $type . '.' . $extension . '"');
        readfile($path);
        exit;
    }

    private function renderError(string $error): void { $this->view('customer/apply', ['name' => $_SESSION['user_name'], 'error' => $error, 'active' => 'overview']); }

    private function upload(string $field, array $mimes, array $extensions): array
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return ['error' => 'Please upload both a valid government ID and a clear selfie.'];
        $file = $_FILES[$field];
        if ($file['size'] > 5 * 1024 * 1024) return ['error' => 'Each verification file must be 5MB or smaller.'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = finfo_file($finfo, $file['tmp_name']);
        if (!in_array($extension, $extensions, true) || !in_array($mime, $mimes, true)) return ['error' => 'Use a PDF, PNG, or JPEG for ID; selfie must be a PNG or JPEG.'];
        $directory = __DIR__ . '/../storage/verification/';
        if (!is_dir($directory)) mkdir($directory, 0755, true);
        $filename = uniqid('verify_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) return ['error' => 'Could not save your verification file. Please try again.'];
        return ['path' => $filename];
    }
}
