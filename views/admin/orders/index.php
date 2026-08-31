<div class="flex min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-4 sm:px-8 py-8 overflow-y-auto">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mb-1">Menu <span class="mx-1">/</span> <span class="text-indigo-600 dark:text-indigo-400 font-semibold">Orders</span></p>
        <h1 class="font-display font-bold text-2xl text-slate-900 dark:text-white">Orders</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Orders for <span class="font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($profile['branch_name'] ?? 'your branch') ?></span> only.
        </p>
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
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Orders placed for this branch will show up here.</p>
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
              <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50 transition-colors">
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
                  <a href="/staff/orders/<?= (int) $order['id'] ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
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

<!-- Lucide Icons -->
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
        pagingType: 'full_numbers',
        language: {
            paginate: {
                first: '«',
                previous: '‹',
                next: '›',
                last: '»'
            }
        },
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