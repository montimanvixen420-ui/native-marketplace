<?php
/**
 * Renders a small 7-bar trend chart. $trend is a fixed-length array of
 * ['label'=>..., <valueKey>=>...] rows (oldest first), from one of the
 * StaffController::daily*() helpers. $format lets a value be shown as
 * currency in the tooltip/total line.
 */
function renderTrendBars(array $trend, string $valueKey, string $title, string $format = 'int'): void {
    $values = array_column($trend, $valueKey);
    $max = max(1, max($values));
    $total = array_sum($values);
    $totalDisplay = $format === 'currency' ? '₱' . number_format((float) $total, 2) : number_format((float) $total);
    ?>
    <div class="dashboard-panel">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-display text-base font-bold text-ink dark:text-white"><?= htmlspecialchars($title) ?></h2>
        <span class="text-xs font-semibold text-gray-400">Total: <?= $totalDisplay ?></span>
      </div>
      <div class="flex items-end gap-2 h-24">
        <?php foreach ($trend as $day): $v = (float) $day[$valueKey]; $h = max(4, (int) round(($v / $max) * 80)); ?>
          <div class="flex-1 flex flex-col items-center gap-1.5">
            <div class="w-full rounded-t bg-brand/70" style="height: <?= $h ?>px" title="<?= htmlspecialchars($format === 'currency' ? '₱' . number_format($v, 2) : (string) (int) $v) ?>"></div>
            <span class="text-[10px] text-gray-400"><?= htmlspecialchars($day['label']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
}
?>
<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <main class="flex-1 px-5 py-7 md:px-8">
    <header class="mb-6">
      <p class="eyebrow text-brand">Staff Dashboard</p>
      <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Welcome, <?= htmlspecialchars($profile['first_name']) ?></h1>
      <p class="mt-2 text-sm text-gray-500 dark:text-white/60">
        Position: <span class="font-semibold text-ink dark:text-white"><?= htmlspecialchars(Staff::POSITIONS[$profile['position']] ?? $profile['position']) ?></span>
      </p>
    </header>

    <section class="mb-5 dashboard-panel">
      <p class="text-xs font-semibold text-gray-500">Assigned Branch</p>
      <p class="mt-1 font-display text-xl font-bold text-ink dark:text-white"><?= htmlspecialchars($profile['branch_name']) ?></p>
      <p class="mt-1 text-xs text-gray-500">Your branch is assigned by your Branch Manager and cannot be changed here.</p>
    </section>

      <?php if ($profile['position'] === 'customer_service'): ?>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Orders (this branch)</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) $stats['orders_total'] ?></p>
          <a href="/staff/orders" class="mt-1 inline-block text-[11px] font-semibold text-brand hover:underline">Look up an order &rarr;</a>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Pending Return Requests</p>
          <p class="mt-2 font-display text-2xl font-bold <?= $stats['returns_pending'] > 0 ? 'text-brand' : 'text-ink dark:text-white' ?>"><?= (int) $stats['returns_pending'] ?></p>
          <a href="/staff/returns" class="mt-1 inline-block text-[11px] font-semibold text-brand hover:underline">Review requests &rarr;</a>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Open Product Reports</p>
          <p class="mt-2 font-display text-2xl font-bold <?= $stats['reports_open'] > 0 ? 'text-amber-600' : 'text-ink dark:text-white' ?>"><?= (int) $stats['reports_open'] ?></p>
          <a href="/staff/reports" class="mt-1 inline-block text-[11px] font-semibold text-brand hover:underline">View reports &rarr;</a>
        </div>
      </div>
      <div class="mt-4">
        <?php renderTrendBars($stats['activity_trend'], 'count', 'Returns & reports filed, last 7 days'); ?>
      </div>
      <?php elseif ($profile['position'] === 'order_staff'): ?>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Pending Orders</p>
          <p class="mt-2 font-display text-2xl font-bold <?= $stats['orders_pending'] > 0 ? 'text-brand' : 'text-ink dark:text-white' ?>"><?= (int) $stats['orders_pending'] ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Waiting to be packed</p>
          <a href="/staff/orders" class="mt-1 inline-block text-[11px] font-semibold text-brand hover:underline">Process orders &rarr;</a>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Packed, Awaiting Pickup</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) $stats['orders_packed'] ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Handed to courier next</p>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Today's Orders</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) $stats['orders_today'] ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Placed today, this branch</p>
        </div>
      </div>
      <div class="mt-4">
        <?php renderTrendBars($stats['orders_trend'], 'count', 'Orders placed, last 7 days'); ?>
      </div>
      <?php elseif ($profile['position'] === 'cashier'): ?>
      <div class="mb-4">
        <a href="/staff/pos" class="inline-flex items-center gap-2 rounded-xl bg-brand px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-dark">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/></svg>
          Open Point of Sale
        </a>
      </div>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Today's Sales</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white">₱<?= number_format((float) $stats['sales_today'], 2) ?></p>
          <p class="mt-1 text-[11px] text-gray-400">This branch, today</p>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Today's Orders</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) $stats['orders_today'] ?></p>
          <a href="/staff/orders" class="mt-1 inline-block text-[11px] font-semibold text-brand hover:underline">View orders &rarr;</a>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Orders (this branch)</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) $stats['orders_total'] ?></p>
        </div>
      </div>
      <div class="mt-4">
        <?php renderTrendBars($stats['sales_trend'], 'revenue', 'Sales, last 7 days', 'currency'); ?>
      </div>
      <?php elseif ($profile['position'] === 'inventory_staff'): ?>
      <div class="mb-4">
        <a href="/manager/stock" class="inline-flex items-center gap-2 rounded-xl bg-brand px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-dark">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
          Manage Branch Stock
        </a>
      </div>
      <div class="grid gap-4 sm:grid-cols-3 mb-4">
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Products (this branch)</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) $stats['product_count'] ?></p>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Low Stock</p>
          <p class="mt-2 font-display text-2xl font-bold <?= $stats['low_stock_count'] > 0 ? 'text-amber-600' : 'text-ink dark:text-white' ?>"><?= (int) $stats['low_stock_count'] ?></p>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Out of Stock</p>
          <p class="mt-2 font-display text-2xl font-bold <?= $stats['out_of_stock_count'] > 0 ? 'text-red-600' : 'text-ink dark:text-white' ?>"><?= (int) $stats['out_of_stock_count'] ?></p>
        </div>
      </div>
      <div class="dashboard-panel">
        <h2 class="font-display text-base font-bold text-ink dark:text-white mb-3">Needs restocking</h2>
        <?php if (empty($stats['low_stock_items'])): ?>
          <p class="text-sm text-gray-500 text-center py-6">All products are sufficiently stocked.</p>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach ($stats['low_stock_items'] as $item): ?>
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
          </div>
        <?php endif; ?>
      </div>
      <div class="mt-4">
        <?php renderTrendBars($stats['restock_trend'], 'count', 'Restock activity, last 7 days'); ?>
      </div>
      <?php else: ?>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Assigned Branch</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white">1</p>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Today's Orders</p>
          <p class="mt-2 font-display text-2xl font-bold text-gray-300">—</p>
          <p class="mt-1 text-[11px] text-gray-400">Connects once Orders is branch-scoped</p>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Pending Orders</p>
          <p class="mt-2 font-display text-2xl font-bold text-gray-300">—</p>
          <p class="mt-1 text-[11px] text-gray-400">Connects once Orders is branch-scoped</p>
        </div>
        <div class="dashboard-panel">
          <p class="text-xs font-semibold text-gray-500">Low Stock Products</p>
          <p class="mt-2 font-display text-2xl font-bold text-gray-300">—</p>
          <p class="mt-1 text-[11px] text-gray-400">Connects once Inventory is branch-scoped</p>
        </div>
      </div>
      <?php endif; ?>

  </main>
</div>