<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Report.php';
class CustomerReportController extends Controller
{
    private Report $reports;
    private const REASONS = ['Counterfeit or misleading item','Prohibited item','Inappropriate content','Scam or suspicious activity','Harassment or unsafe seller conduct','Other'];
    public function __construct() { if(session_status()===PHP_SESSION_NONE) session_start(); $this->requireRole('customer'); $this->reports=new Report(); }
    public function create(): void { $type=$_GET['type']??''; $target=$this->reports->targetExists($type,(int)($_GET['id']??0)); if(!in_array($type,['product','seller'],true)||!$target){$this->redirect('/shop');return;} $this->form($type,$target); }
    public function store(): void { $type=$_POST['target_type']??''; $id=(int)($_POST['target_id']??0); $target=$this->reports->targetExists($type,$id); $reason=trim($_POST['reason']??''); $details=trim($_POST['details']??''); if(!in_array($type,['product','seller'],true)||!$target||!in_array($reason,self::REASONS,true)||strlen($details)<10||strlen($details)>1000){$this->form($type,$target?:['id'=>$id,'label'=>'this listing'],'Choose a valid reason and provide 10–1,000 characters of detail.');return;} if($this->reports->hasOpen((int)$_SESSION['user_id'],$type,$id)){$this->redirect('/shop?report_error='.urlencode('You already have an open report for this '.$type.'.'));return;} $this->reports->create((int)$_SESSION['user_id'],$type,$id,$reason,$details);$this->redirect('/shop?report_success='.urlencode('Thanks—your report was sent to our moderation team.')); }
private function form(string $type,array $target,?string $error=null):void {
    $this->view('customer/report_form',['name'=>$_SESSION['user_name'],'type'=>$type,'target'=>$target,'reasons'=>self::REASONS,'error'=>$error,'active'=>'browse']);
}

    // GET /customer/reports — customer's own "My Reports" monitoring page
   // GET /customer/reports — customer's own "My Reports" monitoring page
public function index(): void
{
    $this->view('customer/report', [        // ← tinanggal yung "s", dapat "report" hindi "reports"
        'name' => $_SESSION['user_name'],
        'reports' => $this->reports->getByReporterId((int) $_SESSION['user_id']),
        'active' => 'reports',
    ]);
}
}