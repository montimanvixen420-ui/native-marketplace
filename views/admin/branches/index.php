<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<style>
  #branch-pin-map { height: 320px; }
  .branch-row-active { 
    border-color: #059669 !important; 
    background-color: rgba(5, 150, 105, 0.08) !important; 
  }
</style>

<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">
  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    
    <!-- Page Header -->
    <header class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400 mb-1">Store operations</p>
        <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white">Maps &amp; Branches</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Pin each store on the locator. Branch Managers and Staff are managed from Branch Managers.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="/admin/branches?branch=0" style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md hover:opacity-90 transition cursor-pointer">
          <i data-lucide="plus" class="w-4 h-4" style="color: #ffffff !important;"></i> Add Branch
        </a>
        <a href="/branches" target="_blank" class="text-sm font-semibold text-teal-600 dark:text-teal-400 hover:underline inline-flex items-center gap-1">
          Preview public map <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
        </a>
      </div>
    </header>

    <!-- Error Alert -->
    <?php if (!empty($_SESSION['branch_error'])): ?>
      <div class="mb-5 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/40 px-4 py-3 text-sm text-red-700 dark:text-red-300 flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0"></i>
        <span><?= htmlspecialchars($_SESSION['branch_error']); unset($_SESSION['branch_error']); ?></span>
      </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[.85fr_1.4fr]">
      
      <!-- Branch List Section -->
      <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5 shadow-sm h-fit">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pb-3">
          <h2 class="font-display font-semibold text-base text-gray-900 dark:text-white">Your branches</h2>
          <span class="rounded-full bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800 px-2.5 py-0.5 text-xs font-bold text-teal-700 dark:text-teal-300">
            <?= count($branches) ?>
          </span>
        </div>

        <div class="space-y-2.5">
          <?php foreach ($branches as $branch): ?>
            <a href="/admin/branches?branch=<?= (int)$branch['id'] ?>" class="block rounded-xl border p-3.5 transition hover:border-teal-500 dark:hover:border-teal-400 <?= $selectedBranch && $selectedBranch['id'] == $branch['id'] ? 'branch-row-active' : 'border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900' ?> <?= $branch['status'] === 'archived' ? 'opacity-60' : '' ?>">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white text-sm"><?= htmlspecialchars($branch['name']) ?></p>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($branch['address']) ?></p>
                </div>
                <?php 
                  $statusStyles = [
                    'active' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', 
                    'inactive' => 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-slate-700', 
                    'archived' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-300 border-rose-200 dark:border-rose-800'
                  ]; 
                  $statusLabels = ['active' => 'Live', 'inactive' => 'Hidden', 'archived' => 'Archived']; 
                ?>
                <span class="shrink-0 rounded-full border px-2.5 py-0.5 text-[10px] font-bold <?= $statusStyles[$branch['status']] ?? $statusStyles['inactive'] ?>">
                  <?= $statusLabels[$branch['status']] ?? 'Hidden' ?>
                </span>
              </div>
              <p class="mt-2 text-xs font-medium text-gray-400 dark:text-gray-500 flex items-center gap-1">
                <i data-lucide="users" class="w-3.5 h-3.5"></i> <?= (int)$branch['staff_count'] ?> staff assigned
              </p>
            </a>
          <?php endforeach; ?>

          <?php if (empty($branches)): ?>
            <p class="rounded-xl bg-gray-50 dark:bg-slate-900 p-4 text-center text-sm text-gray-500 dark:text-gray-400">
              Add your first physical location using the form.
            </p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Edit / Add Branch Form Section -->
      <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm">
        <div class="mb-5 border-b border-gray-100 dark:border-slate-700 pb-3">
          <p class="text-xs font-semibold uppercase tracking-wider text-teal-600 dark:text-teal-400">
            <?= $selectedBranch ? 'Edit branch' : 'New branch' ?>
          </p>
          <h2 class="font-display font-bold text-xl text-gray-900 dark:text-white mt-0.5">
            <?= $selectedBranch ? htmlspecialchars($selectedBranch['name']) : 'Add a branch' ?>
          </h2>
        </div>

        <form method="POST" action="<?= $selectedBranch ? '/admin/branches/' . (int)$selectedBranch['id'] . '/update' : '/admin/branches/store' ?>" class="space-y-4">
          
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Branch name</label>
              <input required name="name" value="<?= htmlspecialchars($selectedBranch['name'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. TINDA Makati">
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">City / municipality</label>
              <input name="city" value="<?= htmlspecialchars($selectedBranch['city'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Makati City">
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Street address</label>
            <input required name="address" value="<?= htmlspecialchars($selectedBranch['address'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Building, street, barangay">
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Phone</label>
              <input
                type="tel"
                name="phone"
                id="phoneInput"
                value="<?= htmlspecialchars($selectedBranch['phone'] ?? '+63') ?>"
                class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500"
                placeholder="+63 9XX XXX XXXX"
                maxlength="17"
                pattern="^\+63 9\d{2} \d{3} \d{4}$"
                title="Enter a valid PH mobile number (e.g. +63 917 123 4567)"
              >
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Opening hours</label>
              <input name="hours" value="<?= htmlspecialchars($selectedBranch['hours'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Mon–Sat, 9 AM–6 PM">
            </div>
          </div>

          <!-- Map Pin Picker -->
          <div>
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Map pin <span class="font-normal text-gray-400 dark:text-gray-500">(Click the map to move it)</span>
              </span>
              <span id="coordinates" class="font-mono text-xs text-teal-600 dark:text-teal-400 font-bold"></span>
            </div>
            <div id="branch-pin-map" class="overflow-hidden rounded-xl border border-gray-300 dark:border-slate-600 shadow-inner"></div>
            <input required type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($selectedBranch['latitude'] ?? '14.5995') ?>">
            <input required type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($selectedBranch['longitude'] ?? '120.9842') ?>">
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-wrap gap-3 pt-2">
            <button style="background-color: #059669 !important; color: #ffffff !important;" class="rounded-xl px-5 py-2.5 text-sm font-bold shadow-md hover:opacity-90 transition cursor-pointer">
              <?= $selectedBranch ? 'Save changes' : 'Add branch' ?>
            </button>
            <?php if ($selectedBranch && $selectedBranch['status'] !== 'archived'): ?>
              <button type="submit" form="branch-toggle" class="rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                <?= $selectedBranch['is_active'] ? 'Deactivate' : 'Activate' ?>
              </button>
              <button type="submit" form="branch-archive" class="rounded-xl border border-rose-300 dark:border-rose-800/80 bg-rose-50 dark:bg-rose-950/40 px-4 py-2.5 text-sm font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition">
                Archive branch
              </button>
            <?php elseif ($selectedBranch): ?>
              <button type="submit" form="branch-restore" class="rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                Restore branch
              </button>
            <?php endif; ?>
          </div>
        </form>

        <?php if ($selectedBranch): ?>
          <form id="branch-toggle" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/toggle"></form>
          <form id="branch-archive" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/archive" class="js-confirm-form" data-title="Archive this branch?" data-text="It will be hidden from the locator and can no longer receive a Branch Manager. Existing staff and orders stay on record. You can restore it later." data-confirm-text="Yes, archive it"></form>
          <form id="branch-restore" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/restore" class="js-confirm-form" data-title="Restore this branch?" data-text="It will come back as Inactive — activate it afterward to publish it again." data-icon="question" data-confirm-color="#059669" data-confirm-text="Yes, restore it"></form>
        <?php endif; ?>
      </section>

    </div>
  </main>
</div>

<!-- Leaflet Map Script -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
  // Phone Formatting Logic
  const phoneInput = document.getElementById('phoneInput');
  const PREFIX = '+63 ';

  function formatPhone(rawDigits) {
    let out = '';
    if (rawDigits.length > 0) out += rawDigits.slice(0, 3);
    if (rawDigits.length > 3) out += ' ' + rawDigits.slice(3, 6);
    if (rawDigits.length > 6) out += ' ' + rawDigits.slice(6, 10);
    return out;
  }

  function enforcePrefix() {
    let val = phoneInput.value;
    let digitsOnly = val.replace(/\D/g, '');

    if (digitsOnly.startsWith('63')) {
      digitsOnly = digitsOnly.slice(2);
    }

    digitsOnly = digitsOnly.slice(0, 10);
    phoneInput.value = PREFIX + formatPhone(digitsOnly);
  }

  if (phoneInput) {
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
  }

  // Leaflet Pin Map Setup
  const latInput = document.getElementById('latitude');
  const lngInput = document.getElementById('longitude');
  const coordinateLabel = document.getElementById('coordinates');

  const map = L.map('branch-pin-map').setView([Number(latInput.value), Number(lngInput.value)], 13);

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { 
    maxZoom: 19, 
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' 
  }).addTo(map);

  const marker = L.marker([Number(latInput.value), Number(lngInput.value)], { draggable: true }).addTo(map);

  function setPin(point) { 
    marker.setLatLng(point); 
    latInput.value = point.lat.toFixed(7); 
    lngInput.value = point.lng.toFixed(7); 
    coordinateLabel.textContent = latInput.value + ', ' + lngInput.value; 
  }

  setPin(marker.getLatLng()); 
  map.on('click', function(event) { setPin(event.latlng); }); 
  marker.on('dragend', function() { setPin(marker.getLatLng()); });

  if (typeof lucide !== 'undefined') lucide.createIcons();
</script>