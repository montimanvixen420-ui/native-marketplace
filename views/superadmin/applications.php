<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Review applications</h1>
        <p class="text-sm text-gray-500">Approve or reject pending seller and supplier sign-ups.</p>
      </div>
    </div>

    <!-- Role filter -->
    <form method="GET" action="/superadmin/applications" class="flex items-center gap-3 mb-6">
      <select name="role" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal">
        <option value="">All applicants</option>
        <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Sellers only</option>
        <option value="supplier" <?= $roleFilter === 'supplier' ? 'selected' : '' ?>>Suppliers only</option>
      </select>
      <?php if ($roleFilter !== ''): ?>
        <a href="/superadmin/applications" class="text-xs text-gray-500 hover:underline">Clear</a>
      <?php endif; ?>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <?php if (empty($applicants)): ?>
        <div class="px-5 py-8 text-center text-sm text-gray-400">No pending applications right now.</div>
      <?php else: ?>
        <table id="applicationsTable" class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
              <th class="px-5 py-3 font-medium">Name</th>
              <th class="px-5 py-3 font-medium">Email / business</th>
              <th class="px-5 py-3 font-medium">Role</th>
              <th class="px-5 py-3 font-medium">Applied</th>
              <th class="px-5 py-3 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($applicants as $applicant): ?>
              <tr>
                <td class="px-5 py-3 font-medium text-gray-900"><?= htmlspecialchars($applicant['name']) ?></td>
                <td class="px-5 py-3 text-gray-600">
                  <p><?= htmlspecialchars($applicant['email']) ?></p>
                  <?php if (isset($sellerApplications[$applicant['id']])): $application = $sellerApplications[$applicant['id']]; ?>
                    <p class="text-xs text-brand mt-1"><?= htmlspecialchars($application['business_name']) ?></p>
                    <div class="mt-1 flex gap-2 text-xs">
                      <a class="text-gray-500 hover:underline" href="/superadmin/users/<?= (int) $applicant['id'] ?>/verification/id" target="_blank">View ID</a>
                      <?php if (!empty($application['selfie_path'])): ?>
                        <a class="text-gray-500 hover:underline" href="/superadmin/users/<?= (int) $applicant['id'] ?>/verification/selfie" target="_blank">View selfie</a>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-gray-600">
                  <?= $applicant['role'] === 'admin' ? 'Seller' : ucfirst($applicant['role']) ?>
                </td>
                <td class="px-5 py-3 text-gray-500"><?= date('M j, Y', strtotime($applicant['created_at'])) ?></td>
                <td class="px-5 py-3">
                  <div class="flex items-center justify-end gap-2 flex-wrap">
                    <a href="/superadmin/users/<?= (int) $applicant['id'] ?>/edit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                      <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                    </a>
                    <form method="POST" action="/superadmin/users/<?= (int) $applicant['id'] ?>/approve" class="js-confirm-form" data-title="Approve this applicant?" data-text="They will gain full access as a <?= $applicant['role'] === 'admin' ? 'seller' : 'supplier' ?>." data-icon="question" data-confirm-text="Yes, approve" data-confirm-color="#0d9488">
                      <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-teal text-white hover:bg-teal/90">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
                      </button>
                    </form>
                    <form method="POST" action="/superadmin/users/<?= (int) $applicant['id'] ?>/reject" class="js-confirm-form" data-title="Reject this applicant?" data-text="This will decline their pending application." data-icon="warning" data-confirm-text="Yes, reject" data-confirm-color="#dc2626">
                      <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i> Reject
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </main>

</div>

<!-- DataTables (Tailwind styling) -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(document).ready(function () {
    <?php if (!empty($applicants)): ?>
    $('#applicationsTable').DataTable({
        searching: true,
        paging: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        lengthChange: true,
        order: [[3, 'desc']], // sort by "Applied" column
        columnDefs: [
            { orderable: false, targets: 4 } // "Actions" column - walang sort
        ],
        layout: { topStart: 'pageLength', topEnd: 'search', bottomStart: 'info', bottomEnd: 'paging' },
        drawCallback: function () {
            lucide.createIcons();
        }
    });
    <?php endif; ?>

    lucide.createIcons();

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
