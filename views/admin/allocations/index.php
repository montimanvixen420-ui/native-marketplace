<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white">Allocate stock to branch</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Allocation moves stock from Seller Inventory to the selected branch. It does not use Seller POS stock.</p>
    </div>

    <!-- Error Alert Banner -->
    <?php if (!empty($error)): ?>
      <div class="mb-6 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/40 dark:border-red-800 p-4 text-sm text-red-700 dark:text-red-300 flex items-center gap-2 shadow-sm">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-600 dark:text-red-400"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Table Container -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden p-4 shadow-sm">
      
      <!-- Custom Search & Entries Header Toolbar -->
      <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
        <div class="relative w-full sm:w-72">
          <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
          <input type="text" id="customSearchInput" placeholder="Search product or variant..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

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

      <table id="allocationsTable" class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
            <th class="px-5 py-3.5 font-semibold">Product / Variant</th>
            <th class="px-4 py-3.5 font-semibold">Seller Inventory Stock</th>
            <th class="px-5 py-3.5 font-semibold text-right">Allocate to Branch</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
          <?php foreach ($rows as $row): 
            $available = (int)($row['size'] !== null ? $row['variant_stock'] : $row['stock']); 
            if ($available < 1) continue; 
          ?>
            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition-colors">
              <td class="px-5 py-4 font-semibold text-gray-900 dark:text-gray-100">
                <?= htmlspecialchars($row['name']) ?>
                <?php if ($row['size'] !== null): ?>
                  <span class="text-xs font-normal text-gray-500 dark:text-gray-400 block mt-0.5">
                    Variant: <?= htmlspecialchars($row['size'] . ' / ' . $row['color']) ?>
                  </span>
                <?php endif; ?>
              </td>

              <td class="px-4 py-4 whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 px-3 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300 shadow-sm">
                  <i data-lucide="warehouse" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400"></i>
                  <?= $available ?> in inventory
                </span>
              </td>

              <td class="px-5 py-4">
                <form class="js-confirm-form flex flex-wrap items-center justify-end gap-2" 
                      data-title="Allocate stock to this branch?" 
                      data-text="This deducts the entered quantity from Seller Inventory and adds it to the selected branch." 
                      data-icon="question" 
                      data-confirm-text="Yes, allocate" 
                      data-confirm-color="#059669" 
                      method="post" 
                      action="/admin/allocations/store">
                  
                  <input type="hidden" name="product_id" value="<?= (int)$row['product_id'] ?>">
                  <input type="hidden" name="variant_size" value="<?= htmlspecialchars($row['size'] ?? '') ?>">
                  <input type="hidden" name="variant_color" value="<?= htmlspecialchars($row['color'] ?? '') ?>">

                  <!-- Branch Selector -->
                  <select required name="branch_id" aria-label="Destination branch" class="rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Select branch</option>
                    <?php foreach($branches as $branch): ?>
                      <option value="<?= (int)$branch['id'] ?>">
                        <?= htmlspecialchars($branch['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>

                  <!-- Quantity Input -->
                  <div class="relative">
                    <i data-lucide="boxes" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500"></i>
                    <input id="allocation-<?= (int)$row['product_id'] ?>-<?= (int)($row['id'] ?? 0) ?>" 
                           required min="1" max="<?= $available ?>" 
                           type="number" name="quantity" 
                           placeholder="Qty" 
                           title="Stock quantity to allocate" 
                           class="w-24 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 py-2 pl-8 pr-2 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500" 
                           aria-label="Stock quantity to allocate">
                  </div>

                  <!-- Optional Note -->
                  <input name="note" placeholder="Note (optional)" aria-label="Allocation note" class="w-36 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">

                  <!-- FIXED BUTTON: Direct BG Styling na Sure na Lalabas sa Light at Dark Mode -->
                  <button type="submit" style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-bold shadow-md hover:opacity-90 active:scale-95 transition-all cursor-pointer">
                    <i data-lucide="package-plus" class="w-4 h-4" style="color: #ffffff !important;"></i> 
                    <span style="color: #ffffff !important;">Allocate stock</span>
                  </button>
                </form>
              </td>
            </tr>
                   <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Footer & Pagination -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-5 mt-2 border-t border-gray-100 dark:border-slate-700/60">
        <div id="alloc-info" class="text-xs font-medium text-gray-500 dark:text-gray-400">Showing 0 to 0 of 0 entries</div>
        <div id="alloc-pagination" class="flex items-center gap-1"></div>
      </div>
    </div>
  </main>

</div>

<!-- DataTables & Scripts -->
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
    <?php if (!empty($rows)): ?>
    const table = $('#allocationsTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 2 }
        ],
               layout: {
            topStart: null,
            topEnd: null,
            bottomStart: null,
            bottomEnd: null
        },
        drawCallback: function () {
            renderDtPillPagination(this.api(), 'alloc-pagination', 'alloc-info');
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

    lucide.createIcons();

    // SweetAlert2 Form Handler
    $(document).on('submit', '.js-confirm-form', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: $(form).data('title'),
            text: $(form).data('text'),
            icon: $(form).data('icon') || 'question',
            showCancelButton: true,
            confirmButtonText: $(form).data('confirm-text') || 'Yes, continue',
            cancelButtonText: 'Cancel',
            confirmButtonColor: $(form).data('confirm-color') || '#059669',
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