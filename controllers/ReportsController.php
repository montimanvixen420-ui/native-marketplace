<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Report.php';

class ReportsController extends Controller
{
    private User $userModel;
    private Report $reportModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
        $this->reportModel = new Report();
    }

    // GET /superadmin/reports — Customer Safety Reports (moderation queue)
    public function index(): void
    {
        $this->requireRole('superadmin');

        $this->view('superadmin/reports', [
            'name' => $_SESSION['user_name'],
            'customerReports' => $this->reportModel->all(),
            'reportSummary' => $this->reportModel->summary(),
            'active' => 'reports',
        ]);
    }

    // GET /superadmin/signups — Platform sign-up analytics
    public function signups(): void
    {
        $this->requireRole('superadmin');

        // Only allow 6 or 12 months, default to 6
        $range = (int) ($_GET['range'] ?? 6);
        $range = in_array($range, [6, 12], true) ? $range : 6;

        $this->view('superadmin/signups', [
            'name' => $_SESSION['user_name'],
            'signups' => $this->userModel->getSignupsByMonth($range),
            'range' => $range,
            'active' => 'signups',
        ]);
    }

    public function updateReport(int $id): void
    {
        $this->requireRole('superadmin');
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['reviewing', 'resolved', 'dismissed'], true)) $this->reportModel->updateStatus($id, $status, (int) $_SESSION['user_id'], trim($_POST['review_note'] ?? ''));
        $this->redirect('/superadmin/reports');
    }
}