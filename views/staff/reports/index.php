<div class="flex min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">
  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
  <main class="flex-1 overflow-y-auto px-4 py-8 sm:px-8">
    <header class="mb-6">
      <p class="mb-1 text-xs font-medium text-slate-400">Branch awareness <span class="mx-1">/</span> Reports</p>
      <h1 class="flex items-center gap-2 font-display text-2xl font-bold text-slate-900 dark:text-white"><i data-lucide="file-warning" class="h-6 w-6 text-indigo-600"></i>Product reports</h1>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">View reports for products carried by your branch. Platform administrators resolve them.</p>
    </header>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
      <?php if (empty($reports)): ?>
        <div class="p-14 text-center"><i data-lucide="shield-check" class="mx-auto h-9 w-9 text-emerald-500"></i><p class="mt-3 font-semibold">No product reports for this branch.</p></div>
      <?php else: ?>
        <div class="overflow-x-auto"><table id="staffReportsDataTable" data-datatable class="w-full text-left text-sm"><thead><tr class="border-b bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-slate-800"><th class="px-4 py-3"><i data-lucide="package" class="mr-1 inline h-3.5 w-3.5"></i>Product</th><th class="px-4 py-3"><i data-lucide="message-square-warning" class="mr-1 inline h-3.5 w-3.5"></i>Reason</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"><i data-lucide="user-round" class="mr-1 inline h-3.5 w-3.5"></i>Reported by</th><th class="px-4 py-3"><i data-lucide="badge-check" class="mr-1 inline h-3.5 w-3.5"></i>Reviewed by</th><th class="px-4 py-3"><i data-lucide="clock-3" class="mr-1 inline h-3.5 w-3.5"></i>Date</th></tr></thead><tbody>
        <?php foreach ($reports as $report): $status = strtolower($report['status']); $badge = $status === 'resolved' ? 'bg-emerald-50 text-emerald-700' : ($status === 'dismissed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-700'); ?><tr class="border-b last:border-0 dark:border-slate-700"><td class="px-4 py-3.5 font-semibold"><?= htmlspecialchars($report['target_label']) ?></td><td class="px-4 py-3.5"><p class="font-medium"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $report['reason']))) ?></p><?php if (!empty($report['details'])): ?><p class="mt-1 max-w-sm text-xs text-slate-500"><?= htmlspecialchars($report['details']) ?></p><?php endif; ?></td><td class="px-4 py-3.5"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $badge ?>"><?= htmlspecialchars(ucfirst($report['status'])) ?></span></td><td class="px-4 py-3.5"><span class="inline-flex items-center gap-1 text-xs"><i data-lucide="user" class="h-3.5 w-3.5 text-slate-400"></i><?= htmlspecialchars($report['reporter_name'] ?? 'Customer') ?></span></td><td class="px-4 py-3.5"><span class="inline-flex items-center gap-1 text-xs"><i data-lucide="user-check" class="h-3.5 w-3.5 text-slate-400"></i><?= htmlspecialchars($report['reviewed_by_name'] ?? 'Not reviewed') ?></span></td><td class="px-4 py-3.5 text-xs text-slate-500"><?= date('M j, Y g:ia', strtotime($report['created_at'])) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
    </section>
  </main>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.lucide)lucide.createIcons();});</script>
