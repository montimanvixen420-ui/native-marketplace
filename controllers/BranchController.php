<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Branch.php';

class BranchController extends Controller
{
    private Branch $branches;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->branches = new Branch();
    }

    public function locator(): void
    {
        $this->view('branches/locator', ['branches' => $this->branches->activeForLocator()]);
    }

    /** Customer map for the branches belonging to a single seller. */
    public function sellerBranches(int $sellerId): void
    {
        $this->requireRole('customer');
        $seller = $this->branches->approvedSeller($sellerId);
        if (!$seller) { http_response_code(404); echo 'Seller not found.'; return; }
        $this->view('customer/seller-branches', [
            'name' => $_SESSION['user_name'], 'seller' => $seller,
            'branches' => $this->branches->activeForSeller($sellerId), 'active' => 'browse',
        ]);
    }

    public function index(): void
    {
        $this->requireApprovedSeller();
        $sellerId = (int) $_SESSION['user_id'];
        $branches = $this->branches->allBySeller($sellerId);
        $selectedId = (int) ($_GET['branch'] ?? ($branches[0]['id'] ?? 0));
        $selected = $selectedId ? $this->branches->findForSeller($selectedId, $sellerId) : null;
        $this->view('admin/branches/index', [
            'name' => $_SESSION['user_name'], 'branches' => $branches, 'selectedBranch' => $selected, 'active' => 'branches',
        ]);
    }

    public function store(): void
    {
        $this->requireApprovedSeller();
        $data = $this->branchData();
        if ($data === null) { $_SESSION['branch_error'] = 'Provide a branch name, address, and a valid map pin.'; $this->redirect('/admin/branches'); }
        $id = $this->branches->create((int) $_SESSION['user_id'], $data);
        $this->redirect('/admin/branches?branch=' . $id);
    }

    public function update(int $id): void
    {
        $this->requireApprovedSeller();
        $data = $this->branchData();
        if ($data === null) { $_SESSION['branch_error'] = 'Provide a branch name, address, and a valid map pin.'; $this->redirect('/admin/branches?branch=' . $id); }
        $this->branches->update($id, (int) $_SESSION['user_id'], $data);
        $this->redirect('/admin/branches?branch=' . $id);
    }

    public function toggle(int $id): void { $this->requireApprovedSeller(); $this->branches->toggle($id, (int) $_SESSION['user_id']); $this->redirect('/admin/branches?branch=' . $id); }

    public function archive(int $id): void
    {
        $this->requireApprovedSeller();
        if (!$this->branches->archive($id, (int) $_SESSION['user_id'])) {
            $_SESSION['branch_error'] = 'Unable to archive this branch.';
        }
        $this->redirect('/admin/branches?branch=' . $id);
    }

    public function restore(int $id): void
    {
        $this->requireApprovedSeller();
        if (!$this->branches->restore($id, (int) $_SESSION['user_id'])) {
            $_SESSION['branch_error'] = 'Unable to restore this branch.';
        }
        $this->redirect('/admin/branches?branch=' . $id);
    }

    private function branchData(): ?array
    {
        $name = trim($_POST['name'] ?? ''); $address = trim($_POST['address'] ?? '');
        $lat = filter_var($_POST['latitude'] ?? null, FILTER_VALIDATE_FLOAT); $lng = filter_var($_POST['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($name === '' || $address === '' || $lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) return null;
        return ['name' => $name, 'address' => $address, 'city' => trim($_POST['city'] ?? ''), 'phone' => trim($_POST['phone'] ?? ''),
            'hours' => trim($_POST['hours'] ?? ''), 'latitude' => $lat, 'longitude' => $lng];
    }
}