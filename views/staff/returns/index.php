<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
<?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
<main class="flex-1 px-5 py-8 sm:px-8">
<div class="mb-7">
<p class="text-xs font-bold uppercase tracking-[.18em] text-coral">After-sales</p>
<h1 class="mt-2 font-display text-3xl font-bold text-ink dark:text-white">Return & refund requests</h1>
<p class="mt-2 text-sm text-gray-500 dark:text-white/55">Review customer requests from delivered orders in your branch.</p>
</div>
<?php if($success): ?><div class="mb-5 rounded-xl bg-brand-light px-4 py-3 text-sm text-brand">Request status updated.</div><?php endif; ?>
<?php if(!empty($error)): ?><div class="mb-5 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php $canProcess = $profile['position'] === 'customer_service'; ?>
<?php if(!$canProcess): ?>
<div class="mb-5 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
  <p class="font-semibold">View only</p>
  <p class="mt-1">Only customer service staff can approve, reject, or mark requests refunded. Your role (<?= htmlspecialchars(ucfirst(str_replace('_', ' ', $profile['position']))) ?>) has view access only.</p>
</div>
<?php endif; ?>
<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-ink-2">
<?php if(empty($requests)): ?>
<div class="p-12 text-center"><p class="font-display text-xl font-bold text-ink dark:text-white">No return requests.</p><p class="mt-2 text-sm text-gray-500">New customer requests for this branch will appear here.</p></div>
<?php else: ?>
<div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
  <div class="relative w-full max-w-xs">
    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
    <input type="text" id="rt-search-input" oninput="staffReturns_updateTable(true)" placeholder="Search order or customer..." class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs text-ink placeholder:text-gray-400 focus:border-brand focus:outline-none dark:border-white/15 dark:bg-ink dark:text-white">
  </div>
  <div class="flex flex-wrap items-center gap-3">
    <select id="rt-status-filter" onchange="staffReturns_updateTable(true)" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 focus:border-brand focus:outline-none dark:border-white/15 dark:bg-ink dark:text-white">
      <option value="">All statuses</option>
      <option value="requested">Requested</option>
      <option value="approved">Approved</option>
      <option value="rejected">Rejected</option>
      <option value="refunded">Refunded</option>
    </select>
    <div class="flex items-center gap-2">
      <select id="rt-per-page" onchange="staffReturns_updateTable(true)" class="rounded-xl border border-gray-200 bg-white px-2.5 py-2 text-xs font-semibold text-gray-700 focus:border-brand focus:outline-none dark:border-white/15 dark:bg-ink dark:text-white">
        <option value="5" selected>5</option>
        <option value="10">10</option>
        <option value="15">15</option>
        <option value="20">20</option>
      </select>
      <span class="text-xs text-gray-500 dark:text-white/55">entries per page</span>
    </div>
  </div>
</div>
<table id="returnsTable" class="w-full text-sm">
  <thead>
    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200 dark:border-white/10">
      <th class="px-5 py-3 font-semibold">Product</th>
      <th class="px-3 py-3 font-semibold">Order / Customer</th>
      <th class="px-3 py-3 font-semibold">Reason</th>
      <th class="px-3 py-3 font-semibold">Status</th>
      <th class="px-3 py-3 font-semibold">Handled by</th>
      <th class="px-5 py-3 font-semibold text-right">Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($requests as $request): ?>
    <tr class="return-row border-b border-gray-100 last:border-0 dark:border-white/10"
        data-search="<?= htmlspecialchars(strtolower($request['product_name'] . ' ' . $request['order_number'] . ' ' . $request['customer_name'])) ?>"
        data-status="<?= htmlspecialchars(strtolower($request['status'])) ?>">
      <td class="px-5 py-3.5 align-top">
        <p class="font-bold text-ink dark:text-white"><?= htmlspecialchars($request['product_name']) ?> <span class="font-normal text-gray-400">× <?= (int)$request['quantity'] ?></span></p>
      </td>
      <td class="px-3 py-3.5 align-top text-gray-600 dark:text-white/70">
        Order #<?= (int)$request['order_number'] ?><br>
        <span class="text-xs text-gray-400"><?= htmlspecialchars($request['customer_name']) ?><?= !empty($request['variant_label']) ? ' · '.htmlspecialchars($request['variant_label']) : '' ?></span>
      </td>
      <td class="px-3 py-3.5 align-top text-gray-700 dark:text-white/75">
        <p><b><?= htmlspecialchars(ucwords(str_replace('_',' ',$request['reason']))) ?></b></p>
        <?php if($request['details']): ?><p class="mt-1 text-xs text-gray-500 dark:text-white/55"><?= nl2br(htmlspecialchars($request['details'])) ?></p><?php endif; ?>
      </td>
      <td class="px-3 py-3.5 align-top">
        <span class="rounded-full bg-brand-light px-3 py-1 text-xs font-bold text-brand"><?= htmlspecialchars(ucfirst($request['status'])) ?></span>
      </td>
      <td class="px-3 py-3.5 align-top"><span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700"><i data-lucide="user-round" class="h-3.5 w-3.5"></i><?= htmlspecialchars($request['handled_by_name'] ?? 'Not handled yet') ?></span></td>
      <td class="px-5 py-3.5 align-top text-right">
        <?php if($canProcess && in_array($request['status'],['requested','approved'],true)): ?>
        <form method="POST" action="/staff/returns/<?= (int)$request['id'] ?>" class="js-return-status-form inline-flex items-center gap-1">
          <select name="status" class="js-return-status-select rounded-lg border border-gray-200 px-2 py-2 text-xs dark:border-white/15 dark:bg-ink dark:text-white">
            <option value="approved">Approve</option>
            <option value="rejected">Reject</option>
            <?php if($request['status']==='approved'): ?><option value="refunded">Mark refunded</option><?php endif; ?>
          </select>
          <button type="submit" class="ml-1 inline-flex items-center gap-1.5 rounded-lg bg-brand px-3 py-2 text-xs font-bold text-white">
            <i data-lucide="save" class="w-3.5 h-3.5"></i> Save
          </button>
        </form>
        <?php else: ?>
        <span class="text-xs text-gray-400">—</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Custom pagination footer (same style as Orders page) -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-5 py-4 border-t border-gray-100 dark:border-white/10">
  <div id="rt-info" class="text-xs font-medium text-gray-500 dark:text-white/55">Showing 0 to 0 of 0 entries</div>
  <div id="rt-pagination" class="flex items-center gap-1"></div>
</div>
<?php endif; ?>
</section>
</main>
</div>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
let rtCurrentPage = 1;

function staffReturns_updateTable(resetPage = false) {
  if (resetPage) rtCurrentPage = 1;

  const searchValue = (document.getElementById('rt-search-input')?.value || '').toLowerCase().trim();
  const statusValue = (document.getElementById('rt-status-filter')?.value || '').toLowerCase();
  const perPage = parseInt(document.getElementById('rt-per-page')?.value || '5');

  const rows = Array.from(document.querySelectorAll('#returnsTable tbody .return-row'));

  // Filter matching rows
  const matchingRows = rows.filter(row => {
    const matchesSearch = !searchValue || row.dataset.search.includes(searchValue);
    const matchesStatus = !statusValue || row.dataset.status === statusValue;
    return matchesSearch && matchesStatus;
  });

  const totalEntries = matchingRows.length;
  const totalPages = Math.ceil(totalEntries / perPage) || 1;

  if (rtCurrentPage > totalPages) rtCurrentPage = totalPages;

  const startIndex = (rtCurrentPage - 1) * perPage;
  const endIndex = startIndex + perPage;

  // Hide all rows first
  rows.forEach(r => r.style.display = 'none');

  // Show only paginated matching rows
  matchingRows.slice(startIndex, endIndex).forEach(r => r.style.display = '');

  // Update footer info text
  const infoEl = document.getElementById('rt-info');
  if (infoEl) {
    if (totalEntries === 0) {
      infoEl.textContent = 'Showing 0 to 0 of 0 entries';
    } else {
      const displayStart = startIndex + 1;
      const displayEnd = Math.min(endIndex, totalEntries);
      infoEl.textContent = `Showing ${displayStart} to ${displayEnd} of ${totalEntries} entries`;
    }
  }

  // Render pagination buttons
  const pagEl = document.getElementById('rt-pagination');
  if (pagEl) {
    pagEl.innerHTML = '';

    const mkBtn = (label, disabled, onClick) => {
      const btn = document.createElement('button');
      btn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-gray-200 dark:border-white/15 transition-colors ${disabled ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-100 dark:hover:bg-white/10 text-gray-700 dark:text-white'}`;
      btn.textContent = label;
      btn.disabled = disabled;
      btn.onclick = onClick;
      return btn;
    };

    pagEl.appendChild(mkBtn('«', rtCurrentPage === 1, () => { rtCurrentPage = 1; staffReturns_updateTable(); }));
    pagEl.appendChild(mkBtn('‹', rtCurrentPage === 1, () => { if (rtCurrentPage > 1) { rtCurrentPage--; staffReturns_updateTable(); } }));

    for (let p = 1; p <= totalPages; p++) {
      const isActive = p === rtCurrentPage;
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 text-xs font-bold rounded-lg transition-colors ${isActive ? 'bg-brand text-white' : 'border border-gray-200 dark:border-white/15 hover:bg-gray-100 dark:hover:bg-white/10 text-gray-700 dark:text-white'}`;
      pageBtn.textContent = p;
      pageBtn.onclick = () => { rtCurrentPage = p; staffReturns_updateTable(); };
      pagEl.appendChild(pageBtn);
    }

    pagEl.appendChild(mkBtn('›', rtCurrentPage === totalPages || totalEntries === 0, () => { if (rtCurrentPage < totalPages) { rtCurrentPage++; staffReturns_updateTable(); } }));
    pagEl.appendChild(mkBtn('»', rtCurrentPage === totalPages || totalEntries === 0, () => { rtCurrentPage = totalPages; staffReturns_updateTable(); }));
  }
}

document.addEventListener('DOMContentLoaded', () => {
  <?php if (!empty($requests)): ?>
  staffReturns_updateTable(true);
  <?php endif; ?>
  if (typeof lucide !== 'undefined') lucide.createIcons();
  bindReturnStatusForms();
});

var returnStatusMeta = {
  approved: {
    title: 'Approve this return?',
    text: 'The customer will be notified that their return request was approved.',
    icon: 'question',
    confirmText: 'Yes, approve',
    confirmColor: '#0d9488'
  },
  rejected: {
    title: 'Reject this return?',
    text: 'The customer will be notified that their return request was declined.',
    icon: 'warning',
    confirmText: 'Yes, reject',
    confirmColor: '#dc2626'
  },
  refunded: {
    title: 'Mark this order as refunded?',
    text: 'This confirms the refund has been processed for the customer.',
    icon: 'question',
    confirmText: 'Yes, mark refunded',
    confirmColor: '#0d9488'
  }
};

function bindReturnStatusForms() {
  document.querySelectorAll('.js-return-status-form').forEach(function (form) {
    if (form.dataset.swalBound === '1') return; // iwas double-bind pag na-redraw ng DataTables
    form.dataset.swalBound = '1';

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var select = form.querySelector('.js-return-status-select');
      var meta = returnStatusMeta[select.value] || {
        title: 'Update this request?',
        text: 'This will change the status of the return request.',
        icon: 'question',
        confirmText: 'Yes, continue',
        confirmColor: '#0d9488'
      };

      if (typeof Swal === 'undefined') {
        form.submit();
        return;
      }

      Swal.fire({
        title: meta.title,
        text: meta.text,
        icon: meta.icon,
        showCancelButton: true,
        confirmButtonText: meta.confirmText,
        cancelButtonText: 'Cancel',
        confirmButtonColor: meta.confirmColor,
        cancelButtonColor: '#6b7280',
        reverseButtons: true
      }).then(function (result) {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
}
</script>