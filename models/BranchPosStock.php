<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BranchAllocation.php';
class BranchPosStock {
  private PDO $db; private BranchAllocation $allocations; public function __construct(){ $this->db=Database::getConnection();$this->allocations=new BranchAllocation(); }
  public function transfer(int $branchId,int $productId,string $size,string $color,int $quantity,string $direction,int $userId,bool $isManager,?string $note):array{
    if($quantity<1||!in_array($direction,['inventory_to_pos','pos_to_inventory','inventory_to_seller'],true))return ['success'=>false,'error'=>'Invalid transfer.'];
    if($direction==='inventory_to_seller'&&!$isManager)return ['success'=>false,'error'=>'Only the Branch Manager can return stock to Seller Inventory.'];
    // A Branch POS listing created from inventory (see createListingFromInventory) lives under its OWN
    // product_id, separate from the Branch Inventory product/variant it was created from. When returning
    // such a listing's stock, resolve the original source identity so we credit the right Branch
    // Inventory row, instead of the listing's own id.
    $sourceProductId=$productId; $sourceSize=$size; $sourceColor=$color; $isListingReturn=false;
    if($direction==='pos_to_inventory'){
      $src=$this->db->prepare('SELECT inventory_source_product_id, inventory_source_variant_size, inventory_source_variant_color FROM products WHERE id=:id');
      $src->execute(['id'=>$productId]);
      $srcRow=$src->fetch();
      if($srcRow && $srcRow['inventory_source_product_id']){
        $sourceProductId=(int)$srcRow['inventory_source_product_id'];
        $sourceSize=(string)($srcRow['inventory_source_variant_size'] ?? '');
        $sourceColor=(string)($srcRow['inventory_source_variant_color'] ?? '');
        $isListingReturn=true;
      }
    }
    $this->db->beginTransaction();try{
      $inv=$this->db->prepare('SELECT stock FROM branch_stock WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color FOR UPDATE');$inv->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);$inventory=(int)$inv->fetchColumn();
      $pos=$this->db->prepare('SELECT stock FROM branch_pos_stock WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color FOR UPDATE');$pos->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);$posStock=(int)$pos->fetchColumn();
      if($direction==='inventory_to_pos'||$direction==='inventory_to_seller'){if($inventory<$quantity)throw new RuntimeException('Not enough Branch Inventory stock.');$this->db->prepare('UPDATE branch_stock SET stock=stock-:quantity WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color')->execute(['quantity'=>$quantity,'branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);$this->allocations->consume($branchId,$productId,$size,$color,$quantity);}
      else {if($posStock<$quantity)throw new RuntimeException('Not enough Branch POS stock.');$this->db->prepare('UPDATE branch_pos_stock SET stock=stock-:quantity WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color')->execute(['quantity'=>$quantity,'branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);}
      if($direction==='inventory_to_pos'||$direction==='pos_to_inventory')$this->db->prepare('INSERT INTO branch_pos_stock (branch_id,product_id,variant_size,variant_color,stock) VALUES (:branch_id,:product_id,:size,:color,:stock) ON DUPLICATE KEY UPDATE stock=stock+:change')->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color,'stock'=>$direction==='inventory_to_pos'?$quantity:0,'change'=>$direction==='inventory_to_pos'?$quantity:-$quantity]);
      if($direction==='pos_to_inventory'){$this->db->prepare('INSERT INTO branch_stock (branch_id,product_id,variant_size,variant_color,stock) VALUES (:branch_id,:product_id,:size,:color,:stock) ON DUPLICATE KEY UPDATE stock=stock+:change')->execute(['branch_id'=>$branchId,'product_id'=>$sourceProductId,'size'=>$sourceSize,'color'=>$sourceColor,'stock'=>$quantity,'change'=>$quantity]);
        // Listings created via createListingFromInventory() decrement branch_stock directly without ever
        // calling BranchAllocation::consume() (see createListingFromInventory's own comment on this), so
        // there is no "used" allocation to give back here — crediting branch_stock above is the full fix.
        // Only a direct (non-listing) pos_to_inventory transfer went through consume() and needs restore().
        if(!$isListingReturn) $this->allocations->restore($branchId,$sourceProductId,$sourceSize,$sourceColor,$quantity);
      }
      if($direction==='inventory_to_seller'){$seller=$this->db->prepare('SELECT seller_id FROM branches WHERE id=:id');$seller->execute(['id'=>$branchId]);$sellerId=(int)$seller->fetchColumn();$this->db->prepare('UPDATE products SET stock=stock+:quantity WHERE id=:id')->execute(['quantity'=>$quantity,'id'=>$productId]);if($size!==''||$color!=='')$this->db->prepare('UPDATE product_variants SET stock=stock+:quantity WHERE product_id=:product_id AND size=:size AND color=:color')->execute(['quantity'=>$quantity,'product_id'=>$productId,'size'=>$size,'color'=>$color]);}
      $this->db->prepare('INSERT INTO branch_inventory_transfers (branch_id,product_id,variant_size,variant_color,quantity,direction,performed_by_user_id,note) VALUES (:branch_id,:product_id,:size,:color,:quantity,:direction,:user_id,:note)')->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color,'quantity'=>$quantity,'direction'=>$direction,'user_id'=>$userId,'note'=>$note?:null]);$this->db->commit();return ['success'=>true];
    }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();return ['success'=>false,'error'=>$e->getMessage()];}
  }
    public function productsForBranch(int $branchId): array {
    $stmt=$this->db->prepare("SELECT p.id AS product_id,p.name,p.price,p.image_url,p.status,bps.variant_size,bps.variant_color,bps.stock FROM branch_pos_stock bps INNER JOIN products p ON p.id=bps.product_id WHERE bps.branch_id=:branch_id AND bps.stock>0 ORDER BY p.name,bps.variant_color,bps.variant_size");
    $stmt->execute(['branch_id'=>$branchId]);return $stmt->fetchAll();
  }

  /** Kabuuang totoong stock ng isang listing sa Branch POS (lahat ng variant rows), para sa display sa Edit form. */
  public function totalStockForProduct(int $branchId,int $productId):int{
    $stmt=$this->db->prepare('SELECT COALESCE(SUM(stock),0) FROM branch_pos_stock WHERE branch_id=:branch_id AND product_id=:product_id');
    $stmt->execute(['branch_id'=>$branchId,'product_id'=>$productId]);
    return (int)$stmt->fetchColumn();
  }

  /** Creates a separate Branch POS listing from one Branch Inventory source. */
  public function createListingFromInventory(int $branchId,int $sourceProductId,string $sourceSize,string $sourceColor,int $listingProductId,int $quantity,array $variants,int $userId): array {
    if($quantity<1)return ['success'=>false,'error'=>'Stock quantity must be at least 1.'];
    $this->db->beginTransaction();
    try {
      $source=$this->db->prepare('SELECT stock FROM branch_stock WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color FOR UPDATE');
      $source->execute(['branch_id'=>$branchId,'product_id'=>$sourceProductId,'size'=>$sourceSize,'color'=>$sourceColor]);
      if((int)$source->fetchColumn()<$quantity)throw new RuntimeException('Selected Branch Inventory stock is no longer sufficient.');
      $this->db->prepare('UPDATE branch_stock SET stock=stock-:quantity WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color')->execute(['quantity'=>$quantity,'branch_id'=>$branchId,'product_id'=>$sourceProductId,'size'=>$sourceSize,'color'=>$sourceColor]);
      // branch_stock is the live Branch Inventory balance. Older allocations
      // may have a stale remaining balance from the former direct-to-sale
      // flow, so do not let that historical counter block a valid transfer.
      $this->db->prepare('UPDATE products SET stock=0 WHERE id=:id')->execute(['id'=>$listingProductId]);
      if($variants){
        $insert=$this->db->prepare('INSERT INTO branch_pos_stock (branch_id,product_id,variant_size,variant_color,stock) VALUES (:branch_id,:product_id,:size,:color,:stock)');
        foreach($variants as $variant)$insert->execute(['branch_id'=>$branchId,'product_id'=>$listingProductId,'size'=>$variant['size'],'color'=>$variant['color'],'stock'=>$variant['stock']]);
      } else {
        $this->db->prepare('INSERT INTO branch_pos_stock (branch_id,product_id,variant_size,variant_color,stock) VALUES (:branch_id,:product_id,"","",:stock)')->execute(['branch_id'=>$branchId,'product_id'=>$listingProductId,'stock'=>$quantity]);
      }
      $this->db->prepare('INSERT INTO product_branches (product_id,branch_id) VALUES (:product_id,:branch_id) ON DUPLICATE KEY UPDATE product_id=product_id')->execute(['product_id'=>$listingProductId,'branch_id'=>$branchId]);
      $this->db->prepare('INSERT INTO branch_inventory_transfers (branch_id,product_id,variant_size,variant_color,quantity,direction,performed_by_user_id,note) VALUES (:branch_id,:product_id,:size,:color,:quantity,"inventory_to_pos",:user_id,:note)')->execute(['branch_id'=>$branchId,'product_id'=>$sourceProductId,'size'=>$sourceSize,'color'=>$sourceColor,'quantity'=>$quantity,'user_id'=>$userId,'note'=>'Created Branch POS listing #'.$listingProductId]);
      $this->db->commit(); return ['success'=>true];
    } catch(Throwable $e) {if($this->db->inTransaction())$this->db->rollBack();return ['success'=>false,'error'=>$e->getMessage()];}
  }

  /**
   * A branch's total sellable stock — what's currently loaded into its POS
   * (branch_pos_stock). This is the number that actually goes down when an
   * order for that branch is completed, whether placed online or through the
   * Cashier POS, so it's the right basis for a "which branch is running low"
   * monitoring view.
   */
  public function getInventorySummaryForBranch(int $branchId, int $lowStockThreshold = 5): array {
    $stmt=$this->db->prepare(
      "SELECT
          COUNT(DISTINCT bps.product_id) AS product_count,
          COALESCE(SUM(bps.stock), 0) AS total_units,
          COALESCE(SUM(CASE WHEN bps.stock BETWEEN 1 AND :threshold THEN 1 ELSE 0 END), 0) AS low_stock_count,
          COALESCE(SUM(CASE WHEN bps.stock = 0 THEN 1 ELSE 0 END), 0) AS out_of_stock_count
       FROM branch_pos_stock bps
       WHERE bps.branch_id = :branch_id"
    );
    $stmt->execute(['branch_id'=>$branchId,'threshold'=>$lowStockThreshold]);
    return $stmt->fetch() ?: ['product_count'=>0,'total_units'=>0,'low_stock_count'=>0,'out_of_stock_count'=>0];
  }

  public function getLowStockForBranch(int $branchId, int $lowStockThreshold = 5): array {
    $stmt=$this->db->prepare(
      "SELECT p.id AS product_id, p.name, p.status,
              NULLIF(bps.variant_size, '') AS variant_id,
              bps.variant_size AS size, bps.variant_color AS color,
              bps.stock AS stock
       FROM branch_pos_stock bps
       INNER JOIN products p ON p.id = bps.product_id
       WHERE bps.branch_id = :branch_id AND bps.stock <= :threshold
       ORDER BY bps.stock ASC, p.name ASC"
    );
    $stmt->execute(['branch_id'=>$branchId,'threshold'=>$lowStockThreshold]);
    return $stmt->fetchAll();
  }
}