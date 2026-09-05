<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white">Seller inventory</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Master stock. Transfer units to Seller POS or allocate them to branches from this inventory.</p>
    </div>

    <!-- Error Alert -->
    <?php if(!empty($error)):?>
      <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/40 p-4 text-sm text-red-700 dark:text-red-300 flex items-center gap-2 shadow-sm">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- Master Inventory Card & Table -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden p-4 shadow-sm mb-10">

      <!-- Custom Search Toolbar for Seller Inventory -->
      <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
        <div class="relative w-full sm:w-72">
          <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
          <input type="text" id="customSearchInventory" placeholder="Search master inventory..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <select id="stockStatusFilterInventory" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <option value="">All stock</option>
          <option value="in">In stock</option>
          <option value="out">Out of stock</option>
        </select>

        <div class="flex items-center gap-2 shrink-0">
          <select id="customEntriesInventory" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="20">20</option>
          </select>
          <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
        </div>
      </div>

      <table id="sellerInventoryTable" class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
            <th class="px-5 py-3.5 font-semibold">Product</th>
            <th class="px-5 py-3.5 font-semibold text-right">Inventory Stock</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
          <?php foreach($products as $p): ?>
            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition-colors" data-stock-status="<?= (int)$p['stock'] < 1 ? 'out' : 'in' ?>">
              <?php
                // Sort key: in-stock items always rank above out-of-stock ones (big
                // fixed offset), and within each group, most recently updated first.
                $sortKey = ((int) $p['stock'] > 0 ? 1000000000000 : 0) + strtotime($p['updated_at'] ?? $p['created_at'] ?? 'now');
              ?>
              <td class="px-5 py-4 font-semibold text-gray-900 dark:text-gray-100" data-order="<?= $sortKey ?>"><?= htmlspecialchars($p['name']) ?></td>
              <td class="px-5 py-4 text-right whitespace-nowrap">
                <?php if ((int)$p['stock'] < 1): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800 px-3 py-1 text-xs font-semibold text-red-700 dark:text-red-300 shadow-sm">
                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                    0 in inventory — restock needed
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 px-3 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300 shadow-sm">
                    <i data-lucide="warehouse" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400"></i>
                    <?= (int)$p['stock'] ?> in inventory
                  </span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- POS Stock Section Header -->
    <h2 class="font-display text-xl font-semibold text-gray-900 dark:text-white mb-3">Seller POS stock</h2>

    <!-- POS Stock Card & Table -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden p-4 shadow-sm">

      <!-- Custom Search Toolbar for POS Stock -->
      <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
        <div class="relative w-full sm:w-72">
          <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
          <input type="text" id="customSearchPos" placeholder="Search POS stock..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <select id="stockStatusFilterPos" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <option value="">All stock</option>
          <option value="in">In stock</option>
          <option value="out">Out of stock</option>
        </select>

        <div class="flex items-center gap-2 shrink-0">
          <select id="customEntriesPos" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="20">20</option>
          </select>
          <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
        </div>
      </div>

      <table id="sellerPosStockTable" class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
            <th class="px-5 py-3.5 font-semibold">Product</th>
            <th class="px-5 py-3.5 font-semibold text-right">POS Stock</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
          <?php foreach($posProducts as $p): ?>
            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition-colors" data-stock-status="<?= (int)$p['stock'] < 1 ? 'out' : 'in' ?>">
              <td class="px-5 py-4 font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($p['name']) ?></td>
              <td class="px-5 py-4 text-right whitespace-nowrap">
                <?php if ((int)$p['stock'] < 1): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800 px-3 py-1 text-xs font-semibold text-red-700 dark:text-red-300 shadow-sm">
                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                    0 in POS — restock needed
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 shadow-sm">
                    <i data-lucide="store" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <?= (int)$p['stock'] ?> in POS
                  </span>
                <?php endif; ?>
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
    // 1. Initialize Master Inventory Table
    <?php if(!empty($products)): ?>
    const invTable = $('#sellerInventoryTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        order: [[0, 'desc']], // In-stock items first, then most recently updated (see data-order on the Product cell)
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        drawCallback: function () { lucide.createIcons(); }
    });

    $('#customSearchInventory').on('keyup', function () { invTable.search(this.value).draw(); });
    $('#customEntriesInventory').on('change', function () { invTable.page.len(this.value).draw(); });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'sellerInventoryTable') return true;
        const status = $('#stockStatusFilterInventory').val();
        if (status === '') return true;
        const row = invTable.row(dataIndex).node();
        return $(row).data('stock-status') === status;
    });

    $('#stockStatusFilterInventory').on('change', function () { invTable.draw(); });
    <?php endif; ?>

    // 2. Initialize POS Stock Table
    <?php if(!empty($posProducts)): ?>
    const posTable = $('#sellerPosStockTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        order: [[0, 'asc']],
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        drawCallback: function () { lucide.createIcons(); }
    });

    $('#customSearchPos').on('keyup', function () { posTable.search(this.value).draw(); });
    $('#customEntriesPos').on('change', function () { posTable.page.len(this.value).draw(); });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'sellerPosStockTable') return true;
        const status = $('#stockStatusFilterPos').val();
        if (status === '') return true;
        const row = posTable.row(dataIndex).node();
        return $(row).data('stock-status') === status;
    });

    $('#stockStatusFilterPos').on('change', function () { posTable.draw(); });
    <?php endif; ?>

    lucide.createIcons();
});
</script>