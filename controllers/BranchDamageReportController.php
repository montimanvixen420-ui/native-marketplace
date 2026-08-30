<?php
require_once __DIR__.'/../core/Controller.php';
require_once __DIR__.'/../models/Staff.php';
require_once __DIR__.'/../models/BranchDamageReport.php';

class BranchDamageReportController extends Controller {
    private BranchDamageReport $reports;
    public function __construct(){ if(session_status()===PHP_SESSION_NONE) session_start(); $this->reports=new BranchDamageReport(); }
    private function profile(): array { return $this->requireActiveStaff(); }
    public function report(): void {
        $p=$this->profile();
        if($p['position']!=='inventory_staff'){ http_response_code(403); exit('Only Inventory Staff may file a damage report.'); }
        $r=$this->reports->report((int)$p['branch_id'],(int)($_POST['product_id']??0),trim($_POST['variant_size']??''),trim($_POST['variant_color']??''),(int)($_POST['quantity']??0),(int)$_SESSION['user_id'],trim($_POST['note']??''));
        $this->redirect($r['success']?'/manager/inventory?success='.urlencode('Damage report sent to your Branch Manager.'):'/manager/inventory?error='.urlencode($r['error']));
    }
    public function index(): void {
        $p=$this->profile();
        if($p['position']!=='branch_manager'){ http_response_code(403); exit('Only the Branch Manager may review damaged items.'); }
        $this->view('manager/damaged-items',['profile'=>$p,'reports'=>$this->reports->pending((int)$p['branch_id']),'active'=>'damaged-items','error'=>$_GET['error']??null,'success'=>$_GET['success']??null]);
    }
    public function approve(string $id): void {
        $p=$this->profile();
        if($p['position']!=='branch_manager'){ http_response_code(403); exit('Only the Branch Manager may approve damaged items.'); }
        $r=$this->reports->approve((int)$id,(int)$p['branch_id'],(int)$_SESSION['user_id']);
        $this->redirect($r['success']?'/manager/damaged-items?success='.urlencode('Damaged item recorded and permanently removed from Branch Inventory.'):'/manager/damaged-items?error='.urlencode($r['error']));
    }
}
