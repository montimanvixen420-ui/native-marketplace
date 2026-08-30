<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Report.php';

/**
 * Seller-side reports (view only).
 *
 * Reports filed against a seller — either their seller account or one of
 * their products — are trust & safety / policy matters, not order-level
 * customer service. Only superadmin (a neutral platform authority) may
 * resolve them (see ReportsController). Sellers may only VIEW what was
 * reported and how superadmin resolved it, so they're aware of standing
 * flags without being able to dismiss reports against themselves — the
 * same view-vs-act separation used for branch staff and returns.
 */
class SellerReportsController extends Controller
{
    private Report $reportModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->requireApprovedSeller();
        $this->reportModel = new Report();
    }

    // GET /admin/reports
    public function index(): void
    {
        $this->view('admin/reports/index', [
            'name' => $_SESSION['user_name'],
            'reports' => $this->reportModel->allBySeller((int) $_SESSION['user_id']),
            'active' => 'reports',
        ]);
    }
}