<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>#branch-pin-map { height: 310px; } .branch-row-active { border-color:#2563eb; background:#eff6ff; }</style>

<div class="flex min-h-screen bg-gray-50 dark:bg-ink">
  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
  <main class="flex-1 px-5 py-7 md:px-8">
    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="eyebrow text-brand">Store operations</p><h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Maps &amp; Branches</h1>
    <p class="mt-2 text-sm text-gray-500 dark:text-white/60">Pin each store on the locator. Branch Managers and Staff are managed from Branch Managers.</p>
  </div>
  <div class="flex items-center gap-3">
  <a href="/admin/branches?branch=0" class="rounded-lg bg-brand px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-dark">+ Add Branch</a>
  <a href="/branches" target="_blank" class="text-sm font-semibold text-brand hover:underline">Preview public map ↗</a>
</div>
</header>
    <?php if (!empty($_SESSION['branch_error'])): ?><div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($_SESSION['branch_error']); unset($_SESSION['branch_error']); ?>
    </div>
    <?php endif; ?>
    <div class="grid gap-5 xl:grid-cols-[.85fr_1.4fr]">
      <section class="dashboard-panel h-fit">
        <div class="mb-4 flex items-center justify-between">
          <h2>Your branches</h2>
      <span class="rounded-full bg-brand-light px-2.5 py-1 text-xs font-bold text-brand"><?= count($branches) ?></span>
    </div>
        <div class="space-y-2">
          <?php foreach ($branches as $branch): ?><a href="/admin/branches?branch=<?= (int)$branch['id'] ?>" class="block rounded-xl border p-3 transition hover:border-brand <?= $selectedBranch && $selectedBranch['id'] == $branch['id'] ? 'branch-row-active' : 'border-gray-200 dark:border-white/10' ?> <?= $branch['status'] === 'archived' ? 'opacity-60' : '' ?>">
            <div class="flex items-start justify-between gap-3"><div><p class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($branch['name']) ?></p><p class="mt-1 text-xs text-gray-500 dark:text-white/60"><?= htmlspecialchars($branch['address']) ?></p>
          </div>
          <?php $statusStyles = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-500', 'archived' => 'bg-red-50 text-red-500']; $statusLabels = ['active' => 'Live', 'inactive' => 'Hidden', 'archived' => 'Archived']; ?>
          <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-bold <?= $statusStyles[$branch['status']] ?? $statusStyles['inactive'] ?>"><?= $statusLabels[$branch['status']] ?? 'Hidden' ?>
        </span>
        </div>
        <p class="mt-2 text-xs text-gray-400"><?= (int)$branch['staff_count'] ?> staff assigned</p>
      </a>
      <?php endforeach; ?>
          <?php if (empty($branches)): ?><p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500 dark:bg-white/5 dark:text-white/60">Add your first physical location using the form.</p><?php endif; ?>
        </div>
      </section>
      <section class="dashboard-panel"><div class="mb-5"><p class="eyebrow"><?= $selectedBranch ? 'Edit branch' : 'New branch' ?>
    </p>
    <h2><?= $selectedBranch ? htmlspecialchars($selectedBranch['name']) : 'Add a branch' ?>
    </h2>
  </div>
        <form method="POST" action="<?= $selectedBranch ? '/admin/branches/' . (int)$selectedBranch['id'] . '/update' : '/admin/branches/store' ?>" class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-2"><label class="field-label">Branch name<input required name="name" value="<?= htmlspecialchars($selectedBranch['name'] ?? '') ?>" class="field-input mt-2" placeholder="e.g. TINDA Makati">
        </label>
          <label class="field-label">City / municipality<input name="city" value="<?= htmlspecialchars($selectedBranch['city'] ?? '') ?>" class="field-input mt-2" placeholder="Makati City">
        </label>
        </div>
          <label class="field-label">Street address<input required name="address" value="<?= htmlspecialchars($selectedBranch['address'] ?? '') ?>" class="field-input mt-2" placeholder="Building, street, barangay">
        </label>
          <div class="grid gap-4 sm:grid-cols-2">
  
  <div class="grid gap-4 sm:grid-cols-1">
  <label class="field-label">Phone
    <input
      type="tel"
      name="phone"
      id="phoneInput"
      value="<?= htmlspecialchars($selectedBranch['phone'] ?? '+63') ?>"
      class="field-input mt-2 w-full"
      placeholder="+63 9XX XXX XXXX"
      maxlength="17"
      pattern="^\+63 9\d{2} \d{3} \d{4}$"
      title="Enter a valid PH mobile number (e.g. +63 917 123 4567)"
    >
  </label>
</div>

<script>
  const phoneInput = document.getElementById('phoneInput');
  const PREFIX = '+63 ';

  function formatPhone(rawDigits) {
    // rawDigits = digits lang pagkatapos ng 63 (e.g. "9171234567")
    let out = '';
    if (rawDigits.length > 0) out += rawDigits.slice(0, 3);
    if (rawDigits.length > 3) out += ' ' + rawDigits.slice(3, 6);
    if (rawDigits.length > 6) out += ' ' + rawDigits.slice(6, 10);
    return out;
  }

  function enforcePrefix() {
    let val = phoneInput.value;

    // tanggalin lahat ng bukod sa digits, ilagay ulit ang +63 sa una
    let digitsOnly = val.replace(/\D/g, '');

    // tanggalin ang "63" sa una kung nandun (mula sa +63)
    if (digitsOnly.startsWith('63')) {
      digitsOnly = digitsOnly.slice(2);
    }

    // max 10 digits pagkatapos ng 63 (9XXXXXXXXX)
    digitsOnly = digitsOnly.slice(0, 10);

    phoneInput.value = PREFIX + formatPhone(digitsOnly);
  }

  phoneInput.addEventListener('input', enforcePrefix);

  phoneInput.addEventListener('keydown', (e) => {
    const cursorPos = phoneInput.selectionStart;
    if (
      (e.key === 'Backspace' || e.key === 'Delete') &&
      cursorPos <= PREFIX.length &&
      phoneInput.selectionEnd <= PREFIX.length
    ) {
      e.preventDefault();
    }
  });

  phoneInput.addEventListener('click', () => {
    if (phoneInput.selectionStart < PREFIX.length) {
      phoneInput.setSelectionRange(PREFIX.length, PREFIX.length);
    }
  });

  phoneInput.addEventListener('focus', () => {
    if (phoneInput.value.length < PREFIX.length) {
      phoneInput.value = PREFIX;
    }
  });
</script>
</div>
          <label class="field-label">Opening hours<input name="hours" value="<?= htmlspecialchars($selectedBranch['hours'] ?? '') ?>" class="field-input mt-2" placeholder="Mon–Sat, 9 AM–6 PM"></label></div>
          <div><div class="mb-2 flex flex-wrap items-center justify-between gap-2"><span class="field-label !mb-0">Map pin <span class="font-normal text-gray-500">Click the map to move it.</span></span><span id="coordinates" class="font-mono text-xs text-gray-500"></span></div><div id="branch-pin-map" class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10"></div><input required type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($selectedBranch['latitude'] ?? '14.5995') ?>"><input required type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($selectedBranch['longitude'] ?? '120.9842') ?>"></div>
          <div class="flex flex-wrap gap-3">
            <button class="rounded-lg bg-brand px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-dark"><?= $selectedBranch ? 'Save changes' : 'Add branch' ?></button>
            <?php if ($selectedBranch && $selectedBranch['status'] !== 'archived'): ?>
              <button type="submit" form="branch-toggle" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50"><?= $selectedBranch['is_active'] ? 'Deactivate' : 'Activate' ?></button>
              <button type="submit" form="branch-archive" class="rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50">Archive branch</button>
            <?php elseif ($selectedBranch): ?>
              <button type="submit" form="branch-restore" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50">Restore branch</button>
            <?php endif; ?>
          </div>
        </form>
        <?php if ($selectedBranch): ?>
          <form id="branch-toggle" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/toggle"></form>
          <form id="branch-archive" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/archive" class="js-confirm-form" data-title="Archive this branch?" data-text="It will be hidden from the locator and can no longer receive a Branch Manager. Existing staff and orders stay on record. You can restore it later." data-confirm-text="Yes, archive it"></form>
          <form id="branch-restore" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/restore" class="js-confirm-form" data-title="Restore this branch?" data-text="It will come back as Inactive — activate it afterward to publish it again." data-icon="question" data-confirm-color="#2563eb" data-confirm-text="Yes, restore it"></form>
        <?php endif; ?>
        
  </main>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
  const latInput = document.getElementById('latitude'), lngInput = document.getElementById('longitude'), coordinateLabel = document.getElementById('coordinates');
  const map = L.map('branch-pin-map').setView([Number(latInput.value), Number(lngInput.value)], 13);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19, attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
  const marker = L.marker([Number(latInput.value), Number(lngInput.value)], {draggable:true}).addTo(map);
  function setPin(point) { marker.setLatLng(point); latInput.value = point.lat.toFixed(7); lngInput.value = point.lng.toFixed(7); coordinateLabel.textContent = latInput.value + ', ' + lngInput.value; }
  setPin(marker.getLatLng()); map.on('click', function(event) { setPin(event.latlng); }); marker.on('dragend', function() { setPin(marker.getLatLng()); });
</script>