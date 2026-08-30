<style>
  .icon-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e5e7eb; color:#4b5563; }
  .icon-btn:hover { background:#f9fafb; }
  .stock-pill { display:inline-flex; align-items:center; padding:2px 10px; border-radius:9999px; font-size:12px; font-weight:700; }
</style>

<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <main class="flex-1 px-5 py-7 md:px-8">
    <header class="mb-6">
      <p class="eyebrow text-brand">Branch Stock</p>
      <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white"><?= htmlspecialchars($profile['branch_name'] ?? 'Your Branch') ?></h1>
      <p class="mt-2 text-sm text-gray-500 dark:text-white/60">
        Adjust stock for products your branch carries. Every change needs a reason — your Seller can see the full history.
      </p>
    </header>

    <?php if (!empty($error)): ?>
      <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($rows)): ?>
      <div class="bg-white border border-gray-200 rounded-lg p-12 text-center dark:bg-ink-2 dark:border-white/10">
        <p class="font-display text-lg font-semibold text-ink dark:text-white">No products yet</p>
        <p class="text-sm text-gray-500 mt-1">Your Seller hasn't assigned any products to this branch yet.</p>
      </div>
    <?php else: ?>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden dark:bg-ink-2 dark:border-white/10">
        <table id="stockTable" class="w-full text-sm" data-datatable>
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200 dark:border-white/10">
              <th class="px-5 py-3 font-semibold">Product</th>
              <th class="px-3 py-3 font-semibold">Variant</th>
              <th class="px-3 py-3 font-semibold text-right">Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <?php $stock = (int) $row['stock']; ?>
              <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                <td class="px-5 py-3.5 font-medium text-ink dark:text-white"><?= htmlspecialchars($row['product_name']) ?></td>
                <td class="px-3 py-3.5 text-gray-500">
                  <?= (!empty($row['size']) || !empty($row['color'])) ? htmlspecialchars(trim(($row['size'] ?? '') . ' ' . ($row['color'] ?? ''))) : '—' ?>
                </td>
                <td class="px-3 py-3.5 text-right">
                  <span class="stock-pill <?= $stock === 0 ? 'bg-red-100 text-red-600' : ($stock <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-teal-light text-teal') ?>">
                    <?= $stock ?>
                  </span>
                </td>
                
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>

<!-- ── Adjust stock modal ─────────────────────────────── -->
<div id="adjustStockModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <div class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-ink-2">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="font-display text-lg font-bold text-ink dark:text-white">Adjust Stock</h3>
      <button type="button" id="adjustStockClose" class="text-gray-400 hover:text-gray-600">✕</button>
    </div>
    <form method="POST" action="/manager/stock/adjust" class="js-confirm-form"
          data-title="Save this stock adjustment?" data-text="This will update your branch's live stock count."
          data-icon="question" data-confirm-text="Yes, save it" data-confirm-color="#0d9488">
      <input type="hidden" name="product_id" id="adjustProductId">
      <input type="hidden" name="variant_size" id="adjustVariantSize">
      <input type="hidden" name="variant_color" id="adjustVariantColor">

      <p id="adjustProductLabel" class="text-sm font-medium text-ink dark:text-white mb-1"></p>
      <p class="text-xs text-gray-500 mb-4">Current stock: <span id="adjustCurrentStock" class="font-semibold"></span></p>

      <label class="block text-xs font-medium text-gray-500 mb-1.5">New stock quantity</label>
      <input required type="number" min="0" name="stock" id="adjustNewStock" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-4">

      <label class="block text-xs font-medium text-gray-500 mb-1.5">Reason</label>
      <select required name="reason" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-4">
        <option value="">Select a reason</option>
        <?php foreach ($reasons as $key => $label): ?>
          <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>

      <label class="block text-xs font-medium text-gray-500 mb-1.5">Note (optional)</label>
      <textarea name="note" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-4" placeholder="Any extra detail your Seller should know"></textarea>

      <button type="submit" class="w-full rounded-lg bg-teal px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90">Save adjustment</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var modal = document.getElementById('adjustStockModal');
  document.querySelectorAll('.js-adjust-stock').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var d = btn.dataset;
      document.getElementById('adjustProductId').value = d.productId;
      document.getElementById('adjustVariantSize').value = d.size || '';
      document.getElementById('adjustVariantColor').value = d.color || '';
      var variantLabel = (d.size || d.color) ? (' — ' + [d.size, d.color].filter(Boolean).join(' ')) : '';
      document.getElementById('adjustProductLabel').textContent = d.productName + variantLabel;
      document.getElementById('adjustCurrentStock').textContent = d.currentStock;
      document.getElementById('adjustNewStock').value = d.currentStock;
      modal.classList.remove('hidden'); modal.classList.add('flex');
    });
  });
  document.getElementById('adjustStockClose').addEventListener('click', function () {
    modal.classList.add('hidden'); modal.classList.remove('flex');
  });
});
</script>
