<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/RestockNotification.php';
require_once __DIR__ . '/../models/ProductVariant.php';
class RestockNotificationController extends Controller {
 public function __construct(){ if(session_status()===PHP_SESSION_NONE)session_start(); $this->requireRole('customer'); }
 public function subscribe(): void { $productId=(int)($_POST['product_id']??0); if ($productId < 1) { $this->redirect('/shop'); return; } (new RestockNotification())->subscribe((int)$_SESSION['user_id'],$productId,null); $_SESSION['restock_success']='We will notify you when this item is available again.'; $this->redirect('/shop'); }
}
