<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">
  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
  
  <main class="flex-1 px-8 py-8">
    
    <!-- Page Header -->
    <header class="mb-6 flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-1">Trust &amp; safety</p>
        <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white">Reports against your store</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Reports filed by customers against your account or your products. Our moderation team reviews and resolves these — this list is for your awareness only.</p>
      </div>
      <?php if ($openCount > 0): ?>
        <span class="shrink-0 rounded-full bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 px-3 py-1.5 text-xs font-bold text-rose-700 dark:text-rose-300"><?= $openCount ?> open</span>
      <?php else: ?>
        <span class="shrink-0 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300">0 open</span>
      <?php endif; ?>
    </header>

    <!-- View Only Warning Notice -->
    <div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 p-4 text-sm text-amber-900 dark:text-amber-200 flex items-start gap-3 shadow-xs">
      <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
      <div>
        <p class="font-bold text-amber-950 dark:text-amber-100">View-only mode</p>
        <p class="mt-0.5 text-xs text-amber-800/90 dark:text-amber-300/80">To keep moderation impartial, sellers cannot resolve reports filed against their own account or products — only our platform moderation team can review and process them.</p>
      </div>
    </div>

    <!-- Main Table Card Panel -->
    <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden p-5 shadow-sm">
      
      <?php if (empty($reports)): ?>
        <div class="p-12 text-center">
          <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3 border border-emerald-200 dark:border-emerald-800">
            <i data-lucide="shield-check" class="w-6 h-6"></i>
          </div>
          <p class="font-display text-lg font-bold text-gray-900 dark:text-white">No reports found</p>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You're in good standing — nothing has been reported against your store.</p>
        </div>
      <?php else: ?>

        <!-- Custom Search & Entries Control Toolbar -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
          <div class="relative w-full sm:w-72">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="customSearchInput" placeholder="Search report reason, item..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500">
          </div>

          <select id="statusFilterReports" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <option value="">All statuses</option>
            <option value="open">Open</option>
            <option value="reviewing">Reviewing</option>
            <option value="resolved">Resolved</option>
            <option value="dismissed">Dismissed</option>
          </select>

          <div class="flex items-center gap-2 shrink-0">
            <select id="customEntriesSelect" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="20">20</option>
            </select>
            <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
          </div>
        </div>

        <table id="sellerReportsTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
              <th class="px-4 py-3.5 font-semibold">Type</th>
              <th class="px-4 py-3.5 font-semibold">Reported Item</th>
              <th class="px-4 py-3.5 font-semibold">Reason</th>
              <th class="px-4 py-3.5 font-semibold text-center">Status</th>
              <th class="px-4 py-3.5 font-semibold">Moderator Note</th>
              <th class="px-4 py-3.5 font-semibold text-right">Submitted</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
            <?php
              $statusStyles = [
                  'open'      => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                  'reviewing' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                  'resolved'  => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                  'dismissed' => 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-slate-700',
              ];
            ?>
            <?php foreach($reports as $report): ?>
              <?php $statusClass = $statusStyles[$report['status']] ?? $statusStyles['dismissed']; ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition-colors" data-status="<?= htmlspecialchars($report['status']) ?>">
                <td class="px-4 py-4 align-top text-gray-700 dark:text-gray-300 font-medium">
                  <?= htmlspecialchars(ucfirst($report['target_type'])) ?>
                </td>
                <td class="px-4 py-4 align-top font-semibold text-gray-900 dark:text-white">
                  <?= htmlspecialchars($report['target_label'] ?? 'Removed item') ?>
                </td>
                <td class="px-4 py-4 align-top text-gray-600 dark:text-gray-300 max-w-xs leading-relaxed">
                  <?= htmlspecialchars($report['reason']) ?>
                </td>
                <td class="px-4 py-4 align-top text-center whitespace-nowrap">
                  <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full border <?= $statusClass ?>">
                    <?= htmlspecialchars(ucfirst($report['status'])) ?>
                  </span>
                </td>
                <td class="px-4 py-4 align-top text-xs text-gray-500 dark:text-gray-400 max-w-xs italic">
                  <?= $report['review_note'] ? htmlspecialchars($report['review_note']) : '—' ?>
                </td>
                <td class="px-4 py-4 align-top text-right text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                  <?= date('M j, Y', strtotime($report['created_at'])) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
               </table>

        <!-- Footer & Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-5 mt-2 border-t border-gray-100 dark:border-slate-700/60">
          <div id="reports-info" class="text-xs font-medium text-gray-500 dark:text-gray-400">Showing 0 to 0 of 0 entries</div>
          <div id="reports-pagination" class="flex items-center gap-1"></div>
        </div>
      <?php endif; ?>

    </section>
  </main>
</div>

<!-- DataTables Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(function () {
  <?php if (!empty($reports)): ?>
  const table = $('#sellerReportsTable').DataTable({
    searching: true,
    paging: true,
    pageLength: 5,
    lengthChange: false,
    order: [[5, 'desc']], // Sort by Submitted Date DESC
        layout: {
      topStart: null,
      topEnd: null,
      bottomStart: null,
      bottomEnd: null
    },
    drawCallback: function () {
      renderDtPillPagination(this.api(), 'reports-pagination', 'reports-info');
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }
  });

  // Custom Search Toolbar Listeners
  $('#customSearchInput').on('keyup', function () {
    table.search(this.value).draw();
  });

  $('#customEntriesSelect').on('change', function () {
    table.page.len(this.value).draw();
  });

  // Status filter (Fix #6)
  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    if (settings.nTable.id !== 'sellerReportsTable') return true;
    const status = $('#statusFilterReports').val();
    if (status === '') return true;
    const row = table.row(dataIndex).node();
    return $(row).data('status') === status;
  });

  $('#statusFilterReports').on('change', function () { table.draw(); });
  <?php endif; ?>

  if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>