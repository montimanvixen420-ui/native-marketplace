<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Users</h1>
        <p class="text-sm text-gray-500">Manage every account on the platform.</p>
      </div>
    </div>

    <!-- Search & filters -->
    <form method="GET" action="/superadmin/users" class="flex items-center gap-3 mb-5">
  <input
    type="text"
    name="search"
    value="<?= htmlspecialchars($filters['search']) ?>"
    placeholder="Search by name or email…"
    class="flex-1 max-w-xs text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal"
  >

  <select name="role" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal">
    <option value="">All roles</option>
    <option value="superadmin" <?= $filters['role'] === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
    <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Owner (seller)</option>
    <option value="supplier" <?= $filters['role'] === 'supplier' ? 'selected' : '' ?>>Supplier</option>
    <option value="customer" <?= $filters['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
  </select>

  <select name="status" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal">
    <option value="">All statuses</option>
    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
    <option value="suspended" <?= $filters['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
    <option value="banned" <?= $filters['status'] === 'banned' ? 'selected' : '' ?>>Banned</option>
  </select>

  <div class="flex items-center gap-2 ml-auto">
    <select id="usersPageLength" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal">
      <option value="5">5</option>
      <option value="10">10</option>
      <option value="25">25</option>
      <option value="50">50</option>
    </select>
    <span class="text-sm text-gray-500">entries per page</span>
  </div>
</form>

    <!-- Users table -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <table id="usersTable" class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
            <th class="px-5 py-3 font-medium">Name</th>
            <th class="px-5 py-3 font-medium">Email</th>
            <th class="px-5 py-3 font-medium">Role</th>
            <th class="px-5 py-3 font-medium">Status</th>
            <th class="px-5 py-3 font-medium">Joined</th>
            <th class="px-5 py-3 font-medium text-right">Actions</th>
          </tr>
        </thead>
        
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($users)): ?>
            <tr>
              <td colspan="6" class="px-5 py-8 text-center text-gray-400">No users match your filters.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $user): ?>
              <?php
                $statusStyles = [
                    'approved' => 'bg-teal/10 text-teal',
                    'pending' => 'bg-amber/15 text-amber-700',
                    'suspended' => 'bg-orange-100 text-orange-700',
                    'banned' => 'bg-red-100 text-red-700',
                ];
                $statusClass = $statusStyles[$user['status']] ?? 'bg-gray-100 text-gray-600';
              ?>
              <tr>
                <td class="px-5 py-3 font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                <td class="px-5 py-3 text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                <td class="px-5 py-3 text-gray-600">
                  <?= $user['role'] === 'admin' ? 'Admin (seller)' : ucfirst($user['role']) ?>
                </td>
                <td class="px-5 py-3">
                  <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-full <?= $statusClass ?>">
                    <?= ucfirst($user['status']) ?>
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-500"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                <td class="px-5 py-3">
                  <div class="flex items-center justify-end gap-2 flex-wrap">
                    <a href="/superadmin/users/<?= (int) $user['id'] ?>/edit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                      <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                    </a>

                    <?php if ($user['status'] === 'pending'): ?>
                      <form method="POST" action="/superadmin/users/<?= (int) $user['id'] ?>/approve" class="js-confirm-form" data-title="Approve this user?" data-text="They will gain full access as a <?= $user['role'] === 'admin' ? 'seller admin' : ucfirst($user['role']) ?>." data-icon="question" data-confirm-text="Yes, approve" data-confirm-color="#0d9488">
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-teal text-white hover:bg-teal/90">
                          <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
                        </button>
                      </form>
                      <form method="POST" action="/superadmin/users/<?= (int) $user['id'] ?>/reject" class="js-confirm-form" data-title="Reject this user?" data-text="This will decline their pending request." data-icon="warning" data-confirm-text="Yes, reject" data-confirm-color="#dc2626">
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
                          <i data-lucide="x" class="w-3.5 h-3.5"></i> Reject
                        </button>
                      </form>
                    <?php elseif ($user['status'] === 'suspended'): ?>
                      <form method="POST" action="/superadmin/users/<?= (int) $user['id'] ?>/reactivate" class="js-confirm-form" data-title="Reactivate this user?" data-text="They will regain access to their account." data-icon="question" data-confirm-text="Yes, reactivate" data-confirm-color="#0d9488">
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-teal text-white hover:bg-teal/90">
                          <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reactivate
                        </button>
                      </form>
                    <?php elseif ($user['status'] === 'approved' && $user['role'] !== 'superadmin'): ?>
                      <form method="POST" action="/superadmin/users/<?= (int) $user['id'] ?>/suspend" class="js-confirm-form" data-title="Suspend this user?" data-text="They won't be able to access their account until reactivated." data-icon="warning" data-confirm-text="Yes, suspend" data-confirm-color="#ea580c">
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
                          <i data-lucide="ban" class="w-3.5 h-3.5"></i> Suspend
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if ($user['role'] !== 'superadmin'): ?>
                      <form method="POST" action="/superadmin/users/<?= (int) $user['id'] ?>/delete" class="js-confirm-form" data-title="Delete this user permanently?" data-text="This cannot be undone. All their data will be removed." data-icon="error" data-confirm-text="Yes, delete" data-confirm-color="#dc2626">
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50">
                          <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
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
    <?php if (!empty($users)): ?>
    var table = $('#usersTable').DataTable({
        searching: true,
        paging: true,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: 5 }
        ],
        layout: { topStart: null, topEnd: 'search', bottomStart: 'info', bottomEnd: 'paging' },
        drawCallback: function () {
            lucide.createIcons();
        }
    });

    $('#usersPageLength').on('change', function () {
        table.page.len(parseInt(this.value, 10)).draw();
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
