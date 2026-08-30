<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Stock requests</h1>
        <p class="text-sm text-gray-500">Ask a supplier to restock an item.</p>
      </div>
      <a href="/stock-requests/create" class="inline-flex items-center gap-1.5 bg-amber text-ink text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition">
        <i data-lucide="plus" class="w-4 h-4"></i> New request
      </a>
    </div>

    <?php if (empty($requests)): ?>
      <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
        <p class="font-display text-lg font-semibold text-gray-900">No stock requests yet</p>
        <p class="text-sm text-gray-500 mt-1 mb-5">Running low on something? Ask a supplier to restock it.</p>
        <a href="/stock-requests/create" class="inline-flex items-center gap-1.5 bg-amber text-ink text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition">
          <i data-lucide="plus" class="w-4 h-4"></i> New request
        </a>
      </div>
    <?php else: ?>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table id="stockRequestsTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
              <th class="px-5 py-3 font-semibold">Item</th>
              <th class="px-3 py-3 font-semibold">Supplier</th>
              <th class="px-3 py-3 font-semibold text-right">Qty requested</th>
              <th class="px-3 py-3 font-semibold">Status</th>
              <th class="px-5 py-3 font-semibold">Requested</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $statusStyles = [
                  'pending' => 'bg-amber/15 text-amber-700',
                  'fulfilled' => 'bg-teal-light text-teal',
                  'rejected' => 'bg-red-100 text-red-600',
              ];
            ?>
            <?php foreach ($requests as $req): ?>
              <tr class="border-b border-gray-100 last:border-0">
                <td class="px-5 py-3.5 font-medium text-gray-900">
                  <?= htmlspecialchars($req['item_name']) ?>
                  <?php if (!empty($req['note'])): ?>
                    <p class="text-xs text-gray-400 font-normal mt-0.5"><?= htmlspecialchars($req['note']) ?></p>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-3.5 text-gray-700"><?= htmlspecialchars($req['supplier_name']) ?></td>
                <td class="px-3 py-3.5 text-right text-gray-700"><?= (int) $req['quantity_requested'] ?></td>
                <td class="px-3 py-3.5">
                  <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full <?= $statusStyles[$req['status']] ?? 'bg-gray-100 text-gray-500' ?>">
                    <?= htmlspecialchars(ucfirst($req['status'])) ?>
                  </span>
                </td>
                <td class="px-5 py-3.5 text-gray-500"><?= date('M j, Y', strtotime($req['created_at'])) ?></td>
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

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
    <?php if (!empty($requests)): ?>
    $('#stockRequestsTable').DataTable({
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
});
</script>