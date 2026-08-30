<?php
$statusStyles = [
    'approved' => 'bg-teal/10 text-teal',
    'pending' => 'bg-amber/15 text-amber-700',
    'suspended' => 'bg-orange-100 text-orange-700',
    'banned' => 'bg-red-100 text-red-700',
];

if (!function_exists('renderAccountsTable')) {
function renderAccountsTable(array $accounts, array $statusStyles, array $sellerApplications = []): void
{
    if (empty($accounts)) {
        echo '<div class="px-5 py-8 text-center text-sm text-gray-400">No accounts match this filter.</div>';
        return;
    }
    ?>
    <table id="accountsTable" class="w-full text-sm">
      <thead>
        <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
          <th class="px-5 py-3 font-medium">Name</th>
          <th class="px-5 py-3 font-medium">Email / business</th>
          <th class="px-5 py-3 font-medium">Status</th>
          <th class="px-5 py-3 font-medium">Joined</th>
          <th class="px-5 py-3 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($accounts as $account): ?>
          <?php $statusClass = $statusStyles[$account['status']] ?? 'bg-gray-100 text-gray-600'; ?>
          <tr>
            <td class="px-5 py-3 font-medium text-gray-900"><?= htmlspecialchars($account['name']) ?></td>
            <td class="px-5 py-3 text-gray-600"><p><?= htmlspecialchars($account['email']) ?></p><?php if (isset($sellerApplications[$account['id']])): $application = $sellerApplications[$account['id']]; ?><p class="text-xs text-brand mt-1"><?= htmlspecialchars($application['business_name']) ?></p><div class="mt-1 flex gap-2 text-xs"><a class="text-gray-500 hover:underline" href="/superadmin/users/<?= (int) $account['id'] ?>/verification/id" target="_blank">View ID</a><?php if (!empty($application['selfie_path'])): ?><a class="text-gray-500 hover:underline" href="/superadmin/users/<?= (int) $account['id'] ?>/verification/selfie" target="_blank">View selfie</a><?php endif; ?></div><?php endif; ?></td>
            <td class="px-5 py-3">
              <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-full <?= $statusClass ?>">
                <?= ucfirst($account['status']) ?>
              </span>
            </td>
            <td class="px-5 py-3 text-gray-500"><?= date('M j, Y', strtotime($account['created_at'])) ?></td>
            <td class="px-5 py-3">
              <div class="flex items-center justify-end gap-2 flex-wrap">
                <a href="/superadmin/users/<?= (int) $account['id'] ?>/edit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                  <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                </a>

                <?php if ($account['status'] === 'pending'): ?>
                  <form method="POST" action="/superadmin/users/<?= (int) $account['id'] ?>/approve" class="js-confirm-form" data-title="Approve this account?" data-text="They will gain full access to their account." data-icon="question" data-confirm-text="Yes, approve" data-confirm-color="#0d9488">
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-teal text-white hover:bg-teal/90">
                      <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
                    </button>
                  </form>
                  <form method="POST" action="/superadmin/users/<?= (int) $account['id'] ?>/reject" class="js-confirm-form" data-title="Reject this account?" data-text="This will decline their pending application." data-icon="warning" data-confirm-text="Yes, reject" data-confirm-color="#dc2626">
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
                      <i data-lucide="x" class="w-3.5 h-3.5"></i> Reject
                    </button>
                  </form>
                <?php elseif ($account['status'] === 'suspended'): ?>
                  <form method="POST" action="/superadmin/users/<?= (int) $account['id'] ?>/reactivate" class="js-confirm-form" data-title="Reactivate this account?" data-text="They will regain access to their account." data-icon="question" data-confirm-text="Yes, reactivate" data-confirm-color="#0d9488">
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-teal text-white hover:bg-teal/90">
                      <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reactivate
                    </button>
                  </form>
                <?php elseif ($account['status'] === 'approved'): ?>
                  <form method="POST" action="/superadmin/users/<?= (int) $account['id'] ?>/suspend" class="js-confirm-form" data-title="Suspend this account?" data-text="They won't be able to access their account until reactivated." data-icon="warning" data-confirm-text="Yes, suspend" data-confirm-color="#ea580c">
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
                      <i data-lucide="ban" class="w-3.5 h-3.5"></i> Suspend
                    </button>
                  </form>
                <?php endif; ?>

                <form method="POST" action="/superadmin/users/<?= (int) $account['id'] ?>/delete" class="js-confirm-form" data-title="Delete this account permanently?" data-text="This cannot be undone. All their data will be removed." data-icon="error" data-confirm-text="Yes, delete" data-confirm-color="#dc2626">
                  <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
}
}
?>
<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Suppliers</h1>
        <p class="text-sm text-gray-500">Review applications and manage supplier accounts.</p>
      </div>
    </div>

    <!-- Status filter -->
    <form method="GET" action="/superadmin/suppliers" class="flex items-center gap-3 mb-6">
      <select name="status" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal">
        <option value="">All statuses</option>
        <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
        <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        <option value="banned" <?= $statusFilter === 'banned' ? 'selected' : '' ?>>Banned</option>
      </select>
      <button type="submit" class="text-xs font-medium px-4 py-2 rounded-lg bg-ink text-white hover:bg-ink/90">Filter</button>
      <?php if ($statusFilter !== ''): ?>
        <a href="/superadmin/suppliers" class="text-xs text-gray-500 hover:underline">Clear</a>
      <?php endif; ?>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <?php renderAccountsTable($suppliers, $statusStyles, $sellerApplications); ?>
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
    if ($('#accountsTable').length) {
        $('#accountsTable').DataTable({
            searching: true,
            paging: true,
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            lengthChange: true,
            order: [[3, 'desc']],
            columnDefs: [
                { orderable: false, targets: 4 }
            ],
            layout: { topStart: 'pageLength', topEnd: 'search', bottomStart: 'info', bottomEnd: 'paging' },
            drawCallback: function () {
                lucide.createIcons();
            }
        });
    }

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
