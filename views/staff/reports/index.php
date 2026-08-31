<div class="flex min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-4 sm:px-8 py-8 overflow-y-auto">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mb-1">After-Sales <span class="mx-1">/</span> <span class="text-indigo-600 dark:text-indigo-400 font-semibold">Returns</span></p>
        <h1 class="font-display font-bold text-2xl text-slate-900 dark:text-white flex items-center gap-2">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"><path d="M3 10h10a5 5 0 015 5v2"/><path d="M7 6l-4 4 4 4"/></svg>
          Return & refund requests
        </h1>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Review customer requests from delivered orders in your branch.</p>
      </div>
    </div>

    <!-- View-only Notice Banner -->
    <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-2xl p-4 mb-6 flex items-center gap-3 text-indigo-700 dark:text-indigo-300">
      <div class="p-2 bg-indigo-500/20 rounded-xl shrink-0">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
      </div>
      <div class="text-xs">
        <span class="font-bold block mb-0.5">View only</span>
        Only customer service staff can approve, reject, or mark requests refunded. Your role (Cashier) has view access only.
      </div>
    </div>

    <!-- Main Container -->
    <div class="bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 rounded-2xl shadow-sm backdrop-blur-sm overflow-hidden">
      
      <!-- DataTables Controls Header -->
      <div class="px-4 sm:px-5 pt-4 pb-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div></div>

        <div class="flex flex-wrap items-center justify-end gap-2.5">
          <!-- Search Box -->
          <div class="relative w-full sm:w-[220px]">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
            <input 
              type="text" 
              id="dt-returns-search" 
              oninput="returnsTable_update()" 
              placeholder="Search product or order..." 
              class="w-full h-9 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/60 text-[11px] text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 focus:border-indigo-500 transition-colors"
            >
          </div>

          <!-- Status Dropdown Filter -->
          <select 
            id="dt-returns-status" 
            onchange="returnsTable_update()" 
            class="h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/60 text-[11px] font-medium text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 focus:border-indigo-500"
          >
            <option value="">All statuses</option>
            <option value="requested">Requested</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="refunded">Refunded</option>
          </select>

          <!-- Page Entries Selector -->
          <div class="flex items-center gap-2">
            <select 
              id="dt-returns-per-page" 
              onchange="returnsTable_update(true)" 
              class="h-9 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/60 text-[11px] font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 focus:border-indigo-500"
            >
              <option value="5" selected>5</option>
              <option value="10">10</option>
              <option value="25">25</option>
            </select>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">entries per page</span>
          </div>
        </div>
      </div>

      <?php if (empty($returns)): ?>
        <div class="text-center py-16">
          <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700/50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400 dark:text-slate-500">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-8 h-8"><path d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <h3 class="font-bold text-slate-700 dark:text-slate-200 text-base mb-1">No return requests</h3>
          <p class="text-slate-500 dark:text-slate-400 text-xs">Customer return requests will appear here.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse" id="dt-returns-table" data-custom-dt="true">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-800/80 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">
                <th class="py-3 px-3 sm:px-4">Product</th>
                <th class="py-3 px-3 sm:px-4">Order / Customer</th>
                <th class="py-3 px-3 sm:px-4">Reason</th>
                <th class="py-3 px-3 sm:px-4">Status</th>
                <th class="py-3 px-3 sm:px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
              <?php foreach ($returns as $ret): ?>
                <tr 
                  class="return-row border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors"
                  data-search="<?= htmlspecialchars(strtolower(($ret['product_name'] ?? 'clothes') . ' order #' . ($ret['order_id'] ?? '') . ' ' . ($ret['customer_name'] ?? 'customer') . ' ' . ($ret['reason'] ?? ''))) ?>"
                  data-status="<?= htmlspecialchars(strtolower($ret['status'] ?? 'requested')) ?>"
                >
                  <td class="py-3.5 px-3 sm:px-4 font-semibold text-[12px] text-slate-800 dark:text-slate-200">
                    <?= htmlspecialchars($ret['product_name'] ?? 'Clothes') ?> <span class="text-xs text-slate-400 font-normal">× 1</span>
                  </td>
                  <td class="py-3.5 px-3 sm:px-4">
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 block">Order #<?= htmlspecialchars($ret['order_id'] ?? '') ?></span>
                    <span class="text-xs text-slate-400 dark:text-slate-500"><?= htmlspecialchars($ret['customer_name'] ?? 'Customer') ?></span>
                  </td>
                  <td class="py-3.5 px-3 sm:px-4 text-[11px] font-medium text-slate-600 dark:text-slate-300">
                    <?= htmlspecialchars($ret['reason'] ?? 'Damaged') ?>
                  </td>
                  <td class="py-3.5 px-3 sm:px-4">
                    <?php 
                      $st = strtolower($ret['status'] ?? 'requested');
                      $badgeStyle = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
                      if ($st === 'requested') $badgeStyle = 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 border border-amber-500/20';
                      if ($st === 'approved') $badgeStyle = 'bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 border border-indigo-500/20';
                      if ($st === 'rejected') $badgeStyle = 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 border border-rose-500/20';
                      if ($st === 'refunded') $badgeStyle = 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-500/20';
                    ?>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold capitalize <?= $badgeStyle ?>">
                      <?= htmlspecialchars($ret['status'] ?? 'Requested') ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-3 sm:px-4 text-right">
                    <span class="text-xs text-slate-400 dark:text-slate-500 italic">—</span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- DataTables Footer & Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 sm:px-5 py-4 border-t border-slate-100 dark:border-slate-700/60">
          <div id="dt-returns-info" class="text-[11px] font-medium text-slate-500 dark:text-slate-400">
            Showing 0 to 0 of 0 entries
          </div>
          <div id="dt-returns-pagination" class="flex items-center gap-1"></div>
        </div>
      <?php endif; ?>

    </div>

  </main>
</div>

<script>
let returnsCurrentPage = 1;

/*
 * The site may have a global jQuery DataTables initializer.
 * Destroy it for this table so its default controls/arrows do not replace
 * the custom DataTables-style UI shown in the reference design.
 */
function returnsTable_disableNativeDataTables() {
  try {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
      const table = window.jQuery('#dt-returns-table');
      if (table.length && window.jQuery.fn.DataTable.isDataTable('#dt-returns-table')) {
        table.DataTable().destroy();
      }
    }
  } catch (e) {
    console.warn('Returns table DataTables cleanup skipped:', e);
  }
}

function returnsTable_update(resetPage = false) {
  returnsTable_disableNativeDataTables();

  if (resetPage) returnsCurrentPage = 1;

  const searchValue = (document.getElementById('dt-returns-search')?.value || '').toLowerCase().trim();
  const statusValue = (document.getElementById('dt-returns-status')?.value || '').toLowerCase();
  const perPage = parseInt(document.getElementById('dt-returns-per-page')?.value || '5', 10);

  const table = document.getElementById('dt-returns-table');
  const rows = table ? Array.from(table.querySelectorAll('tbody .return-row')) : [];

  const matchingRows = rows.filter(row => {
    const matchesSearch = !searchValue || (row.dataset.search || '').includes(searchValue);
    const matchesStatus = !statusValue || (row.dataset.status || '') === statusValue;
    return matchesSearch && matchesStatus;
  });

  const totalEntries = matchingRows.length;
  const totalPages = Math.max(1, Math.ceil(totalEntries / perPage));

  if (returnsCurrentPage > totalPages) returnsCurrentPage = totalPages;

  const startIndex = (returnsCurrentPage - 1) * perPage;
  const endIndex = startIndex + perPage;

  rows.forEach(row => {
    row.style.display = 'none';
  });

  matchingRows.slice(startIndex, endIndex).forEach(row => {
    row.style.display = 'table-row';
  });

  const infoEl = document.getElementById('dt-returns-info');
  if (infoEl) {
    if (totalEntries === 0) {
      infoEl.textContent = 'Showing 0 to 0 of 0 entries';
    } else {
      const displayStart = startIndex + 1;
      const displayEnd = Math.min(endIndex, totalEntries);
      infoEl.textContent = `Showing ${displayStart} to ${displayEnd} of ${totalEntries} entries`;
    }
  }

  const pagEl = document.getElementById('dt-returns-pagination');
  if (!pagEl) return;

  pagEl.innerHTML = '';

  const makeButton = (label, disabled, active, onClick, extraClass = '') => {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = label;
    button.disabled = disabled;
    button.className = [
      'h-7 min-w-7 px-2 flex items-center justify-center text-[10px] font-semibold rounded-lg',
      'border transition-all duration-150',
      active
        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm dark:bg-indigo-500 dark:border-indigo-500'
        : disabled
          ? 'bg-white dark:bg-slate-800 text-slate-300 dark:text-slate-600 border-slate-200 dark:border-slate-700 cursor-not-allowed'
          : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600',
      extraClass
    ].join(' ');
    if (!disabled) button.addEventListener('click', onClick);
    return button;
  };

  // Previous page
  pagEl.appendChild(makeButton(
    '‹',
    returnsCurrentPage === 1 || totalEntries === 0,
    false,
    () => {
      returnsCurrentPage--;
      returnsTable_update();
    }
  ));

  // Show a compact page range similar to the reference DataTable.
  const maxVisiblePages = 7;
  let pages = [];

  if (totalPages <= maxVisiblePages) {
    pages = Array.from({ length: totalPages }, (_, i) => i + 1);
  } else {
    pages.push(1);
    if (returnsCurrentPage > 4) pages.push('...');

    const from = Math.max(2, returnsCurrentPage - 2);
    const to = Math.min(totalPages - 1, returnsCurrentPage + 2);
    for (let p = from; p <= to; p++) pages.push(p);

    if (returnsCurrentPage < totalPages - 3) pages.push('...');
    pages.push(totalPages);
  }

  pages.forEach(page => {
    if (page === '...') {
      const dots = document.createElement('span');
      dots.className = 'h-7 min-w-7 flex items-center justify-center text-[10px] text-slate-400';
      dots.textContent = '…';
      pagEl.appendChild(dots);
      return;
    }

    pagEl.appendChild(makeButton(
      String(page),
      false,
      page === returnsCurrentPage,
      () => {
        returnsCurrentPage = page;
        returnsTable_update();
      }
    ));
  });

  // Next page
  pagEl.appendChild(makeButton(
    '›',
    returnsCurrentPage === totalPages || totalEntries === 0,
    false,
    () => {
      returnsCurrentPage++;
      returnsTable_update();
    }
  ));
}

function returnsTable_init() {
  returnsTable_disableNativeDataTables();
  returnsTable_update(true);

  // Some layouts initialize DataTables after page load, so check once more.
  setTimeout(() => {
    returnsTable_disableNativeDataTables();
    returnsTable_update();
  }, 50);
}

document.addEventListener('DOMContentLoaded', returnsTable_init);
</script>
