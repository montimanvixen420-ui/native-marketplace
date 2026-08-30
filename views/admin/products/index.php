<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">My products</h1>
        <p class="text-sm text-gray-500">Create and manage your product catalog. To sell an existing product in POS, transfer it from Seller Inventory.</p>
      </div>
      <a href="/products/create" class="inline-flex items-center gap-1.5 bg-amber text-ink text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition">
        <i data-lucide="plus" class="w-4 h-4"></i> Add product
      </a>
    </div>

    <?php if (empty($products)): ?>
      <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
        <p class="font-display text-lg font-semibold text-gray-900">Nothing on your shelf yet</p>
        <p class="text-sm text-gray-500 mt-1 mb-5">Add your first product to start selling.</p>
        <a href="/products/create" class="inline-flex items-center gap-1.5 bg-amber text-ink text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition">
          <i data-lucide="plus" class="w-4 h-4"></i> Add product
        </a>
      </div>
    <?php else: ?>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table id="productsTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
              <th class="px-5 py-3 font-semibold">Product</th>
              <th class="px-3 py-3 font-semibold">Category</th>
              <th class="px-3 py-3 font-semibold text-right">Price</th>
              <th class="px-3 py-3 font-semibold text-right">Stock remaining</th>
              <th class="px-3 py-3 font-semibold">Status</th>
              <th class="px-5 py-3 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr class="border-b border-gray-100 last:border-0">
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <?php if (!empty($product['image_url'])): ?>
                      <img src="/<?= htmlspecialchars($product['image_url']) ?>" alt=""
                        class="w-9 h-9 rounded-lg object-cover border border-gray-200">
                    <?php else: ?>
                      <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-xs text-gray-400">—</div>
                    <?php endif; ?>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></span>
                  </div>
                </td>
                <td class="px-3 py-3.5 text-gray-500"><?= htmlspecialchars($product['category'] ?? '—') ?></td>
                <td class="px-3 py-3.5 text-right font-medium text-gray-900">₱<?= number_format((float) $product['price'], 2) ?></td>
                <td class="px-3 py-3.5 text-right font-medium <?= (int) $product['stock'] === 0 ? 'text-red-600' : ((int) $product['stock'] <= 5 ? 'text-amber-700' : 'text-gray-700') ?>"><?= (int) $product['stock'] ?> pcs</td>
                <td class="px-3 py-3.5">
                  <?php if ($product['status'] === 'active'): ?>
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-teal-light text-teal">Available</span>
                  <?php elseif ($product['status'] === 'pending_review'): ?>
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">Pending review</span>
                  <?php elseif ($product['status'] === 'rejected'): ?>
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-red-100 text-red-600">Rejected</span>
                  <?php else: ?>
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">Hidden</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-3.5 text-right">
                  <div class="flex items-center justify-end gap-3">
                    <a href="/products/edit?id=<?= $product['id'] ?>" class="inline-flex items-center gap-1.5 text-teal font-medium hover:underline text-sm">
                      <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                    </a>
                    <form method="POST" action="/products/delete" class="js-confirm-form inline"
                      data-title="Delete this product?"
                      data-text="This cannot be undone. It will be removed from your shop."
                      data-icon="error"
                      data-confirm-text="Yes, delete"
                      data-confirm-color="#dc2626">
                      <input type="hidden" name="id" value="<?= $product['id'] ?>">
                      <button type="submit" class="inline-flex items-center gap-1.5 text-gray-400 hover:text-red-500 font-medium text-sm">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                      </button>
                    </form>
                  </div>
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

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
    <?php if (!empty($products)): ?>
    $('#productsTable').DataTable({
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

    if (typeof lucide !== 'undefined') lucide.createIcons();

    // SweetAlert2 confirmation para sa mga forms na may class="js-confirm-form" (hal. Delete)
    $(document).on('submit', '.js-confirm-form', function (e) {
        e.preventDefault();
        const form = this;
        const title = $(form).data('title');
        const text = $(form).data('text');
        const icon = $(form).data('icon') || 'question';
        const confirmText = $(form).data('confirm-text') || 'Yes, continue';
        const confirmColor = $(form).data('confirm-color') || '#0d9488';

        if (typeof Swal === 'undefined') {
            form.submit();
            return;
        }

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
