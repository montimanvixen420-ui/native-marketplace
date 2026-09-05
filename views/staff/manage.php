<style>
  .staff-avatar{width:34px;height:34px;border-radius:9999px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;}
  .status-pill{display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:700;}
  .status-active{background:#dcfce7;color:#15803d;}
  .status-inactive{background:#f3f4f6;color:#6b7280;}
  .status-suspended{background:#fee2e2;color:#b91c1c;}
  .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e5e7eb;color:#4b5563;}
  .icon-btn:hover{background:#f9fafb;}
</style>

<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <main class="flex-1 px-5 py-7 md:px-8">
    <header class="mb-6 flex items-start justify-between gap-4">
      <div>
        <p class="eyebrow text-brand">Branch Manager</p>
        <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Staff Management</h1>
        <p class="mt-2 text-sm text-gray-500">Branch: <span class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($manager['branch_name']) ?></span></p>
      </div>
      <button id="add" class="flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-dark">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Add Staff
      </button>
    </header>

       <section class="dashboard-panel overflow-x-auto">

           <!-- Custom Search & Filters Toolbar -->
      <div class="flex flex-col sm:flex-row flex-wrap items-center justify-end gap-3 mb-4">
        <div class="relative w-full sm:w-72">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="staffSearchInput" placeholder="Search staff..." class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-gray-200 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
        </div>

        <select id="staffRoleFilter" class="bg-white border border-gray-200 text-sm rounded-lg px-2 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand">
          <option value="">All roles</option>
          <?php foreach ($positions as $v => $label): ?>
            <option value="<?= htmlspecialchars($label) ?>"><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>

        <select id="staffStatusFilter" class="bg-white border border-gray-200 text-sm rounded-lg px-2 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand">
          <option value="">All statuses</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Suspended">Suspended</option>
        </select>

        <div class="flex items-center gap-2 shrink-0">
          <select id="staffEntriesSelect" class="bg-white border border-gray-200 text-sm rounded-lg px-2 py-1.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand">
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
          <tr><th>Profile</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Date Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($staffList as $s): ?>
            <tr data-id="<?= (int) $s['id'] ?>">
              <td><div class="staff-avatar"><?= htmlspecialchars(strtoupper(substr($s['first_name'], 0, 1))) ?></div></td>
              <td class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
              <td><?= htmlspecialchars($s['email']) ?></td>
              <td><?= htmlspecialchars($positions[$s['position']] ?? $s['position']) ?></td>
              <td><span class="status-pill status-<?= htmlspecialchars($s['status']) ?>"><?= htmlspecialchars(ucfirst($s['status'])) ?></span></td>
              <td class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y', strtotime($s['created_at']))) ?></td>
              <td>
                <div class="flex gap-1.5">
                  <button type="button" class="icon-btn js-view" title="View"
                          data-id="<?= (int) $s['id'] ?>" data-name="<?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>"
                          data-email="<?= htmlspecialchars($s['email']) ?>" data-phone="<?= htmlspecialchars($s['phone'] ?? '') ?>"
                          data-position="<?= htmlspecialchars($positions[$s['position']] ?? $s['position']) ?>"
                          data-status="<?= htmlspecialchars(ucfirst($s['status'])) ?>" data-created="<?= htmlspecialchars(date('F j, Y', strtotime($s['created_at']))) ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <button type="button" class="icon-btn js-edit" title="Edit"
                          data-id="<?= (int) $s['id'] ?>" data-first="<?= htmlspecialchars($s['first_name']) ?>" data-last="<?= htmlspecialchars($s['last_name']) ?>"
                          data-email="<?= htmlspecialchars($s['email']) ?>" data-phone="<?= htmlspecialchars($s['phone'] ?? '') ?>" data-position="<?= htmlspecialchars($s['position']) ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                  </button>
                  <?php if ($s['status'] === 'active'): ?>
                    <button type="button" class="icon-btn js-status" title="Deactivate" data-id="<?= (int) $s['id'] ?>" data-status="inactive" data-name="<?= htmlspecialchars($s['first_name']) ?>">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="10" y1="9" x2="10" y2="15"/><line x1="14" y1="9" x2="14" y2="15"/></svg>
                    </button>
                  <?php else: ?>
                    <button type="button" class="icon-btn js-status" title="Activate" data-id="<?= (int) $s['id'] ?>" data-status="active" data-name="<?= htmlspecialchars($s['first_name']) ?>">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                    </button>
                  <?php endif; ?>
                  <button type="button" class="icon-btn js-reset-pw" title="Reset Password" data-id="<?= (int) $s['id'] ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  </button>
                  <button type="button" class="icon-btn js-archive" title="Archive" data-id="<?= (int) $s['id'] ?>" data-name="<?= htmlspecialchars($s['first_name']) ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
              </tbody>
      </table>

      <!-- Footer & Pagination -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-5 mt-2 border-t border-gray-100">
        <div id="staff-info" class="text-xs font-medium text-gray-500">Showing 0 to 0 of 0 entries</div>
        <div id="staff-pagination" class="flex items-center gap-1"></div>
      </div>
    </section>
  </main>
</div>

<!-- ── Add / Edit Staff Modal ─────────────────────────────── -->
<div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <form id="form" class="w-full max-w-lg space-y-3 rounded-2xl bg-white p-6 dark:bg-ink-2">
    <h2 id="modalTitle" class="font-display text-xl font-bold">Add Staff</h2>
    <input type="hidden" name="id" id="staffId">
    <div class="grid grid-cols-2 gap-3">
      <input class="field-input" required name="first_name" id="firstName" placeholder="First name">
      <input class="field-input" required name="last_name" id="lastName" placeholder="Last name">
    </div>
    <input class="field-input w-full" required type="email" name="email" id="email" placeholder="Email">
    <div class="flex gap-2">
      <span class="flex shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-600 select-none" style="width:60px;">+63</span>
      <input class="field-input flex-1 min-w-0" type="tel" inputmode="numeric" pattern="9\d{2} \d{3} \d{4}" maxlength="12" id="phoneDigits" placeholder="9XX XXX XXXX" title="Enter a valid PH mobile number: 9XX XXX XXXX" autocomplete="off">
    </div>
    <input type="hidden" name="phone" id="phone">
    <select class="field-input w-full" required name="position" id="position">
      <option value="" selected disabled>Select position</option>
      <?php foreach ($positions as $v => $label): ?><option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($label) ?></option><?php endforeach; ?>
    </select>
    <div id="passwordWrap">
      <input class="field-input w-full" id="password" minlength="8" type="password" name="password" placeholder="Password (at least 8 characters)">
    </div>
    <p class="text-xs text-gray-500">Staff will automatically belong to <?= htmlspecialchars($manager['branch_name']) ?>.</p>
    <div class="flex justify-end gap-2">
      <button type="button" id="cancel" class="rounded-lg border px-4 py-2">Cancel</button>
      <button id="saveBtn" class="rounded-lg bg-brand px-4 py-2 text-white">Save</button>
    </div>
  </form>
</div>

<!-- ── View Staff Modal (read-only) ─────────────────────────────── -->
<div id="viewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <div class="w-full max-w-md space-y-3 rounded-2xl bg-white p-6 dark:bg-ink-2">
    <div class="mb-2 flex items-center justify-between">
      <h2 class="font-display text-xl font-bold">Staff Details</h2>
      <button type="button" id="viewClose" class="text-gray-400 hover:text-gray-600">✕</button>
    </div>
    <dl class="space-y-2 text-sm">
      <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd id="viewName" class="font-semibold text-ink dark:text-white"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd id="viewEmail"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd id="viewPhone"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Role</dt><dd id="viewPosition"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Branch</dt><dd><?= htmlspecialchars($manager['branch_name']) ?></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd id="viewStatus"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Date Created</dt><dd id="viewCreated"></dd></div>
    </dl>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const staffTable = $('#staffTable').DataTable({
    responsive: true,
    columnDefs: [{ orderable: false, targets: -1 }],
    pageLength: 5,
    lengthChange: false,
    searching: true,
    language: { emptyTable: 'No staff yet — click "Add Staff" to create the first one for this branch.' },
    layout: {
        topStart: null,
        topEnd: null,
        bottomStart: null,
        bottomEnd: null
    },
    drawCallback: function () {
        renderDtPillPagination(this.api(), 'staff-pagination', 'staff-info');
    }
  });
  $('#staffSearchInput').on('keyup', function () { staffTable.search(this.value).draw(); });
  $('#staffEntriesSelect').on('change', function () { staffTable.page.len(this.value).draw(); });

  $('#staffRoleFilter').on('change', function () {
    var val = this.value;
    staffTable.column(3).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
  });

  $('#staffStatusFilter').on('change', function () {
    var val = this.value;
    staffTable.column(4).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
  });

  function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
  function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }
  function setLoading($btn, loading, loadingText, normalText) { $btn.prop('disabled', loading).text(loading ? loadingText : normalText); }
  function toast(success, message) { Swal.fire({ toast: true, position: 'top-end', icon: success ? 'success' : 'error', title: message || (success ? 'Done.' : 'Something went wrong. Please try again.'), showConfirmButton: false, timer: 3000, timerProgressBar: true }); }

  // ── PH phone number (+63, digits only, max 10 digits, grouped as XXX XXX XXXX) ─────
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
    $('#phoneDigits').val(formatPhoneDisplay(d));
    $('#phone').val(d ? '+63' + d : '');
  }
  $('#phoneDigits').on('input', function () {
    var digits = this.value.replace(/\D/g, '').slice(0, 10);
    this.value = formatPhoneDisplay(digits);
    $('#phone').val(digits ? '+63' + digits : '');
  });

  // ── Add ─────────────────────────────
  $('#add').click(function () {
    $('#form')[0].reset();
    $('#staffId').val('');
    setPhone('');
    $('#modalTitle').text('Add Staff');
    $('#passwordWrap').show();
    $('#password').prop('required', true);
    openModal('modal');
  });
  $('#cancel').click(() => closeModal('modal'));

  // ── Edit ─────────────────────────────
  $(document).on('click', '.js-edit', function () {
    $('#form')[0].reset();
    $('#modalTitle').text('Edit Staff');
    $('#staffId').val(this.dataset.id);
    $('#firstName').val(this.dataset.first);
    $('#lastName').val(this.dataset.last);
    $('#email').val(this.dataset.email);
    setPhone(this.dataset.phone);
    $('#position').val(this.dataset.position);
    $('#passwordWrap').hide();
    $('#password').prop('required', false);
    openModal('modal');
  });

  // ── Add / Edit submit ─────────────────────────────
  $('#form').submit(function (e) {
    e.preventDefault();
    const id = $('#staffId').val();
    const url = id ? `/staff/manage/${id}/update` : '/staff/manage/store';
    const $btn = $('#saveBtn');
    setLoading($btn, true, 'Saving...', 'Save');
    $.post(url, $(this).serialize())
      .done(function (res) {
        toast(res.success, res.message);
        if (res.success) setTimeout(() => location.reload(), 900);
      })
      .fail(x => toast(false, x.responseJSON?.message || 'Unable to save staff.'))
      .always(() => setLoading($btn, false, 'Saving...', 'Save'));
  });

  // ── View (read-only) ─────────────────────────────
  $(document).on('click', '.js-view', function () {
    $('#viewName').text(this.dataset.name);
    $('#viewEmail').text(this.dataset.email);
    $('#viewPhone').text(this.dataset.phone || '—');
    $('#viewPosition').text(this.dataset.position);
    $('#viewStatus').text(this.dataset.status);
    $('#viewCreated').text(this.dataset.created);
    openModal('viewModal');
  });
  $('#viewClose').click(() => closeModal('viewModal'));

  // ── Activate / Deactivate ─────────────────────────────
  $(document).on('click', '.js-status', function () {
    const id = this.dataset.id, status = this.dataset.status, name = this.dataset.name;
    const verb = status === 'active' ? 'activate' : 'deactivate';
    Swal.fire({
      title: 'Are you sure?',
      text: `You are about to ${verb} ${name}'s staff account.`,
      icon: 'warning', showCancelButton: true,
      confirmButtonText: `Yes, ${verb}`, cancelButtonText: 'Cancel', confirmButtonColor: '#0d9488',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.post(`/staff/manage/${id}/status`, { status: status })
        .done(res => { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
        .fail(() => toast(false, 'Something went wrong. Please try again.'));
    });
  });

  // ── Archive ─────────────────────────────
  $(document).on('click', '.js-archive', function () {
    const id = this.dataset.id, name = this.dataset.name;
    Swal.fire({
      title: 'Are you sure?',
      text: `You are about to archive ${name}'s staff account. This cannot be undone.`,
      icon: 'warning', showCancelButton: true,
      confirmButtonText: 'Yes, archive', cancelButtonText: 'Cancel', confirmButtonColor: '#dc2626',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.post(`/staff/manage/${id}/archive`)
        .done(res => { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
        .fail(() => toast(false, 'Something went wrong. Please try again.'));
    });
  });

  // ── Reset Password ─────────────────────────────
  $(document).on('click', '.js-reset-pw', function () {
    const id = this.dataset.id;
    Swal.fire({
      title: 'Reset Password', input: 'password',
      inputLabel: 'New password (min. 8 characters)', inputAttributes: { minlength: 8 },
      showCancelButton: true, confirmButtonText: 'Reset', confirmButtonColor: '#0d9488',
    }).then(function (result) {
      if (!result.isConfirmed || !result.value) return;
      $.post(`/staff/manage/${id}/reset-password`, { password: result.value })
        .done(res => toast(res.success, res.message))
        .fail(x => toast(false, x.responseJSON?.message || 'Something went wrong. Please try again.'));
    });
  });
});
</script>