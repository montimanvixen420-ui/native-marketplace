<style>
  .mgr-avatar { width: 34px; height: 34px; border-radius: 9999px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; }
  .status-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; }
  .status-active { background: #dcfce7; color: #15803d; }
  .status-inactive { background: #f3f4f6; color: #6b7280; }
  .status-archived { background: #fee2e2; color: #b91c1c; }
</style>

<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">
  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <!-- Header Section -->
    <header class="mb-8 flex items-center justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400 mb-1">Seller Team</p>
        <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white">Branch Managers</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Each manager is assigned to exactly one of your branches.</p>
      </div>
      <button id="addManager" style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md hover:opacity-90 active:scale-95 transition-all cursor-pointer">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Add Branch Manager
      </button>
    </header>

    <?php if ($unmanagedCount > 0): ?>
      <div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 p-4 text-sm text-amber-900 dark:text-amber-200 flex items-start gap-3 shadow-xs">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
        <div>
          <p class="font-bold text-amber-950 dark:text-amber-100"><?= $unmanagedCount ?> of <?= $totalActiveBranches ?> active branches have no manager assigned</p>
          <p class="mt-0.5 text-xs text-amber-800/90 dark:text-amber-300/80">Use "Add Branch Manager" above to assign someone to those branches.</p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Table Card Panel -->
    <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden p-4 shadow-sm">
      
      <!-- Custom Search Toolbar (Nasa Kanan) -->
      <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-4">
        <div class="relative w-full sm:w-72">
          <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
          <input type="text" id="customSearchInput" placeholder="Search manager, email, branch..." class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <select id="customEntriesSelect" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="20">20</option>
          </select>
          <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
        </div>
      </div>

      <table id="managersTable" class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
            <th class="px-4 py-3.5 font-semibold text-center w-12">Profile</th>
            <th class="px-4 py-3.5 font-semibold">Manager</th>
            <th class="px-4 py-3.5 font-semibold">Email</th>
            <th class="px-4 py-3.5 font-semibold">Assigned Branch</th>
            <th class="px-4 py-3.5 font-semibold text-center">Status</th>
            <th class="px-4 py-3.5 font-semibold">Date Created</th>
            <th class="px-4 py-3.5 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
          <?php foreach ($managers as $manager): ?>
            <?php [$mFirst, $mLast] = array_pad(explode(' ', $manager['name'], 2), 2, ''); ?>
            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition-colors">
              <td class="px-4 py-4 text-center">
                <div class="mgr-avatar dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mx-auto">
                  <?= htmlspecialchars(strtoupper(substr($manager['name'], 0, 1))) ?>
                </div>
              </td>
              <td class="px-4 py-4 font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                <?= htmlspecialchars($manager['name']) ?>
              </td>
              <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                <?= htmlspecialchars($manager['email']) ?>
              </td>
              <td class="px-4 py-4 font-medium text-gray-800 dark:text-gray-200">
                <?= htmlspecialchars($manager['branch_name']) ?>
              </td>
              <td class="px-4 py-4 text-center whitespace-nowrap">
                <span class="status-pill status-<?= htmlspecialchars($manager['status']) ?>">
                  <?= htmlspecialchars(ucfirst($manager['status'])) ?>
                </span>
              </td>
              <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                <?= htmlspecialchars(date('M d, Y', strtotime($manager['created_at']))) ?>
              </td>
              <td class="px-4 py-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <button type="button" class="p-1.5 rounded-lg border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition js-view" title="View"
                          data-name="<?= htmlspecialchars($manager['name']) ?>" data-email="<?= htmlspecialchars($manager['email']) ?>"
                          data-phone="<?= htmlspecialchars($manager['phone'] ?? '') ?>" data-branch="<?= htmlspecialchars($manager['branch_name']) ?>"
                          data-status="<?= htmlspecialchars(ucfirst($manager['status'])) ?>" data-created="<?= htmlspecialchars(date('F j, Y', strtotime($manager['created_at']))) ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <button type="button" class="p-1.5 rounded-lg border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition js-edit" title="Edit"
                          data-id="<?= (int) $manager['user_id'] ?>" data-first="<?= htmlspecialchars($mFirst) ?>" data-last="<?= htmlspecialchars($mLast) ?>"
                          data-email="<?= htmlspecialchars($manager['email']) ?>" data-phone="<?= htmlspecialchars($manager['phone'] ?? '') ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                  </button>
                  <?php if ($manager['status'] === 'active'): ?>
                    <button type="button" class="p-1.5 rounded-lg border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition js-status" title="Deactivate" data-id="<?= (int) $manager['user_id'] ?>" data-status="inactive" data-name="<?= htmlspecialchars($manager['name']) ?>">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="10" y1="9" x2="10" y2="15"/><line x1="14" y1="9" x2="14" y2="15"/></svg>
                    </button>
                  <?php elseif ($manager['status'] === 'inactive'): ?>
                    <button type="button" class="p-1.5 rounded-lg border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition js-status" title="Activate" data-id="<?= (int) $manager['user_id'] ?>" data-status="active" data-name="<?= htmlspecialchars($manager['name']) ?>">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                    </button>
                  <?php endif; ?>
                  <!-- Fix #3: Change Branch / Reset Password / Restore / Archive now live in a shared "more actions" menu, opened via this trigger -->
                  <button type="button" class="js-row-menu p-1.5 rounded-lg border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition" title="More actions"
                          data-id="<?= (int) $manager['user_id'] ?>" data-name="<?= htmlspecialchars($manager['name']) ?>" data-archived="<?= $manager['status'] === 'archived' ? '1' : '0' ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1.5" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="12" cy="19" r="1.5" fill="currentColor" stroke="none"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>

<!-- ── Add / Edit Branch Manager Modal ─────────────────────────────── -->
<div id="managerModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
  <form id="managerForm" class="w-full max-w-lg space-y-4 rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-6 shadow-xl">
    <h2 id="managerModalTitle" class="font-display text-xl font-bold text-gray-900 dark:text-white">Add Branch Manager</h2>
    <input type="hidden" name="id" id="managerId">
    
    <div class="grid grid-cols-2 gap-3">
      <input class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" required name="first_name" id="mFirstName" placeholder="First name">
      <input class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" required name="last_name" id="mLastName" placeholder="Last name">
    </div>
    
    <input class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" required type="email" name="email" id="mEmail" placeholder="Email">
    
    <div class="flex gap-2">
      <span class="flex shrink-0 items-center justify-center rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-sm font-semibold text-gray-600 dark:text-gray-300 select-none px-3">+63</span>
      <input class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" type="tel" inputmode="numeric" pattern="9\d{2} \d{3} \d{4}" maxlength="12" id="mPhoneDigits" placeholder="9XX XXX XXXX" autocomplete="off">
    </div>
    <input type="hidden" name="phone" id="mPhone">
    
    <div id="branchWrap">
      <select class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" required name="branch_id" id="mBranch">
        <option value="">Select branch</option>
        <?php foreach ($branches as $branch): if ($branch['is_active']): ?>
          <option value="<?= (int) $branch['id'] ?>"><?= htmlspecialchars($branch['name']) ?></option>
        <?php endif; endforeach; ?>
      </select>
    </div>
    
    <div id="mPasswordWrap">
      <input class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" id="mPassword" minlength="8" name="password" type="password" placeholder="Password (at least 8 characters)">
    </div>
    
    <div class="flex justify-end gap-2 pt-2">
      <button type="button" id="managerCancel" class="rounded-xl border border-gray-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">Cancel</button>
      <button id="managerSaveBtn" style="background-color: #059669 !important; color: #ffffff !important;" class="rounded-xl px-4 py-2 text-sm font-bold shadow-md hover:opacity-90 transition">Save</button>
    </div>
  </form>
</div>

<!-- ── View Branch Manager Modal (read-only) ─────────────────────────────── -->
<div id="viewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
  <div class="w-full max-w-md space-y-4 rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-6 shadow-xl">
    <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pb-3">
      <h2 class="font-display text-lg font-bold text-gray-900 dark:text-white">Branch Manager Details</h2>
      <button type="button" id="viewClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-bold text-lg">&times;</button>
    </div>
    <dl class="space-y-2.5 text-sm">
      <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Name</dt><dd id="viewName" class="font-semibold text-gray-900 dark:text-white"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Email</dt><dd id="viewEmail" class="text-gray-700 dark:text-gray-300"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Phone</dt><dd id="viewPhone" class="text-gray-700 dark:text-gray-300"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Branch</dt><dd id="viewBranch" class="font-medium text-gray-800 dark:text-gray-200"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd id="viewStatus"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Date Created</dt><dd id="viewCreated" class="text-gray-600 dark:text-gray-300"></dd></div>
    </dl>
  </div>
</div>

<!-- Shared "more actions" dropdown — repositioned + repopulated per row on open, appended to <body> so it can never be clipped by the table card's overflow-hidden -->
<div id="rowActionsMenu" class="hidden fixed z-50 w-48 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg py-1.5 text-sm">
  <button type="button" id="menuChangeBranch" class="js-change w-full flex items-center gap-2 px-3 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700">
    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0115-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 01-15 6.7L3 16"/><path d="M8 16H3v5"/></svg>
    Change Branch
  </button>
  <button type="button" id="menuResetPw" class="js-reset-pw w-full flex items-center gap-2 px-3 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700">
    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
    Reset Password
  </button>
  <button type="button" id="menuRestore" class="js-restore w-full flex items-center gap-2 px-3 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700">
    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0115-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 01-15 6.7L3 16"/><path d="M8 16H3v5"/></svg>
    Restore
  </button>
  <div class="my-1 border-t border-gray-100 dark:border-slate-700"></div>
  <button type="button" id="menuArchive" class="js-archive w-full flex items-center gap-2 px-3 py-2 text-left text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40">
    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
    Archive
  </button>
</div>

<!-- DataTables Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
$(function () {
  <?php if (!empty($managers)): ?>
  const table = $('#managersTable').DataTable({
    paging: true,
    pageLength: 5,
    lengthChange: false,
    searching: true,
    order: [[1, 'asc']], // Sort by Manager Name
    columnDefs: [
      { orderable: false, targets: [0, -1] } // Disable sort on Avatar & Actions
    ],
    layout: {
      topStart: null,
      topEnd: null,
      bottomStart: 'info',
      bottomEnd: 'paging'
    },
    drawCallback: function () {
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }
  });

  // Custom Search Toolbar Listeners
  $('#customSearchInput').on('keyup', function () {
    table.search(this.value).draw();
  });

  $('#customEntriesSelect').on('change', function () {
    table.page.len(this.value).draw();
  });
  <?php endif; ?>

  if (typeof lucide !== 'undefined') lucide.createIcons();

  function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
  function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }
  function setLoading($btn, loading, loadingText, normalText) { $btn.prop('disabled', loading).text(loading ? loadingText : normalText); }
  function toast(success, message) { Swal.fire({ toast: true, position: 'top-end', icon: success ? 'success' : 'error', title: message || (success ? 'Done.' : 'Something went wrong. Please try again.'), showConfirmButton: false, timer: 3000, timerProgressBar: true }); }

  // ── PH Phone Format ─────
  function formatPhoneDisplay(rawDigits) {
    var out = '';
    if (rawDigits.length > 0) out += rawDigits.slice(0, 3);
    if (rawDigits.length > 3) out += ' ' + rawDigits.slice(3, 6);
    if (rawDigits.length > 6) out += ' ' + rawDigits.slice(6, 10);
    return out;
  }
  function phoneDigitsFrom(raw) {
    var d = (raw || '').replace(/\D/g, '');
    if (d.indexOf('63') === 0) d = d.slice(2);
    if (d.charAt(0) === '0') d = d.slice(1);
    return d.slice(0, 10);
  }
  function setPhone(raw) {
    var d = phoneDigitsFrom(raw);
    $('#mPhoneDigits').val(formatPhoneDisplay(d));
    $('#mPhone').val(d ? '+63' + d : '');
  }
  $('#mPhoneDigits').on('input', function () {
    var digits = this.value.replace(/\D/g, '').slice(0, 10);
    this.value = formatPhoneDisplay(digits);
    $('#mPhone').val(digits ? '+63' + digits : '');
  });

  // ── Add ─────
  $('#addManager').click(function () {
    $('#managerForm')[0].reset();
    $('#managerId').val('');
    setPhone('');
    $('#managerModalTitle').text('Add Branch Manager');
    $('#branchWrap').show(); $('#mBranch').prop('required', true);
    $('#mPasswordWrap').show(); $('#mPassword').prop('required', true);
    openModal('managerModal');
  });
  $('#managerCancel').click(() => closeModal('managerModal'));

  // ── Edit ─────
  $(document).on('click', '.js-edit', function () {
    $('#managerForm')[0].reset();
    $('#managerModalTitle').text('Edit Branch Manager');
    $('#managerId').val(this.dataset.id);
    $('#mFirstName').val(this.dataset.first);
    $('#mLastName').val(this.dataset.last);
    $('#mEmail').val(this.dataset.email);
    setPhone(this.dataset.phone);
    $('#branchWrap').hide(); $('#mBranch').prop('required', false);
    $('#mPasswordWrap').hide(); $('#mPassword').prop('required', false);
    openModal('managerModal');
  });

  // ── Add / Edit Submit ─────
  $('#managerForm').submit(function (e) {
    e.preventDefault();
    const id = $('#managerId').val();
    const url = id ? `/admin/branch-managers/${id}/update` : '/admin/branch-managers/store';
    const $btn = $('#managerSaveBtn');
    setLoading($btn, true, 'Saving...', 'Save');
    $.post(url, $(this).serialize())
      .done(function (res) { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
      .fail(x => toast(false, x.responseJSON?.message || 'Unable to save Branch Manager.'))
      .always(() => setLoading($btn, false, 'Saving...', 'Save'));
  });

  // ── View ─────
  $(document).on('click', '.js-view', function () {
    $('#viewName').text(this.dataset.name);
    $('#viewEmail').text(this.dataset.email);
    $('#viewPhone').text(this.dataset.phone || '—');
    $('#viewBranch').text(this.dataset.branch);
    $('#viewStatus').text(this.dataset.status);
    $('#viewCreated').text(this.dataset.created);
    openModal('viewModal');
  });
  $('#viewClose').click(() => closeModal('viewModal'));

  // ── Change Branch ─────
  $(document).on('click', '.js-change', function () {
    const id = this.dataset.id;
    Swal.fire({
      title: 'Change Branch', text: `Assign ${this.dataset.name} to a different branch.`,
      input: 'select',
      inputValue: '',
      inputOptions: { '': 'Select branch', <?php foreach ($branches as $branch): if ($branch['is_active']): ?>'b<?= (int) $branch['id'] ?>':'<?= htmlspecialchars(addslashes($branch['name'])) ?>',<?php endif; endforeach; ?> },
      inputValidator: (value) => !value ? 'Please select a branch.' : undefined,
      showCancelButton: true, confirmButtonText: 'Save', confirmButtonColor: '#059669',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      const branchId = result.value.replace(/^b/, '');
      $.post(`/admin/branch-managers/${id}/branch`, { branch_id: branchId })
        .done(res => { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
        .fail(x => toast(false, x.responseJSON?.message || 'Unable to update assignment.'));
    });
  });

  // ── Status (Activate / Deactivate) ─────
  $(document).on('click', '.js-status', function () {
    const id = this.dataset.id, status = this.dataset.status, name = this.dataset.name;
    const verb = status === 'active' ? 'activate' : 'deactivate';
    Swal.fire({
      title: 'Are you sure?', text: `You are about to ${verb} ${name}'s Branch Manager account.`,
      icon: 'warning', showCancelButton: true,
      confirmButtonText: `Yes, ${verb}`, cancelButtonText: 'Cancel', confirmButtonColor: '#059669',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.post(`/admin/branch-managers/${id}/status`, { status: status })
        .done(res => { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
        .fail(() => toast(false, 'Something went wrong. Please try again.'));
    });
  });

  // ── Shared "more actions" menu (Fix #3) ─────
  $(document).on('click', '.js-row-menu', function (e) {
    e.stopPropagation();
    const btn = this;
    const id = btn.dataset.id, name = btn.dataset.name, archived = btn.dataset.archived === '1';
    const $menu = $('#rowActionsMenu');

    $('#menuChangeBranch').attr('data-id', id).attr('data-name', name).toggle(!archived);
    $('#menuResetPw').attr('data-id', id).toggle(!archived);
    $('#menuRestore').attr('data-id', id).attr('data-name', name).toggle(archived);
    $('#menuArchive').attr('data-id', id).attr('data-name', name).toggle(!archived);

    const rect = btn.getBoundingClientRect();
    $menu
      .css({ top: (rect.bottom + 4) + 'px', left: Math.max(8, rect.right - 192) + 'px' })
      .removeClass('hidden');
  });

  $(document).on('click', function () { $('#rowActionsMenu').addClass('hidden'); });

  // ── Restore (Fix #1) ─────
  $(document).on('click', '.js-restore', function () {
    const id = this.dataset.id, name = this.dataset.name;
    Swal.fire({
      title: 'Restore this Branch Manager?',
      text: `${name}'s account will be restored as Inactive. Activate it afterward to let them log in again.`,
      icon: 'question', showCancelButton: true,
      confirmButtonText: 'Yes, restore', cancelButtonText: 'Cancel', confirmButtonColor: '#059669',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.post(`/admin/branch-managers/${id}/status`, { status: 'inactive' })
        .done(res => { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
        .fail(() => toast(false, 'Something went wrong. Please try again.'));
    });
  });

  // ── Archive ─────
  $(document).on('click', '.js-archive', function () {
    const id = this.dataset.id, name = this.dataset.name;
    Swal.fire({
      title: 'Are you sure?', text: `You are about to archive ${name}'s Branch Manager account. Their branch will need a new manager.`,
      icon: 'warning', showCancelButton: true,
      confirmButtonText: 'Yes, archive', cancelButtonText: 'Cancel', confirmButtonColor: '#dc2626',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.post(`/admin/branch-managers/${id}/status`, { status: 'archived' })
        .done(res => { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
        .fail(() => toast(false, 'Something went wrong. Please try again.'));
    });
  });

  // ── Reset Password ─────
  $(document).on('click', '.js-reset-pw', function () {
    const id = this.dataset.id;
    Swal.fire({
      title: 'Reset Password', input: 'password',
      inputLabel: 'New password (min. 8 characters)', inputAttributes: { minlength: 8 },
      showCancelButton: true, confirmButtonText: 'Reset', confirmButtonColor: '#059669',
    }).then(function (result) {
      if (!result.isConfirmed || !result.value) return;
      $.post(`/admin/branch-managers/${id}/reset-password`, { password: result.value })
        .done(res => toast(res.success, res.message))
        .fail(x => toast(false, x.responseJSON?.message || 'Something went wrong. Please try again.'));
    });
  });
});
</script>