<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Voucher.php';

class VoucherController extends Controller {
    private PDO $db;
    public function __construct() { if (session_status() === PHP_SESSION_NONE) session_start(); $this->requireApprovedSeller(); $this->db = Database::getConnection(); }
    public function index(): void { $s = $this->db->prepare('SELECT * FROM vouchers WHERE seller_id = :id ORDER BY created_at DESC'); $s->execute(['id'=>(int)$_SESSION['user_id']]); $this->view('admin/vouchers/index', ['vouchers'=>$s->fetchAll(), 'active'=>'vouchers']); }
    public function store(): void {
        $type = $_POST['discount_type'] ?? 'fixed'; $code = strtoupper(trim($_POST['code'] ?? ''));
        if ($code === '' || !in_array($type, ['fixed','percent','free_shipping'], true)) { $_SESSION['voucher_error']='Enter a valid voucher code.'; $this->redirect('/admin/vouchers'); }
        $s=$this->db->prepare('INSERT INTO vouchers (seller_id,code,discount_type,discount_value,minimum_order,maximum_discount,ends_at) VALUES (:seller,:code,:type,:value,:min,:max,:ends)');
        try { $s->execute(['seller'=>(int)$_SESSION['user_id'],'code'=>$code,'type'=>$type,'value'=>max(0,(float)($_POST['discount_value']??0)),'min'=>max(0,(float)($_POST['minimum_order']??0)),'max'=>($_POST['maximum_discount']??'')===''?null:max(0,(float)$_POST['maximum_discount']),'ends_at'=>($_POST['ends_at']??'')===''?null:$_POST['ends_at']]); }
        catch (PDOException $e) { $_SESSION['voucher_error']='That voucher code already exists.'; }
        $this->redirect('/admin/vouchers');
    }
    public function toggle(): void { $s=$this->db->prepare('UPDATE vouchers SET is_active = 1-is_active WHERE id=:id AND seller_id=:seller'); $s->execute(['id'=>(int)($_POST['id']??0),'seller'=>(int)$_SESSION['user_id']]); $this->redirect('/admin/vouchers'); }
}
