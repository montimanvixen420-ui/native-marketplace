<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__.'/../../partials/admin-sidebar.php';?>
  <main class="flex-1 px-5 py-7 md:px-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="font-display text-2xl font-semibold text-gray-900">Branch POS products</h1>
        <p class="mt-1 text-sm text-gray-500">Products currently available for sale at <?=htmlspecialchars($profile['branch_name'])?>.</p>
      </div>
      <a href="/manager/products/create" class="inline-flex items-center rounded-lg bg-teal px-4 py-2.5 text-sm font-semibold text-white">
        <i data-lucide="plus" class="mr-1.5 h-4 w-4"></i>Add product
      </a>
    </div>

    <?php if(!empty($error)):?>
      <p class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?=htmlspecialchars($error)?></p>
    <?php endif;?>
    <?php if(!empty($success)):?>
      <p class="mt-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700"><?=htmlspecialchars($success)?></p>
    <?php endif;?>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white">
      <table id="branchProductsTable" data-datatable class="w-full text-sm">
        <thead>
          <tr class="border-b text-left text-xs uppercase text-gray-500">
            <th class="p-3">Product</th>
            <th class="p-3">Variant</th>
            <th class="p-3">Price</th>
            <th class="p-3">Branch POS stock</th>
            <th class="p-3">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($products as $product):?>
            <tr class="border-b">
              <td class="p-3 font-medium">
                <div class="flex items-center gap-3">
                  <?php if (!empty($product['image_url'])): ?>
                    <img src="/<?= htmlspecialchars($product['image_url']) ?>" alt="" class="w-9 h-9 rounded-lg object-cover border border-gray-200">
                  <?php else: ?>
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-xs text-gray-400">—</div>
                  <?php endif; ?>
                  <span><?=htmlspecialchars($product['name'])?></span>
                </div>
              </td>
              <td class="p-3"><?=htmlspecialchars(trim($product['variant_size'].' / '.$product['variant_color'],' /')?:'—')?></td>
              <td class="p-3">₱<?=number_format((float)$product['price'],2)?></td>
              <td class="p-3">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700">
                  <i data-lucide="store" class="h-3.5 w-3.5"></i><?= (int)$product['stock']?> in POS
                </span>
              </td>
              <td class="p-3">
                <div class="flex items-center gap-3">
                  <a href="/manager/products/edit?id=<?= (int)$product['product_id']?>"
                     class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-all">
                    <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-500"></i> Edit
                  </a>
                  <form action="/manager/products/return" method="post" class="js-confirm-form inline"
                        data-title="Delete this product from Branch POS?"
                        data-text="This will return all stock (<?= (int)$product['stock']?> pcs) back to Branch Inventory. This cannot be undone."
                        data-icon="error"
                        data-confirm-text="Yes, delete"
                        data-confirm-color="#dc2626">
                    <input type="hidden" name="product_id" value="<?= (int)$product['product_id']?>">
                    <input type="hidden" name="variant_size" value="<?=htmlspecialchars($product['variant_size'])?>">
                    <input type="hidden" name="variant_color" value="<?=htmlspecialchars($product['variant_color'])?>">
                    <input type="hidden" name="quantity" value="<?= (int)$product['stock']?>">
                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-red-100 text-red-600 bg-red-50 hover:bg-red-100 shadow-sm transition-all">
                      <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </main>
</div>