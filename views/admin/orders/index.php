<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Orders</h1>
        <p class="text-sm text-gray-500">Every sale — POS and online checkout — all in one place.</p>
      </div>
      <form method="GET" action="/admin/orders">
        <select name="branch" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700">
          <option value="" <?= $branchFilter === '' ? 'selected' : '' ?>>All branches</option>
          <option value="none" <?= $branchFilter === 'none' ? 'selected' : '' ?>>Unassigned (no branch)</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= (int) $branch['id'] ?>" <?= $branchFilter === (string) $branch['id'] ? 'selected' : '' ?>><?= htmlspecialchars($branch['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <?php if ($pendingOnlineOrders > 0): ?>
      <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <span class="font-semibold"><?= (int) $pendingOnlineOrders ?></span> online <?= $pendingOnlineOrders === 1 ? 'order needs' : 'orders need' ?> processing.
      </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
      <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
        <p class="font-display text-lg font-semibold text-gray-900">No orders yet</p>
        <p class="text-sm text-gray-500 mt-1">Sales from POS and your online store will show up here.</p>
      </div>
    <?php else: ?>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table id="ordersTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
              <th class="px-5 py-3 font-semibold">Order</th>
              <th class="px-3 py-3 font-semibold">Customer</th>
              <th class="px-3 py-3 font-semibold">Payment</th>
              <th class="px-3 py-3 font-semibold text-right">Total</th>
              <th class="px-3 py-3 font-semibold">Status</th>
              <th class="px-3 py-3 font-semibold">Date</th>
              <th class="px-5 py-3 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $statusStyles = [
                  'completed' => 'bg-teal-light text-teal',
                  'pending' => 'bg-amber/15 text-amber-700',
                  'packed' => 'bg-blue-50 text-blue-700',
                  'shipped' => 'bg-indigo-50 text-indigo-700',
                  'cancelled' => 'bg-gray-100 text-gray-500',
                  'refunded' => 'bg-red-100 text-red-600',
              ];
            ?>
            <?php foreach ($orders as $order): ?>
              <tr class="border-b border-gray-100 last:border-0">
                <td class="px-5 py-3.5 font-medium text-gray-900">#<?= (int) $order['id'] ?></td>
                <td class="px-3 py-3.5 text-gray-700">
                  <?= htmlspecialchars($order['linked_customer_name'] ?? $order['customer_name'] ?? 'Walk-in') ?>
                </td>
                <td class="px-3 py-3.5 text-gray-500"><?= htmlspecialchars(ucfirst($order['payment_method'])) ?></td>
                <td class="px-3 py-3.5 text-right font-medium text-gray-900">₱<?= number_format((float) $order['total_amount'], 2) ?></td>
                <td class="px-3 py-3.5">
                  <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full <?= $statusStyles[$order['status']] ?? 'bg-gray-100 text-gray-500' ?>">
                    <?= htmlspecialchars(ucfirst($order['status'])) ?>
                  </span>
                </td>
                <td class="px-3 py-3.5 text-gray-500"><?= date('M j, Y g:ia', strtotime($order['created_at'])) ?></td>
                <td class="px-5 py-3.5 text-right">
                  <a href="/admin/orders/<?= (int) $order['id'] ?>" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

</div>

<!-- DataTables (Tailwind styling) -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
    <?php if (!empty($orders)): ?>
    $('#ordersTable').DataTable({
     searching: false,
        paging: true,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        lengthChange: true,
        order: [[4, 'asc']],
        columnDefs: [
            { orderable: false, targets: 5 } // "Actions" column - walang sort
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

    // Unang render ng lahat ng <i data-lucide="..."> sa page
    lucide.createIcons();

    // SweetAlert2 confirmation para sa mga forms na may class="js-confirm-form"
    $(document).on('submit', '.js-confirm-form', function (e) {
        e.preventDefault();
        const form = this;
        const title = $(form).data('title');
        const text = $(form).data('text');
        const icon = $(form).data('icon') || 'question';
        const confirmText = $(form).data('confirm-text') || 'Yes, continue';
        const confirmColor = $(form).data('confirm-color') || '#0d9488';

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>