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
              <tr class="user-row"
                  data-search="<?= htmlspecialchars(strtolower($user['name'] . ' ' . $user['email'] . ' ' . $user['role'] . ' ' . $user['status'])) ?>">
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

      <!-- Custom pagination footer (same style as Orders page) -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-5 py-4 border-t border-gray-100">
        <div id="us-info" class="text-xs font-medium text-gray-500">Showing 0 to 0 of 0 entries</div>
        <div id="us-pagination" class="flex items-center gap-1"></div>
      </div>
    </div>

  </main>

</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
let usCurrentPage = 1;

function superadminUsers_updateTable(resetPage = false) {
  if (resetPage) usCurrentPage = 1;

  const perPage = parseInt(document.getElementById('usersPageLength')?.value || '5');
  const rows = Array.from(document.querySelectorAll('#usersTable tbody .user-row'));

  const totalEntries = rows.length;
  const totalPages = Math.ceil(totalEntries / perPage) || 1;
  if (usCurrentPage > totalPages) usCurrentPage = totalPages;

  const startIndex = (usCurrentPage - 1) * perPage;
  const endIndex = startIndex + perPage;

  rows.forEach(r => r.style.display = 'none');
  rows.slice(startIndex, endIndex).forEach(r => r.style.display = '');

  const infoEl = document.getElementById('us-info');
  if (infoEl) {
    infoEl.textContent = totalEntries === 0
      ? 'Showing 0 to 0 of 0 entries'
      : `Showing ${startIndex + 1} to ${Math.min(endIndex, totalEntries)} of ${totalEntries} entries`;
  }

  const pagEl = document.getElementById('us-pagination');
  if (pagEl) {
    pagEl.innerHTML = '';
    const mkBtn = (label, disabled, onClick) => {
      const btn = document.createElement('button');
      btn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-gray-200 transition-colors ${disabled ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-100 text-gray-700'}`;
      btn.textContent = label;
      btn.disabled = disabled;
      btn.onclick = onClick;
      return btn;
    };
    pagEl.appendChild(mkBtn('«', usCurrentPage === 1, () => { usCurrentPage = 1; superadminUsers_updateTable(); }));
    pagEl.appendChild(mkBtn('‹', usCurrentPage === 1, () => { if (usCurrentPage > 1) { usCurrentPage--; superadminUsers_updateTable(); } }));
    for (let p = 1; p <= totalPages; p++) {
      const isActive = p === usCurrentPage;
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 text-xs font-bold rounded-lg transition-colors ${isActive ? 'bg-blue-600 text-white border border-blue-600' : 'border border-gray-200 hover:bg-gray-100 text-gray-700'}`;
      pageBtn.textContent = p;
      pageBtn.onclick = () => { usCurrentPage = p; superadminUsers_updateTable(); };
      pagEl.appendChild(pageBtn);
    }
    pagEl.appendChild(mkBtn('›', usCurrentPage === totalPages || totalEntries === 0, () => { if (usCurrentPage < totalPages) { usCurrentPage++; superadminUsers_updateTable(); } }));
    pagEl.appendChild(mkBtn('»', usCurrentPage === totalPages || totalEntries === 0, () => { usCurrentPage = totalPages; superadminUsers_updateTable(); }));
  }
}

document.addEventListener('DOMContentLoaded', () => {
  <?php if (!empty($users)): ?>
  superadminUsers_updateTable(true);
  document.getElementById('usersPageLength')?.addEventListener('change', () => superadminUsers_updateTable(true));
  <?php endif; ?>
  lucide.createIcons();

  document.addEventListener('submit', function (e) {
    if (!e.target.classList.contains('js-confirm-form')) return;
    e.preventDefault();
    const form = e.target;
    const title = form.dataset.title;
    const text = form.dataset.text;
    const icon = form.dataset.icon || 'question';
    const confirmText = form.dataset.confirmText || 'Yes, continue';
    const confirmColor = form.dataset.confirmColor || '#0d9488';

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