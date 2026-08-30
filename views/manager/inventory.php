<div class="flex min-h-screen bg-gray-50"><?php require __DIR__.'/../partials/admin-sidebar.php';?>
<main class="flex-1 px-5 py-7 md:px-8">
    <h1 class="font-display text-2xl font-semibold text-gray-900">Branch inventory</h1>
    <p class="mt-1 text-sm text-gray-500"><?= $profile['position']==='branch_manager' ? 'Monitor stock assigned to your branch. Add products to Branch POS from the Branch POS Products page.' : 'Review branch stock and report any damaged items to your Branch Manager.' ?></p>
    <?php if(!empty($error)): ?><div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <?php if(!empty($success)): ?><div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"><?=htmlspecialchars($success)?></div><?php endif; ?>
    <div class="mt-6 overflow-hidden rounded-lg border bg-white">
        <table id="branchInventoryTable" data-datatable class="w-full text-sm">
            <thead><tr class="border-b text-left text-xs uppercase text-gray-500">
                <th class="p-3">Product</th>
                <th class="p-3">Branch inventory</th>
                <?php if($profile['position']==='inventory_staff'): ?><th class="p-3">Action</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach($rows as $r):if(!(int)$r['stock'])continue;?>
            <tr class="border-b">
                <td class="p-3 font-medium"><?=htmlspecialchars($r['product_name'])?></td>
                <td class="p-3"><?= (int)$r['stock']?></td>
                <?php if($profile['position']==='inventory_staff'): ?><td class="p-3">
                    <form method="post" action="/manager/damage-reports" class="js-confirm-form mt-2 flex gap-2" data-title="Report damaged item?" data-text="This does not deduct stock yet. Your Branch Manager must review and confirm it." data-confirm-text="Send report">
                      <input type="hidden" name="product_id" value="<?= (int)$r['product_id']?>"><input type="hidden" name="variant_size" value="<?=htmlspecialchars($r['size']??'')?>"><input type="hidden" name="variant_color" value="<?=htmlspecialchars($r['color']??'')?>">
                      <input required min="1" max="<?= (int)$r['stock']?>" name="quantity" type="number" placeholder="Damaged qty" class="w-28 rounded border px-2"><input name="note" maxlength="1000" placeholder="Damage note" class="w-40 rounded border px-2">
                      <button type="submit" class="report-damage-btn inline-flex items-center gap-1 rounded px-3 py-1"><i data-lucide="triangle-alert" class="h-4 w-4"></i>Report damage</button>
                    </form>
                    </td><?php endif; ?>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
</main>
</div>
<style>
  .report-damage-btn { background: #f59e0b; color: #fff; font-weight: 600; border: 1px solid #d97706; }
  .report-damage-btn:hover { background: #d97706; }
</style>
