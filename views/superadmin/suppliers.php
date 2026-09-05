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
          <tr class="acct-row"
              data-search="<?= htmlspecialchars(strtolower($account['name'] . ' ' . $account['email'] . ' ' . $account['status'])) ?>">
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
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
        <div class="relative w-full max-w-xs">
          <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
          <input type="text" id="acctSearchInput" placeholder="Search name or email..." class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm focus:outline-none focus:border-blue-500">
        </div>
        <div class="flex items-center gap-2">
          <select id="acctPageLength" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
          <span class="text-sm text-gray-500">entries per page</span>
        </div>
      </div>
      <?php renderAccountsTable($suppliers, $statusStyles, $sellerApplications); ?>
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-5 py-4 border-t border-gray-100">
        <div id="acct-info" class="text-xs font-medium text-gray-500">Showing 0 to 0 of 0 entries</div>
        <div id="acct-pagination" class="flex items-center gap-1"></div>
      </div>
    </div>

  </main>

</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
let acctCurrentPage = 1;

function acctTable_update(resetPage = false) {
  if (resetPage) acctCurrentPage = 1;

  const searchValue = (document.getElementById('acctSearchInput')?.value || '').toLowerCase().trim();
  const perPage = parseInt(document.getElementById('acctPageLength')?.value || '5');

  const rows = Array.from(document.querySelectorAll('#accountsTable tbody .acct-row'));
  const matchingRows = rows.filter(row => !searchValue || row.dataset.search.includes(searchValue));

  const totalEntries = matchingRows.length;
  const totalPages = Math.ceil(totalEntries / perPage) || 1;
  if (acctCurrentPage > totalPages) acctCurrentPage = totalPages;

  const startIndex = (acctCurrentPage - 1) * perPage;
  const endIndex = startIndex + perPage;

  rows.forEach(r => r.style.display = 'none');
  matchingRows.slice(startIndex, endIndex).forEach(r => r.style.display = '');

  const infoEl = document.getElementById('acct-info');
  if (infoEl) {
    infoEl.textContent = totalEntries === 0
      ? 'Showing 0 to 0 of 0 entries'
      : `Showing ${startIndex + 1} to ${Math.min(endIndex, totalEntries)} of ${totalEntries} entries`;
  }

  const pagEl = document.getElementById('acct-pagination');
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
    pagEl.appendChild(mkBtn('«', acctCurrentPage === 1, () => { acctCurrentPage = 1; acctTable_update(); }));
    pagEl.appendChild(mkBtn('‹', acctCurrentPage === 1, () => { if (acctCurrentPage > 1) { acctCurrentPage--; acctTable_update(); } }));
    for (let p = 1; p <= totalPages; p++) {
      const isActive = p === acctCurrentPage;
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 text-xs font-bold rounded-lg transition-colors ${isActive ? 'bg-blue-600 text-white border border-blue-600' : 'border border-gray-200 hover:bg-gray-100 text-gray-700'}`;
      pageBtn.textContent = p;
      pageBtn.onclick = () => { acctCurrentPage = p; acctTable_update(); };
      pagEl.appendChild(pageBtn);
    }
    pagEl.appendChild(mkBtn('›', acctCurrentPage === totalPages || totalEntries === 0, () => { if (acctCurrentPage < totalPages) { acctCurrentPage++; acctTable_update(); } }));
    pagEl.appendChild(mkBtn('»', acctCurrentPage === totalPages || totalEntries === 0, () => { acctCurrentPage = totalPages; acctTable_update(); }));
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('accountsTable')) {
    acctTable_update(true);
    document.getElementById('acctSearchInput')?.addEventListener('keyup', () => acctTable_update(true));
    document.getElementById('acctPageLength')?.addEventListener('change', () => acctTable_update(true));
  }

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