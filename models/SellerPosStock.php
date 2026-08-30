<?php
require_once __DIR__ . '/../config/database.php';

class SellerPosStock {
  private PDO $db;
  public function __construct(){ $this->db=Database::getConnection(); }
  public function transfer(int $sellerId,int $productId,string $size,string $color,int $quantity,string $direction,int $userId,?string $note): array {
    if($quantity<1 || !in_array($direction,['inventory_to_pos','pos_to_inventory'],true)) return ['success'=>false,'error'=>'Invalid transfer.'];
    $this->db->beginTransaction(); try {
      $product=$this->db->prepare('SELECT stock FROM products WHERE id=:id AND seller_id=:seller_id FOR UPDATE'); $product->execute(['id'=>$productId,'seller_id'=>$sellerId]); $inventory=(int)$product->fetchColumn(); if($inventory===0 && $direction==='inventory_to_pos') throw new RuntimeException('Inventory product was not found or has no stock.');
      $variant=null; if($size!==''||$color!==''){ $v=$this->db->prepare('SELECT id,stock FROM product_variants WHERE product_id=:product_id AND size=:size AND color=:color FOR UPDATE');$v->execute(['product_id'=>$productId,'size'=>$size,'color'=>$color]);$variant=$v->fetch();if(!$variant)throw new RuntimeException('Variant not found.');$inventory=(int)$variant['stock']; }
      $pos=$this->db->prepare('SELECT stock FROM seller_pos_stock WHERE seller_id=:seller_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color FOR UPDATE');$pos->execute(['seller_id'=>$sellerId,'product_id'=>$productId,'size'=>$size,'color'=>$color]);$posStock=(int)$pos->fetchColumn();
      if($direction==='inventory_to_pos'){if($inventory<$quantity)throw new RuntimeException('Only '.$inventory.' units remain in Seller Inventory.');$this->db->prepare('UPDATE products SET stock=stock-:quantity WHERE id=:id')->execute(['quantity'=>$quantity,'id'=>$productId]);if($variant)$this->db->prepare('UPDATE product_variants SET stock=stock-:quantity WHERE id=:id')->execute(['quantity'=>$quantity,'id'=>$variant['id']]);$change=$quantity;}
      else {if($posStock<$quantity)throw new RuntimeException('Only '.$posStock.' units remain in Seller POS.');$this->db->prepare('UPDATE products SET stock=stock+:quantity WHERE id=:id')->execute(['quantity'=>$quantity,'id'=>$productId]);if($variant)$this->db->prepare('UPDATE product_variants SET stock=stock+:quantity WHERE id=:id')->execute(['quantity'=>$quantity,'id'=>$variant['id']]);$change=-$quantity;}
      $this->db->prepare('INSERT INTO seller_pos_stock (seller_id,product_id,variant_size,variant_color,stock) VALUES (:seller_id,:product_id,:size,:color,:stock) ON DUPLICATE KEY UPDATE stock=stock+:change')->execute(['seller_id'=>$sellerId,'product_id'=>$productId,'size'=>$size,'color'=>$color,'stock'=>$direction==='inventory_to_pos'?$quantity:0,'change'=>$change]);
      $this->db->prepare('INSERT INTO seller_inventory_transfers (seller_id,product_id,variant_size,variant_color,quantity,direction,performed_by_user_id,note) VALUES (:seller_id,:product_id,:size,:color,:quantity,:direction,:user_id,:note)')->execute(['seller_id'=>$sellerId,'product_id'=>$productId,'size'=>$size,'color'=>$color,'quantity'=>$quantity,'direction'=>$direction,'user_id'=>$userId,'note'=>$note?:null]);
      $this->db->commit();return ['success'=>true];
    }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();return ['success'=>false,'error'=>$e->getMessage()];}
  }
  public function posProducts(int $sellerId):array{$s=$this->db->prepare("SELECT p.*,sps.stock FROM seller_pos_stock sps INNER JOIN products p ON p.id=sps.product_id WHERE sps.seller_id=:seller_id AND sps.variant_size='' AND sps.variant_color='' AND sps.stock>0 AND p.status='active' ORDER BY p.name");$s->execute(['seller_id'=>$sellerId]);return $s->fetchAll();}
  /** Creates a separate POS listing from one master Seller Inventory product. */
  public function createListingFromInventory(int $sellerId,int $inventoryProductId,int $posProductId,int $quantity,int $userId): void {
    $this->db->beginTransaction(); try {
      $source=$this->db->prepare('SELECT stock FROM products WHERE id=:id AND seller_id=:seller_id FOR UPDATE');$source->execute(['id'=>$inventoryProductId,'seller_id'=>$sellerId]);$available=(int)$source->fetchColumn();if($available<$quantity)throw new RuntimeException('The selected Seller Inventory stock changed. Please try again.');
      $this->db->prepare('UPDATE products SET stock=stock-:quantity WHERE id=:id')->execute(['quantity'=>$quantity,'id'=>$inventoryProductId]);
      $this->db->prepare('UPDATE products SET stock=0 WHERE id=:id AND seller_id=:seller_id')->execute(['id'=>$posProductId,'seller_id'=>$sellerId]);
      $this->db->prepare('INSERT INTO seller_pos_stock (seller_id,product_id,variant_size,variant_color,stock) VALUES (:seller_id,:product_id,"","",:stock)')->execute(['seller_id'=>$sellerId,'product_id'=>$posProductId,'stock'=>$quantity]);
      $this->db->prepare('INSERT INTO seller_inventory_transfers (seller_id,product_id,variant_size,variant_color,quantity,direction,performed_by_user_id,note) VALUES (:seller_id,:product_id,"","",:quantity,"inventory_to_pos",:user_id,:note)')->execute(['seller_id'=>$sellerId,'product_id'=>$posProductId,'quantity'=>$quantity,'user_id'=>$userId,'note'=>'Created POS listing from Seller Inventory product #'.$inventoryProductId]);
      $this->db->commit();
    }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
  }
}
