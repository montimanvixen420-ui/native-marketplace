<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
<?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
<main class="flex-1 px-5 py-8 sm:px-8">
<div class="mb-7">
<p class="text-xs font-bold uppercase tracking-[.18em] text-coral">After-sales</p>
<h1 class="mt-2 font-display text-3xl font-bold text-ink dark:text-white">Return & refund requests</h1>
<p class="mt-2 text-sm text-gray-500 dark:text-white/55">Review customer requests from delivered orders.</p>
</div>
<?php if($success): ?><div class="mb-5 rounded-xl bg-brand-light px-4 py-3 text-sm text-brand">Request status updated.</div><?php endif; ?>
<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-ink-2">
<?php if(empty($requests)): ?>
<div class="p-12 text-center"><p class="font-display text-xl font-bold text-ink dark:text-white">No return requests.</p><p class="mt-2 text-sm text-gray-500">New customer requests will appear here.</p></div>
<?php else: ?>
<table id="returnsTable" class="w-full text-sm">
  <thead>
    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200 dark:border-white/10">
      <th class="px-5 py-3 font-semibold">Product</th>
      <th class="px-3 py-3 font-semibold">Order / Customer</th>
      <th class="px-3 py-3 font-semibold">Reason</th>
      <th class="px-3 py-3 font-semibold">Status</th>
      <th class="px-5 py-3 font-semibold text-right">Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($requests as $request): ?>
    <tr class="border-b border-gray-100 last:border-0 dark:border-white/10">
      <td class="px-5 py-3.5 align-top">
        <p class="font-bold text-ink dark:text-white"><?= htmlspecialchars($request['product_name']) ?> <span class="font-normal text-gray-400">× <?= (int)$request['quantity'] ?></span></p>
      </td>
      <td class="px-3 py-3.5 align-top text-gray-600 dark:text-white/70">
        Order #<?= (int)$request['order_number'] ?><br>
        <span class="text-xs text-gray-400"><?= htmlspecialchars($request['customer_name']) ?><?= !empty($request['variant_label']) ? ' · '.htmlspecialchars($request['variant_label']) : '' ?></span>
      </td>
      <td class="px-3 py-3.5 align-top text-gray-700 dark:text-white/75">
        <p><b><?= htmlspecialchars(ucwords(str_replace('_',' ',$request['reason']))) ?></b></p>
        <?php if($request['details']): ?><p class="mt-1 text-xs text-gray-500 dark:text-white/55"><?= nl2br(htmlspecialchars($request['details'])) ?></p><?php endif; ?>
      </td>
      <td class="px-3 py-3.5 align-top">
        <span class="rounded-full bg-brand-light px-3 py-1 text-xs font-bold text-brand"><?= htmlspecialchars(ucfirst($request['status'])) ?></span>
      </td>
      <td class="px-5 py-3.5 align-top text-right">
        <?php if(in_array($request['status'],['requested','approved'],true)): ?>
        <form method="POST" action="/admin/returns/<?= (int)$request['id'] ?>" class="js-return-status-form inline-flex items-center gap-1">
          <select name="status" class="js-return-status-select rounded-lg border border-gray-200 px-2 py-2 text-xs dark:border-white/15 dark:bg-ink dark:text-white">
            <option value="approved">Approve</option>
            <option value="rejected">Reject</option>
            <?php if($request['status']==='approved'): ?><option value="refunded">Mark refunded</option><?php endif; ?>
          </select>
          <button type="submit" class="ml-1 inline-flex items-center gap-1.5 rounded-lg bg-brand px-3 py-2 text-xs font-bold text-white">
            <i data-lucide="save" class="w-3.5 h-3.5"></i> Save
          </button>
        </form>
        <?php else: ?>
        <span class="text-xs text-gray-400">—</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</section>
</main>
</div>

<!-- DataTables (Tailwind styling) -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
  <?php if (!empty($requests)): ?>
  $('#returnsTable').DataTable({
 searching: false,
        paging: true,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        lengthChange: true,
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: 4 } // "Actions" column - walang sort
        ],
        layout: {
            topStart: null,
            topEnd: 'pageLength',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        drawCallback: function () {
            // I-render ulit ang icons kapag nagbago ang page (paging/sorting)
            lucide.createIcons();
        }
    });
    <?php endif; ?>

  if (typeof lucide !== 'undefined') lucide.createIcons();
  bindReturnStatusForms();
});

var returnStatusMeta = {
  approved: {
    title: 'Approve this return?',
    text: 'The customer will be notified that their return request was approved.',
    icon: 'question',
    confirmText: 'Yes, approve',
    confirmColor: '#0d9488'
  },
  rejected: {
    title: 'Reject this return?',
    text: 'The customer will be notified that their return request was declined.',
    icon: 'warning',
    confirmText: 'Yes, reject',
    confirmColor: '#dc2626'
  },
  refunded: {
    title: 'Mark this order as refunded?',
    text: 'This confirms the refund has been processed for the customer.',
    icon: 'question',
    confirmText: 'Yes, mark refunded',
    confirmColor: '#0d9488'
  }
};

function bindReturnStatusForms() {
  document.querySelectorAll('.js-return-status-form').forEach(function (form) {
    if (form.dataset.swalBound === '1') return; // iwas double-bind pag na-redraw ng DataTables
    form.dataset.swalBound = '1';

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var select = form.querySelector('.js-return-status-select');
      var meta = returnStatusMeta[select.value] || {
        title: 'Update this request?',
        text: 'This will change the status of the return request.',
        icon: 'question',
        confirmText: 'Yes, continue',
        confirmColor: '#0d9488'
      };

      if (typeof Swal === 'undefined') {
        form.submit();
        return;
      }

      Swal.fire({
        title: meta.title,
        text: meta.text,
        icon: meta.icon,
        showCancelButton: true,
        confirmButtonText: meta.confirmText,
        cancelButtonText: 'Cancel',
        confirmButtonColor: meta.confirmColor,
        cancelButtonColor: '#6b7280',
        reverseButtons: true
      }).then(function (result) {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
}
</script>