<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/BranchAllocation.php';
require_once __DIR__ . '/../models/Branch.php';

class BranchAllocationController extends Controller
{
    private BranchAllocation $allocations;
    public function __construct() { if (session_status() === PHP_SESSION_NONE) session_start(); $this->requireApprovedSeller(); $this->allocations = new BranchAllocation(); }
    public function index(): void { $sellerId=(int)$_SESSION['user_id']; $this->view('admin/allocations/index',['rows'=>$this->allocations->availableForSeller($sellerId),'branches'=>(new Branch())->activeSimpleForSeller($sellerId),'error'=>$_GET['error']??null,'active'=>'allocations']); }
    public function store(): void {
        $result=$this->allocations->allocate((int)$_SESSION['user_id'],(int)($_POST['branch_id']??0),(int)($_POST['product_id']??0),trim($_POST['variant_size']??''),trim($_POST['variant_color']??''),(int)($_POST['quantity']??0),(int)$_SESSION['user_id'],trim($_POST['note']??''));
        $this->redirect($result['success'] ? '/admin/allocations' : '/admin/allocations?error='.urlencode($result['error']));
    }
}
