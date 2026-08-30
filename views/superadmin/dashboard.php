<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="min-w-0 flex-1 px-5 py-7 sm:px-8">

    <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[.16em] text-brand">Platform overview</p>
        <h1 class="mt-1 font-display text-2xl font-bold text-ink dark:text-white">Good day, <?= htmlspecialchars($name) ?></h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-white/50">Monitor users, applications, and platform activity.</p>
      </div>
      <div class="flex gap-2">
        <a href="/superadmin/reports" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-ink dark:border-white/10 dark:bg-ink-2 dark:text-white">
          View reports
        </a>
      </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <div class="dashboard-stat">
        <span class="stat-label">Total users</span>
        <b><?= (int) $stats['total'] ?></b>
        <small>All accounts</small>
      </div>

      <div class="dashboard-stat">
        <span class="stat-label">Sellers</span>
        <b><?= (int) $stats['sellers'] ?></b>
        <small>Shop operators</small>
      </div>

      <div class="dashboard-stat">
        <span class="stat-label">Suppliers</span>
        <b><?= (int) $stats['suppliers'] ?></b>
        <small>Inventory partners</small>
      </div>

      <div class="dashboard-stat">
        <span class="stat-label">Customers</span>
        <b><?= (int) $stats['customers'] ?></b>
        <small>Registered shoppers</small>
      </div>

      <div class="dashboard-stat">
        <span class="stat-label">Pending approvals</span>
        <b><?= (int) $stats['pending'] ?></b>
        <small class="text-brand">See applications below</small>
      </div>
    </section>

    <section class="dashboard-panel mt-5 overflow-hidden p-0">
      <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-white/10">
        <div>
          <p class="eyebrow">Applications</p>
          <h2>Pending approvals</h2>
        </div>
        <div class="flex gap-3">
          <a href="/superadmin/sellers?status=pending" class="text-sm font-semibold text-brand">Sellers</a>
          <a href="/superadmin/suppliers?status=pending" class="text-sm font-semibold text-brand">Suppliers</a>
        </div>
      </div>

      <?php if (empty($pendingApprovals)): ?>
        <div class="p-12 text-center text-sm text-gray-500">No pending approvals right now.</div>
      <?php else: ?>
        <div class="divide-y divide-gray-100 dark:divide-white/10">
          <?php foreach ($pendingApprovals as $applicant): ?>
            <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p class="text-sm font-semibold text-ink dark:text-white"><?= htmlspecialchars($applicant['name']) ?></p>
                <p class="mt-1 text-xs text-gray-500">
                  <?= $applicant['role'] === 'admin' ? 'Seller' : ucfirst($applicant['role']) ?> · <?= htmlspecialchars($applicant['email']) ?>
                </p>
              </div>
              <div class="flex gap-2">
                <form method="POST" action="/superadmin/users/<?= (int) $applicant['id'] ?>/approve">
                  <button class="rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-white">Approve</button>
                </form>
                <form method="POST" action="/superadmin/users/<?= (int) $applicant['id'] ?>/reject">
                  <button class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 dark:border-white/10 dark:text-white">Reject</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </main>

</div>