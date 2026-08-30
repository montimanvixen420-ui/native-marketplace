<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <main class="flex-1 min-w-0 px-4 sm:px-8 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-7">
      <div>
        <h1 class="font-display font-semibold text-2xl text-ink dark:text-white">Inventory &amp; sales analytics</h1>
        <p class="text-sm text-gray-500 dark:text-white/50">A clear view of your stock and completed sales.</p>
      </div>
      <a href="/products/create" class="inline-flex justify-center rounded-xl bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">+ Add product</a>
    </div>

    <section class="mb-8">
      <h2 class="font-display text-base font-semibold text-ink dark:text-white mb-3">Inventory</h2>
      <div class="grid grid-cols-1 min-[420px]:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2"><p class="text-xs text-gray-500 dark:text-white/50">Products</p><p class="mt-1 text-2xl font-semibold text-ink dark:text-white"><?= (int) $inventory['product_count'] ?></p></div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2"><p class="text-xs text-gray-500 dark:text-white/50">Total units</p><p class="mt-1 text-2xl font-semibold text-ink dark:text-white"><?= (int) $inventory['total_units'] ?></p></div>
        <div class="rounded-2xl border border-amber/30 bg-amber/10 p-5 dark:border-amber-400/20 dark:bg-amber-500/10"><p class="text-xs text-amber-700">Low-stock variants</p><p class="mt-1 text-2xl font-semibold text-ink dark:text-white"><?= (int) $inventory['low_stock_count'] ?></p><p class="mt-1 text-[11px] text-amber-700">1–<?= (int) $threshold ?> units left</p></div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5"><p class="text-xs text-red-600">Out of stock</p><p class="mt-1 text-2xl font-semibold text-ink dark:text-white"><?= (int) $inventory['out_of_stock_count'] ?></p><p class="mt-1 text-[11px] text-red-600">Needs restocking</p></div>
      </div>
    </section>

    <section class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
        <h2 class="font-display text-base font-semibold text-ink dark:text-white">Sales</h2>
        <?php if (!empty($branches)): ?>
          <form method="get" action="/admin/analytics" class="flex items-center gap-2">
            <label for="branch_id" class="text-xs text-gray-500 dark:text-white/50">Branch</label>
            <select id="branch_id" name="branch_id" onchange="this.form.submit()" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-ink dark:border-white/10 dark:bg-ink-2 dark:text-white">
              <option value="">All branches</option>
              <?php foreach ($branches as $branch): ?>
                <option value="<?= (int) $branch['id'] ?>" <?= $selectedBranchId === (int) $branch['id'] ? 'selected' : '' ?>><?= htmlspecialchars($branch['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        <?php endif; ?>
      </div>
      <div class="grid grid-cols-1 min-[420px]:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2"><p class="text-xs text-gray-500 dark:text-white/50">Revenue — last 30 days</p><p class="mt-1 text-2xl font-semibold text-ink dark:text-white">₱<?= number_format((float) $sales['revenue_30_days'], 2) ?></p></div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2"><p class="text-xs text-gray-500 dark:text-white/50">Orders — last 30 days</p><p class="mt-1 text-2xl font-semibold text-ink dark:text-white"><?= (int) $sales['orders_30_days'] ?></p></div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2"><p class="text-xs text-gray-500 dark:text-white/50">All-time revenue</p><p class="mt-1 text-2xl font-semibold text-ink dark:text-white">₱<?= number_format((float) $sales['lifetime_revenue'], 2) ?></p></div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2"><p class="text-xs text-gray-500 dark:text-white/50">Completed orders</p><p class="mt-1 text-2xl font-semibold text-ink dark:text-white"><?= (int) $sales['completed_orders'] ?></p></div>
      </div>
    </section>

    <?php if (!empty($branchComparison)): ?>
    <section class="mb-8 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2">
      <h2 class="font-display font-semibold text-ink dark:text-white mb-1">Branch comparison</h2>
      <p class="text-sm text-gray-500 dark:text-white/50 mb-4">Revenue by branch — last <?= (int) $salesTrendDays ?> days, sorted highest first.</p>
      <div class="relative" style="height: <?= max(160, count($branchComparison) * 40 + 40) ?>px;">
        <canvas id="branchComparisonChart" role="img" aria-label="Horizontal bar chart comparing revenue across branches">
          <?php foreach ($branchComparison as $b): ?><?= htmlspecialchars($b['branch_name']) ?>: ₱<?= number_format((float) $b['revenue'], 2) ?> (<?= (int) $b['orders'] ?> orders). <?php endforeach; ?>
        </canvas>
      </div>
    </section>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2">
        <div class="flex items-center justify-between mb-4"><h2 class="font-display font-semibold text-ink dark:text-white">Low stock</h2><span class="text-xs text-gray-500">≤ <?= (int) $threshold ?> units</span></div>
        <?php if (empty($lowStockItems)): ?>
          <p class="py-6 text-center text-sm text-gray-500">All inventory levels look healthy.</p>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($lowStockItems as $item): ?>
              <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3 last:border-0 last:pb-0 dark:border-white/10">
                <div class="min-w-0"><p class="truncate text-sm font-medium text-ink dark:text-white"><?= htmlspecialchars($item['name']) ?></p><?php if (!empty($item['variant_id'])): ?><p class="text-xs text-gray-500"><?= htmlspecialchars($item['size']) ?><?= $item['color'] !== 'N/A' ? ' · ' . htmlspecialchars($item['color']) : '' ?></p><?php endif; ?></div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold <?= (int) $item['stock'] === 0 ? 'bg-red-50 text-red-600' : 'bg-amber/15 text-amber-700' ?>"><?= (int) $item['stock'] ?> left</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2">
        <h2 class="font-display font-semibold text-ink dark:text-white mb-4">Top-selling products</h2>
        <?php if (empty($topProducts)): ?>
          <p class="py-6 text-center text-sm text-gray-500">Completed sales will appear here.</p>
        <?php else: ?>
          <div class="relative" style="height: <?= max(160, count($topProducts) * 40 + 40) ?>px;">
            <canvas id="topProductsChart" role="img" aria-label="Horizontal bar chart of top-selling products by units sold">
              <?php foreach ($topProducts as $item): ?><?= htmlspecialchars($item['product_name']) ?><?= !empty($item['variant_label']) ? ' (' . htmlspecialchars($item['variant_label']) . ')' : '' ?>: <?= (int) $item['units_sold'] ?> units, ₱<?= number_format((float) $item['revenue'], 2) ?>. <?php endforeach; ?>
            </canvas>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-ink-2">
      <h2 class="font-display font-semibold text-ink dark:text-white mb-4">Daily completed sales</h2>
      <?php if (empty($dailySales)): ?>
        <p class="py-6 text-center text-sm text-gray-500">No completed sales in the last <?= (int) ($salesTrendDays ?? 7) ?> days.</p>
      <?php else: ?>
        <div class="relative h-64">
          <canvas id="dailySalesChart" role="img" aria-label="Line chart of daily completed sales revenue">
            <?php foreach ($dailySales as $day): ?><?= date('M j', strtotime($day['sale_date'])) ?>: ₱<?= number_format((float) $day['revenue'], 2) ?> (<?= (int) $day['orders'] ?> orders). <?php endforeach; ?>
          </canvas>
        </div>
      <?php endif; ?>
    </section>
  </main>
</div>

<?php if (!empty($dailySales) || !empty($topProducts) || !empty($branchComparison)): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<?php endif; ?>

<?php if (!empty($branchComparison)): ?>
<script>
(function () {
  var branches = <?= json_encode(array_reverse($branchComparison)) ?>;
  var labels = branches.map(function (b) { return b.branch_name; });
  var revenue = branches.map(function (b) { return parseFloat(b.revenue); });
  var orders = branches.map(function (b) { return parseInt(b.orders, 10); });

  new Chart(document.getElementById('branchComparisonChart'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Revenue',
        data: revenue,
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
              return '\u20b1' + ctx.parsed.x.toLocaleString('en-PH', { minimumFractionDigits: 2 }) + '  (' + orders[i] + ' orders)';
            }
          }
        }
      },
      scales: {
        x: {
          grid: { color: '#F3F4F6' },
          ticks: {
            color: '#9CA3AF',
            font: { size: 11 },
            callback: function (v) { return '\u20b1' + Number(v).toLocaleString('en-PH'); }
          }
        },
        y: { grid: { display: false }, ticks: { color: '#374151', font: { size: 12 } } }
      }
    }
  });
})();
</script>
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

  new Chart(document.getElementById('dailySalesChart'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Revenue',
        data: revenue,
        orders: orders,
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

  new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Units sold',
        data: units,
        revenue: revenue,
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