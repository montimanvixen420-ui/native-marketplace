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

    public function showEdit():void{
    $p=$this->profile();
    $id=(int)($_GET['id']??0);
    $product=$this->products->findByIdForSeller($id,(int)$p['seller_id']);
    if(!$product){$this->redirect('/manager/products?error='.urlencode('Product not found.'));return;}

    // Ang products.stock ay sinasadyang 0 para sa mga Branch POS listing —
    // ipakita sa halip ang totoong number mula sa branch_pos_stock, dahil
    // 'yon ang aktwal na binabawas kapag may benta.
    $product['stock']=$this->pos->totalStockForProduct((int)$p['branch_id'],$id);

    $this->view('admin/products/form',[
      'mode'=>'edit',
      'product'=>$product,
      'error'=>null,
      'variants'=>$this->variants->allByProduct($id),
      'sourceStockLimit'=>$this->stockCapFor($product,(int)$p['branch_id']),
      'active'=>'products',
      'managerMode'=>true,
      'profile'=>$p,
      'formAction'=>'/manager/products/update',
      'backHref'=>'/manager/products',
      'formTitle'=>'Edit Branch POS product',
      'categories'=>$this->products->distinctCategoriesForBranch((int)$p['branch_id']),
    ]);
  }

  public function update():void{
    $p=$this->profile();
    $id=(int)($_POST['id']??0);
    $existing=$this->products->findByIdForSeller($id,(int)$p['seller_id']);
    if(!$existing){$this->redirect('/manager/products?error='.urlencode('Product not found.'));return;}

    $renderEdit=function(string $error,?int $stockCap=null) use ($p,$id) {
      $this->view('admin/products/form',[
        'mode'=>'edit',
        'product'=>array_merge($_POST,['id'=>$id]),
        'error'=>$error,
        'sourceStockLimit'=>$stockCap,
        'active'=>'products',
        'managerMode'=>true,
        'profile'=>$p,
        'formAction'=>'/manager/products/update',
        'backHref'=>'/manager/products',
        'formTitle'=>'Edit Branch POS product',
        'categories'=>$this->products->distinctCategoriesForBranch((int)$p['branch_id']),
      ]);
    };

    $data=$this->validate($_POST);
    $variants=$this->parseVariants($_POST['variants']??[]);

    if(isset($data['error'])||isset($variants['error'])){
      $renderEdit($data['error']??$variants['error']);
      return;
    }

    // Branch POS listings track their real sellable quantity sa branch_pos_stock,
    // hindi sa products.stock (sinasadyang pinapanatiling 0 iyon — tingnan ang
    // BranchPosStock::createListingFromInventory()). Kaya dito, panatilihin lang
    // ang existing stock value — hindi dapat baguhin ang quantity sa edit form
    // na ito; gamitin sa halip ang "Transfer from Branch Inventory" o "Damaged
    // Products" para diyan.
    $data['stock']=(int)$existing['stock'];

    $upload=$this->uploadImage(false);
    if(isset($upload['error'])){$renderEdit($upload['error']);return;}
    $data['image_url']=$upload['path'] ?: $existing['image_url'];

    $this->products->update($id,(int)$p['seller_id'],$data);
    if(!empty($variants))$this->variants->replaceForProduct($id,$variants);

    $this->redirect('/manager/products?success='.urlencode('Product updated.'));
  }

  /** Katumbas ng ProductController::stockCapFor(), pero naka-scope sa Branch Inventory ng branch na ito. */
  private function stockCapFor(array $product,int $branchId):?int{
    if(empty($product['inventory_source_product_id']))return null;
    $available=$this->inventory->getStock(
      (int)$product['inventory_source_product_id'],
      (string)($product['inventory_source_variant_size']??''),
      (string)($product['inventory_source_variant_color']??''),
      $branchId
    );
    // Kung ano man ang natitira sa Branch Inventory, PLUS kung ano na ang
    // hawak nitong listing (dahil naibawas na 'yon sa Branch Inventory noong
    // una itong ginawa/tinaasan).
    return $available + (int)$product['stock'];
  }

  public function returnToInventory():void{$p=$this->profile();$r=$this->pos->transfer((int)$p['branch_id'],(int)($_POST['product_id']??0),trim($_POST['variant_size']??''),trim($_POST['variant_color']??''),(int)($_POST['quantity']??0),'pos_to_inventory',(int)$_SESSION['user_id'],true,trim($_POST['note']??''));$this->redirect($r['success']?'/manager/products?success='.urlencode('Product returned to Branch Inventory.'):'/manager/products?error='.urlencode($r['error']));}
  private function sources(int $branchId):array{$out=[];foreach($this->inventory->forBranch($branchId) as $r)if((int)$r['stock']>0)$out[]=['id'=>(int)$r['product_id'],'name'=>$r['product_name'],'stock'=>(int)$r['stock'],'size'=>$r['size']??'','color'=>$r['color']??''];return $out;}
  private function renderCreate(array $p,array $product,?string $error):void{$this->view('admin/products/form',['mode'=>'create','product'=>$product,'error'=>$error,'availableStocks'=>$this->sources((int)$p['branch_id']),'active'=>'products','managerMode'=>true,'profile'=>$p,'formAction'=>'/manager/products/create','backHref'=>'/manager/products','formTitle'=>'Add a new Branch POS product','sourceLabel'=>'Branch inventory stock','sourceHelp'=>'Choose stock from your Branch Inventory. Details below create a separate listing for this branch only.','categories'=>$this->products->distinctCategoriesForBranch((int)$p['branch_id'])]);}
  private function validate(array $i):array{$name=trim($i['name']??'');$price=$i['price']??'';$stock=$i['stock']??'';if($name==='')return ['error'=>'Product name is required.'];if(!is_numeric($price)||(float)$price<0)return ['error'=>'Please enter a valid price.'];if(!ctype_digit((string)$stock)&&!is_int($stock))return ['error'=>'Please enter a valid stock quantity.'];return ['name'=>$name,'description'=>trim($i['description']??''),'size_guide'=>trim($i['size_guide']??''),'fit_information'=>trim($i['fit_information']??''),'price'=>(float)$price,'stock'=>(int)$stock,'category'=>trim($i['category']??'')?:null,'status'=>in_array(($i['status']??'active'),['active','inactive'],true)?$i['status']:'active'];}
  private function parseVariants(array $raw):array{$out=[];$seen=[];foreach($raw as $r){$size=trim($r['size']??'');$color=trim($r['color']??'');$sku=trim($r['sku']??'');$stock=$r['stock']??'';if($size===''&&$color===''&&$stock==='')continue;if(($size===''&&$color==='')||filter_var($stock,FILTER_VALIDATE_INT)===false||(int)$stock<0)return ['error'=>'Each variant needs a size or color and valid stock.'];$size=$size?:'One size';$color=$color?:'N/A';$key=strtolower($size.'|'.$color);if(isset($seen[$key]))return ['error'=>'Each size/color combination must be unique.'];$seen[$key]=true;$out[]=['size'=>$size,'color'=>$color,'sku'=>$sku,'stock'=>(int)$stock];}return $out;}
   private function uploadImage(bool $required=true):array{if(!isset($_FILES['image'])||$_FILES['image']['error']===UPLOAD_ERR_NO_FILE)return $required?['error'=>'Please upload a product image (PNG or JPEG).']:['path'=>null];$f=$_FILES['image'];if($f['error']!==UPLOAD_ERR_OK)return ['error'=>'Image upload failed.'];if($f['size']>5*1024*1024)return ['error'=>'Image must be 5MB or smaller.'];$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);if(!in_array($ext,['png','jpg','jpeg'],true)||!in_array($mime,['image/png','image/jpeg'],true))return ['error'=>'Only PNG or JPEG images are allowed.'];$dir=__DIR__.'/../public/uploads/products/';if(!is_dir($dir))mkdir($dir,0755,true);$name=uniqid('product_',true).'.'.$ext;if(!move_uploaded_file($f['tmp_name'],$dir.$name))return ['error'=>'Failed to save image.'];return ['path'=>'uploads/products/'.$name];}
}