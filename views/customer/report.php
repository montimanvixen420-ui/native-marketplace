<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="min-w-0 flex-1 px-5 py-7 sm:px-8">
    <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[.16em] text-brand">My reports</p>
        <h1 class="mt-1 font-display text-2xl font-bold text-ink dark:text-white">Hi, <?= htmlspecialchars($name) ?></h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-white/50">Track the status of reports you've submitted.</p>
      </div>
      <a href="/shop" class="w-fit rounded-lg border border-brand px-4 py-2.5 text-sm font-semibold text-brand hover:bg-brand-light">Back to shop</a>
    </header>

    <div class="dashboard-panel overflow-x-auto">
      <table id="myReportsTable" class="w-full text-sm" style="width:100%">
        <thead>
          <tr>
            <th>Type</th>
            <th>Reported</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reports as $report): ?>
            <?php
              $statusStyles = [
                  'open'      => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                  'reviewing' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                  'resolved'  => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                  'dismissed' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-white/50',
              ];
              $statusLabel = ucfirst($report['status']);
              $statusClass = $statusStyles[$report['status']] ?? 'bg-gray-100 text-gray-600';
              $targetKind = $report['target_type'] === 'product' ? 'Product' : 'Seller';
            ?>
            <tr>
              <td><?= $targetKind ?></td>
              <td><?= htmlspecialchars($report['target_label'] ?? 'Item no longer available') ?></td>
              <td><?= htmlspecialchars($report['reason']) ?></td>
              <td><span class="rounded-full px-3 py-1 text-xs font-semibold <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
              <td data-order="<?= strtotime($report['created_at']) ?>"><?= date('M j, Y g:i A', strtotime($report['created_at'])) ?></td>
              <td>
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-ink hover:bg-slate-50 dark:border-white/10 dark:text-white dark:hover:bg-white/5 view-report-btn"
                        data-target="<?= htmlspecialchars($report['target_label'] ?? 'Item no longer available') ?>"
                        data-reason="<?= htmlspecialchars($report['reason']) ?>"
                        data-details="<?= htmlspecialchars($report['details']) ?>"
                        data-status="<?= htmlspecialchars($statusLabel) ?>"
                        data-note="<?= htmlspecialchars($report['review_note'] ?? '') ?>"
                        data-reviewed-at="<?= $report['reviewed_at'] ? date('M j, Y g:i A', strtotime($report['reviewed_at'])) : '' ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  View
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
  $('#myReportsTable').DataTable({
    order: [[4, 'desc']],
    pageLength: 5,
    lengthMenu: [5, 10, 15, 20],
    language: { search: '', searchPlaceholder: 'Search reports...' },
  });

  $('#myReportsTable').on('click', '.view-report-btn', function () {
    var btn = this;
    var note = btn.dataset.note
      ? '<p style="margin-top:12px;padding:10px 12px;background:#f0fdf4;border-radius:8px;font-size:13px;color:#166534;text-align:left;"><strong>Moderator note:</strong><br>' + btn.dataset.note + '</p>'
      : '<p style="margin-top:12px;font-size:13px;color:#9ca3af;text-align:left;">No moderator note yet.</p>';
    var reviewedAt = btn.dataset.reviewedAt
      ? '<p style="margin-top:6px;font-size:12px;color:#9ca3af;text-align:left;">Last updated: ' + btn.dataset.reviewedAt + '</p>'
      : '';

    Swal.fire({
      title: btn.dataset.target,
      html:
        '<p style="text-align:left;font-size:13px;color:#6b7280;"><strong>Reason:</strong> ' + btn.dataset.reason + '</p>' +
        '<p style="margin-top:8px;text-align:left;font-size:13px;color:#6b7280;"><strong>Your details:</strong><br>' + btn.dataset.details + '</p>' +
        '<p style="margin-top:8px;text-align:left;font-size:13px;color:#6b7280;"><strong>Status:</strong> ' + btn.dataset.status + '</p>' +
        note + reviewedAt,
      confirmButtonText: 'Close',
      confirmButtonColor: '#16a34a',
    });
  });
});
</script>