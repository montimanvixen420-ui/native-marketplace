<div class="flex min-h-screen bg-gray-50 dark:bg-ink"><?php require __DIR__.'/../../partials/admin-sidebar.php'; ?>
<main class="flex-1 px-5 py-7 md:px-8">
    <h1 class="font-display text-2xl font-semibold text-gray-900">Seller inventory</h1>
    <p class="mt-1 text-sm text-gray-500">Master stock. Transfer units to Seller POS or allocate them to branches from this inventory.</p>
    <?php if(!empty($error)):?>
        <p class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?=htmlspecialchars($error)?></p>
        <?php endif;?>
        <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white">
            <table id="sellerInventoryTable" data-datatable class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-xs uppercase text-gray-500">
                        <th class="p-3">Product</th>
                        <th class="p-3">Inventory stock</th>
                        <th class="p-3">Transfer to POS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): if((int)$p['stock']<1)continue;?>
                        <tr class="border-b">
                            <td class="p-3 font-medium"><?=htmlspecialchars($p['name'])?></td>
                            <td class="p-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 font-semibold text-blue-700">
                                    <i data-lucide="warehouse" class="h-3.5 w-3.5"></i><?= (int)$p['stock']?> in inventory</span>
                                </td>
                                <td class="p-3">
                                    <form action="/admin/inventory/transfer" method="post" class="js-confirm-form flex gap-2" data-title="Transfer stock to Seller POS?" data-text="This moves stock out of Seller Inventory and into Seller POS." data-confirm-text="Yes, transfer" data-confirm-color="#2563EB">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id']?>">
                                        <input type="hidden" name="direction" value="inventory_to_pos">
                                        <input required min="1" max="<?= (int)$p['stock']?>" name="quantity" type="number" placeholder="Qty" class="w-20 rounded border px-2">
                                        <button class="inline-flex items-center rounded bg-teal px-3 py-1 text-white"><i data-lucide="arrow-right-left" class="mr-1 h-4 w-4">                                         
                                        </i>To POS
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>
            <h2 class="mt-8 font-display text-xl font-semibold text-gray-900">Seller POS stock</h2>
            <div class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-white">
                <table id="sellerPosStockTable" data-datatable class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-xs uppercase text-gray-500">
                            <th class="p-3">Product</th><th class="p-3">POS stock</th>
                            <th class="p-3">Return</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($posProducts as $p):?>
                            <tr class="border-b">
                                <td class="p-3 font-medium"><?=htmlspecialchars($p['name'])?></td>
                                <td class="p-3"><?= (int)$p['stock']?> in POS</td>
                                <td class="p-3">
                                    <form action="/admin/inventory/transfer" method="post" class="js-confirm-form flex gap-2" data-title="Return stock to Seller Inventory?" data-text="This moves the selected Seller POS stock back to Inventory." data-confirm-text="Yes, return" data-confirm-color="#2563EB"><input type="hidden" name="product_id" value="<?= (int)$p['id']?>">
                                    <input type="hidden" name="direction" value="pos_to_inventory">
                                    <input required min="1" max="<?= (int)$p['stock']?>" name="quantity" type="number" placeholder="Qty" class="w-20 rounded border px-2">
                                    <button class="inline-flex items-center rounded border border-teal px-3 py-1 text-teal"><i data-lucide="undo-2" class="mr-1 h-4 w-4"></i>Return</button>
                                </form>
                            </td>
                        </tr><?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
