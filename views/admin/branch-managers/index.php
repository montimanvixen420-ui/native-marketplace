<style>
  .mgr-avatar{width:34px;height:34px;border-radius:9999px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;}
  .status-pill{display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:700;}
  .status-active{background:#dcfce7;color:#15803d;}
  .status-inactive{background:#f3f4f6;color:#6b7280;}
  .status-archived{background:#fee2e2;color:#b91c1c;}
  .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e5e7eb;color:#4b5563;}
  .icon-btn:hover{background:#f9fafb;}
</style>

<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
  <main class="flex-1 px-5 py-7 md:px-8">
    <header class="mb-6 flex items-start justify-between gap-4">
      <div>
        <p class="eyebrow text-brand">Seller Team</p>
        <h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Branch Managers</h1>
        <p class="mt-2 text-sm text-gray-500">Each manager is assigned to exactly one of your branches.</p>
      </div>
      <button id="addManager" class="flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-dark">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Add Branch Manager
      </button>
    </header>

    <section class="dashboard-panel overflow-x-auto">
      <table id="managersTable" class="display w-full text-left text-sm">
        <thead>
          <tr><th>Profile</th><th>Manager</th><th>Email</th><th>Assigned Branch</th><th>Status</th><th>Date Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($managers as $manager): ?>
            <?php [$mFirst, $mLast] = array_pad(explode(' ', $manager['name'], 2), 2, ''); ?>
            <tr>
              <td><div class="mgr-avatar"><?= htmlspecialchars(strtoupper(substr($manager['name'], 0, 1))) ?></div></td>
              <td class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($manager['name']) ?></td>
              <td><?= htmlspecialchars($manager['email']) ?></td>
              <td><?= htmlspecialchars($manager['branch_name']) ?></td>
              <td><span class="status-pill status-<?= htmlspecialchars($manager['status']) ?>"><?= htmlspecialchars(ucfirst($manager['status'])) ?></span></td>
              <td class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y', strtotime($manager['created_at']))) ?></td>
              <td>
                <div class="flex gap-1.5">
                  <button type="button" class="icon-btn js-view" title="View"
                          data-name="<?= htmlspecialchars($manager['name']) ?>" data-email="<?= htmlspecialchars($manager['email']) ?>"
                          data-phone="<?= htmlspecialchars($manager['phone'] ?? '') ?>" data-branch="<?= htmlspecialchars($manager['branch_name']) ?>"
                          data-status="<?= htmlspecialchars(ucfirst($manager['status'])) ?>" data-created="<?= htmlspecialchars(date('F j, Y', strtotime($manager['created_at']))) ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <button type="button" class="icon-btn js-edit" title="Edit"
                          data-id="<?= (int) $manager['user_id'] ?>" data-first="<?= htmlspecialchars($mFirst) ?>" data-last="<?= htmlspecialchars($mLast) ?>"
                          data-email="<?= htmlspecialchars($manager['email']) ?>" data-phone="<?= htmlspecialchars($manager['phone'] ?? '') ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                  </button>
                  <?php if ($manager['status'] !== 'archived'): ?>
                    <button type="button" class="icon-btn js-change" title="Change Branch" data-id="<?= (int) $manager['user_id'] ?>" data-name="<?= htmlspecialchars($manager['name']) ?>">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0115-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 01-15 6.7L3 16"/><path d="M8 16H3v5"/></svg>
                    </button>
                  <?php endif; ?>
                  <?php if ($manager['status'] === 'active'): ?>
                    <button type="button" class="icon-btn js-status" title="Deactivate" data-id="<?= (int) $manager['user_id'] ?>" data-status="inactive" data-name="<?= htmlspecialchars($manager['name']) ?>">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="10" y1="9" x2="10" y2="15"/><line x1="14" y1="9" x2="14" y2="15"/></svg>
                    </button>
                  <?php elseif ($manager['status'] === 'inactive'): ?>
                    <button type="button" class="icon-btn js-status" title="Activate" data-id="<?= (int) $manager['user_id'] ?>" data-status="active" data-name="<?= htmlspecialchars($manager['name']) ?>">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                    </button>
                  <?php endif; ?>
                  <button type="button" class="icon-btn js-reset-pw" title="Reset Password" data-id="<?= (int) $manager['user_id'] ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  </button>
                  <?php if ($manager['status'] !== 'archived'): ?>
                    <button type="button" class="icon-btn js-archive" title="Archive" data-id="<?= (int) $manager['user_id'] ?>" data-name="<?= htmlspecialchars($manager['name']) ?>">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($managers)): ?>
            <tr><td colspan="7" class="py-8 text-center text-sm text-gray-400">No Branch Managers yet — click "Add Branch Manager" to assign one to a branch.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>

<!-- ── Add / Edit Branch Manager Modal ─────────────────────────────── -->
<div id="managerModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <form id="managerForm" class="w-full max-w-lg space-y-3 rounded-2xl bg-white p-6 dark:bg-ink-2">
    <h2 id="managerModalTitle" class="font-display text-xl font-bold">Add Branch Manager</h2>
    <input type="hidden" name="id" id="managerId">
    <div class="grid grid-cols-2 gap-3">
      <input class="field-input" required name="first_name" id="mFirstName" placeholder="First name">
      <input class="field-input" required name="last_name" id="mLastName" placeholder="Last name">
    </div>
    <input class="field-input w-full" required type="email" name="email" id="mEmail" placeholder="Email">
    <div class="flex gap-2">
      <span class="flex shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-600 select-none" style="width:60px;">+63</span>
      <input class="field-input flex-1 min-w-0" type="tel" inputmode="numeric" pattern="9\d{2} \d{3} \d{4}" maxlength="12" id="mPhoneDigits" placeholder="9XX XXX XXXX" title="Enter a valid PH mobile number: 9XX XXX XXXX" autocomplete="off">
    </div>
    <input type="hidden" name="phone" id="mPhone">
    <div id="branchWrap">
      <select class="field-input w-full" required name="branch_id" id="mBranch">
        <option value="">Select branch</option>
        <?php foreach ($branches as $branch): if ($branch['is_active']): ?>
          <option value="<?= (int) $branch['id'] ?>"><?= htmlspecialchars($branch['name']) ?></option>
        <?php endif; endforeach; ?>
      </select>
    </div>
    <div id="mPasswordWrap">
      <input class="field-input w-full" id="mPassword" minlength="8" name="password" type="password" placeholder="Password (at least 8 characters)">
    </div>
    <div class="flex justify-end gap-2">
      <button type="button" id="managerCancel" class="rounded-lg border px-4 py-2">Cancel</button>
      <button id="managerSaveBtn" class="rounded-lg bg-brand px-4 py-2 text-white">Save</button>
    </div>
  </form>
</div>

<!-- ── View Branch Manager Modal (read-only) ─────────────────────────────── -->
<div id="viewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <div class="w-full max-w-md space-y-3 rounded-2xl bg-white p-6 dark:bg-ink-2">
    <div class="mb-2 flex items-center justify-between">
      <h2 class="font-display text-xl font-bold">Branch Manager Details</h2>
      <button type="button" id="viewClose" class="text-gray-400 hover:text-gray-600">✕</button>
    </div>
    <dl class="space-y-2 text-sm">
      <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd id="viewName" class="font-semibold text-ink dark:text-white"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd id="viewEmail"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd id="viewPhone"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Branch</dt><dd id="viewBranch"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd id="viewStatus"></dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Date Created</dt><dd id="viewCreated"></dd></div>
    </dl>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
  $('#managersTable').DataTable({ responsive: true, pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        lengthChange: true,
        order: [[4, 'asc']], columnDefs: [{ orderable: false, targets: -1 }] });

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
    $('#mPhoneDigits').val(formatPhoneDisplay(d));
    $('#mPhone').val(d ? '+63' + d : '');
  }
  $('#mPhoneDigits').on('input', function () {
    var digits = this.value.replace(/\D/g, '').slice(0, 10);
    this.value = formatPhoneDisplay(digits);
    $('#mPhone').val(digits ? '+63' + digits : '');
  });

  // ── Add ─────────────────────────────
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

  // ── Edit ─────────────────────────────
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

  // ── Add / Edit submit ─────────────────────────────
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

  // ── View (read-only) ─────────────────────────────
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

  // ── Change Branch ─────────────────────────────
  $(document).on('click', '.js-change', function () {
    const id = this.dataset.id;
    Swal.fire({
      title: 'Change Branch', text: `Assign ${this.dataset.name} to a different branch.`,
      input: 'select',
      inputOptions: { <?php foreach ($branches as $branch): if ($branch['is_active']): ?>'<?= (int) $branch['id'] ?>':'<?= htmlspecialchars(addslashes($branch['name'])) ?>',<?php endif; endforeach; ?> },
      showCancelButton: true, confirmButtonText: 'Save', confirmButtonColor: '#0d9488',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.post(`/admin/branch-managers/${id}/branch`, { branch_id: result.value })
        .done(res => { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
        .fail(x => toast(false, x.responseJSON?.message || 'Unable to update assignment.'));
    });
  });

  // ── Activate / Deactivate ─────────────────────────────
  $(document).on('click', '.js-status', function () {
    const id = this.dataset.id, status = this.dataset.status, name = this.dataset.name;
    const verb = status === 'active' ? 'activate' : 'deactivate';
    Swal.fire({
      title: 'Are you sure?', text: `You are about to ${verb} ${name}'s Branch Manager account.`,
      icon: 'warning', showCancelButton: true,
      confirmButtonText: `Yes, ${verb}`, cancelButtonText: 'Cancel', confirmButtonColor: '#0d9488',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.post(`/admin/branch-managers/${id}/status`, { status: status })
        .done(res => { toast(res.success, res.message); if (res.success) setTimeout(() => location.reload(), 900); })
        .fail(() => toast(false, 'Something went wrong. Please try again.'));
    });
  });

  // ── Archive ─────────────────────────────
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

  // ── Reset Password ─────────────────────────────
  $(document).on('click', '.js-reset-pw', function () {
    const id = this.dataset.id;
    Swal.fire({
      title: 'Reset Password', input: 'password',
      inputLabel: 'New password (min. 8 characters)', inputAttributes: { minlength: 8 },
      showCancelButton: true, confirmButtonText: 'Reset', confirmButtonColor: '#0d9488',
    }).then(function (result) {
      if (!result.isConfirmed || !result.value) return;
      $.post(`/admin/branch-managers/${id}/reset-password`, { password: result.value })
        .done(res => toast(res.success, res.message))
        .fail(x => toast(false, x.responseJSON?.message || 'Something went wrong. Please try again.'));
    });
  });
});
</script>