<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white">Stock requests</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Ask a supplier to restock an item.</p>
      </div>
      <a href="/stock-requests/create" style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-lg shadow-md hover:opacity-90 active:scale-95 transition-all cursor-pointer">
        <i data-lucide="plus" class="w-4 h-4" style="color: #ffffff !important;"></i> New request
      </a>
    </div>

    <?php if (empty($requests)): ?>
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-12 text-center shadow-sm">
        <p class="font-display text-lg font-semibold text-gray-900 dark:text-white">No stock requests yet</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-5">Running low on something? Ask a supplier to restock it.</p>
        <a href="/stock-requests/create" style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-lg shadow-md hover:opacity-90 transition-all cursor-pointer">
          <i data-lucide="plus" class="w-4 h-4" style="color: #ffffff !important;"></i> New request
        </a>
      </div>
    <?php else: ?>
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden p-4 shadow-sm">

        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
          <div class="relative w-full sm:w-72">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="customSearchInput" placeholder="Search item or supplier..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>

          <select id="statusFilterRequests" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="fulfilled">Fulfilled</option>
            <option value="rejected">Rejected</option>
          </select>

          <div class="flex items-center gap-2 shrink-0">
            <select id="customEntriesSelect" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="20">20</option>
            </select>
            <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
          </div>
        </div>

        <table id="stockRequestsTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
              <th class="px-5 py-3.5 font-semibold">Item</th>
              <th class="px-4 py-3.5 font-semibold">Supplier</th>
              <th class="px-4 py-3.5 font-semibold text-right">Qty Requested</th>
              <th class="px-4 py-3.5 font-semibold text-center">Status</th>
              <th class="px-5 py-3.5 font-semibold">Requested Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
            <?php foreach ($requests as $req): ?>
              <?php
                $status = strtolower($req['status']);
                $badgeStyle = "background-color: #6b7280; color: #ffffff;"; // Gray
                if ($status === 'fulfilled') {
                  $badgeStyle = "background-color: #059669; color: #ffffff;"; // Emerald Green
                } elseif ($status === 'pending') {
                  $badgeStyle = "background-color: #fbbf24; color: #451a03;"; // Amber Yellow
                } elseif ($status === 'rejected') {
                  $badgeStyle = "background-color: #be123c; color: #ffffff;"; // Rose Red
                }
              ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition-colors" data-status="<?= htmlspecialchars($status) ?>">
                <td class="px-5 py-4 font-semibold text-gray-900 dark:text-gray-100">
                  <?= htmlspecialchars($req['item_name']) ?>
                  <?php if (!empty($req['note'])): ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-normal mt-0.5"><?= htmlspecialchars($req['note']) ?></p>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-4 text-gray-700 dark:text-gray-300 font-medium">
                  <?= htmlspecialchars($req['supplier_name']) ?>
                </td>
                <td class="px-4 py-4 text-right font-bold text-gray-900 dark:text-gray-100">
                  <?= (int) $req['quantity_requested'] ?>
                </td>
                <td class="px-4 py-4 text-center whitespace-nowrap">
                  <span style="<?= $badgeStyle ?>" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">
                    <?= htmlspecialchars(ucfirst($req['status'])) ?>
                  </span>
                </td>
                <td class="px-5 py-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                  <?= date('M j, Y', strtotime($req['created_at'])) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
    <?php if (!empty($requests)): ?>
    const table = $('#stockRequestsTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        order: [[4, 'desc']], // Default sort by Requested Date
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        drawCallback: function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    });

    // FIX: global search na (item AT supplier), hindi na column(0)-only
    $('#customSearchInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    $('#customEntriesSelect').on('change', function () {
        table.page.len(this.value).draw();
    });

    // BAGO: status filter dropdown
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'stockRequestsTable') return true;
        const status = $('#statusFilterRequests').val();
        if (status === '') return true;
        const row = table.row(dataIndex).node();
        return $(row).data('status') === status;
    });

    $('#statusFilterRequests').on('change', function () { table.draw(); });
    <?php endif; ?>

    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>