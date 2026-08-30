<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="max-w-xl mx-auto">
      
      <!-- Back Link -->
      <a href="/stock-requests" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors mb-3">
        &larr; Back to stock requests
      </a>

      <!-- Page Header -->
      <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white mb-6">
        New stock request
      </h1>

      <!-- Error Alert -->
      <?php if (!empty($error)): ?>
        <div class="mb-5 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/40 px-4 py-3 text-sm text-red-700 dark:text-red-300 flex items-center gap-2">
          <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- Warning Alert if No Supplies -->
      <?php if (empty($supplies)): ?>
        <div class="mb-5 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-800 dark:text-amber-300 flex items-center gap-2">
          <i data-lucide="info" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0"></i>
          <span>No available supplier supplies yet. Suppliers must add stock first.</span>
        </div>
      <?php endif; ?>

      <!-- Main Form Card -->
      <form method="POST" action="/stock-requests/store" class="space-y-5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 shadow-sm">
        
        <!-- Supplier Select -->
        <div>
          <label for="supplier_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            Supplier
          </label>
          <select id="supplier_id" name="supplier_id" required class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition">
            <option value="">Select a supplier...</option>
            <?php 
              $supplierOptions = []; 
              foreach($supplies as $supply) $supplierOptions[$supply['supplier_id']] = $supply['supplier_name']; 
              foreach($supplierOptions as $id => $supplierName): 
            ?>
              <option value="<?= (int)$id ?>"><?= htmlspecialchars($supplierName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Available Supply Select -->
        <div>
          <label for="supply_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            Available supply
          </label>
          <select id="supply_id" name="supply_id" required disabled class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed transition">
            <option value="">Choose a supplier first...</option>
            <?php foreach($supplies as $supply): ?>
              <option value="<?= (int)$supply['id'] ?>" data-supplier="<?= (int)$supply['supplier_id'] ?>" data-stock="<?= (int)$supply['quantity_available'] ?>" hidden>
                <?= htmlspecialchars($supply['item_name']) ?> — ₱<?= number_format((float)$supply['unit_price'], 2) ?>/<?= htmlspecialchars($supply['unit']) ?> (<?= (int)$supply['quantity_available'] ?> available)
              </option>
            <?php endforeach; ?>
          </select>
          <p id="stock-help" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            Select a supplier to view their in-stock supplies.
          </p>
        </div>

        <!-- Quantity Input -->
        <div>
          <label for="quantity" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            Quantity
          </label>
          <input id="quantity" name="quantity" type="number" min="1" required placeholder="e.g. 50" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition" />
        </div>

        <!-- Note Input -->
        <div>
          <label for="note" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            Note (optional)
          </label>
          <textarea id="note" name="note" rows="3" placeholder="Any extra detail for the supplier..." class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <button type="submit" style="background-color: #059669 !important; color: #ffffff !important;" class="w-full rounded-xl py-3 text-sm font-bold shadow-md hover:opacity-90 active:scale-95 transition-all cursor-pointer">
            Send request
          </button>
        </div>

      </form>
    </div>
  </main>

</div>

<!-- Lucide icons initialization -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
const supplierSelect = document.getElementById('supplier_id');
const supplySelect = document.getElementById('supply_id');
const stockHelp = document.getElementById('stock-help');

function filterSupplies() {
  const supplierId = supplierSelect.value;
  supplySelect.disabled = !supplierId;
  supplySelect.value = '';
  
  for (const option of supplySelect.options) {
    if (!option.value) continue;
    option.hidden = option.dataset.supplier !== supplierId;
  }
  
  stockHelp.textContent = supplierId 
    ? "Choose one of this supplier's available supplies." 
    : 'Select a supplier to view their in-stock supplies.';
}

supplierSelect.addEventListener('change', filterSupplies);

supplySelect.addEventListener('change', () => {
  const option = supplySelect.selectedOptions[0];
  if (option?.value) {
    stockHelp.textContent = `${option.dataset.stock} available.`;
  }
});

lucide.createIcons();
</script>