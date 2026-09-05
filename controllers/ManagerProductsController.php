<?php
require_once __DIR__.'/../core/Controller.php';
require_once __DIR__.'/../models/Staff.php';
require_once __DIR__.'/../models/BranchStock.php';
require_once __DIR__.'/../models/BranchPosStock.php';
require_once __DIR__.'/../models/Product.php';
require_once __DIR__.'/../models/ProductVariant.php';

class ManagerProductsController extends Controller {
  private BranchStock $inventory; private BranchPosStock $pos; private Product $products; private ProductVariant $variants;
  public function __construct(){if(session_status()===PHP_SESSION_NONE)session_start();$this->inventory=new BranchStock();$this->pos=new BranchPosStock();$this->products=new Product();$this->variants=new ProductVariant();}
  private function profile():array{$p=$this->requireActiveStaff();if($p['position']!=='branch_manager'){http_response_code(403);exit('Only the Branch Manager may manage Branch POS products.');}return $p;}
  public function index():void{$p=$this->profile();$this->view('manager/products/index',['profile'=>$p,'products'=>$this->pos->productsForBranch((int)$p['branch_id']),'active'=>'products','error'=>$_GET['error']??null,'success'=>$_GET['success']??null]);}
  public function showCreate():void{$this->renderCreate($this->profile(),[],null);}
  public function store():void{
    $p=$this->profile();$sourceId=(int)($_POST['inventory_source_product_id']??0);$size=trim($_POST['branch_source_variant_size']??'');$color=trim($_POST['branch_source_variant_color']??'');$source=null;
    foreach($this->sources((int)$p['branch_id']) as $row)if((int)$row['id']===$sourceId&&$row['size']===$size&&$row['color']===$color){$source=$row;break;}
    $data=$this->validate($_POST);$variants=$this->parseVariants($_POST['variants']??[]);
    if(isset($data['error'])||isset($variants['error'])||!$source){$this->renderCreate($p,$_POST,$data['error']??$variants['error']??'Choose a valid Branch Inventory stock source.');return;}
    if($variants)$data['stock']=array_sum(array_column($variants,'stock'));
    if($data['stock']>(int)$source['stock']){$this->renderCreate($p,$_POST,'Product stock cannot exceed the selected Branch Inventory stock ('.(int)$source['stock'].').');return;}
    $upload=$this->uploadImage();if(isset($upload['error'])){$this->renderCreate($p,$_POST,$upload['error']);return;}
    $data['stock_request_id']=null;$data['inventory_source_product_id']=$sourceId;$data['inventory_source_variant_size']=$size;$data['inventory_source_variant_color']=$color;$data['image_url']=$upload['path'];
    $listingId=$this->products->create((int)$p['seller_id'],$data);$this->variants->replaceForProduct($listingId,$variants);
    $result=$this->pos->createListingFromInventory((int)$p['branch_id'],$sourceId,$size,$color,$listingId,(int)$data['stock'],$variants,(int)$_SESSION['user_id']);
    if(!$result['success']){$this->products->delete($listingId,(int)$p['seller_id']);$this->renderCreate($p,$_POST,$result['error']);return;}$this->redirect('/manager/products?success='.urlencode('New Branch POS product created from Branch Inventory.'));
  }
  public function add():void{$this->redirect('/manager/products/create');}
  public function returnToInventory():void{$p=$this->profile();$r=$this->pos->transfer((int)$p['branch_id'],(int)($_POST['product_id']??0),trim($_POST['variant_size']??''),trim($_POST['variant_color']??''),(int)($_POST['quantity']??0),'pos_to_inventory',(int)$_SESSION['user_id'],true,trim($_POST['note']??''));$this->redirect($r['success']?'/manager/products?success='.urlencode('Product returned to Branch Inventory.'):'/manager/products?error='.urlencode($r['error']));}
  private function sources(int $branchId):array{$out=[];foreach($this->inventory->forBranch($branchId) as $r)if((int)$r['stock']>0)$out[]=['id'=>(int)$r['product_id'],'name'=>$r['product_name'],'stock'=>(int)$r['stock'],'size'=>$r['size']??'','color'=>$r['color']??''];return $out;}
  private function renderCreate(array $p,array $product,?string $error):void{$this->view('admin/products/form',['mode'=>'create','product'=>$product,'error'=>$error,'availableStocks'=>$this->sources((int)$p['branch_id']),'active'=>'products','managerMode'=>true,'profile'=>$p,'formAction'=>'/manager/products/create','backHref'=>'/manager/products','formTitle'=>'Add a new Branch POS product','sourceLabel'=>'Branch inventory stock','sourceHelp'=>'Choose stock from your Branch Inventory. Details below create a separate listing for this branch only.','categories'=>$this->products->distinctCategoriesForBranch((int)$p['branch_id'])]);}
  private function validate(array $i):array{$name=trim($i['name']??'');$price=$i['price']??'';$stock=$i['stock']??'';if($name==='')return ['error'=>'Product name is required.'];if(!is_numeric($price)||(float)$price<0)return ['error'=>'Please enter a valid price.'];if(!ctype_digit((string)$stock)&&!is_int($stock))return ['error'=>'Please enter a valid stock quantity.'];return ['name'=>$name,'description'=>trim($i['description']??''),'size_guide'=>trim($i['size_guide']??''),'fit_information'=>trim($i['fit_information']??''),'price'=>(float)$price,'stock'=>(int)$stock,'category'=>trim($i['category']??'')?:null,'status'=>in_array(($i['status']??'active'),['active','inactive'],true)?$i['status']:'active'];}
  private function parseVariants(array $raw):array{$out=[];$seen=[];foreach($raw as $r){$size=trim($r['size']??'');$color=trim($r['color']??'');$sku=trim($r['sku']??'');$stock=$r['stock']??'';if($size===''&&$color===''&&$stock==='')continue;if(($size===''&&$color==='')||filter_var($stock,FILTER_VALIDATE_INT)===false||(int)$stock<0)return ['error'=>'Each variant needs a size or color and valid stock.'];$size=$size?:'One size';$color=$color?:'N/A';$key=strtolower($size.'|'.$color);if(isset($seen[$key]))return ['error'=>'Each size/color combination must be unique.'];$seen[$key]=true;$out[]=['size'=>$size,'color'=>$color,'sku'=>$sku,'stock'=>(int)$stock];}return $out;}
  private function uploadImage():array{if(!isset($_FILES['image'])||$_FILES['image']['error']===UPLOAD_ERR_NO_FILE)return ['error'=>'Please upload a product image (PNG or JPEG).'];$f=$_FILES['image'];if($f['error']!==UPLOAD_ERR_OK)return ['error'=>'Image upload failed.'];if($f['size']>5*1024*1024)return ['error'=>'Image must be 5MB or smaller.'];$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);if(!in_array($ext,['png','jpg','jpeg'],true)||!in_array($mime,['image/png','image/jpeg'],true))return ['error'=>'Only PNG or JPEG images are allowed.'];$dir=__DIR__.'/../public/uploads/products/';if(!is_dir($dir))mkdir($dir,0755,true);$name=uniqid('product_',true).'.'.$ext;if(!move_uploaded_file($f['tmp_name'],$dir.$name))return ['error'=>'Failed to save image.'];return ['path'=>'uploads/products/'.$name];}
}