<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    
    <!-- Page Header -->
    <div class="flex items-center gap-3.5 mb-8">
      <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 shadow-sm shrink-0">
        <i data-lucide="package-x" class="h-5 w-5"></i>
      </span>
      <div>
        <h1 class="font-display text-2xl font-semibold text-gray-900 dark:text-white">Damaged products</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Permanent write-offs from branch stock. These units never return to Seller POS.</p>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden p-4 shadow-sm">
      
      <!-- Custom Search & Entries Header Toolbar -->
      <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
        <div class="relative w-full sm:w-72">
          <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
          <input type="text" id="customSearchInput" placeholder="Search damaged records..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500">
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <select id="customEntriesSelect" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="20">20</option>
          </select>
          <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
        </div>
      </div>

      <table id="damagedProductsTable" class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
            <th class="px-5 py-3.5 font-semibold">Date</th>
            <th class="px-4 py-3.5 font-semibold">Product</th>
            <th class="px-4 py-3.5 font-semibold">Branch</th>
            <th class="px-4 py-3.5 font-semibold text-center">Quantity</th>
            <th class="px-4 py-3.5 font-semibold">Recorded By</th>
            <th class="px-5 py-3.5 font-semibold">Note</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
          <?php foreach($rows as $row): ?>
            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition-colors">
              <td class="px-5 py-4 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                <?= date('M j, Y • g:i a', strtotime($row['created_at'])) ?>
              </td>

              <td class="px-4 py-4 font-semibold text-gray-900 dark:text-gray-100">
                <?= htmlspecialchars($row['product_name']) ?>
                <?php if (!empty($row['variant_size']) || !empty($row['variant_color'])): ?>
                  <span class="text-xs font-normal text-gray-500 dark:text-gray-400 block mt-0.5">
                    Variant: <?= htmlspecialchars(trim(($row['variant_size'] ?? '').' / '.($row['variant_color'] ?? ''), ' /')) ?>
                  </span>
                <?php endif; ?>
              </td>

              <td class="px-4 py-4 text-gray-700 dark:text-gray-300 font-medium">
                <?= htmlspecialchars($row['branch_name']) ?>
              </td>

              <td class="px-4 py-4 text-center whitespace-nowrap">
                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 px-2.5 py-1 text-xs font-bold text-rose-600 dark:text-rose-300 shadow-sm">
                  <i data-lucide="package-minus" class="h-3.5 w-3.5 text-rose-600 dark:text-rose-400"></i>
                  -<?= (int)$row['quantity'] ?>
                </span>
              </td>

              <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                <?= htmlspecialchars($row['recorded_by_name'] ?? 'System') ?>
              </td>

              <td class="px-5 py-4 text-gray-500 dark:text-gray-400 italic">
                <?= htmlspecialchars(!empty($row['note']) ? $row['note'] : '—') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

</div>

<!-- DataTables Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
    <?php if(!empty($rows)): ?>
    const table = $('#damagedProductsTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        order: [[0, 'desc']], // Default sort by latest date
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        drawCallback: function () {
            lucide.createIcons();
        }
    });

    $('#customSearchInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    $('#customEntriesSelect').on('change', function () {
        table.page.len(this.value).draw();
    });
    <?php endif; ?>

    lucide.createIcons();
});
</script>