<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/SupplierInventory.php';
class SupplierInventoryController extends Controller {
    private SupplierInventory $inventory;
    public function __construct() { if (session_status() === PHP_SESSION_NONE) session_start(); $this->requireRole('supplier'); $this->inventory = new SupplierInventory(); }
    public function index(): void { $this->render(null); }
    public function store(): void { $data=$this->input(); if(isset($data['error'])){$this->render($data['error']);return;} $this->inventory->create((int)$_SESSION['user_id'],$data['itemName'],$data['description'],$data['unit'],$data['unitPrice'],$data['quantity']); $this->redirect('/supplier/inventory'); }
    public function update(): void { $id=(int)($_POST['id']??0);$data=$this->input();if($id<=0||isset($data['error'])){$this->render($data['error']??'Invalid inventory item.');return;} $this->inventory->updateForSupplier($id,(int)$_SESSION['user_id'],$data['itemName'],$data['description'],$data['unit'],$data['unitPrice'],$data['quantity'],isset($_POST['is_active']));$this->redirect('/supplier/inventory'); }
    private function input(): array { $itemName=trim($_POST['item_name']??'');$description=trim($_POST['description']??'');$unit=trim($_POST['unit']??'piece');$unitPrice=filter_var($_POST['unit_price']??null,FILTER_VALIDATE_FLOAT);$quantity=filter_var($_POST['quantity_available']??null,FILTER_VALIDATE_INT);if($itemName==='')return['error'=>'Enter the supply name.'];if($unit==='')return['error'=>'Enter a unit.'];if($unitPrice===false||$unitPrice<0)return['error'=>'Enter a valid price.'];if($quantity===false||$quantity<0)return['error'=>'Enter a valid stock quantity.'];return compact('itemName','description','unit','unitPrice','quantity'); }
    private function render(?string $error): void { $this->view('supplier/inventory/index',['name'=>$_SESSION['user_name'],'items'=>$this->inventory->allBySupplier((int)$_SESSION['user_id']),'error'=>$error,'active'=>'inventory']); }
}
