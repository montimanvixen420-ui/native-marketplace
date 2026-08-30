<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../models/BranchManager.php';
require_once __DIR__ . '/../models/order.php';
require_once __DIR__ . '/../models/PostPurchase.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/BranchStock.php';

class StaffController extends Controller
{
    private Staff $staff;
    private BranchManager $managers;
    public function __construct() { if(session_status()===PHP_SESSION_NONE) session_start(); $this->staff=new Staff(); $this->managers=new BranchManager(); }

    /** Branch-manager-only staff list. Seller administration is deliberately excluded. */
    public function index(): void
    {
        $manager=$this->requireManager();
        $this->view('staff/manage',['name'=>$_SESSION['user_name'],'manager'=>$manager,'staffList'=>$this->staff->allByManager((int)$_SESSION['user_id'],(int)$manager['branch_id']),'positions'=>Staff::POSITIONS,'active'=>'staff']);
    }
    public function store(): void
    {
        $manager=$this->requireManager(); $data=$this->data(); $password=(string)($_POST['password']??'');
        if(!$data || strlen($password)<8) { $this->error('Provide valid details and a password of at least 8 characters.'); return; }
        if($this->staff->emailExists($data['email'])) { $this->error('That email is already in use.'); return; }
        $id=$this->staff->createForManager((int)$_SESSION['user_id'],(int)$manager['seller_id'],(int)$manager['branch_id'],$data,$password);
        $this->success('Staff account created successfully.',['id'=>$id]);
    }
    public function update(int $id): void
    {
        $manager=$this->requireManager(); $data=$this->data();
        if(!$data || !$this->staff->updateForManager($id,(int)$_SESSION['user_id'],(int)$manager['branch_id'],$data)) { $this->error('Staff member was not found or the details are invalid.'); return; }
        $this->success('Staff information saved.');
    }
    public function setStatus(int $id): void
    {
        $manager=$this->requireManager(); $status=(string)($_POST['status']??'');
        if(!$this->staff->setStatusForManager($id,(int)$_SESSION['user_id'],(int)$manager['branch_id'],$status)) { $this->error('Unable to update this staff account.'); return; }
        $this->success('Staff status updated.');
    }
    public function archive(int $id): void
    {
        $manager=$this->requireManager();
        if(!$this->staff->archiveForManager($id,(int)$_SESSION['user_id'],(int)$manager['branch_id'])) { $this->error('Staff member was not found.'); return; }
        $this->success('Staff account archived.');
    }
    public function resetPassword(int $id): void
    {
        $manager=$this->requireManager(); $password=(string)($_POST['password']??'');
        if(strlen($password)<8) { $this->error('Password must be at least 8 characters.'); return; }
        if(!$this->staff->resetPasswordForManager($id,(int)$_SESSION['user_id'],(int)$manager['branch_id'],$password)) { $this->error('Staff member was not found.'); return; }
        $this->success('Password reset successfully.');
    }
    public function dashboard(): void
    {
        $profile=$this->requireActiveStaff();
        $branchId=(int)$profile['branch_id'];
        $stats=[];
        if($profile['position']==='customer_service'){
            $orders=(new Order())->allByBranch($branchId);
            $returns=(new PostPurchase())->returnsByBranch($branchId);
            $reports=(new Report())->productReportsByBranch($branchId);
            $stats=[
                'orders_total'=>count($orders),
                'returns_pending'=>count(array_filter($returns,fn($r)=>$r['status']==='requested')),
                'reports_open'=>count(array_filter($reports,fn($r)=>in_array($r['status'],['open','reviewing'],true))),
                'activity_trend'=>$this->dailyCounts(array_merge($returns,$reports),'created_at'),
            ];
        } elseif($profile['position']==='order_staff'){
            $orderModel=new Order();
            $orders=$orderModel->allByBranch($branchId);
            $today=date('Y-m-d');
            $stats=[
                'orders_pending'=>$orderModel->countPendingOnlineByBranch($branchId),
                'orders_packed'=>count(array_filter($orders,fn($o)=>$o['status']==='packed')),
                'orders_today'=>count(array_filter($orders,fn($o)=>substr($o['created_at'],0,10)===$today)),
                'orders_trend'=>$this->dailyCounts($orders,'created_at'),
            ];
        } elseif($profile['position']==='cashier'){
            $orderModel=new Order();
            $orders=$orderModel->allByBranch($branchId);
            $today=date('Y-m-d');
            $todaysOrders=array_filter($orders,fn($o)=>substr($o['created_at'],0,10)===$today && $o['status']!=='cancelled');
            $stats=[
                'orders_total'=>count($orders),
                'orders_today'=>count($todaysOrders),
                'sales_today'=>array_sum(array_column($todaysOrders,'total_amount')),
                'sales_trend'=>$this->dailySalesTrend($orderModel->getDailySalesByBranch($branchId,7)),
            ];
        } elseif($profile['position']==='inventory_staff'){
            // Inventory staff manages the branch allocation, not the seller-wide
            // product total. Using BranchStock makes this dashboard match the
            // branch stock page immediately after an online or POS sale.
            $branchStock=new BranchStock();
            $summary=$branchStock->summaryForBranch($branchId);
            $lowStock=$branchStock->lowStockForBranch($branchId);
            $stats=[
                'product_count'=>$summary['product_count'],
                'low_stock_count'=>$summary['low_stock_count'],
                'out_of_stock_count'=>$summary['out_of_stock_count'],
                'low_stock_items'=>array_slice($lowStock,0,6),
                'restock_trend'=>$branchStock->dailyRestockCountsForBranch($branchId,7),
            ];
        }
        $this->view('staff/dashboard',['name'=>$_SESSION['user_name'],'profile'=>$profile,'permissions'=>$this->staff->permissionsForPosition($profile['position']),'stats'=>$stats,'active'=>'dashboard']);
    }
    /**
     * Groups already-fetched records into a 7-day count-per-day trend,
     * using a $dateField (e.g. 'created_at') present on each record.
     * Always returns 7 entries, oldest first, 0-filled for empty days.
     */
    private function dailyCounts(array $records, string $dateField, int $days = 7): array
    {
        $byDay = [];
        foreach ($records as $r) {
            if (empty($r[$dateField])) continue;
            $day = substr($r[$dateField], 0, 10);
            $byDay[$day] = ($byDay[$day] ?? 0) + 1;
        }
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = ['date' => $date, 'label' => date('D', strtotime($date)), 'count' => $byDay[$date] ?? 0];
        }
        return $result;
    }

    /** Normalizes Order::getDailySalesByBranch() into a fixed 7-day, oldest-first, 0-filled trend. */
    private function dailySalesTrend(array $rows, int $days = 7): array
    {
        $byDay = [];
        foreach ($rows as $r) {
            $byDay[$r['sale_date']] = ['orders' => (int) $r['orders'], 'revenue' => (float) $r['revenue']];
        }
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = [
                'date' => $date,
                'label' => date('D', strtotime($date)),
                'orders' => $byDay[$date]['orders'] ?? 0,
                'revenue' => $byDay[$date]['revenue'] ?? 0.0,
            ];
        }
        return $result;
    }

    private function requireManager(): array
    {
        $this->requireRole('manager'); $m=$this->managers->forUser((int)$_SESSION['user_id']);
        if(!$m){ http_response_code(403); exit('Access Denied: only an active branch manager may manage staff.'); } return $m;
    }
    private function data(): ?array
    {
        $d=['first_name'=>trim($_POST['first_name']??''),'last_name'=>trim($_POST['last_name']??''),'email'=>trim($_POST['email']??''),'phone'=>trim($_POST['phone']??''),'position'=>(string)($_POST['position']??'')];
        return $d['first_name']!=='' && $d['last_name']!=='' && filter_var($d['email'],FILTER_VALIDATE_EMAIL) && isset(Staff::POSITIONS[$d['position']]) ? $d : null;
    }
    private function success(string $message,array $extra=[]): void { header('Content-Type: application/json'); echo json_encode(['success'=>true,'message'=>$message]+$extra); }
    private function error(string $message): void { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$message]); }
}