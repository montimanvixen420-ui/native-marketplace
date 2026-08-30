<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
<?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
<main class="flex-1 px-5 py-8 sm:px-8">
<div class="mb-7">
<p class="text-xs font-bold uppercase tracking-[.18em] text-coral">Trust & safety</p>
<h1 class="mt-2 font-display text-3xl font-bold text-ink dark:text-white">Reports against your store</h1>
<p class="mt-2 text-sm text-gray-500 dark:text-white/55">Reports filed by customers against your account or your products. Our moderation team reviews and resolves these — this list is for your awareness only.</p>
</div>

<div class="mb-5 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
  <p class="font-semibold">View only</p>
  <p class="mt-1">To keep moderation impartial, sellers can't resolve reports filed against their own account or products — only our platform team can.</p>
</div>

<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-ink-2">
<?php if(empty($reports)): ?>
<div class="p-12 text-center"><p class="font-display text-xl font-bold text-ink dark:text-white">No reports.</p><p class="mt-2 text-sm text-gray-500">You're in good standing — nothing has been reported against your store.</p></div>
<?php else: ?>
<table id="sellerReportsTable" class="w-full text-sm">
  <thead>
    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200 dark:border-white/10">
      <th class="px-5 py-3 font-semibold">Type</th>
      <th class="px-3 py-3 font-semibold">Reported item</th>
      <th class="px-3 py-3 font-semibold">Reason</th>
      <th class="px-3 py-3 font-semibold">Status</th>
      <th class="px-3 py-3 font-semibold">Moderator note</th>
      <th class="px-5 py-3 font-semibold text-right">Submitted</th>
    </tr>
  </thead>
  <tbody>
    <?php
      $statusStyles = [
          'open'      => 'bg-red-50 text-red-700',
          'reviewing' => 'bg-amber-50 text-amber-700',
          'resolved'  => 'bg-emerald-50 text-emerald-700',
          'dismissed' => 'bg-gray-100 text-gray-600',
      ];
    ?>
    <?php foreach($reports as $report): ?>
    <?php $statusClass = $statusStyles[$report['status']] ?? 'bg-gray-100 text-gray-600'; ?>
    <tr class="border-b border-gray-100 last:border-0 dark:border-white/10">
      <td class="px-5 py-3.5 align-top text-gray-700 dark:text-white/75"><?= htmlspecialchars(ucfirst($report['target_type'])) ?></td>
      <td class="px-3 py-3.5 align-top font-bold text-ink dark:text-white"><?= htmlspecialchars($report['target_label'] ?? 'Removed item') ?></td>
      <td class="px-3 py-3.5 align-top text-gray-700 dark:text-white/75"><?= htmlspecialchars($report['reason']) ?></td>
      <td class="px-3 py-3.5 align-top"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($report['status'])) ?></span></td>
      <td class="px-3 py-3.5 align-top text-xs text-gray-500 dark:text-white/55"><?= $report['review_note'] ? htmlspecialchars($report['review_note']) : '—' ?></td>
      <td class="px-5 py-3.5 align-top text-right text-xs text-gray-400"><?= date('M j, Y', strtotime($report['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</section>
</main>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
<script>
$(function () {
  <?php if (!empty($reports)): ?>
  $('#sellerReportsTable').DataTable({
    searching: false,
    paging: true,
    pageLength: 5,
    lengthMenu: [5, 10, 15, 20],
    lengthChange: true,
    order: [[5, 'desc']],
    layout: { topStart: null, topEnd: 'pageLength', bottomStart: 'info', bottomEnd: 'paging' },
  });
  <?php endif; ?>
});
</script>