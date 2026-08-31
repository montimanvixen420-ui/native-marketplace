<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/BranchManager.php';
require_once __DIR__ . '/../models/Branch.php';

class BranchManagerController extends Controller
{
    private BranchManager $managers;
    private Branch $branches;
    public function __construct() { if (session_status() === PHP_SESSION_NONE) session_start(); $this->managers = new BranchManager(); $this->branches = new Branch(); }
       
    public function index(): void
    {
        $this->requireApprovedSeller(); $sellerId=(int)$_SESSION['user_id'];
        $branches = $this->branches->allBySeller($sellerId);
        $managers = $this->managers->allBySeller($sellerId);

        // Fix #4: how many of this seller's ACTIVE branches have no manager (active or inactive) assigned?
        $managedBranchIds = array_column(array_filter($managers, fn($m) => $m['status'] !== 'archived'), 'branch_id');
        $activeBranches = array_filter($branches, fn($b) => $b['is_active']);
        $unmanagedCount = count(array_diff(array_column($activeBranches, 'id'), $managedBranchIds));

        $this->view('admin/branch-managers/index',['name'=>$_SESSION['user_name'],'managers'=>$managers,'branches'=>$branches,'unmanagedCount'=>$unmanagedCount,'totalActiveBranches'=>count($activeBranches),'active'=>'branch-managers']);
    }
    public function store(): void
    {
        $this->requireApprovedSeller(); $sellerId=(int)$_SESSION['user_id']; $data=$this->data(); $password=(string)($_POST['password']??''); $branchId=(int)($_POST['branch_id']??0);
        if(!$data || strlen($password)<8 || !$this->managers->branchBelongsToSeller($branchId,$sellerId)) { $this->error('Choose one of your active branches and provide valid manager details.'); return; }
        if($this->managers->emailExists($data['email'])) { $this->error('That email address is already registered. Use a different email address.'); return; }
        if($this->managers->branchHasManager($branchId)) { $this->error('This branch already has an active Branch Manager. Choose another branch or change its existing manager.'); return; }
        try { $id=$this->managers->create($sellerId,$branchId,$data,$password); $this->success('Branch Manager created and assigned successfully.',['id'=>$id]); }
        catch(Throwable $e) { error_log('Branch Manager creation failed: '.$e->getMessage()); $this->error('Unable to create the Branch Manager. Please contact the system administrator.'); }
    }
    public function changeBranch(int $id): void
    {
        $this->requireApprovedSeller(); $ok=$this->managers->changeBranch($id,(int)$_SESSION['user_id'],(int)($_POST['branch_id']??0));
        $ok ? $this->success('Branch Manager assignment updated.') : $this->error('Unable to change this assignment.');
    }
    public function update(int $id): void
    {
        $this->requireApprovedSeller(); $sellerId=(int)$_SESSION['user_id']; $data=$this->data();
        if(!$data) { $this->error('Provide valid manager details.'); return; }
        if($this->managers->emailExists($data['email'],$id)) { $this->error('That email address is already registered. Use a different email address.'); return; }
        try { $ok=$this->managers->updateProfile($id,$sellerId,$data); $ok ? $this->success('Branch Manager updated.') : $this->error('Unable to update this manager.'); }
        catch(Throwable $e) { error_log('Branch Manager update failed: '.$e->getMessage()); $this->error('Unable to update this manager. Please contact the system administrator.'); }
    }
    public function resetPassword(int $id): void
    {
        $this->requireApprovedSeller(); $password=(string)($_POST['password']??'');
        if(strlen($password)<8) { $this->error('Password must be at least 8 characters.'); return; }
        $ok=$this->managers->resetPassword($id,(int)$_SESSION['user_id'],$password);
        $ok ? $this->success('Password reset successfully.') : $this->error('Unable to reset this manager\'s password.');
    }
    public function setStatus(int $id): void
    {
        $this->requireApprovedSeller(); $ok=$this->managers->setStatus($id,(int)$_SESSION['user_id'],(string)($_POST['status']??''));
        $ok ? $this->success('Branch Manager status updated.') : $this->error('Unable to update this manager.');
    }
    private function data(): ?array
    {
        $d=['first_name'=>trim($_POST['first_name']??''),'last_name'=>trim($_POST['last_name']??''),'email'=>trim($_POST['email']??''),'phone'=>trim($_POST['phone']??'')];
        if($d['phone']!=='' && !preg_match('/^\+639\d{9}$/',$d['phone'])) return null;
        return $d['first_name']!=='' && $d['last_name']!=='' && filter_var($d['email'],FILTER_VALIDATE_EMAIL) ? $d : null;
    }
    private function success(string $m,array $x=[]):void { header('Content-Type: application/json'); echo json_encode(['success'=>true,'message'=>$m]+$x); }
    private function error(string $m):void { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$m]); }
}