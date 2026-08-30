<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <main class="min-w-0 flex-1 px-5 py-7 sm:px-8">
    <?php require __DIR__ . '/../partials/dashboard-topbar.php'; ?>
    <?php if ($sellerStatus !== 'approved'): ?>
      <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <?php if ($sellerStatus === 'suspended'): ?>
          Your seller account is <strong>
            temporarily locked</strong> 
            while Superadmin reviews a safety concern.
            <?php else: ?>Your seller account is <strong>
              <?= htmlspecialchars($sellerStatus) ?></strong>
              . Selling tools unlock after Superadmin approval.
              <?php endif; ?>
            </div
            ><?php endif; ?>
    <header class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"><div>
      <p class="eyebrow text-brand">Seller command center</p>
      <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Good day, <?= htmlspecialchars($name) ?></h1>
      <p class="mt-2 text-sm text-gray-500 dark:text-white/50">A real-time look at your store performance and inventory.</p>
    </div><?php if ($sellerStatus === 'approved'): ?>
      <div class="flex gap-2">
        <a href="/products/create" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm hover:border-brand dark:border-white/10 dark:bg-ink-2 dark:text-white">Add product</a>
        <a href="/pos" class="rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark">Open POS</a>
      </div>
      <?php endif; ?>
    </header>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <a href="/admin/analytics" class="dashboard-stat">
        <span class="stat-label">Today's revenue</span>
        <b>₱
          <?= number_format((float)$todaysRevenue, 2) ?>
        </b>
        <small class="text-brand">Completed orders only</small>
      </a>
      <a href="/admin/orders" class="dashboard-stat">
        <span class="stat-label">Orders today</span>
        <b><?= (int)$todaysOrders ?></b>
        <small>View fulfilment queue →</small>
      </a
      ><a href="/products" class="dashboard-stat">
        <span class="stat-label">Active products</span>
        <b><?= (int)$activeProducts ?></b>
        <small><?= (int)$inventory['total_units'] ?> units in stock</small>
      </a><a href="/admin/orders" class="dashboard-stat">
        <span class="stat-label">Needs attention</span>
        <b><?= (int)$pendingOnlineOrders ?></b>
        <small class="text-brand">Pending online orders →</small>
      </a
      ></section>
    <section class="mt-5 grid gap-5 xl:grid-cols-[1.45fr_.8fr]">
      <div class="dashboard-panel">
        <div class="flex items-start justify-between"><div>
          <p class="eyebrow">Sales snapshot</p>
          <h2>Today’s activity</h2>
        </div>
        <span class="rounded-full bg-brand-light px-3 py-1 text-xs font-bold text-brand">Live overview</span>
      </div>
      <div class="mt-7 flex h-44 items-end gap-2 border-b border-l border-gray-100 px-4 pt-4 dark:border-white/10"><?php foreach ([28,44,35,65,48,78,56,90,72,50,62,42] as $bar): ?>
        <span class="flex-1 rounded-t bg-brand/<?= $bar === 90 ? '100' : '25' ?>" style="height:<?= $bar ?>%">

        </span>
        <?php endforeach; ?>
      </div>
      <div class="mt-3 flex justify-between text-[10px] font-semibold uppercase tracking-wide text-gray-400">
        <span>8 AM</span>
        <span>12 PM</span>
        <span>4 PM</span>
        <span>8 PM</span>
      </div>
      <div class="mt-6 grid grid-cols-3 gap-3">
        <div class="rounded-xl bg-brand-light p-3">
          <p class="text-[10px] font-bold uppercase tracking-wide text-brand">Avg / order</p>
          <p class="mt-1 font-display text-lg font-bold text-ink">₱<?= $todaysOrders ? number_format($todaysRevenue / $todaysOrders, 2) : '0.00' ?></p>
        </div>
        <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
          <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Stock units</p>
          <p class="mt-1 font-display text-lg font-bold text-ink dark:text-white"><?= (int)$inventory['total_units'] ?></p>
        </div>
        <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
          <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Low stock</p>
          <p class="mt-1 font-display text-lg font-bold text-ink dark:text-white">
            <?= (int)$inventory['low_stock_count'] ?>
          </p>
        </div>
      </div>
    </div>
      <aside class="dashboard-panel">
        <p class="eyebrow">Inventory health</p>
        <h2>Stock at a glance</h2>
        <div class="mt-6 space-y-5"><div>
          <div class="mb-2 flex justify-between text-sm">
            <span class="font-semibold text-ink dark:text-white">Healthy</span>
            <b class="text-brand"><?= max(0, (int)$inventory['product_count'] - (int)$inventory['low_stock_count'] - (int)$inventory['out_of_stock_count']) ?></b>
          </div>
          <div class="stock-meter">
            <span style="width:<?= $inventory['product_count'] ? max(0, min(100, 100 - (($inventory['low_stock_count'] + $inventory['out_of_stock_count']) / $inventory['product_count'] * 100))) : 0 ?>%"></span>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="rounded-xl border border-amber-100 bg-amber-50 p-3">
            <p class="text-[10px] font-bold uppercase text-amber-700">Low</p>
            <b class="mt-1 block text-xl text-amber-800"><?= (int)$inventory['low_stock_count'] ?></b>
          </div>
          <div class="rounded-xl border border-red-100 bg-red-50 p-3">
            <p class="text-[10px] font-bold uppercase text-red-600">Out</p>
            <b class="mt-1 block text-xl text-red-700">
              <?= (int)$inventory['out_of_stock_count'] ?>
            </b>
          </div>
        </div>
        <?php if(empty($lowStockItems)): ?>
          <p class="rounded-xl bg-brand-light p-3 text-sm text-brand">All stock levels look healthy.</p>
          <?php else: ?>
            <div class="space-y-2 border-t border-gray-100 pt-4 dark:border-white/10">
              <?php foreach($lowStockItems as $item): ?>
                <div class="flex items-center justify-between text-sm">
                  <span class="truncate pr-3 text-gray-600 dark:text-white/70">
                    <?= htmlspecialchars($item['name']) ?>
                  </span><b class="text-amber-700"><?= (int)$item['stock'] ?> left</b>
                </div>
                <?php endforeach; ?>
              </div><?php endif; ?>
              <a href="/products" class="inline-block text-sm font-bold text-brand hover:underline">Manage inventory →</a>
            </div>
          </aside>
        </section>
  </main>
</div>
