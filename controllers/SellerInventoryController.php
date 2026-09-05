<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/ProductVariant.php';
require_once __DIR__ . '/../models/SellerPosStock.php';
class SellerInventoryController extends Controller {
  private Product $products; private ProductVariant $variants; private SellerPosStock $pos;
  public function __construct(){if(session_status()===PHP_SESSION_NONE)session_start();$this->requireApprovedSeller();$this->products=new Product();$this->variants=new ProductVariant();$this->pos=new SellerPosStock();}
  public function index():void{$sellerId=(int)$_SESSION['user_id'];$this->view('admin/inventory/index',['products'=>$this->products->rawInventoryBySeller($sellerId),'posProducts'=>$this->pos->posProducts($sellerId),'active'=>'inventory','error'=>$_GET['error']??null]);}
  public function transfer():void{$r=$this->pos->transfer((int)$_SESSION['user_id'],(int)($_POST['product_id']??0),trim($_POST['variant_size']??''),trim($_POST['variant_color']??''),(int)($_POST['quantity']??0),$_POST['direction']??'',(int)$_SESSION['user_id'],trim($_POST['note']??''));$this->redirect($r['success']?'/admin/inventory?success='.urlencode('Stock transferred successfully.'):'/admin/inventory?error='.urlencode($r['error']));}
}