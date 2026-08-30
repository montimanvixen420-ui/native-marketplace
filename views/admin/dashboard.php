<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  
  <main class="min-w-0 flex-1 px-5 py-7 sm:px-8">
    <?php require __DIR__ . '/../partials/dashboard-topbar.php'; ?>

    <!-- Account Status Alert -->
    <?php if ($sellerStatus !== 'approved'): ?>
      <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800/60 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-800 dark:text-amber-200 flex items-center gap-2">
        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 text-amber-600"></i>
        <div>
          <?php if ($sellerStatus === 'suspended'): ?>
            Your seller account is <strong>temporarily locked</strong> while Superadmin reviews a safety concern.
          <?php else: ?>
            Your seller account is <strong><?= htmlspecialchars($sellerStatus) ?></strong>. Selling tools unlock after Superadmin approval.
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Header & Quick Actions -->
    <header class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="eyebrow text-brand">Seller command center</p>
        <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Good day, <?= htmlspecialchars($name) ?></h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-white/50">A real-time analytics look at your store performance, fulfillment, and inventory.</p>
      </div>

      <?php if ($sellerStatus === 'approved'): ?>
        <div class="flex items-center gap-2.5">
          <a href="/products/create" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-xs hover:border-brand dark:border-white/10 dark:bg-ink-2 dark:text-white">
            <i data-lucide="plus" class="w-4 h-4"></i> Add product
          </a>
          <a href="/pos" style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-bold shadow-md hover:opacity-90 transition cursor-pointer">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Open POS
          </a>
        </div>
      <?php endif; ?>
    </header>

    <!-- 1. Top KPI Summary Cards -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      
      <a href="/admin/analytics" class="dashboard-stat group transition">
        <span class="stat-label">Today's revenue</span>
        <b class="text-2xl">₱<?= number_format((float)$todaysRevenue, 2) ?></b>
        <small class="text-brand flex items-center gap-1">
          <i data-lucide="check-circle-2" class="w-3 h-3"></i> Completed orders only
        </small>
      </a>

      <a href="/admin/orders" class="dashboard-stat group transition">
        <span class="stat-label">Orders today</span>
        <b class="text-2xl"><?= (int)$todaysOrders ?></b>
        <small class="group-hover:translate-x-0.5 transition-transform">View fulfilment queue &rarr;</small>
      </a>

      <a href="/products" class="dashboard-stat group transition">
        <span class="stat-label">Active products</span>
        <b class="text-2xl"><?= (int)$activeProducts ?></b>
        <small class="text-gray-500 dark:text-white/50"><?= (int)$inventory['total_units'] ?> units in stock</small>
      </a>

      <a href="/admin/orders" class="dashboard-stat group transition border-amber-200/60 dark:border-amber-800/40">
        <span class="stat-label text-amber-700 dark:text-amber-400">Needs attention</span>
        <b class="text-2xl text-amber-600 dark:text-amber-400"><?= (int)$pendingOnlineOrders ?></b>
        <small class="text-amber-600 dark:text-amber-400 font-semibold group-hover:translate-x-0.5 transition-transform">Pending online orders &rarr;</small>
      </a>

    </section>

    <!-- 2. Order Fulfillment Pipeline Indicator -->
    <div class="mt-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-ink-2 p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <i data-lucide="truck" class="w-5 h-5 text-brand"></i>
        <span class="text-xs font-bold uppercase tracking-wider text-ink dark:text-white">Fulfillment Status Pipeline:</span>
      </div>
      <div class="flex items-center gap-3 flex-wrap text-xs">
        <span class="px-3 py-1 rounded-full font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
          Pending: <?= (int)$pendingOnlineOrders ?>
        </span>
        <span class="px-3 py-1 rounded-full font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
          Completed Today: <?= (int)$todaysOrders ?>
        </span>
      </div>
    </div>

    <!-- 3. Sales Activity & Inventory Health -->
    <section class="mt-5 grid gap-5 xl:grid-cols-[1.45fr_.8fr]">
      
      <!-- Chart Activity Panel -->
      <div class="dashboard-panel flex flex-col justify-between">
        <div>
          <div class="flex items-start justify-between border-b border-gray-100 dark:border-white/10 pb-4">
            <div>
              <p class="eyebrow">Sales snapshot</p>
              <h2 class="font-display text-xl font-bold text-ink dark:text-white">Today’s Activity</h2>
            </div>
            <span class="rounded-full bg-brand-light dark:bg-brand/20 px-3 py-1 text-xs font-bold text-brand">Live overview</span>
          </div>

          <!-- Hourly Bar Chart Visual -->
          <div class="mt-7 flex h-44 items-end gap-2 border-b border-l border-gray-100 dark:border-white/10 px-4 pt-4">
            <?php foreach ([28,44,35,65,48,78,56,90,72,50,62,42] as $bar): ?>
              <span class="flex-1 rounded-t bg-brand/<?= $bar === 90 ? '100' : '25' ?> hover:bg-brand transition-colors cursor-pointer" style="height:<?= $bar ?>%"></span>
            <?php endforeach; ?>
          </div>
          <div class="mt-3 flex justify-between text-[10px] font-semibold uppercase tracking-wide text-gray-400">
            <span>8 AM</span>
            <span>12 PM</span>
            <span>4 PM</span>
            <span>8 PM</span>
          </div>
        </div>

        <!-- Metric Sub-Cards -->
        <div class="mt-6 grid grid-cols-3 gap-3">
          <div class="rounded-xl bg-brand-light dark:bg-brand/10 p-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-brand">Avg / order</p>
            <p class="mt-1 font-display text-lg font-bold text-ink dark:text-white">
              ₱<?= $todaysOrders ? number_format($todaysRevenue / $todaysOrders, 2) : '0.00' ?>
            </p>
          </div>
          <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-white/50">Stock units</p>
            <p class="mt-1 font-display text-lg font-bold text-ink dark:text-white"><?= (int)$inventory['total_units'] ?></p>
          </div>
          <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-white/50">Low stock</p>
            <p class="mt-1 font-display text-lg font-bold text-ink dark:text-white"><?= (int)$inventory['low_stock_count'] ?></p>
          </div>
        </div>
      </div>

      <!-- Inventory Health Aside Panel -->
      <aside class="dashboard-panel flex flex-col justify-between">
        <div>
          <p class="eyebrow">Inventory health</p>
          <h2 class="font-display text-xl font-bold text-ink dark:text-white">Stock at a Glance</h2>

          <div class="mt-6 space-y-5">
            <div>
              <div class="mb-2 flex justify-between text-sm">
                <span class="font-semibold text-ink dark:text-white">Healthy Items</span>
                <b class="text-brand"><?= max(0, (int)$inventory['product_count'] - (int)$inventory['low_stock_count'] - (int)$inventory['out_of_stock_count']) ?></b>
              </div>
              <div class="stock-meter">
                <span style="width:<?= $inventory['product_count'] ? max(0, min(100, 100 - (($inventory['low_stock_count'] + $inventory['out_of_stock_count']) / $inventory['product_count'] * 100))) : 0 ?>%"></span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="rounded-xl border border-amber-100 dark:border-amber-800/40 bg-amber-50 dark:bg-amber-950/30 p-3">
                <p class="text-[10px] font-bold uppercase text-amber-700 dark:text-amber-300">Low Stock</p>
                <b class="mt-1 block text-xl text-amber-800 dark:text-amber-200"><?= (int)$inventory['low_stock_count'] ?></b>
              </div>
              <div class="rounded-xl border border-red-100 dark:border-red-800/40 bg-red-50 dark:bg-red-950/30 p-3">
                <p class="text-[10px] font-bold uppercase text-red-600 dark:text-red-300">Out of Stock</p>
                <b class="mt-1 block text-xl text-red-700 dark:text-red-200"><?= (int)$inventory['out_of_stock_count'] ?></b>
              </div>
            </div>

            <!-- Low Stock Items List -->
            <?php if(empty($lowStockItems)): ?>
              <div class="rounded-xl bg-brand-light dark:bg-brand/10 p-3 text-sm text-brand flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                <span>All stock levels look healthy.</span>
              </div>
            <?php else: ?>
              <div class="space-y-2 border-t border-gray-100 dark:border-white/10 pt-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Items requiring restock:</p>
                <?php foreach($lowStockItems as $item): ?>
                  <div class="flex items-center justify-between text-sm py-1">
                    <span class="truncate pr-3 text-gray-600 dark:text-white/70 font-medium">
                      <?= htmlspecialchars($item['name']) ?>
                    </span>
                    <b class="text-amber-700 dark:text-amber-400 shrink-0"><?= (int)$item['stock'] ?> left</b>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="pt-4 mt-4 border-t border-gray-100 dark:border-white/10">
          <a href="/products" class="inline-flex items-center gap-1 text-sm font-bold text-brand hover:underline">
            Manage inventory &rarr;
          </a>
        </div>
      </aside>

    </section>
  </main>
</div>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
  if (typeof lucide !== 'undefined') lucide.createIcons();
</script>