<?php
  $statusStyles = [
    'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber/10 dark:text-amber',
    'fulfilled' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    'rejected' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
  ];
?>
<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
  <main class="flex-1 px-8 py-8">
    <div class="mb-8"><h1 class="font-display font-semibold text-2xl text-ink dark:text-white">Incoming stock requests</h1><p class="text-sm text-gray-500 dark:text-white/50">Review requests from sellers and mark the outcome.</p></div>
    <?php if (!empty($error)): ?><div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <section class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
      <?php if (empty($requests)): ?>
        <div class="p-12 text-center"><p class="text-sm font-medium text-ink dark:text-white">No incoming requests</p><p class="text-xs text-gray-500 dark:text-white/50 mt-1">Seller stock requests will appear here.</p></div>
      <?php else: ?>
        <div class="divide-y divide-gray-100 dark:divide-white/10">
          <?php foreach ($requests as $request): ?>
            <article class="p-5">
              <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="min-w-0"><div class="flex items-center gap-2 flex-wrap"><h2 class="font-medium text-ink dark:text-white"><?= htmlspecialchars($request['item_name']) ?></h2><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium <?= $statusStyles[$request['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= htmlspecialchars(ucfirst($request['status'])) ?></span></div><p class="text-sm text-gray-500 dark:text-white/50 mt-1">Requested by <?= htmlspecialchars($request['seller_name']) ?> &middot; Quantity: <strong class="text-ink dark:text-white"><?= (int) $request['quantity_requested'] ?></strong></p><?php if (!empty($request['note'])): ?><p class="text-xs text-gray-500 dark:text-white/50 mt-2">Note: <?= htmlspecialchars($request['note']) ?></p><?php endif; ?><p class="text-xs text-gray-400 dark:text-white/30 mt-2">Submitted <?= date('M j, Y g:i A', strtotime($request['created_at'])) ?></p></div>
                <?php if ($request['status'] === 'pending'): ?>
                  <div class="flex gap-2 shrink-0"><form method="POST" action="/supplier/requests/update-status"><input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>"><input type="hidden" name="status" value="fulfilled"><button type="submit" class="px-3.5 py-2 rounded-xl bg-brand text-white text-sm font-medium hover:bg-brand-dark">Mark fulfilled</button></form><form method="POST" action="/supplier/requests/update-status"><input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>"><input type="hidden" name="status" value="rejected"><button type="submit" class="px-3.5 py-2 rounded-xl border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 dark:border-red-900/50 dark:hover:bg-red-500/10">Reject</button></form></div>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</div>
