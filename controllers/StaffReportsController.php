<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Report.php';

/**
 * Branch-scoped product reports (view only, awareness for Branch Manager
 * and staff). Seller-level moderation still stays with superadmin — see
 * SellerReportsController and ReportsController. This exists so a branch
 * knows a product it carries has an open safety report, without giving
 * it any power to resolve that report itself.
 */
class StaffReportsController extends Controller
{
    private Report $reportModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->reportModel = new Report();
    }

    // GET /staff/reports
    public function index(): void
    {
        $profile = $this->requireActiveStaff();
        $branchId = (int) $profile['branch_id'];

        $this->view('staff/reports/index', [
            'name' => $_SESSION['user_name'],
            'profile' => $profile,
            'reports' => $this->reportModel->productReportsByBranch($branchId),
            'active' => 'reports',
        ]);
    }
}