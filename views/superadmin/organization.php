<style>
  .status-pill{display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:700;}
  .status-active{background:#dcfce7;color:#15803d;}
  .status-inactive{background:#f3f4f6;color:#6b7280;}
  .status-suspended{background:#fee2e2;color:#b91c1c;}
  .status-archived{background:#fee2e2;color:#b91c1c;}
  .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e5e7eb;color:#4b5563;}
  .icon-btn:hover{background:#f9fafb;}
  .org-tab{padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:600;color:#6b7280;}
  .org-tab.active{background:#0d9488;color:#fff;}
  .dashboard-panel table th, .dashboard-panel table td{padding:0.75rem 1rem;}
  .dashboard-panel table thead th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.03em;color:#6b7280;border-bottom:1px solid #e5e7eb;}
  .dashboard-panel table tbody tr{border-bottom:1px solid #f3f4f6;}
  .dashboard-panel table tbody tr:last-child{border-bottom:0;}
</style>

<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="flex-1 min-w-0 px-5 py-7 md:px-8">
    <header class="mb-6">
      <p class="eyebrow text-brand">Super Admin</p>
      <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Organization</h1>
      <p class="mt-2 text-sm text-gray-500">View-only. Seller → Branch → Branch Manager → Staff. No create, edit, or status actions here.</p>
    </header>

    <div class="mb-5 flex gap-2">
      <button type="button" class="org-tab active" data-tab="branches">Branches</button>
      <button type="button" class="org-tab" data-tab="managers">Branch Managers</button>
      <button type="button" class="org-tab" data-tab="staff">Staff</button>
    </div>

    <!-- ── Branches ─────────────────────────────── -->
    <section id="tab-branches" class="dashboard-panel overflow-x-auto">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-3 px-1">
        <select id="org-branches-status" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        <div class="flex items-center gap-2">
          <select id="org-branches-perpage" class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-blue-500">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="20">20</option>
          </select>
          <span class="text-sm text-gray-500">entries per page</span>
        </div>
      </div>
      <table id="branchesTable" class="display w-full text-left text-sm">
        <thead>
          <tr><th>Branch Name</th><th>Seller</th><th>Branch Manager</th><th>Staff Count</th><th>Status</th><th>Date Created</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($branches as $b): ?>
            <tr class="branch-row" data-status="<?= $b['is_active'] ? 'active' : 'inactive' ?>"
                data-search="<?= htmlspecialchars(strtolower($b['name'] . ' ' . $b['seller_name'] . ' ' . ($b['branch_manager_name'] ?? ''))) ?>">
              <td class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($b['name']) ?></td>
              <td><?= htmlspecialchars($b['seller_name']) ?></td>
              <td><?= htmlspecialchars($b['branch_manager_name'] ?? '—') ?></td>
              <td><?= (int) $b['staff_count'] ?></td>
              <td><span class="status-pill status-<?= $b['is_active'] ? 'active' : 'inactive' ?>"><?= $b['is_active'] ? 'Active' : 'Inactive' ?></span></td>
              <td class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y', strtotime($b['created_at']))) ?></td>
              <td>
                <button type="button" class="icon-btn js-view-branch" title="View"
                        data-name="<?= htmlspecialchars($b['name']) ?>" data-seller="<?= htmlspecialchars($b['seller_name']) ?>"
                        data-address="<?= htmlspecialchars($b['address']) ?>" data-manager="<?= htmlspecialchars($b['branch_manager_name'] ?? '—') ?>"
                        data-staff-count="<?= (int) $b['staff_count'] ?>" data-status="<?= $b['is_active'] ? 'Active' : 'Inactive' ?>"
                        data-created="<?= htmlspecialchars(date('F j, Y', strtotime($b['created_at']))) ?>">
                  <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 mt-2 border-t border-gray-100 dark:border-white/10">
        <div id="org-branches-info" class="text-xs font-medium text-gray-500 dark:text-gray-400">Showing 0 to 0 of 0 entries</div>
        <div id="org-branches-pagination" class="flex items-center gap-1"></div>
      </div>
    </section>

    <!-- ── Branch Managers ─────────────────────────────── -->
    <section id="tab-managers" class="dashboard-panel overflow-x-auto hidden">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-3 px-1">
        <select id="org-managers-status" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
          <option value="archived">Archived</option>
        </select>
        <div class="flex items-center gap-2">
          <select id="org-managers-perpage" class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-blue-500">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="20">20</option>
          </select>
          <span class="text-sm text-gray-500">entries per page</span>
        </div>
      </div>
      <table id="managersTable" class="display w-full text-left text-sm">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Assigned Branch</th><th>Status</th><th>Date Created</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($managers as $m): ?>
            <tr class="managers-row" data-status="<?= htmlspecialchars(strtolower($m['status'])) ?>"
                data-search="<?= htmlspecialchars(strtolower($m['name'] . ' ' . $m['email'] . ' ' . $m['branch_name'])) ?>">
              <td class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($m['name']) ?></td>
              <td><?= htmlspecialchars($m['email']) ?></td>
              <td><?= htmlspecialchars($m['branch_name']) ?></td>
              <td><span class="status-pill status-<?= htmlspecialchars($m['status']) ?>"><?= htmlspecialchars(ucfirst($m['status'])) ?></span></td>
              <td class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y', strtotime($m['created_at']))) ?></td>
              <td>
                <button type="button" class="icon-btn js-view-manager" title="View"
                        data-name="<?= htmlspecialchars($m['name']) ?>" data-email="<?= htmlspecialchars($m['email']) ?>"
                        data-seller="<?= htmlspecialchars($m['seller_name']) ?>" data-branch="<?= htmlspecialchars($m['branch_name']) ?>"
                        data-status="<?= htmlspecialchars(ucfirst($m['status'])) ?>" data-created="<?= htmlspecialchars(date('F j, Y', strtotime($m['created_at']))) ?>">
                  <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 mt-2 border-t border-gray-100 dark:border-white/10">
        <div id="org-managers-info" class="text-xs font-medium text-gray-500 dark:text-gray-400">Showing 0 to 0 of 0 entries</div>
        <div id="org-managers-pagination" class="flex items-center gap-1"></div>
      </div>
    </section>

    <!-- ── Staff ─────────────────────────────── -->
    <section id="tab-staff" class="dashboard-panel overflow-x-auto hidden">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-3 px-1">
        <select id="org-staff-status" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
          <option value="archived">Archived</option>
        </select>
        <div class="flex items-center gap-2">
          <select id="org-staff-perpage" class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-blue-500">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="20">20</option>
          </select>
          <span class="text-sm text-gray-500">entries per page</span>
        </div>
      </div>
      <table id="staffTable" class="display w-full text-left text-sm">
        <thead>
          <tr><th>Name</th><th>Role</th><th>Branch</th><th>Branch Manager</th><th>Status</th><th>Date Created</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($staffList as $s): ?>
            <tr class="staff-row" data-status="<?= htmlspecialchars(strtolower($s['status'])) ?>"
                data-search="<?= htmlspecialchars(strtolower($s['first_name'] . ' ' . $s['last_name'] . ' ' . $s['branch_name'] . ' ' . ($s['manager_name'] ?? ''))) ?>">
              <td class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
              <td><?= htmlspecialchars($positions[$s['position']] ?? $s['position']) ?></td>
              <td><?= htmlspecialchars($s['branch_name']) ?></td>
              <td><?= htmlspecialchars($s['manager_name'] ?? '—') ?></td>
              <td><span class="status-pill status-<?= htmlspecialchars($s['status']) ?>"><?= htmlspecialchars(ucfirst($s['status'])) ?></span></td>
              <td class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y', strtotime($s['created_at']))) ?></td>
              <td>
                <button type="button" class="icon-btn js-view-staff" title="View"
                        data-name="<?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>" data-email="<?= htmlspecialchars($s['email']) ?>"
                        data-role="<?= htmlspecialchars($positions[$s['position']] ?? $s['position']) ?>" data-branch="<?= htmlspecialchars($s['branch_name']) ?>"
                        data-manager="<?= htmlspecialchars($s['manager_name'] ?? '—') ?>" data-status="<?= htmlspecialchars(ucfirst($s['status'])) ?>"
                        data-created="<?= htmlspecialchars(date('F j, Y', strtotime($s['created_at']))) ?>">
                  <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 mt-2 border-t border-gray-100 dark:border-white/10">
        <div id="org-staff-info" class="text-xs font-medium text-gray-500 dark:text-gray-400">Showing 0 to 0 of 0 entries</div>
        <div id="org-staff-pagination" class="flex items-center gap-1"></div>
      </div>
    </section>
  </main>
</div>

<!-- ── Read-only View Modal (shared) ─────────────────────────────── -->
<div id="viewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <div class="w-full max-w-md space-y-3 rounded-2xl bg-white p-6 dark:bg-ink-2">
    <div class="mb-2 flex items-center justify-between">
      <h2 id="viewTitle" class="font-display text-xl font-bold">Details</h2>
      <button type="button" id="viewClose" class="text-gray-400 hover:text-gray-600">✕</button>
    </div>
    <dl id="viewFields" class="space-y-2 text-sm"></dl>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function makeSimplePagination({ tableSelector, rowSelector, infoId, pagId, statusSelectId, perPageSelectId }) {
  let currentPage = 1;

  function getPerPage() {
    const el = document.getElementById(perPageSelectId);
    return el ? parseInt(el.value || '5') : 5;
  }

  function update(resetPage = false) {
    if (resetPage) currentPage = 1;
    const perPage = getPerPage();
    const statusValue = statusSelectId ? (document.getElementById(statusSelectId)?.value || '') : '';

    const allRows = Array.from(document.querySelectorAll(`${tableSelector} tbody .${rowSelector}`));
    const rows = allRows.filter(row => !statusValue || row.dataset.status === statusValue);

    const totalEntries = rows.length;
    const totalPages = Math.ceil(totalEntries / perPage) || 1;
    if (currentPage > totalPages) currentPage = totalPages;

    const startIndex = (currentPage - 1) * perPage;
    const endIndex = startIndex + perPage;

    allRows.forEach(r => r.style.display = 'none');
    rows.slice(startIndex, endIndex).forEach(r => r.style.display = '');

    const infoEl = document.getElementById(infoId);
    if (infoEl) {
      infoEl.textContent = totalEntries === 0
        ? 'Showing 0 to 0 of 0 entries'
        : `Showing ${startIndex + 1} to ${Math.min(endIndex, totalEntries)} of ${totalEntries} entries`;
    }

    const pagEl = document.getElementById(pagId);
    if (pagEl) {
      pagEl.innerHTML = '';
      const mkBtn = (label, disabled, onClick) => {
        const btn = document.createElement('button');
        btn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-gray-200 dark:border-white/10 transition-colors ${disabled ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-100 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200'}`;
        btn.textContent = label;
        btn.disabled = disabled;
        btn.onclick = onClick;
        return btn;
      };
      pagEl.appendChild(mkBtn('«', currentPage === 1, () => { currentPage = 1; update(); }));
      pagEl.appendChild(mkBtn('‹', currentPage === 1, () => { if (currentPage > 1) { currentPage--; update(); } }));
      for (let p = 1; p <= totalPages; p++) {
        const isActive = p === currentPage;
        const pageBtn = document.createElement('button');
        pageBtn.className = `px-3 py-1 text-xs font-bold rounded-lg transition-colors ${isActive ? 'bg-blue-600 text-white border border-blue-600' : 'border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200'}`;
        pageBtn.textContent = p;
        pageBtn.onclick = () => { currentPage = p; update(); };
        pagEl.appendChild(pageBtn);
      }
      pagEl.appendChild(mkBtn('›', currentPage === totalPages || totalEntries === 0, () => { if (currentPage < totalPages) { currentPage++; update(); } }));
      pagEl.appendChild(mkBtn('»', currentPage === totalPages || totalEntries === 0, () => { currentPage = totalPages; update(); }));
    }
  }

  if (statusSelectId) document.getElementById(statusSelectId)?.addEventListener('change', () => update(true));
  if (perPageSelectId) document.getElementById(perPageSelectId)?.addEventListener('change', () => update(true));

  update(true);
  return update;
}

$(function () {
  makeSimplePagination({ tableSelector: '#branchesTable', rowSelector: 'branch-row', infoId: 'org-branches-info', pagId: 'org-branches-pagination', statusSelectId: 'org-branches-status', perPageSelectId: 'org-branches-perpage' });
  makeSimplePagination({ tableSelector: '#managersTable', rowSelector: 'managers-row', infoId: 'org-managers-info', pagId: 'org-managers-pagination', statusSelectId: 'org-managers-status', perPageSelectId: 'org-managers-perpage' });
  makeSimplePagination({ tableSelector: '#staffTable', rowSelector: 'staff-row', infoId: 'org-staff-info', pagId: 'org-staff-pagination', statusSelectId: 'org-staff-status', perPageSelectId: 'org-staff-perpage' });

  // ── Tabs ─────────────────────────────
  $('.org-tab').click(function () {
    const tab = this.dataset.tab;
    $('.org-tab').removeClass('active');
    $(this).addClass('active');
    $('#tab-branches, #tab-managers, #tab-staff').addClass('hidden');
    $('#tab-' + tab).removeClass('hidden');
  });

  function row(label, value) { return `<div class="flex justify-between"><dt class="text-gray-500">${label}</dt><dd class="font-medium text-ink dark:text-white">${value}</dd></div>`; }
  function openView(title, fields) {
    $('#viewTitle').text(title);
    $('#viewFields').html(fields.map(f => row(f[0], f[1])).join(''));
    $('#viewModal').removeClass('hidden').addClass('flex');
  }
  $('#viewClose').click(() => $('#viewModal').addClass('hidden').removeClass('flex'));

  // ── View: Branch ─────────────────────────────
  $(document).on('click', '.js-view-branch', function () {
    const d = this.dataset;
    openView('Branch Details', [
      ['Branch Name', d.name], ['Seller', d.seller], ['Address', d.address],
      ['Branch Manager', d.manager], ['Staff Count', d.staffCount],
      ['Status', d.status], ['Date Created', d.created],
    ]);
  });

  // ── View: Branch Manager ─────────────────────────────
  $(document).on('click', '.js-view-manager', function () {
    const d = this.dataset;
    openView('Branch Manager Details', [
      ['Name', d.name], ['Email', d.email], ['Seller', d.seller], ['Assigned Branch', d.branch],
      ['Status', d.status], ['Date Created', d.created],
    ]);
  });

  // ── View: Staff ─────────────────────────────
  $(document).on('click', '.js-view-staff', function () {
    const d = this.dataset;
    openView('Staff Details', [
      ['Name', d.name], ['Email', d.email], ['Role', d.role], ['Branch', d.branch],
      ['Branch Manager', d.manager], ['Status', d.status], ['Date Created', d.created],
    ]);
  });
});
</script>