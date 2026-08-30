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
</style>

<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="flex-1 px-5 py-7 md:px-8">
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
      <table id="branchesTable" class="display w-full text-left text-sm">
        <thead>
          <tr><th>Branch Name</th><th>Seller</th><th>Branch Manager</th><th>Staff Count</th><th>Status</th><th>Date Created</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($branches as $b): ?>
            <tr>
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
    </section>

    <!-- ── Branch Managers ─────────────────────────────── -->
    <section id="tab-managers" class="dashboard-panel overflow-x-auto hidden">
      <table id="managersTable" class="display w-full text-left text-sm">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Assigned Branch</th><th>Status</th><th>Date Created</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($managers as $m): ?>
            <tr>
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
    </section>

    <!-- ── Staff ─────────────────────────────── -->
    <section id="tab-staff" class="dashboard-panel overflow-x-auto hidden">
      <table id="staffTable" class="display w-full text-left text-sm">
        <thead>
          <tr><th>Name</th><th>Role</th><th>Branch</th><th>Branch Manager</th><th>Status</th><th>Date Created</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($staffList as $s): ?>
            <tr>
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
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
$(function () {
  $('#branchesTable').DataTable({
    responsive: true,
    columnDefs: [{ orderable: false, targets: -1 }],
    lengthMenu: [5, 10, 15, 20],
    language: { emptyTable: "No branches yet." }
  });
  $('#managersTable').DataTable({
    responsive: true,
    columnDefs: [{ orderable: false, targets: -1 }],
    lengthMenu: [5, 10, 15, 20],
    language: { emptyTable: "No Branch Managers yet." }
  });
  $('#staffTable').DataTable({
    responsive: true,
    columnDefs: [{ orderable: false, targets: -1 }],
    lengthMenu: [5, 10, 15, 20],
    language: { emptyTable: "No staff yet." }
  });

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