<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white">Orders</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Every sale — POS and online checkout — all in one place.</p>
      </div>
      <form method="GET" action="/admin/orders">
        <select name="branch" onchange="this.form.submit()" class="rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
          <option value="" <?= $branchFilter === '' ? 'selected' : '' ?>>All branches</option>
          <option value="none" <?= $branchFilter === 'none' ? 'selected' : '' ?>>Unassigned (no branch)</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= (int) $branch['id'] ?>" <?= $branchFilter === (string) $branch['id'] ? 'selected' : '' ?>><?= htmlspecialchars($branch['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <!-- Alert Banner -->
    <?php if ($pendingOnlineOrders > 0): ?>
      <div class="mb-5 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-100/80 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-900 dark:text-amber-300 flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0"></i>
        <span><strong class="font-bold"><?= (int) $pendingOnlineOrders ?></strong> online <?= $pendingOnlineOrders === 1 ? 'order needs' : 'orders need' ?> processing.</span>
      </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg p-12 text-center">
        <p class="font-display text-lg font-semibold text-gray-900 dark:text-white">No orders yet</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sales from POS and your online store will show up here.</p>
      </div>
    <?php else: ?>
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg overflow-hidden p-4 shadow-sm">
        
        <!-- Top Custom Search & Entries Toolbar -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
          <div class="relative w-full sm:w-72">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="customSearchInput" placeholder="Search Order ID, Customer, or Payment..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <select id="customEntriesSelect" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="20">20</option>
            </select>
            <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
          </div>
        </div>

        <table id="ordersTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
              <th class="px-5 py-3 font-semibold">Order</th>
              <th class="px-3 py-3 font-semibold">Customer</th>
              <th class="px-3 py-3 font-semibold">Payment</th>
              <th class="px-3 py-3 font-semibold text-right">Total</th>
              <th class="px-3 py-3 font-semibold text-center">Status</th>
              <th class="px-3 py-3 font-semibold">Date</th>
              <th class="px-5 py-3 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
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
              <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-5 py-3.5 font-semibold text-gray-900 dark:text-gray-100">#<?= (int) $order['id'] ?></td>
                <td class="px-3 py-3.5 text-gray-700 dark:text-gray-300">
                  <?= htmlspecialchars($order['linked_customer_name'] ?? $order['customer_name'] ?? 'Walk-in') ?>
                </td>
                <td class="px-3 py-3.5 text-gray-500 dark:text-gray-400">
                  <span class="inline-flex items-center gap-1.5">
                    <?php if (strtolower($order['payment_method']) === 'cash'): ?>
                      <i data-lucide="banknote" class="w-3.5 h-3.5 text-gray-400"></i>
                    <?php else: ?>
                      <i data-lucide="credit-card" class="w-3.5 h-3.5 text-gray-400"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars(ucfirst($order['payment_method'])) ?>
                  </span>
                </td>
                <td class="px-3 py-3.5 text-right font-semibold text-gray-900 dark:text-gray-100">₱<?= number_format((float) $order['total_amount'], 2) ?></td>
                <td class="px-3 py-3.5 text-center">
                  <span style="<?= $badgeStyle ?>" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">
                    <?= htmlspecialchars(ucfirst($order['status'])) ?>
                  </span>
                </td>
                <td class="px-3 py-3.5 text-gray-500 dark:text-gray-400 whitespace-nowrap"><?= date('M j, Y g:ia', strtotime($order['created_at'])) ?></td>
                <td class="px-5 py-3.5 text-right">
                  <a href="/admin/orders/<?= (int) $order['id'] ?>" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 dark:border-slate-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> View details
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

</div>

<!-- DataTables Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>

<!-- SweetAlert2 & Lucide -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
    <?php if (!empty($orders)): ?>
    const table = $('#ordersTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: 6 }
        ],
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