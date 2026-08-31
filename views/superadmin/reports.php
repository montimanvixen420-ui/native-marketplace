<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Customer Safety Reports</h1>
        <p class="mt-1 text-sm text-gray-500">Product and seller reports awaiting moderator action.</p>
      </div>
      <div class="flex gap-2 text-xs">
        <span class="rounded-full bg-red-50 px-3 py-1.5 font-semibold text-red-700"><?= (int)$reportSummary['open_count'] ?> open</span>
        <span class="rounded-full bg-gray-100 px-3 py-1.5 font-semibold text-gray-600"><?= (int)$reportSummary['total'] ?> total</span>
      </div>
    </div>

    <section class="rounded-lg border border-gray-200 bg-white p-6">
  <div class="flex flex-wrap items-center justify-end gap-2">
    <select id="reportTypeFilter" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
      <option value="">All types</option>
      <option value="product">Product</option>
      <option value="seller">Seller</option>
    </select>
    <select id="reportStatusFilter" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
      <option value="">All statuses</option>
      <option value="open">Open</option>
      <option value="reviewing">Reviewing</option>
      <option value="resolved">Resolved</option>
      <option value="dismissed">Dismissed</option>
    </select>
       <div class="flex items-center gap-2 shrink-0">
            <select id="customEntriesSelect" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="20">20</option>
            </select>
            <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
          </div>
  </div>


      <div class="mt-4 overflow-x-auto">
        <table id="safetyReportsTable" class="w-full text-sm" style="width:100%">
          <thead>
            <tr>
              <th>Type</th>
              <th>Reported</th>
              <th>Reporter</th>
              <th>Reason</th>
              <th>Status</th>
              <th>Submitted</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($customerReports as $report): ?>
              <?php
                $statusStyles = [
                    'open'      => 'bg-red-50 text-red-700',
                    'reviewing' => 'bg-amber-50 text-amber-700',
                    'resolved'  => 'bg-emerald-50 text-emerald-700',
                    'dismissed' => 'bg-gray-100 text-gray-600',
                ];
                $statusClass = $statusStyles[$report['status']] ?? 'bg-gray-100 text-gray-600';
              ?>
              <tr data-type="<?= htmlspecialchars($report['target_type']) ?>" data-status="<?= htmlspecialchars($report['status']) ?>">
                <td><?= htmlspecialchars(ucfirst($report['target_type'])) ?></td>
                <td><?= htmlspecialchars($report['target_label'] ?? 'Removed item') ?></td>
                <td><?= htmlspecialchars($report['reporter_name']) ?></td>
                <td><?= htmlspecialchars($report['reason']) ?></td>
                <td><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($report['status'])) ?></span></td>
                <td data-order="<?= strtotime($report['created_at']) ?>"><?= date('M j, Y g:i A', strtotime($report['created_at'])) ?></td>
                <td>
                  <div class="flex items-center gap-1.5">
                    <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 view-report-btn"
                            data-target="<?= htmlspecialchars('Report ' . $report['target_type'] . ': ' . ($report['target_label'] ?? 'Removed item')) ?>"
                            data-reporter="<?= htmlspecialchars($report['reporter_name']) ?>"
                            data-reason="<?= htmlspecialchars($report['reason']) ?>"
                            data-details="<?= htmlspecialchars($report['details']) ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                      View
                    </button>

                    <form action="/superadmin/reports/<?= (int)$report['id'] ?>" method="POST" class="update-status-form flex items-center gap-1.5">
                      <?php $isLocked = in_array($report['status'], ['resolved', 'dismissed'], true); ?>
                      <select name="status" class="rounded-lg border border-gray-200 px-2 py-1.5 text-xs" <?= $isLocked ? 'disabled' : '' ?>>
                        <option value="reviewing">Mark reviewing</option>
                        <option value="resolved">Resolve</option>
                        <option value="dismissed">Dismiss</option>
                      </select>
                      <input name="review_note" maxlength="1000" placeholder="Internal note (optional)" class="w-36 rounded-lg border border-gray-200 px-2 py-1.5 text-xs" <?= $isLocked ? 'disabled' : '' ?>>
                      <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-ink px-2.5 py-1.5 text-xs font-bold text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40" <?= $isLocked ? 'disabled' : '' ?>>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                        Save
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(function () {
      var table = $('#safetyReportsTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        order: [[4, 'desc']], // Default sort by Requested Date
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        drawCallback: function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    });

      $.fn.dataTable.ext.search.push(function (settings, searchData, index, rowData, counter) {
        if (settings.nTable.id !== 'safetyReportsTable') return true;

        var row = table.row(index).node();
        var type = $(row).data('type');
        var status = $(row).data('status');

        var typeFilter = $('#reportTypeFilter').val();
        var statusFilter = $('#reportStatusFilter').val();

        if (typeFilter && type !== typeFilter) return false;
        if (statusFilter && status !== statusFilter) return false;

        return true;
      });
 $('#reportPageLength').on('change', function () {
    table.page.len(parseInt(this.value, 10)).draw();
  });
      $('#reportTypeFilter, #reportStatusFilter').on('change', function () {
        table.draw();
      });
      

      $('#safetyReportsTable').on('click', '.view-report-btn', function () {
        var btn = this;
        Swal.fire({
          title: btn.dataset.target,
          html:
            '<p style="text-align:left;font-size:13px;color:#6b7280;"><strong>Reporter:</strong> ' + btn.dataset.reporter + '</p>' +
            '<p style="margin-top:8px;text-align:left;font-size:13px;color:#6b7280;"><strong>Reason:</strong> ' + btn.dataset.reason + '</p>' +
            '<p style="margin-top:8px;text-align:left;font-size:13px;color:#6b7280;"><strong>Details:</strong><br>' + btn.dataset.details + '</p>',
          confirmButtonText: 'Close',
          confirmButtonColor: '#12A594',
        });
      });

      $('#safetyReportsTable').on('submit', '.update-status-form', function (e) {
        e.preventDefault();
        var form = this;
        var statusLabel = $(form).find('select[name="status"] option:selected').text();

        Swal.fire({
          title: 'Update this report?',
          text: 'Status will be set to "' + statusLabel + '".',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, save',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#12A594',
          cancelButtonColor: '#6b7280',
          reverseButtons: true,
        }).then(function (result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
    </script>
  </main>

</div>