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
    
    <!-- 1. HEADER SECTION -->
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="eyebrow text-brand">Staff Dashboard</p>
        <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Welcome, <?= htmlspecialchars($profile['first_name']) ?></h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-white/60">
          Position: <span class="font-semibold text-ink dark:text-white"><?= htmlspecialchars(Staff::POSITIONS[$profile['position']] ?? $profile['position']) ?></span>
        </p>
      </div>

      <!-- Assigned Branch & Status Badge -->
      <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-2.5 dark:border-white/10 dark:bg-ink-2 shadow-sm">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
        </span>
        <div>
          <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Assigned Branch</p>
          <p class="text-xs font-bold text-ink dark:text-white"><?= htmlspecialchars($profile['branch_name']) ?> · <span class="text-emerald-500 font-medium">Shift Active</span></p>
        </div>
      </div>
    </header>

    <?php if ($profile['position'] === 'cashier'): ?>
      
      <!-- 2. PRIMARY CTA & CURRENT SHIFT PANEL -->
      <div class="grid gap-4 lg:grid-cols-3 mb-6">
        
        <!-- Primary New Sale CTA Button Card -->
        <div class="lg:col-span-1 dashboard-panel bg-gradient-to-br from-brand/90 to-brand-dark text-white flex flex-col justify-between p-5 rounded-2xl shadow-lg">
          <div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-white/20 text-white tracking-wider">Quick Terminal</span>
            <h2 class="mt-3 text-xl font-bold font-display">Ready for Checkout?</h2>
            <p class="mt-1 text-xs text-white/80">Start processing customer items, barcode scans, and payments immediately.</p>
          </div>
          <a href="/staff/pos" class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-white text-brand px-5 py-3 font-bold text-sm shadow-md hover:bg-gray-100 transition-all">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/></svg>
            + NEW SALE (POS)
          </a>
        </div>

        <!-- Current Shift & Cash Drawer Reconciliation Card -->
        <div class="lg:col-span-2 dashboard-panel flex flex-col justify-between">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-white/10">
            <div>
              <h2 class="font-display text-sm font-bold text-ink dark:text-white flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                CURRENT SHIFT RECONCILIATION
              </h2>
              <p class="text-[11px] text-gray-400">Shift Started Today · Drawer Balance Active</p>
            </div>
            <span class="text-xs font-mono font-bold text-brand bg-brand/10 dark:bg-brand/20 px-3 py-1 rounded-lg">Drawer #01</span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 py-3">
            <div>
              <p class="text-[10px] font-semibold text-gray-400 uppercase">Opening Cash</p>
              <p class="text-sm font-bold text-ink dark:text-white mt-0.5">₱<?= number_format((float) ($stats['opening_cash'] ?? 2000), 2) ?></p>
            </div>
            <div>
              <p class="text-[10px] font-semibold text-gray-400 uppercase">Cash Collected</p>
              <p class="text-sm font-bold text-emerald-500 mt-0.5">₱<?= number_format((float) ($stats['cash_sales'] ?? ($stats['sales_today'] * 0.6)), 2) ?></p>
            </div>
            <div>
              <p class="text-[10px] font-semibold text-gray-400 uppercase">Expected Cash</p>
              <p class="text-sm font-bold text-ink dark:text-white mt-0.5">₱<?= number_format((float) (($stats['opening_cash'] ?? 2000) + ($stats['cash_sales'] ?? ($stats['sales_today'] * 0.6))), 2) ?></p>
            </div>
            <div>
              <p class="text-[10px] font-semibold text-gray-400 uppercase">Transactions</p>
              <p class="text-sm font-bold text-ink dark:text-white mt-0.5"><?= (int) ($stats['orders_today'] ?? 0) ?></p>
            </div>
          </div>

          <div class="pt-2 flex items-center gap-2">
            <button type="button" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-white/10 text-ink dark:text-white hover:bg-gray-200">View Shift Details</button>
            <button type="button" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500/20">End Shift & Reconcile</button>
          </div>
        </div>

      </div>

      <!-- 3. PRIMARY OPERATIONAL KPI CARDS -->
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
        <div class="dashboard-panel border-l-4 border-l-brand">
          <p class="text-xs font-semibold text-gray-400">Today's Sales</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white">₱<?= number_format((float) ($stats['sales_today'] ?? 0), 2) ?></p>
          <p class="mt-1 text-[11px] text-emerald-500 font-medium">↑ Branch performance today</p>
        </div>

        <div class="dashboard-panel border-l-4 border-l-blue-500">
          <p class="text-xs font-semibold text-gray-400">Transactions Processed</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) ($stats['orders_today'] ?? 0) ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Completed order checkouts</p>
        </div>

        <div class="dashboard-panel border-l-4 border-l-amber-500">
          <p class="text-xs font-semibold text-gray-400">Avg Transaction Value</p>
          <?php 
            $avgValue = ($stats['orders_today'] ?? 0) > 0 ? ($stats['sales_today'] / $stats['orders_today']) : 0;
          ?>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white">₱<?= number_format((float) $avgValue, 2) ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Average ticket size</p>
        </div>

        <div class="dashboard-panel border-l-4 border-l-rose-500">
          <p class="text-xs font-semibold text-gray-400">Today's Returns / Refunds</p>
          <p class="mt-2 font-display text-2xl font-bold text-rose-500"><?= (int) ($stats['returns_pending'] ?? 0) ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Pending customer return tickets</p>
        </div>
      </div>

      <!-- 4. ACTION REQUIRED / NEEDS ATTENTION STREAM -->
      <?php if (($stats['returns_pending'] ?? 0) > 0 || ($stats['reports_open'] ?? 0) > 0): ?>
      <div class="mb-6 p-4 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-200 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <span class="flex h-3 w-3 rounded-full bg-amber-500"></span>
          <div>
            <p class="text-xs font-bold text-ink dark:text-white">Action Required</p>
            <p class="text-[11px] text-gray-400">You have <?= (int) ($stats['returns_pending'] ?? 0) ?> return request(s) awaiting cashier verification.</p>
          </div>
        </div>
        <a href="/staff/returns" class="text-xs font-bold bg-amber-500 text-black px-3 py-1.5 rounded-lg hover:bg-amber-400">Review Refunds &rarr;</a>
      </div>
      <?php endif; ?>

      <!-- 5. RECENT TRANSACTIONS & PAYMENT METHOD BREAKDOWN -->
      <div class="grid gap-6 lg:grid-cols-3 mb-6">
        
        <!-- Recent Transactions Table -->
        <div class="lg:col-span-2 dashboard-panel">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="font-display text-base font-bold text-ink dark:text-white">Recent Transactions</h2>
              <p class="text-[11px] text-gray-400">Latest customer sales in this branch</p>
            </div>
            <a href="/staff/orders" class="text-xs font-semibold text-brand hover:underline">View All Orders &rarr;</a>
          </div>

          <?php if (empty($stats['recent_transactions'])): ?>
            <div class="py-8 text-center border border-dashed border-gray-200 dark:border-white/10 rounded-xl">
              <p class="text-xs text-gray-400">No transactions recorded yet today.</p>
              <a href="/staff/pos" class="mt-2 inline-block text-xs font-bold text-brand hover:underline">+ Start First Sale</a>
            </div>
          <?php else: ?>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="border-b border-gray-100 dark:border-white/10 text-gray-400 font-semibold uppercase">
                    <th class="py-2.5 px-3">Order ID</th>
                    <th class="py-2.5 px-3">Time</th>
                    <th class="py-2.5 px-3">Amount</th>
                    <th class="py-2.5 px-3">Payment</th>
                    <th class="py-2.5 px-3 text-right">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                  <?php foreach ($stats['recent_transactions'] as $tx): ?>
                    <tr>
                      <td class="py-3 px-3 font-bold text-ink dark:text-white">#<?= htmlspecialchars($tx['order_number'] ?? $tx['id']) ?></td>
                      <td class="py-3 px-3 text-gray-400"><?= date('g:i A', strtotime($tx['created_at'] ?? 'now')) ?></td>
                      <td class="py-3 px-3 font-semibold text-ink dark:text-white">₱<?= number_format((float) ($tx['total_amount'] ?? 0), 2) ?></td>
                      <td class="py-3 px-3">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-white/10 text-gray-300">
                          <?= strtoupper(htmlspecialchars($tx['payment_method'] ?? 'CASH')) ?>
                        </span>
                      </td>
                      <td class="py-3 px-3 text-right">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                          Paid
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Payment Method Breakdown (Cash + PayMongo) -->
        <div class="dashboard-panel flex flex-col justify-between">
          <div>
            <h2 class="font-display text-base font-bold text-ink dark:text-white mb-1">Payment Method Breakdown</h2>
            <p class="text-[11px] text-gray-400 mb-4">Shift reconciliation (Over-the-counter & PayMongo)</p>

            <div class="space-y-3">
              <!-- OTC Cash -->
              <div>
                <div class="flex justify-between text-xs font-medium mb-1">
                  <span class="text-ink dark:text-white flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Cash (POS Drawer)
                  </span>
                  <span class="font-bold text-emerald-400">₱<?= number_format((float) ($stats['cash_sales'] ?? 0), 2) ?></span>
                </div>
                <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden">
                  <div class="h-full bg-emerald-500 rounded-full" style="width: <?= ($stats['sales_today'] ?? 0) > 0 ? min(100, round((($stats['cash_sales'] ?? 0) / $stats['sales_today']) * 100)) : 0 ?>%"></div>
                </div>
              </div>

              <!-- PayMongo (E-Wallets / Cards) -->
              <div>
                <div class="flex justify-between text-xs font-medium mb-1">
                  <span class="text-ink dark:text-white flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> PayMongo (Digital Gateway)
                  </span>
                  <span class="font-bold text-blue-400">₱<?= number_format((float) ($stats['paymongo_sales'] ?? 0), 2) ?></span>
                </div>
                <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden">
                  <div class="h-full bg-blue-500 rounded-full" style="width: <?= ($stats['sales_today'] ?? 0) > 0 ? min(100, round((($stats['paymongo_sales'] ?? 0) / $stats['sales_today']) * 100)) : 0 ?>%"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-3 border-t border-gray-100 dark:border-white/10 text-center">
            <span class="text-[11px] text-gray-400">Total Shift Turnover: <strong class="text-ink dark:text-white">₱<?= number_format((float) ($stats['sales_today'] ?? 0), 2) ?></strong></span>
          </div>
        </div>

      </div>

      <!-- 6. SALES PERFORMANCE TREND CHART -->
      <div class="mt-4">
        <?php renderTrendBars($stats['sales_trend'] ?? [], 'revenue', 'Sales Performance, last 7 days', 'currency'); ?>
      </div>

    <?php elseif ($profile['position'] === 'order_staff'): ?>
      <!-- ORDER STAFF BLOCK -->
      <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="/staff/orders" class="inline-flex items-center gap-2 rounded-xl bg-brand px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-dark transition-all">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2l1.5 5H21l-3 8H8L4 4H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg>
          Open Full Orders Queue
        </a>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-5">
        <div class="dashboard-panel border-l-4 border-l-amber-500">
          <p class="text-xs font-semibold text-gray-400">To Pack (Pending)</p>
          <p class="mt-2 font-display text-2xl font-bold <?= ($stats['orders_pending'] ?? 0) > 0 ? 'text-amber-500' : 'text-ink dark:text-white' ?>"><?= (int) ($stats['orders_pending'] ?? 0) ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Waiting to be packed</p>
        </div>

        <div class="dashboard-panel border-l-4 border-l-blue-500">
          <p class="text-xs font-semibold text-gray-400">Packed & Ready</p>
          <p class="mt-2 font-display text-2xl font-bold text-blue-400"><?= (int) ($stats['orders_packed'] ?? 0) ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Handed to courier next</p>
        </div>

        <div class="dashboard-panel border-l-4 border-l-emerald-500">
          <p class="text-xs font-semibold text-gray-400">Orders Today</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) ($stats['orders_today'] ?? 0) ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Placed today in this branch</p>
        </div>

        <div class="dashboard-panel border-l-4 border-l-rose-500">
          <p class="text-xs font-semibold text-gray-400">Returns / Flagged</p>
          <p class="mt-2 font-display text-2xl font-bold text-ink dark:text-white"><?= (int) ($stats['orders_returned'] ?? 0) ?></p>
          <p class="mt-1 text-[11px] text-gray-400">Pending return checks</p>
        </div>
      </div>

      <div class="mt-4">
        <?php renderTrendBars($stats['orders_trend'] ?? [], 'count', 'Orders placed, last 7 days'); ?>
      </div>

    <?php endif; ?>

  </main>
</div>