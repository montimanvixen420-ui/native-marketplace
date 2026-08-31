<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/Staff.php';

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

        // View/Edit are two states of the SAME selected branch (not two different pages).
        $mode = (($_GET['mode'] ?? 'view') === 'edit') ? 'edit' : 'view';

        // Staff roster is only needed for the read-only view panel, so skip the query in edit mode.
        $branchStaff = ($selected && $mode === 'view')
            ? (new Staff())->allForBranchOverview((int) $selected['id'], $sellerId)
            : [];

        $this->view('admin/branches/index', [
            'name' => $_SESSION['user_name'],
            'branches' => $branches,
            'selectedBranch' => $selected,
            'mode' => $mode,
            'branchStaff' => $branchStaff,
            'active' => 'branches',
        ]);
    }

    public function store(): void
    {
        $this->requireApprovedSeller();
        $data = $this->branchData();
        if ($data === null) {
            $this->redirect('/admin/branches?error=' . urlencode('Provide a branch name, address, and a valid map pin.'));
            return;
        }
        $id = $this->branches->create((int) $_SESSION['user_id'], $data);
        $this->redirect('/admin/branches?branch=' . $id . '&success=' . urlencode('Branch added.'));
    }

    public function update(int $id): void
    {
        $this->requireApprovedSeller();
        $data = $this->branchData();
        if ($data === null) {
            $this->redirect('/admin/branches?branch=' . $id . '&mode=edit&error=' . urlencode('Provide a branch name, address, and a valid map pin.'));
            return;
        }
        $this->branches->update($id, (int) $_SESSION['user_id'], $data);
        $this->redirect('/admin/branches?branch=' . $id . '&success=' . urlencode('Branch updated.'));
    }

    public function toggle(int $id): void { $this->requireApprovedSeller(); $this->branches->toggle($id, (int) $_SESSION['user_id']); $this->redirect('/admin/branches?branch=' . $id); }

    public function archive(int $id): void
    {
        $this->requireApprovedSeller();
        if (!$this->branches->archive($id, (int) $_SESSION['user_id'])) {
            $this->redirect('/admin/branches?branch=' . $id . '&error=' . urlencode('Unable to archive this branch.'));
            return;
        }
        $this->redirect('/admin/branches?branch=' . $id . '&success=' . urlencode('Branch archived.'));
    }

    public function restore(int $id): void
    {
        $this->requireApprovedSeller();
        if (!$this->branches->restore($id, (int) $_SESSION['user_id'])) {
            $this->redirect('/admin/branches?branch=' . $id . '&error=' . urlencode('Unable to restore this branch.'));
            return;
        }
        $this->redirect('/admin/branches?branch=' . $id . '&success=' . urlencode('Branch restored.'));
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