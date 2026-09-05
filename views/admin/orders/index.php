<div class="flex min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-4 sm:px-8 py-8 overflow-y-auto">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mb-1">Menu <span class="mx-1">/</span> <span class="text-indigo-600 dark:text-indigo-400 font-semibold">Orders</span></p>
        <h1 class="font-display font-bold text-2xl text-slate-900 dark:text-white">Orders</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          <?php if ($branchFilter === 'none'): ?>
            Showing orders from <span class="font-semibold text-slate-700 dark:text-slate-300">your own Point of Sale Orders</span> (no branch attached).
          <?php elseif ($branchFilter === ''): ?>
            Showing orders from <span class="font-semibold text-slate-700 dark:text-slate-300">all branches</span>, plus your own POS Orders.
          <?php else: ?>
            <?php
              $selectedBranchName = 'the selected branch';
              foreach ($branches as $b) { if ((string) $b['id'] === $branchFilter) { $selectedBranchName = $b['name']; break; } }
            ?>
            Showing orders for <span class="font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($selectedBranchName) ?></span> only.
          <?php endif; ?>
        </p>
      </div>
      <div>
        <label for="orderBranchFilter" class="sr-only">Filter by branch</label>
        <select id="orderBranchFilter" onchange="location.href = '/admin/orders' + (this.value !== '' ? '?branch=' + encodeURIComponent(this.value) : '?branch=')" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="none" <?= $branchFilter === 'none' ? 'selected' : '' ?>>My POS  Orders</option>
          <option value="" <?= $branchFilter === '' ? 'selected' : '' ?>>All branches</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= (int) $branch['id'] ?>" <?= $branchFilter === (string) $branch['id'] ? 'selected' : '' ?>><?= htmlspecialchars($branch['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Alert Banner -->
    <?php if (!empty($pendingOnlineOrders) && $pendingOnlineOrders > 0): ?>
      <div class="mb-5 rounded-2xl border border-amber-300 dark:border-amber-700 bg-amber-100/80 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-900 dark:text-amber-300 flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0"></i>
        <span><strong class="font-bold"><?= (int) $pendingOnlineOrders ?></strong> online order(s) need processing.</span>
      </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-12 text-center shadow-sm">
        <p class="font-display text-lg font-semibold text-slate-900 dark:text-white">No orders yet</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Orders placed here will show up in this list.</p>
      </div>
    <?php else: ?>
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden p-5 shadow-sm">
        
        <!-- Top Custom Search & Entries Toolbar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-4">
          <div class="relative w-full sm:w-72">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="customSearchInput" placeholder="Search Order ID, Customer, or Payment..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <select id="customEntriesSelect" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-1.5 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="5" selected>5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="20">20</option>
            </select>
            <span class="text-sm text-slate-500 dark:text-slate-400">entries per page</span>
          </div>
        </div>

        <table id="ordersTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-slate-400 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
              <th class="px-5 py-3 font-semibold">Order</th>
              <th class="px-3 py-3 font-semibold">Customer</th>
              <th class="px-3 py-3 font-semibold">Payment</th>
              <th class="px-3 py-3 font-semibold text-right">Total</th>
              <th class="px-3 py-3 font-semibold text-center">Status</th>
              <th class="px-3 py-3 font-semibold">Date</th>
              <th class="px-5 py-3 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <?php foreach ($orders as $order): ?>
              <?php 
                $status = strtolower($order['status']);
                $badgeStyle = "background-color: #6b7280; color: #ffffff;";
                if ($status === 'completed') {
                  $badgeStyle = "background-color: #059669; color: #ffffff;";
                } elseif ($status === 'pending') {
                  $badgeStyle = "background-color: #fbbf24; color: #451a03;";
                } elseif ($status === 'cancelled') {
                  $badgeStyle = "background-color: #be123c; color: #ffffff;";
                } elseif ($status === 'shipped' || $status === 'packed') {
                  $badgeStyle = "background-color: #2563eb; color: #ffffff;";
                }
              ?>
              <tr class="order-row hover:bg-slate-50/50 dark:hover:bg-slate-700/50 transition-colors"
                  data-search="<?= htmlspecialchars(strtolower(($order['id'] ?? '') . ' ' . ($order['linked_customer_name'] ?? $order['customer_name'] ?? 'walk-in') . ' ' . $order['payment_method'])) ?>">
                <td class="px-5 py-3.5 font-semibold text-indigo-600 dark:text-indigo-400">#<?= (int) $order['id'] ?></td>
                <td class="px-3 py-3.5 text-slate-700 dark:text-slate-300">
                  <?= htmlspecialchars($order['linked_customer_name'] ?? $order['customer_name'] ?? 'Walk-in') ?>
                </td>
                <td class="px-3 py-3.5 text-slate-500 dark:text-slate-400">
                  <span class="inline-flex items-center gap-1.5 capitalize">
                    <?php if (strtolower($order['payment_method']) === 'cash'): ?>
                      <i data-lucide="banknote" class="w-3.5 h-3.5 text-slate-400"></i>
                    <?php else: ?>
                      <i data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-400"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($order['payment_method']) ?>
                  </span>
                </td>
                <td class="px-3 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">₱<?= number_format((float) $order['total_amount'], 2) ?></td>
                <td class="px-3 py-3.5 text-center">
                  <span style="<?= $badgeStyle ?>" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm capitalize">
                    <?= htmlspecialchars($order['status']) ?>
                  </span>
                </td>
                <td class="px-3 py-3.5 text-slate-500 dark:text-slate-400 whitespace-nowrap"><?= date('M j, Y g:ia', strtotime($order['created_at'])) ?></td>
                <td class="px-5 py-3.5 text-right">
                  <a href="/admin/orders/<?= (int) $order['id'] ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Custom pagination footer (same style as Staff Orders page) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-5 mt-2 border-t border-slate-100 dark:border-slate-700/60">
          <div id="ao-info" class="text-xs font-medium text-slate-500 dark:text-slate-400">Showing 0 to 0 of 0 entries</div>
          <div id="ao-pagination" class="flex items-center gap-1"></div>
        </div>
      </div>
    <?php endif; ?>
  </main>
</div>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
let aoCurrentPage = 1;

function adminOrders_updateTable(resetPage = false) {
  if (resetPage) aoCurrentPage = 1;

  const searchValue = (document.getElementById('customSearchInput')?.value || '').toLowerCase().trim();
  const perPage = parseInt(document.getElementById('customEntriesSelect')?.value || '5');

  const rows = Array.from(document.querySelectorAll('#ordersTable tbody .order-row'));

  const matchingRows = rows.filter(row => !searchValue || row.dataset.search.includes(searchValue));

  const totalEntries = matchingRows.length;
  const totalPages = Math.ceil(totalEntries / perPage) || 1;

  if (aoCurrentPage > totalPages) aoCurrentPage = totalPages;

  const startIndex = (aoCurrentPage - 1) * perPage;
  const endIndex = startIndex + perPage;

  rows.forEach(r => r.style.display = 'none');
  matchingRows.slice(startIndex, endIndex).forEach(r => r.style.display = '');

  const infoEl = document.getElementById('ao-info');
  if (infoEl) {
    if (totalEntries === 0) {
      infoEl.textContent = 'Showing 0 to 0 of 0 entries';
    } else {
      infoEl.textContent = `Showing ${startIndex + 1} to ${Math.min(endIndex, totalEntries)} of ${totalEntries} entries`;
    }
  }

  const pagEl = document.getElementById('ao-pagination');
  if (pagEl) {
    pagEl.innerHTML = '';

    const mkBtn = (label, disabled, onClick) => {
      const btn = document.createElement('button');
      btn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors ${disabled ? 'opacity-40 cursor-not-allowed text-slate-400' : 'hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'}`;
      btn.textContent = label;
      btn.disabled = disabled;
      btn.onclick = onClick;
      return btn;
    };

    pagEl.appendChild(mkBtn('«', aoCurrentPage === 1, () => { aoCurrentPage = 1; adminOrders_updateTable(); }));
    pagEl.appendChild(mkBtn('‹', aoCurrentPage === 1, () => { if (aoCurrentPage > 1) { aoCurrentPage--; adminOrders_updateTable(); } }));

    for (let p = 1; p <= totalPages; p++) {
      const isActive = p === aoCurrentPage;
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 text-xs font-bold rounded-lg transition-colors ${isActive ? 'bg-indigo-600 text-white dark:bg-indigo-500' : 'border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'}`;
      pageBtn.textContent = p;
      pageBtn.onclick = () => { aoCurrentPage = p; adminOrders_updateTable(); };
      pagEl.appendChild(pageBtn);
    }

    pagEl.appendChild(mkBtn('›', aoCurrentPage === totalPages || totalEntries === 0, () => { if (aoCurrentPage < totalPages) { aoCurrentPage++; adminOrders_updateTable(); } }));
    pagEl.appendChild(mkBtn('»', aoCurrentPage === totalPages || totalEntries === 0, () => { aoCurrentPage = totalPages; adminOrders_updateTable(); }));
  }
}

document.addEventListener('DOMContentLoaded', () => {
  <?php if (!empty($orders)): ?>
  adminOrders_updateTable(true);
  document.getElementById('customSearchInput')?.addEventListener('input', () => adminOrders_updateTable(true));
  document.getElementById('customEntriesSelect')?.addEventListener('change', () => adminOrders_updateTable(true));
  <?php endif; ?>
  lucide.createIcons();
});
</script>