<div class="flex min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-4 sm:px-8 py-8 overflow-y-auto">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mb-1">Menu <span class="mx-1">/</span> <span class="text-indigo-600 dark:text-indigo-400 font-semibold">Orders</span></p>
        <h1 class="font-display font-bold text-2xl text-slate-900 dark:text-white flex items-center gap-2">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          Orders
        </h1>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          <?php if ($profile['position'] === 'cashier'): ?>Only orders processed by you are shown.<?php else: ?>Orders for <span class="font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($profile['branch_name'] ?? 'your branch') ?></span> only.<?php endif; ?>
        </p>
      </div>
    </div>

    <!-- Alert Banner for Pending Online Orders -->
    <?php if (!empty($pendingOnlineOrders) && $pendingOnlineOrders > 0): ?>
      <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 mb-6 flex items-center justify-between text-amber-700 dark:text-amber-400">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-amber-500/20 rounded-xl">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          </div>
          <span class="text-sm font-semibold"><?= (int)$pendingOnlineOrders ?> online order(s) need processing.</span>
        </div>
      </div>
    <?php endif; ?>

    <!-- Main Container -->
    <div class="bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm backdrop-blur-sm">
      
      <!-- Complete DataTables Controls Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div></div> <!-- Spacer -->

        <div class="flex flex-wrap items-center gap-3">
          <!-- Search Box -->
          <div class="relative min-w-[220px]">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
            <input 
              type="text" 
              id="dt-search-input" 
              oninput="posOrders_updateTable()" 
              placeholder="Search order or customer..." 
              class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/60 text-xs text-slate-800 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors"
            >
          </div>

          <!-- Status Dropdown Filter -->
          <select 
            id="dt-status-filter" 
            onchange="posOrders_updateTable()" 
            class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/60 text-xs font-medium text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-400"
          >
            <option value="">All statuses</option>
            <option value="completed">Completed</option>
            <option value="pending">Pending</option>
            <option value="cancelled">Cancelled</option>
          </select>

          <!-- Page Entries Selector -->
          <div class="flex items-center gap-2">
            <select 
              id="dt-per-page" 
              onchange="posOrders_updateTable(true)" 
              class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/60 text-xs font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-400"
            >
              <option value="5" selected>5</option>
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
            <span class="text-xs text-slate-500 dark:text-slate-400">entries per page</span>
          </div>
        </div>
      </div>

      <?php if (empty($orders)): ?>
        <div class="text-center py-16">
          <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700/50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400 dark:text-slate-500">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-8 h-8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <h3 class="font-bold text-slate-700 dark:text-slate-200 text-base mb-1">No orders yet</h3>
          <p class="text-slate-500 dark:text-slate-400 text-xs">Orders placed for this branch will show up here.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm border-collapse" id="dt-orders-table">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-700/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">
                <th class="py-3.5 px-4">Order</th>
                <th class="py-3.5 px-4">Customer</th>
                <th class="py-3.5 px-4">Payment</th>
                <th class="py-3.5 px-4">Total</th>
                <th class="py-3.5 px-4">Status</th>
                <th class="py-3.5 px-4">Date</th>
                <?php if ($profile['position'] === 'branch_manager'): ?><th class="py-3.5 px-4">Processed by</th><?php endif; ?>
                <th class="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
              <?php foreach ($orders as $order): ?>
                <tr 
                  class="order-row hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors"
                  data-search="<?= htmlspecialchars(strtolower(($order['id'] ?? '') . ' ' . ($order['linked_customer_name'] ?? $order['customer_name'] ?? 'walk-in customer') . ' ' . ($order['processed_by_name'] ?? ''))) ?>"
                  data-status="<?= htmlspecialchars(strtolower($order['status'])) ?>"
                >
                  <td class="py-4 px-4 font-bold text-indigo-600 dark:text-indigo-400">
                    #<?= $order['id'] ?>
                  </td>
                  <td class="py-4 px-4 font-medium text-slate-800 dark:text-slate-200">
                    <?= htmlspecialchars($order['linked_customer_name'] ?? $order['customer_name'] ?? 'Walk-in customer') ?>
                  </td>
                  <td class="py-4 px-4 text-xs font-semibold capitalize text-slate-600 dark:text-slate-400">
                    <?= htmlspecialchars($order['payment_method']) ?>
                  </td>
                  <td class="py-4 px-4 font-bold text-slate-900 dark:text-white">
                    ₱<?= number_format((float)$order['total_amount'], 2) ?>
                  </td>
                  <td class="py-4 px-4">
                    <?php 
                      $st = strtolower($order['status']);
                      $badgeStyle = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
                      if ($st === 'completed') $badgeStyle = 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-500/20';
                      if ($st === 'pending') $badgeStyle = 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 border border-amber-500/20';
                      if ($st === 'cancelled') $badgeStyle = 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 border border-rose-500/20';
                    ?>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize <?= $badgeStyle ?>">
                      <?= htmlspecialchars($order['status']) ?>
                    </span>
                  </td>
                  <td class="py-4 px-4 text-xs text-slate-500 dark:text-slate-400">
                    <?= date('M j, Y g:ia', strtotime($order['created_at'])) ?>
                  </td>
                  <?php if ($profile['position'] === 'branch_manager'): ?><td class="py-4 px-4"><span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i data-lucide="user-round" class="h-3.5 w-3.5"></i><?= htmlspecialchars($order['processed_by_name'] ?? 'System / online') ?></span></td><?php endif; ?>
                  <td class="py-4 px-4 text-right">
                    <a 
                      href="/staff/orders/<?= $order['id'] ?>" 
                      class="js-view-order inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 transition-all shadow-sm"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      View
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- DataTables Footer & Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-5 mt-2 border-t border-slate-100 dark:border-slate-700/60">
          <div id="dt-info" class="text-xs font-medium text-slate-500 dark:text-slate-400">
            Showing 0 to 0 of 0 entries
          </div>
          <div id="dt-pagination" class="flex items-center gap-1"></div>
        </div>
      <?php endif; ?>

    </div>

  </main>
</div>

<script>
let currentPage = 1;

function posOrders_updateTable(resetPage = false) {
  if (resetPage) currentPage = 1;

  const searchValue = (document.getElementById('dt-search-input')?.value || '').toLowerCase().trim();
  const statusValue = (document.getElementById('dt-status-filter')?.value || '').toLowerCase();
  const perPage = parseInt(document.getElementById('dt-per-page')?.value || '10');

  const rows = Array.from(document.querySelectorAll('#dt-orders-table tbody .order-row'));
  
  // Filter matching rows
  const matchingRows = rows.filter(row => {
    const matchesSearch = !searchValue || row.dataset.search.includes(searchValue);
    const matchesStatus = !statusValue || row.dataset.status === statusValue;
    return matchesSearch && matchesStatus;
  });

  const totalEntries = matchingRows.length;
  const totalPages = Math.ceil(totalEntries / perPage) || 1;

  if (currentPage > totalPages) currentPage = totalPages;

  const startIndex = (currentPage - 1) * perPage;
  const endIndex = startIndex + perPage;

  // Hide all rows first
  rows.forEach(r => r.style.display = 'none');

  // Show only paginated matching rows
  matchingRows.slice(startIndex, endIndex).forEach(r => r.style.display = '');

  // Update Footer Info text
  const infoEl = document.getElementById('dt-info');
  if (infoEl) {
    if (totalEntries === 0) {
      infoEl.textContent = 'Showing 0 to 0 of 0 entries';
    } else {
      const displayStart = startIndex + 1;
      const displayEnd = Math.min(endIndex, totalEntries);
      infoEl.textContent = `Showing ${displayStart} to ${displayEnd} of ${totalEntries} entries`;
    }
  }

  // Render Pagination Buttons
  const pagEl = document.getElementById('dt-pagination');
  if (pagEl) {
    pagEl.innerHTML = '';
    
    // Previous Button
    const prevBtn = document.createElement('button');
    prevBtn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors ${currentPage === 1 ? 'opacity-40 cursor-not-allowed text-slate-400' : 'hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'}`;
    prevBtn.textContent = '«';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => { currentPage = 1; posOrders_updateTable(); };
    pagEl.appendChild(prevBtn);

    const prevPageBtn = document.createElement('button');
    prevPageBtn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors ${currentPage === 1 ? 'opacity-40 cursor-not-allowed text-slate-400' : 'hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'}`;
    prevPageBtn.textContent = '‹';
    prevPageBtn.disabled = currentPage === 1;
    prevPageBtn.onclick = () => { if (currentPage > 1) { currentPage--; posOrders_updateTable(); } };
    pagEl.appendChild(prevPageBtn);

    // Page Number Buttons
    for (let p = 1; p <= totalPages; p++) {
      const pageBtn = document.createElement('button');
      const isActive = p === currentPage;
      pageBtn.className = `px-3 py-1 text-xs font-bold rounded-lg transition-colors ${isActive ? 'bg-indigo-600 text-white dark:bg-indigo-500' : 'border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'}`;
      pageBtn.textContent = p;
      pageBtn.onclick = () => { currentPage = p; posOrders_updateTable(); };
      pagEl.appendChild(pageBtn);
    }

    // Next Button
    const nextBtn = document.createElement('button');
    nextBtn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors ${currentPage === totalPages || totalEntries === 0 ? 'opacity-40 cursor-not-allowed text-slate-400' : 'hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'}`;
    nextBtn.textContent = '›';
    nextBtn.disabled = currentPage === totalPages || totalEntries === 0;
    nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; posOrders_updateTable(); } };
    pagEl.appendChild(nextBtn);

    const lastBtn = document.createElement('button');
    lastBtn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors ${currentPage === totalPages || totalEntries === 0 ? 'opacity-40 cursor-not-allowed text-slate-400' : 'hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'}`;
    lastBtn.textContent = '»';
    lastBtn.disabled = currentPage === totalPages || totalEntries === 0;
    lastBtn.onclick = () => { currentPage = totalPages; posOrders_updateTable(); };
    pagEl.appendChild(lastBtn);
  }
}

// Initial DataTables setup on page load
document.addEventListener('DOMContentLoaded', () => {
  posOrders_updateTable(true);
  if (window.lucide) lucide.createIcons();
  document.querySelectorAll('.js-view-order').forEach((link) => link.addEventListener('click', (event) => {
    if (!window.Swal) return;
    event.preventDefault();
    Swal.fire({title: 'Open order details?', text: 'You can review its items and fulfillment record.', icon: 'info', showCancelButton: true, confirmButtonText: 'View details', confirmButtonColor: '#4f46e5'}).then((result) => { if (result.isConfirmed) window.location.href = link.href; });
  }));
});
</script>
