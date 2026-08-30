<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <main class="flex-1 px-5 py-7 md:px-8">
    <header class="mb-6">
      <p class="eyebrow text-brand">Branch Manager Dashboard</p>
      <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Welcome, <?= htmlspecialchars($manager['first_name']) ?></h1>
      <p class="mt-2 text-sm text-gray-500 dark:text-white/60">
        Everything below is scoped to the branch your Seller assigned to you — you won't see other branches' data here.
      </p>
    </header>

    <section class="mb-5 dashboard-panel flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-gray-500">Assigned Branch</p>
        <p class="mt-1 font-display text-xl font-bold text-ink dark:text-white"><?= htmlspecialchars($manager['branch_name']) ?></p>
        <p class="mt-1 text-xs text-gray-500">
          Status:
          <span class="font-semibold <?= $manager['branch_is_active'] ? 'text-teal' : 'text-gray-400' ?>">
            <?= $manager['branch_is_active'] ? 'Active' : 'Inactive' ?>
          </span>
        </p>
      </div>
      <a href="/staff/manage" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-white/10 dark:text-white/70">
        Manage Staff
      </a>
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2">
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="dashboard-panel">
            <p class="text-xs font-semibold text-gray-500">Staff</p>
            <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) $activeStaffCount ?> <span class="text-sm font-medium text-gray-400">/ <?= (int) $staffCount ?></span></p>
            <p class="mt-1 text-[11px] text-gray-400">Active out of total staff you've created</p>
          </div>
          <div class="dashboard-panel">
            <p class="text-xs font-semibold text-gray-500">Today's Orders</p>
            <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) $todaysOrders ?></p>
            <p class="mt-1 text-[11px] text-gray-400">Orders placed for this branch today</p>
          </div>
          <div class="dashboard-panel">
            <p class="text-xs font-semibold <?= $pendingOrders > 0 ? 'text-amber-600' : 'text-gray-500' ?>">Pending Orders</p>
            <p class="mt-2 font-display text-2xl font-bold <?= $pendingOrders > 0 ? 'text-amber-600' : 'text-ink dark:text-white' ?>"><?= (int) $pendingOrders ?></p>
            <p class="mt-1 text-[11px] text-gray-400">Online orders waiting to be processed</p>
          </div>
          <div class="dashboard-panel">
            <p class="text-xs font-semibold text-gray-500">Today's Sales</p>
            <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white">₱<?= number_format((float) $todaysRevenue, 2) ?></p>
            <p class="mt-1 text-[11px] text-gray-400">Excludes cancelled orders</p>
          </div>
        </div>

        <section class="mt-6">
          <h2 class="font-display text-lg font-bold text-ink dark:text-white mb-3">Sales trend</h2>
          <div class="dashboard-panel">
            <?php if (empty($dailySales)): ?>
              <p class="py-6 text-center text-sm text-gray-500">No completed sales in the last <?= (int) $salesTrendDays ?> days.</p>
            <?php else: ?>
              <div class="relative h-56">
                <canvas id="branchSalesTrendChart" role="img" aria-label="Line chart of daily completed sales revenue for this branch">
                  <?php foreach ($dailySales as $day): ?><?= date('M j', strtotime($day['sale_date'])) ?>: ₱<?= number_format((float) $day['revenue'], 2) ?> (<?= (int) $day['orders'] ?> orders). <?php endforeach; ?>
                </canvas>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <section class="mt-6">
          <h2 class="font-display text-lg font-bold text-ink dark:text-white mb-3">Top-selling products</h2>
          <div class="dashboard-panel">
            <?php if (empty($topProducts)): ?>
              <p class="py-6 text-center text-sm text-gray-500">Completed sales will appear here.</p>
            <?php else: ?>
              <div class="relative" style="height: <?= max(160, count($topProducts) * 40 + 40) ?>px;">
                <canvas id="branchTopProductsChart" role="img" aria-label="Horizontal bar chart of top-selling products for this branch">
                  <?php foreach ($topProducts as $item): ?><?= htmlspecialchars($item['product_name']) ?><?= !empty($item['variant_label']) ? ' (' . htmlspecialchars($item['variant_label']) . ')' : '' ?>: <?= (int) $item['units_sold'] ?> units, ₱<?= number_format((float) $item['revenue'], 2) ?>. <?php endforeach; ?>
                </canvas>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <section class="mt-6">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-display text-lg font-bold text-ink dark:text-white">Recent Orders</h2>
            <a href="/staff/orders" class="text-xs font-semibold text-brand hover:underline">View all &rarr;</a>
          </div>

          <?php if (empty($recentOrders)): ?>
            <div class="dashboard-panel text-center py-10">
              <p class="text-sm text-gray-500">No orders yet for this branch.</p>
            </div>
          <?php else: ?>
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden dark:bg-ink-2 dark:border-white/10">
              <table class="w-full text-sm">
                <thead>
                  <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200 dark:border-white/10">
                    <th class="px-5 py-3 font-semibold">Order</th>
                    <th class="px-3 py-3 font-semibold">Customer</th>
                    <th class="px-3 py-3 font-semibold text-right">Total</th>
                    <th class="px-3 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 font-semibold">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $statusStyles = [
                        'completed' => 'bg-teal-light text-teal',
                        'pending' => 'bg-amber/15 text-amber-700',
                        'packed' => 'bg-blue-50 text-blue-700',
                        'shipped' => 'bg-indigo-50 text-indigo-700',
                        'cancelled' => 'bg-gray-100 text-gray-500',
                        'refunded' => 'bg-red-100 text-red-600',
                    ];
                  ?>
                  <?php foreach ($recentOrders as $order): ?>
                    <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                      <td class="px-5 py-3 font-medium text-ink dark:text-white">
                        <a href="/staff/orders/<?= (int) $order['id'] ?>" class="hover:underline">#<?= (int) $order['id'] ?></a>
                      </td>
                      <td class="px-3 py-3 text-gray-700 dark:text-white/70"><?= htmlspecialchars($order['linked_customer_name'] ?? $order['customer_name'] ?? 'Walk-in') ?></td>
                      <td class="px-3 py-3 text-right font-medium text-ink dark:text-white">₱<?= number_format((float) $order['total_amount'], 2) ?></td>
                      <td class="px-3 py-3">
                        <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full <?= $statusStyles[$order['status']] ?? 'bg-gray-100 text-gray-500' ?>">
                          <?= htmlspecialchars(ucfirst($order['status'])) ?>
                        </span>
                      </td>
                      <td class="px-5 py-3 text-gray-500"><?= date('M j, Y g:ia', strtotime($order['created_at'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>
      </div>

      <!-- ── Stock Monitoring sidebar ─────────────────────────────── -->
      <aside class="lg:col-span-1">
        <div class="dashboard-panel">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-base font-bold text-ink dark:text-white">Stock Monitoring</h2>
            <a href="/manager/stock" class="text-xs font-semibold text-brand hover:underline">Manage &rarr;</a>
          </div>
          <?php if (!empty($inventorySummary['out_of_stock_count']) || !empty($inventorySummary['low_stock_count'])): ?>
            <span class="inline-flex items-center text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 mt-2">Needs attention</span>
          <?php endif; ?>
          <p class="mt-2 text-[11px] text-gray-400">Products your branch carries — true per-branch stock.</p>

          <div class="mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-lg bg-gray-50 dark:bg-white/5 px-2 py-2.5">
              <p class="font-display text-lg font-bold text-ink dark:text-white"><?= (int) $inventorySummary['product_count'] ?></p>
              <p class="text-[10px] text-gray-500">Products</p>
            </div>
            <div class="rounded-lg bg-amber-50 px-2 py-2.5">
              <p class="font-display text-lg font-bold text-amber-600"><?= (int) $inventorySummary['low_stock_count'] ?></p>
              <p class="text-[10px] text-amber-600">Low stock</p>
            </div>
            <div class="rounded-lg bg-red-50 px-2 py-2.5">
              <p class="font-display text-lg font-bold text-red-600"><?= (int) $inventorySummary['out_of_stock_count'] ?></p>
              <p class="text-[10px] text-red-600">Out of stock</p>
            </div>
          </div>

          <div class="mt-4 space-y-2">
            <?php if (empty($lowStockItems)): ?>
              <p class="text-sm text-gray-500 text-center py-6">All products are sufficiently stocked.</p>
            <?php else: ?>
              <?php foreach ($lowStockItems as $item): ?>
                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 dark:border-white/10 px-3 py-2">
                  <div class="min-w-0">
                    <p class="text-sm font-medium text-ink dark:text-white truncate"><?= htmlspecialchars($item['product_name']) ?></p>
                    <?php if (!empty($item['size']) || !empty($item['color'])): ?>
                      <p class="text-[11px] text-gray-400"><?= htmlspecialchars(trim(($item['size'] ?? '') . ' ' . ($item['color'] ?? ''))) ?></p>
                    <?php endif; ?>
                  </div>
                  <span class="shrink-0 text-xs font-bold px-2 py-1 rounded-full <?= (int) $item['stock'] === 0 ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700' ?>">
                    <?= (int) $item['stock'] === 0 ? 'Out of stock' : (int) $item['stock'] . ' left' ?>
                  </span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </aside>
    </div>
  </main>
</div>

<?php if (!empty($dailySales) || !empty($topProducts)): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<?php endif; ?>

<?php if (!empty($dailySales)): ?>
<script>
(function () {
  var sorted = <?= json_encode(array_reverse($dailySales)) ?>;
  var labels = sorted.map(function (d) {
    return new Date(d.sale_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  });
  var revenue = sorted.map(function (d) { return parseFloat(d.revenue); });
  var orders = sorted.map(function (d) { return parseInt(d.orders, 10); });

  new Chart(document.getElementById('branchSalesTrendChart'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Revenue',
        data: revenue,
        borderColor: '#2563EB',
        backgroundColor: 'rgba(37,99,235,0.08)',
        fill: true,
        tension: 0.35,
        pointRadius: 3,
        pointBackgroundColor: '#2563EB',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (ctx) {
              var i = ctx.dataIndex;
              return '\u20b1' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 }) + '  (' + orders[i] + ' orders)';
            }
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#9CA3AF', font: { size: 11 } } },
        y: {
          grid: { color: '#F3F4F6' },
          ticks: {
            color: '#9CA3AF',
            font: { size: 11 },
            callback: function (v) { return '\u20b1' + Number(v).toLocaleString('en-PH'); }
          }
        }
      }
    }
  });
})();
</script>
<?php endif; ?>

<?php if (!empty($topProducts)): ?>
<script>
(function () {
  var products = <?= json_encode(array_reverse($topProducts)) ?>;
  var labels = products.map(function (p) {
    return p.variant_label ? p.product_name + ' (' + p.variant_label + ')' : p.product_name;
  });
  var units = products.map(function (p) { return parseInt(p.units_sold, 10); });
  var revenue = products.map(function (p) { return parseFloat(p.revenue); });

  new Chart(document.getElementById('branchTopProductsChart'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Units sold',
        data: units,
        backgroundColor: '#2563EB',
        borderRadius: 4,
        maxBarThickness: 22
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (ctx) {
              var i = ctx.dataIndex;
              return ctx.parsed.x + ' units \u2022 \u20b1' + revenue[i].toLocaleString('en-PH', { minimumFractionDigits: 2 });
            }
          }
        }
      },
      scales: {
        x: { grid: { color: '#F3F4F6' }, ticks: { color: '#9CA3AF', font: { size: 11 } } },
        y: { grid: { display: false }, ticks: { color: '#374151', font: { size: 12 } } }
      }
    }
  });
})();
</script>
<?php endif; ?>