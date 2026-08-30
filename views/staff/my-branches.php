<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <main class="flex-1 px-5 py-7 md:px-8">
    <header class="mb-6">
      <p class="eyebrow text-brand">Staff</p>
      <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">My Branches</h1>
      <p class="mt-2 text-sm text-gray-500 dark:text-white/60">Branches you've been assigned to by your seller.</p>
    </header>

    <?php if (empty($myBranches)): ?>
      <section class="dashboard-panel">
        <p class="text-sm text-gray-500 dark:text-white/60">
          You haven't been assigned to any branch yet. Please contact your seller/employer.
        </p>
      </section>
    <?php else: ?>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($myBranches as $b): ?>
          <div class="dashboard-panel">
            <div class="flex items-start justify-between gap-2">
              <p class="font-display text-lg font-bold text-ink dark:text-white"><?= htmlspecialchars($b['name']) ?></p>
              <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-bold <?= $b['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                <?= $b['is_active'] ? '🟢 Active' : '⚪ Inactive' ?>
              </span>
            </div>
            <p class="mt-2 text-xs text-gray-500">📍 <?= htmlspecialchars($b['address']) ?><?= $b['city'] ? ', ' . htmlspecialchars($b['city']) : '' ?></p>
            <?php if (!empty($b['phone'])): ?><p class="mt-1 text-xs text-gray-500">📞 <?= htmlspecialchars($b['phone']) ?></p><?php endif; ?>
            <?php if (!empty($b['hours'])): ?><p class="mt-1 text-xs text-gray-500">🕒 <?= htmlspecialchars($b['hours']) ?></p><?php endif; ?>
            <p class="mt-3 text-xs font-semibold text-gray-400">
              Position: <span class="text-ink dark:text-white"><?= htmlspecialchars(Staff::POSITIONS[$profile['position']] ?? $profile['position']) ?></span>
            </p>
            <a href="/staff/dashboard" class="mt-4 inline-block rounded-lg border border-gray-200 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">
              👁 View Branch
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>