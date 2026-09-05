<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ProhibitedItem.php';
require_once __DIR__ . '/../models/SellerApplication.php';
require_once __DIR__ . '/../models/BranchManager.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../core/Mailer.php';

class AuthController extends Controller
{
    private User $userModel;
    private ProhibitedItem $prohibitedItemModel;
    private SellerApplication $sellerApplicationModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
        $this->prohibitedItemModel = new ProhibitedItem();
        $this->sellerApplicationModel = new SellerApplication();
    }

    // GET /register
    public function showRegister(): void
    {
        $this->view('auth/register', [
            'error' => null,
            'prohibitedItems' => $this->prohibitedItemModel->getAll(),
        ]);
    }

    // POST /register
    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        // Public registration is customer-only. Seller/supplier access is requested
        // later from the customer's dashboard and reviewed by Superadmin.
        $role = User::ROLE_CUSTOMER;
        $agreeProhibited = isset($_POST['agree_prohibited']);
        $agreeLegal = isset($_POST['agree_legal']);
        $businessName = trim($_POST['business_name'] ?? '');
        $businessDescription = trim($_POST['business_description'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $businessAddress = trim($_POST['business_address'] ?? '');

        // Superadmin cannot be created through the registration form
        // (must be added manually to the database by the developer/team)
        $allowedRoles = [User::ROLE_ADMIN, User::ROLE_SUPPLIER, User::ROLE_CUSTOMER];

        $rerender = function (string $message) {
            $this->view('auth/register', [
                'error' => $message,
                'prohibitedItems' => $this->prohibitedItemModel->getAll(),
            ]);
        };

        if ($name === '' || $email === '' || $password === '') {
            $rerender('Please fill in all fields.');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $rerender('Please enter a valid email address.');
            return;
        }

        if (strlen($password) < 8) {
            $rerender('Password must be at least 8 characters.');
            return;
        }

        if ($password !== $confirmPassword) {
            $rerender('Passwords do not match.');
            return;
        }

        if (!$agreeLegal) {
            $rerender('Please accept the Terms & Conditions and Privacy Policy.');
            return;
        }

        if (!in_array($role, $allowedRoles, true)) {
            $rerender('Please select a valid role.');
            return;
        }

        // Sellers and suppliers must agree to the prohibited items policy
        if (in_array($role, [User::ROLE_ADMIN, User::ROLE_SUPPLIER], true) && !$agreeProhibited) {
            $rerender('Please confirm that you agree not to sell any of the prohibited items listed.');
            return;
        }

        if ($this->userModel->emailExists($email)) {
            $rerender('An account with this email already exists.');
            return;
        }

        $sellerApplication = null;
        if ($role === User::ROLE_ADMIN) {
            if ($businessName === '' || $businessDescription === '' || $phone === '' || $businessAddress === '') {
                $rerender('Please complete all business details for your seller application.');
                return;
            }

            $logo = $this->uploadFile('business_logo', ['image/png', 'image/jpeg'], ['png', 'jpg', 'jpeg'], 'logos');
            if (isset($logo['error'])) {
                $rerender($logo['error']);
                return;
            }
            $document = $this->uploadFile('business_document', ['application/pdf', 'image/png', 'image/jpeg'], ['pdf', 'png', 'jpg', 'jpeg'], 'documents');
            if (isset($document['error'])) {
                $rerender($document['error']);
                return;
            }
            $sellerApplication = [
                'business_name' => $businessName,
                'business_description' => $businessDescription,
                'phone' => $phone,
                'business_address' => $businessAddress,
                'logo_path' => $logo['path'],
                'document_path' => $document['path'],
            ];
        }

        $userId = $this->userModel->create($name, $email, $password, $role);
        if ($sellerApplication !== null) {
            $this->sellerApplicationModel->create($userId, $sellerApplication);
        }
        $user = $this->userModel->findById($userId);

        // Pending accounts (admin/supplier awaiting approval) are NOT logged in
        // automatically — they need to see the pending-approval message.
        if ($user['status'] === User::STATUS_PENDING) {
            $this->view('auth/login', [
                'error' => 'Your account has been created and is pending approval. We will notify you once approved.',
            ]);
            return;
        }

        // Customers are approved immediately, so log them in right away
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_name'] = $name;

        $this->redirectByRole($role);
    }

    // GET /login
    public function showLogin(): void
    {
        $this->view('auth/login', ['error' => null]);
    }

    public function showForgotPassword(): void
    {
        $this->view('auth/forgot-password', ['error' => null, 'message' => null, 'email' => '']);
    }

    public function sendPasswordReset(): void
    {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth/forgot-password', ['error' => 'Enter a valid email address.', 'message' => null, 'email' => $email]);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->createPasswordReset((int) $user['id'], $token);
            $link = $this->resetLink($token);
            $mail = new Mailer();
            $html = $this->passwordResetEmail($user['name'], $link);
            if (!$mail->send($email, 'Reset your TINDA password', $html)) {
                $this->view('auth/forgot-password', ['error' => 'Email service is not configured yet. Add your SMTP details in config/mail.php.', 'message' => null, 'email' => $email]);
                return;
            }
        }

        $this->view('auth/forgot-password', [
            'error' => null,
            'message' => 'If an account uses that email, a password-reset link has been sent.',
            'email' => '',
        ]);
    }

    public function showResetPassword(): void
    {
        $this->view('auth/reset-password', ['error' => null, 'token' => trim($_GET['token'] ?? '')]);
    }

    public function resetPassword(): void
    {
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if ($token === '' || strlen($password) < 8) {
            $this->view('auth/reset-password', ['error' => 'Use a password with at least 8 characters.', 'token' => $token]);
            return;
        }
        if ($password !== $confirmPassword) {
            $this->view('auth/reset-password', ['error' => 'Passwords do not match.', 'token' => $token]);
            return;
        }
        if (!$this->userModel->resetPassword($token, $password)) {
            $this->view('auth/reset-password', ['error' => 'This reset link is invalid or has expired. Request a new one.', 'token' => '']);
            return;
        }
        $this->view('auth/login', ['error' => 'Password updated. You can now sign in with your new password.']);
    }

    // POST /login
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->view('auth/login', ['error' => 'Please enter your email and password.']);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            $this->view('auth/login', ['error' => 'Incorrect email or password.']);
            return;
        }

        // ── Status check — block banned/suspended/pending accounts ──
        $statusError = match ($user['status']) {
            User::STATUS_BANNED => 'Your account has been banned. Please contact support for more information.',
            User::STATUS_SUSPENDED => 'Your account has been suspended. Please contact support for more information.',
            User::STATUS_PENDING => 'Your account is still pending approval. Please wait for an administrator to review it.',
            default => null,
        };

               if ($statusError !== null) {
            $this->view('auth/login', ['error' => $statusError]);
            return;
        }

              // Branch Managers have a second, role-specific status (active/inactive/archived) on
        // top of their main account status — check it here too, so a deactivated manager
        // sees a clear message on the login page instead of reaching their dashboard first.
        if ($user['role'] === User::ROLE_MANAGER && !(new BranchManager())->forUser((int) $user['id'])) {
            $this->view('auth/login', ['error' => 'Your Branch Manager access has been deactivated. Please contact your seller/admin.']);
            return;
        }

        // Same idea for regular Staff (Cashier, Order Staff, Inventory Staff, Customer
        // Service Staff): their staff_profiles row has its own active/inactive/archived
        // status separate from the main account. Without this check, a deactivated staff
        // member's credentials still "work" here, only to get silently bounced back to
        // /login by requireActiveStaff() once they reach their dashboard — with no
        // message at all, which just looks like the page refreshed for no reason.
        if ($user['role'] === User::ROLE_STAFF) {
            $staffProfile = (new Staff())->profileForUser((int) $user['id']);
            if (!$staffProfile) {
                $this->view('auth/login', ['error' => 'Your staff account could not be found. Please contact your Branch Manager.']);
                return;
            }
            if ($staffProfile['status'] !== 'active') {
                $this->view('auth/login', ['error' => 'Your staff account has been deactivated. Please contact your Branch Manager.']);
                return;
            }
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        $this->redirectByRole($user['role']);
    }

    // GET /logout
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }

private function redirectByRole(string $role): void
{
    match ($role) {
        User::ROLE_SUPERADMIN => $this->redirect('/superadmin/dashboard'),
        User::ROLE_ADMIN => $this->redirect('/admin/dashboard'),
        User::ROLE_SUPPLIER => $this->redirect('/supplier/dashboard'),
        User::ROLE_STAFF => $this->redirect('/staff/dashboard'),
        User::ROLE_MANAGER => $this->redirect('/manager/dashboard'),
        User::ROLE_CUSTOMER => $this->redirect('/customer/dashboard'),
        default => $this->redirect('/'),
    };
}

    private function resetLink(string $token): string
    {
        $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');
        $scheme = $scheme === 'https' ? 'https' : 'http';
        $host = preg_replace('/[^a-zA-Z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost:8000');
        return $scheme . '://' . $host . '/reset-password?token=' . rawurlencode($token);
    }

    private function passwordResetEmail(string $name, string $link): string
    {
        $timezone = new DateTimeZone('Asia/Manila');
        $sentAt = new DateTimeImmutable('now', $timezone);
        $expiresAt = $sentAt->modify('+15 minutes');
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $sent = $sentAt->format('F j, Y, g:i A') . ' PHT';
        $expires = $expiresAt->format('F j, Y, g:i A') . ' PHT';

        return <<<HTML
<!doctype html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#111827;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr><td align="center">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;background:#ffffff;border-radius:18px;overflow:hidden;">
        <tr><td style="padding:28px 36px;background:#2563eb;color:#ffffff;">
          <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr>
            <td style="width:30px;height:30px;border-radius:15px;background:#bfdbfe;color:#1d4ed8;text-align:center;font-size:18px;font-weight:bold;line-height:30px;">T</td>
            <td style="padding-left:10px;font-size:20px;font-weight:800;letter-spacing:3px;">TINDA</td>
          </tr></table>
        </td></tr>
        <tr><td style="padding:36px;">
          <p style="margin:0 0 10px;font-size:12px;font-weight:bold;letter-spacing:1.8px;color:#2563eb;text-transform:uppercase;">Account security</p>
          <h1 style="margin:0 0 18px;font-size:28px;line-height:1.25;color:#171717;">Reset your password</h1>
          <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#4b5563;">Hello {$safeName}, we received a request to reset your TINDA Marketplace password.</p>
          <p style="margin:0 0 26px;"><a href="{$safeLink}" style="display:inline-block;padding:14px 22px;border-radius:8px;background:#2563eb;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">Reset password</a></p>
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e5e7eb;border-radius:10px;background:#fafafa;"><tr><td style="padding:16px 18px;font-size:13px;line-height:1.65;color:#4b5563;">
            <strong style="color:#171717;">Request details</strong><br>
            Requested: {$sent}<br>
            Expires: {$expires}
          </td></tr></table>
          <p style="margin:24px 0 0;font-size:13px;line-height:1.6;color:#6b7280;">If you did not request a password reset, you can safely ignore this email. Your password will not change.</p>
          <p style="margin:20px 0 0;font-size:12px;line-height:1.6;color:#9ca3af;">Button not working? Copy this link into your browser:<br><a href="{$safeLink}" style="color:#2563eb;word-break:break-all;">{$safeLink}</a></p>
        </td></tr>
        <tr><td style="padding:18px 36px;background:#fafafa;border-top:1px solid #e5e7eb;font-size:12px;color:#9ca3af;">TINDA Marketplace &middot; Secure account recovery</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

    private function uploadFile(string $field, array $allowedMimeTypes, array $allowedExtensions, string $directory): array
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return ['error' => $field === 'business_logo' ? 'Please upload your business logo.' : 'Please upload a business registration document.'];
        }
        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
            return ['error' => 'Each uploaded file must be valid and 5MB or smaller.'];
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
            return ['error' => $field === 'business_logo' ? 'Business logo must be a PNG or JPEG image.' : 'Business document must be a PDF, PNG, or JPEG file.'];
        }
        $uploadDir = __DIR__ . '/../public/uploads/seller-applications/' . $directory . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = uniqid('application_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return ['error' => 'Unable to save the uploaded file. Please try again.'];
        }
        return ['path' => 'uploads/seller-applications/' . $directory . '/' . $filename];
    }
}
