<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BranchAllocation.php';
class BranchPosStock {
  private PDO $db; private BranchAllocation $allocations; public function __construct(){ $this->db=Database::getConnection();$this->allocations=new BranchAllocation(); }
  public function transfer(int $branchId,int $productId,string $size,string $color,int $quantity,string $direction,int $userId,bool $isManager,?string $note):array{
    if($quantity<1||!in_array($direction,['inventory_to_pos','pos_to_inventory','inventory_to_seller'],true))return ['success'=>false,'error'=>'Invalid transfer.'];
    if($direction==='inventory_to_seller'&&!$isManager)return ['success'=>false,'error'=>'Only the Branch Manager can return stock to Seller Inventory.'];
    $this->db->beginTransaction();try{
      $inv=$this->db->prepare('SELECT stock FROM branch_stock WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color FOR UPDATE');$inv->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);$inventory=(int)$inv->fetchColumn();
      $pos=$this->db->prepare('SELECT stock FROM branch_pos_stock WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color FOR UPDATE');$pos->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);$posStock=(int)$pos->fetchColumn();
      if($direction==='inventory_to_pos'||$direction==='inventory_to_seller'){if($inventory<$quantity)throw new RuntimeException('Not enough Branch Inventory stock.');$this->db->prepare('UPDATE branch_stock SET stock=stock-:quantity WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color')->execute(['quantity'=>$quantity,'branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);$this->allocations->consume($branchId,$productId,$size,$color,$quantity);}
      else {if($posStock<$quantity)throw new RuntimeException('Not enough Branch POS stock.');$this->db->prepare('UPDATE branch_pos_stock SET stock=stock-:quantity WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color')->execute(['quantity'=>$quantity,'branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);}
      if($direction==='inventory_to_pos'||$direction==='pos_to_inventory')$this->db->prepare('INSERT INTO branch_pos_stock (branch_id,product_id,variant_size,variant_color,stock) VALUES (:branch_id,:product_id,:size,:color,:stock) ON DUPLICATE KEY UPDATE stock=stock+:change')->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color,'stock'=>$direction==='inventory_to_pos'?$quantity:0,'change'=>$direction==='inventory_to_pos'?$quantity:-$quantity]);
      if($direction==='pos_to_inventory'){$this->db->prepare('UPDATE branch_stock SET stock=stock+:quantity WHERE branch_id=:branch_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color')->execute(['quantity'=>$quantity,'branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);$this->allocations->restore($branchId,$productId,$size,$color,$quantity);}
      if($direction==='inventory_to_seller'){$seller=$this->db->prepare('SELECT seller_id FROM branches WHERE id=:id');$seller->execute(['id'=>$branchId]);$sellerId=(int)$seller->fetchColumn();$this->db->prepare('UPDATE products SET stock=stock+:quantity WHERE id=:id')->execute(['quantity'=>$quantity,'id'=>$productId]);if($size!==''||$color!=='')$this->db->prepare('UPDATE product_variants SET stock=stock+:quantity WHERE product_id=:product_id AND size=:size AND color=:color')->execute(['quantity'=>$quantity,'product_id'=>$productId,'size'=>$size,'color'=>$color]);}
      $this->db->prepare('INSERT INTO branch_inventory_transfers (branch_id,product_id,variant_size,variant_color,quantity,direction,performed_by_user_id,note) VALUES (:branch_id,:product_id,:size,:color,:quantity,:direction,:user_id,:note)')->execute(['branch_id'=>$branchId,'product_id'=>$productId,'size'=>$size,'color'=>$color,'quantity'=>$quantity,'direction'=>$direction,'user_id'=>$userId,'note'=>$note?:null]);$this->db->commit();return ['success'=>true];
    }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();return ['success'=>false,'error'=>$e->getMessage()];}
  }
  public function productsForBranch(int $branchId): array {
    $stmt=$this->db->prepare("SELECT p.id AS product_id,p.name,p.price,p.image_url,p.status,bps.variant_size,bps.variant_color,bps.stock FROM branch_pos_stock bps INNER JOIN products p ON p.id=bps.product_id WHERE bps.branch_id=:branch_id AND bps.stock>0 ORDER BY p.name,bps.variant_color,bps.variant_size");
    $stmt->execute(['branch_id'=>$branchId]);return $stmt->fetchAll();
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
}
