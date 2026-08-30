<div class="flex min-h-screen bg-gray-50">
  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Stock Adjustments</h1>
        <p class="text-sm text-gray-500">Every branch stock change, who made it, and why.</p>
      </div>
      <button type="button" id="openAddAdjustment" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-ink text-white hover:bg-gray-800">
        <i data-lucide="plus" class="h-3.5 w-3.5"></i> New Adjustment
      </button>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="GET" action="/admin/stock-adjustments" class="mb-5 flex flex-wrap gap-3">
      <select name="branch" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <option value="">All branches</option>
        <?php foreach ($branches as $b): ?>
          <option value="<?= (int) $b['id'] ?>" <?= (int) $branchFilter === (int) $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="product" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <option value="">All products</option>
        <?php foreach ($products as $p): ?>
          <option value="<?= (int) $p['id'] ?>" <?= (int) $productFilter === (int) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if (empty($adjustments)): ?>
      <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
        <p class="font-display text-lg font-semibold text-gray-900">No adjustments yet</p>
        <p class="text-sm text-gray-500 mt-1">Stock changes made by you or your branch managers will show up here.</p>
      </div>
    <?php else: ?>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table id="adjustmentsTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
              <th class="px-5 py-3 font-semibold">Product</th>
              <th class="px-3 py-3 font-semibold">Branch</th>
              <th class="px-3 py-3 font-semibold text-right">Change</th>
              <th class="px-3 py-3 font-semibold">Reason</th>
              <th class="px-3 py-3 font-semibold">By</th>
              <th class="px-5 py-3 font-semibold">Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($adjustments as $adj): ?>
              <?php $delta = (int) $adj['change_amount']; ?>
              <tr class="border-b border-gray-100 last:border-0">
                <td class="px-5 py-3.5 font-medium text-gray-900">
                  <?= htmlspecialchars($adj['product_name']) ?>
                  <?php if (!empty($adj['variant_size']) || !empty($adj['variant_color'])): ?>
                    <p class="text-xs font-normal text-gray-500"><?= htmlspecialchars(trim($adj['variant_size'] . ' ' . $adj['variant_color'])) ?></p>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-3.5 text-gray-500"><?= htmlspecialchars($adj['branch_name']) ?></td>
                <td class="px-3 py-3.5 text-right font-semibold <?= $delta > 0 ? 'text-teal' : ($delta < 0 ? 'text-red-600' : 'text-gray-400') ?>">
                  <?= $delta > 0 ? '+' : '' ?><?= $delta ?>
                  <span class="block text-[11px] font-normal text-gray-400"><?= (int) $adj['previous_stock'] ?> &rarr; <?= (int) $adj['new_stock'] ?></span>
                </td>
                <td class="px-3 py-3.5 text-gray-700"><?= htmlspecialchars($reasons[$adj['reason']] ?? ucfirst($adj['reason'])) ?>
                  <?php if (!empty($adj['note'])): ?><p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($adj['note']) ?></p><?php endif; ?>
                </td>
                <td class="px-3 py-3.5 text-gray-500">
                  <?= htmlspecialchars($adj['adjusted_by_name']) ?>
                  <span class="block text-[11px] text-gray-400"><?= $adj['adjusted_by_role'] === 'manager' ? 'Branch Manager' : ($adj['adjusted_by_role'] === 'admin' ? 'You' : 'System') ?></span>
                </td>
                <td class="px-5 py-3.5 text-gray-500"><?= date('M j, Y g:ia', strtotime($adj['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>

<!-- ── New adjustment modal ─────────────────────────────── -->
<div id="addAdjustmentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <div class="w-full max-w-md rounded-2xl bg-white p-6">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="font-display text-lg font-bold text-gray-900">New Stock Adjustment</h3>
      <button type="button" id="addAdjustmentClose" class="text-gray-400 hover:text-gray-600">✕</button>
    </div>
    <form method="POST" action="/admin/stock-adjustments/adjust">
      <label class="block text-xs font-medium text-gray-500 mb-1.5">Branch</label>
      <select required name="branch_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-3">
        <option value="">Select branch</option>
        <?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
      </select>

      <label class="block text-xs font-medium text-gray-500 mb-1.5">Product</label>
      <select required name="product_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-3">
        <option value="">Select product</option>
        <?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option><?php endforeach; ?>
      </select>
      <p class="text-[11px] text-gray-400 -mt-2 mb-3">Product must already be assigned to the selected branch.</p>

      <div class="grid grid-cols-2 gap-3 mb-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1.5">Size (if variant)</label>
          <input type="text" name="variant_size" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Leave blank if none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1.5">Color (if variant)</label>
          <input type="text" name="variant_color" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Leave blank if none">
        </div>
      </div>

      <label class="block text-xs font-medium text-gray-500 mb-1.5">New stock quantity</label>
      <input required type="number" min="0" name="stock" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-3">

      <label class="block text-xs font-medium text-gray-500 mb-1.5">Reason</label>
      <select required name="reason" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-3">
        <option value="">Select a reason</option>
        <?php foreach ($reasons as $key => $label): ?><option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option><?php endforeach; ?>
      </select>

      <label class="block text-xs font-medium text-gray-500 mb-1.5">Note (optional)</label>
      <textarea name="note" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-4"></textarea>

      <button type="submit" class="w-full rounded-lg bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">Save adjustment</button>
    </form>
  </div>
</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
  <?php if (!empty($adjustments)): ?>
  $('#adjustmentsTable').DataTable({
    searching: false, paging: true, pageLength: 5, lengthMenu: [5, 10, 25, 50],
    order: [],
  });
  <?php endif; ?>
  lucide.createIcons();

  var modal = document.getElementById('addAdjustmentModal');
  document.getElementById('openAddAdjustment').addEventListener('click', function () {
    modal.classList.remove('hidden'); modal.classList.add('flex');
  });
  document.getElementById('addAdjustmentClose').addEventListener('click', function () {
    modal.classList.add('hidden'); modal.classList.remove('flex');
  });
});
</script>