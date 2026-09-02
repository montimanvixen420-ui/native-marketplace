<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">My products</h1>
        <p class="text-sm text-gray-500">Create and manage your product catalog. To sell an existing product in POS, transfer it from Seller Inventory.</p>
      </div>
      <a href="/products/create" class="inline-flex items-center gap-1.5 bg-teal text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i> Add product
      </a>
    </div>

    <?php if (!empty($error)): ?>
      <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <p class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <!-- Active / Archived tabs -->
    <div class="flex items-center gap-2 mb-6 border-b border-gray-200">
      <a href="/products" class="px-3 py-2 text-sm font-semibold border-b-2 -mb-px transition-colors <?= $productsView === 'active' ? 'border-teal text-teal' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Active</a>
      <a href="/products?view=archived" class="px-3 py-2 text-sm font-semibold border-b-2 -mb-px transition-colors <?= $productsView === 'archived' ? 'border-teal text-teal' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Archived</a>
    </div>

    <!-- Empty State or Table -->
    <?php if (empty($products)): ?>
      <div class="bg-white border border-gray-200 rounded-lg p-12 text-center shadow-sm">
        <?php if ($productsView === 'archived'): ?>
          <p class="font-display text-lg font-semibold text-gray-900">No archived products</p>
          <p class="text-sm text-gray-500 mt-1">Products you delete show up here, in case you want them back.</p>
        <?php else: ?>
          <p class="font-display text-lg font-semibold text-gray-900">Nothing on your shelf yet</p>
          <p class="text-sm text-gray-500 mt-1 mb-5">Add your first product to start selling.</p>
          <a href="/products/create" class="inline-flex items-center gap-1.5 bg-teal text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition">
            <i data-lucide="plus" class="w-4 h-4"></i> Add product
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden p-4 shadow-sm">

        <!-- Category filter pills -->
        <div id="productCategoryPills" class="flex items-center gap-2 flex-wrap mb-4">
          <button type="button" onclick="sellerProductsFilterCategory('', this)" data-category="" class="product-cat-pill text-xs font-medium px-3 py-1.5 rounded-full bg-teal text-white transition-colors">All Products</button>
          <?php foreach ($categories as $cat): ?>
            <button type="button" onclick="sellerProductsFilterCategory('<?= htmlspecialchars(addslashes($cat)) ?>', this)" data-category="<?= htmlspecialchars($cat) ?>" class="product-cat-pill text-xs font-medium px-3 py-1.5 rounded-full text-gray-500 hover:bg-gray-100 transition-colors">
              <?= htmlspecialchars($cat) ?>
            </button>
          <?php endforeach; ?>
        </div>

        <table id="productsTable" data-datatable class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
              <th class="px-5 py-3 font-semibold">Product</th>
              <th class="px-3 py-3 font-semibold">Category</th>
              <th class="px-3 py-3 font-semibold text-right">Price</th>
              <th class="px-3 py-3 font-semibold text-right">Stock remaining</th>
              <th class="px-3 py-3 font-semibold text-center">Status</th>
              <th class="px-5 py-3 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <?php 
                $stock = (int) $product['stock'];
                $status = strtolower($product['status']);

                // Smart Inventory Status Logic with Inline Style Colors
                if ($status === 'archived') {
                    $badgeText = 'Archived';
                    $badgeStyle = 'background-color: #6b7280; color: #ffffff;'; // Gray
                } elseif ($stock === 0) {
                    $badgeText = 'Out of stock';
                    $badgeStyle = 'background-color: #be123c; color: #ffffff;'; // Solid Red
                } elseif ($stock <= 5 && $status === 'active') {
                    $badgeText = 'Low stock';
                    $badgeStyle = 'background-color: #fbbf24; color: #451a03;'; // Solid Amber/Yellow
                } elseif ($status === 'active') {
                    $badgeText = 'Available';
                    $badgeStyle = 'background-color: #059669; color: #ffffff;'; // Solid Emerald Green
                } elseif ($status === 'pending_review') {
                    $badgeText = 'Pending review';
                    $badgeStyle = 'background-color: #f59e0b; color: #ffffff;'; // Amber
                } elseif ($status === 'rejected') {
                    $badgeText = 'Rejected';
                    $badgeStyle = 'background-color: #9f1239; color: #ffffff;'; // Dark Red
                } else {
                    $badgeText = 'Hidden';
                    $badgeStyle = 'background-color: #6b7280; color: #ffffff;'; // Gray
                }
              ?>
              <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/50">
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <?php if (!empty($product['image_url'])): ?>
                      <img src="/<?= htmlspecialchars($product['image_url']) ?>" alt="" class="w-9 h-9 rounded-lg object-cover border border-gray-200">
                    <?php else: ?>
                      <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-xs text-gray-400">—</div>
                    <?php endif; ?>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></span>
                  </div>
                </td>
                <td class="px-3 py-3.5 text-gray-500" data-category="<?= htmlspecialchars($product['category'] ?? '') ?>"><?= htmlspecialchars($product['category'] ?? '—') ?></td>
                <td class="px-3 py-3.5 text-right font-semibold text-gray-900">₱<?= number_format((float) $product['price'], 2) ?></td>
                <td class="px-3 py-3.5 text-right font-medium <?= $stock === 0 ? 'text-red-600 font-bold' : ($stock <= 5 ? 'text-amber-700 font-bold' : 'text-gray-700') ?>">
                  <?= $stock ?> pcs
                </td>
                <td class="px-3 py-3.5 text-center">
                  <span style="<?= $badgeStyle ?>" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">
                    <?= $badgeText ?>
                  </span>
                </td>
                <td class="px-5 py-3.5 text-right">
                  <div class="flex items-center justify-end gap-3">
                    <?php if ($productsView === 'archived'): ?>
                      <form method="POST" action="/products/restore" class="js-confirm-form inline"
                        data-title="Restore this product?"
                        data-text="It will show up in your active catalog again."
                        data-icon="question"
                        data-confirm-text="Yes, restore"
                        data-confirm-color="#0d9488">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-teal-100 text-teal bg-teal-50 hover:bg-teal-100 shadow-sm transition-all">
                          <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Restore
                        </button>
                      </form>
                    <?php else: ?>
                      <a href="/products/edit?id=<?= $product['id'] ?>" class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-all">
                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-500"></i> Edit
                      </a>
                      <form method="POST" action="/products/delete" class="js-confirm-form inline"
                        data-title="Delete this product?"
                        data-text="It will move to Archived and disappear from your shop. You can restore it later."
                        data-icon="warning"
                        data-confirm-text="Yes, delete"
                        data-confirm-color="#dc2626">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-red-100 text-red-600 bg-red-50 hover:bg-red-100 shadow-sm transition-all">
                          <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

</div>

<script>
// Category pill filtering. The table itself is initialized automatically
// (see views/layouts/footer.php's global `table[data-datatable]` handler),
// so we just grab its existing DataTable API instance when a pill is clicked.
function sellerProductsFilterCategory(category, btnEl) {
  document.querySelectorAll('.product-cat-pill').forEach(function (pill) {
    pill.classList.remove('bg-teal', 'text-white');
    pill.classList.add('text-gray-500');
  });
  btnEl.classList.add('bg-teal', 'text-white');
  btnEl.classList.remove('text-gray-500');

  var table = window.jQuery && window.jQuery.fn.DataTable.isDataTable('#productsTable')
    ? window.jQuery('#productsTable').DataTable()
    : null;
  if (!table) return;

  var needle = category ? '^' + window.jQuery.fn.dataTable.util.escapeRegex(category) + '$' : '';
  table.column(1).search(needle, true, false).draw();
}
</script>