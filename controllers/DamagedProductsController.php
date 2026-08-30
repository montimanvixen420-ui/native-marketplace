<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/BranchStock.php';
class DamagedProductsController extends Controller {
  public function __construct(){ if(session_status()===PHP_SESSION_NONE) session_start(); $this->requireApprovedSeller(); }
  public function index(): void { $this->view('admin/damaged-products/index',['rows'=>(new BranchStock())->damagesForSeller((int)$_SESSION['user_id']),'active'=>'damaged-products']); }
}
