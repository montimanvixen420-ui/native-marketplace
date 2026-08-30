<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">My products</h1>
        <p class="text-sm text-gray-500">Create and manage your product catalog. To sell an existing product in POS, transfer it from Seller Inventory.</p>
      </div>
      <a href="/products/create" class="inline-flex items-center gap-1.5 bg-amber text-ink text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i> Add product
      </a>
    </div>

    <!-- Empty State or Table -->
    <?php if (empty($products)): ?>
      <div class="bg-white border border-gray-200 rounded-lg p-12 text-center shadow-sm">
        <p class="font-display text-lg font-semibold text-gray-900">Nothing on your shelf yet</p>
        <p class="text-sm text-gray-500 mt-1 mb-5">Add your first product to start selling.</p>
        <a href="/products/create" class="inline-flex items-center gap-1.5 bg-amber text-ink text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition">
          <i data-lucide="plus" class="w-4 h-4"></i> Add product
        </a>
      </div>
    <?php else: ?>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden p-4 shadow-sm">
        
        <!-- Search & Entries Header Toolbar -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
          <div class="relative w-full sm:w-72">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="customSearchInput" placeholder="Search product or category..." class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900">
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <select id="customEntriesSelect" class="bg-white border border-gray-200 text-sm rounded-lg px-2 py-1.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-slate-900">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="20">20</option>
            </select>
            <span class="text-sm text-gray-500">entries per page</span>
          </div>
        </div>

        <table id="productsTable" class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
              <th class="px-5 py-3 font-semibold">Product</th>
              <th class="px-3 py-3 font-semibold">Category</th>
              <th class="px-3 py-3 font-semibold text-right">Price</th>
              <th class="px-3 py-3 font-semibold text-right">Stock remaining</th>
              <th class="px-3 py-3 font-semibold text-center">Status</th>
              <th class="px-5 py-3 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <?php 
                $stock = (int) $product['stock'];
                $status = strtolower($product['status']);

                // Smart Inventory Status Logic with Inline Style Colors
                if ($stock === 0) {
                    $badgeText = 'Out of stock';
                    $badgeStyle = 'background-color: #be123c; color: #ffffff;'; // Solid Red
                } elseif ($stock <= 5 && $status === 'active') {
                    $badgeText = 'Low stock';
                    $badgeStyle = 'background-color: #fbbf24; color: #451a03;'; // Solid Amber/Yellow
                } elseif ($status === 'active') {
                    $badgeText = 'Available';
                    $badgeStyle = 'background-color: #059669; color: #ffffff;'; // Solid Emerald Green
                } elseif ($status === 'pending_review') {
                    $badgeText = 'Pending review';
                    $badgeStyle = 'background-color: #f59e0b; color: #ffffff;'; // Amber
                } elseif ($status === 'rejected') {
                    $badgeText = 'Rejected';
                    $badgeStyle = 'background-color: #9f1239; color: #ffffff;'; // Dark Red
                } else {
                    $badgeText = 'Hidden';
                    $badgeStyle = 'background-color: #6b7280; color: #ffffff;'; // Gray
                }
              ?>
              <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/50">
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <?php if (!empty($product['image_url'])): ?>
                      <img src="/<?= htmlspecialchars($product['image_url']) ?>" alt="" class="w-9 h-9 rounded-lg object-cover border border-gray-200">
                    <?php else: ?>
                      <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-xs text-gray-400">—</div>
                    <?php endif; ?>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></span>
                  </div>
                </td>
                <td class="px-3 py-3.5 text-gray-500"><?= htmlspecialchars($product['category'] ?? '—') ?></td>
                <td class="px-3 py-3.5 text-right font-semibold text-gray-900">₱<?= number_format((float) $product['price'], 2) ?></td>
                <td class="px-3 py-3.5 text-right font-medium <?= $stock === 0 ? 'text-red-600 font-bold' : ($stock <= 5 ? 'text-amber-700 font-bold' : 'text-gray-700') ?>">
                  <?= $stock ?> pcs
                </td>
                <td class="px-3 py-3.5 text-center">
                  <span style="<?= $badgeStyle ?>" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">
                    <?= $badgeText ?>
                  </span>
                </td>
                <td class="px-5 py-3.5 text-right">
                  <div class="flex items-center justify-end gap-3">
                    <a href="/products/edit?id=<?= $product['id'] ?>" class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-all">
                      <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-500"></i> Edit
                    </a>
                    <form method="POST" action="/products/delete" class="js-confirm-form inline"
                      data-title="Delete this product?"
                      data-text="This cannot be undone. It will be removed from your shop."
                      data-icon="error"
                      data-confirm-text="Yes, delete"
                      data-confirm-color="#dc2626">
                      <input type="hidden" name="id" value="<?= $product['id'] ?>">
                      <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-red-100 text-red-600 bg-red-50 hover:bg-red-100 shadow-sm transition-all">
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
    <?php if (!empty($products)): ?>
    const table = $('#productsTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: 5 }
        ],
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        drawCallback: function () {
            lucide.createIcons();
        }
    });

    $('#customSearchInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    $('#customEntriesSelect').on('change', function () {
        table.page.len(this.value).draw();
    });
    <?php endif; ?>

    if (typeof lucide !== 'undefined') lucide.createIcons();

    // SweetAlert2 Confirmation
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