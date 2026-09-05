<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<style>
  /* isolation: isolate traps Leaflet's own internal z-index (200-1000 for
     panes/controls) inside this container's stacking context, so it can
     never render above a lower z-index element outside it — like a modal. */
  .branch-pin-map-el { height: 320px; isolation: isolate; }
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
        <button type="button" id="openAddBranchModalBtn" style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md hover:opacity-90 transition cursor-pointer">
          <i data-lucide="plus" class="w-4 h-4" style="color: #ffffff !important;"></i> Add Branch
        </button>
        <a href="/branches" target="_blank" class="text-sm font-semibold text-teal-600 dark:text-teal-400 hover:underline inline-flex items-center gap-1">
          Preview public map <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
        </a>
      </div>
    </header>
<div class="flex flex-col gap-8">

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
              <?php if (!empty($branch['phone'])): ?>
                <p class="mt-1 text-xs font-medium text-gray-400 dark:text-gray-500 flex items-center gap-1">
                  <i data-lucide="phone" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($branch['phone']) ?>
                </p>
              <?php endif; ?>
              <?php if (!empty($branch['hours'])): ?>
                <p class="mt-1 text-xs font-medium text-gray-400 dark:text-gray-500 flex items-center gap-1">
                  <i data-lucide="clock" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($branch['hours']) ?>
                </p>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>

          <?php if (empty($branches)): ?>
            <p class="rounded-xl bg-gray-50 dark:bg-slate-900 p-4 text-center text-sm text-gray-500 dark:text-gray-400">
              Add your first physical location using the "Add Branch" button above.
            </p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Right Section: Empty state / View / Edit -->
      <?php if (!$selectedBranch): ?>

        <!-- Empty state -->
        <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-10 shadow-sm h-fit flex flex-col items-center justify-center text-center gap-3">
          <div class="w-12 h-12 rounded-full bg-teal-50 dark:bg-teal-950/60 flex items-center justify-center">
            <i data-lucide="map-pin" class="w-6 h-6 text-teal-600 dark:text-teal-400"></i>
          </div>
          <div>
            <p class="font-display font-semibold text-gray-900 dark:text-white">No branch selected</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-xs">Pumili ng branch sa listahan sa kaliwa, o pindutin ang "Add Branch" para gumawa ng bago.</p>
          </div>
        </section>

      <?php elseif ($mode === 'edit'): ?>

        <!-- Edit Branch Panel -->
        <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm">
          <div class="mb-5 border-b border-gray-100 dark:border-slate-700 pb-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-teal-600 dark:text-teal-400">Edit branch</p>
            <h2 class="font-display font-bold text-xl text-gray-900 dark:text-white mt-0.5"><?= htmlspecialchars($selectedBranch['name']) ?></h2>
          </div>

          <form method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/update" class="space-y-4">

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

            <!-- Map Pin Picker (interactive, unchanged logic) -->
            <div>
              <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                  Map pin <span class="font-normal text-gray-400 dark:text-gray-500">(Click the map to move it)</span>
                </span>
                <span id="coordinates" class="font-mono text-xs text-teal-600 dark:text-teal-400 font-bold"></span>
              </div>
              <div id="branch-pin-map" class="branch-pin-map-el overflow-hidden rounded-xl border border-gray-300 dark:border-slate-600 shadow-inner"></div>
              <input required type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($selectedBranch['latitude'] ?? '14.5995') ?>">
              <input required type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($selectedBranch['longitude'] ?? '120.9842') ?>">
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 pt-2">
              <button style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-bold shadow-md hover:opacity-90 transition cursor-pointer">
                Save changes
              </button>
              <a href="/admin/branches?branch=<?= (int)$selectedBranch['id'] ?>" class="inline-flex items-center rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                Cancel
              </a>
              <?php if ($selectedBranch['status'] !== 'archived'): ?>
                <button type="submit" form="branch-toggle" class="inline-flex items-center rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                  <i data-lucide="power" class="w-4 h-4 mr-1.5"></i> <?= $selectedBranch['is_active'] ? 'Deactivate' : 'Activate' ?>
                </button>
                <button type="submit" form="branch-archive" class="inline-flex items-center rounded-xl border border-rose-300 dark:border-rose-800/80 bg-rose-50 dark:bg-rose-950/40 px-4 py-2.5 text-sm font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition">
                  <i data-lucide="archive" class="w-4 h-4 mr-1.5"></i> Archive branch
                </button>
              <?php else: ?>
                <button type="submit" form="branch-restore" class="inline-flex items-center rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                  Restore branch
                </button>
              <?php endif; ?>
            </div>
          </form>

          <form id="branch-toggle" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/toggle"></form>
          <form id="branch-archive" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/archive" class="js-confirm-form" data-title="Archive this branch?" data-text="It will be hidden from the locator and can no longer receive a Branch Manager. Existing staff and orders stay on record. You can restore it later." data-confirm-text="Yes, archive it"></form>
          <form id="branch-restore" method="POST" action="/admin/branches/<?= (int)$selectedBranch['id'] ?>/restore" class="js-confirm-form" data-title="Restore this branch?" data-text="It will come back as Inactive — activate it afterward to publish it again." data-icon="question" data-confirm-color="#059669" data-confirm-text="Yes, restore it"></form>
        </section>

      <?php else: ?>

        <!-- View Branch Panel (read-only) -->
        <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm">
          <div class="mb-5 flex items-start justify-between border-b border-gray-100 dark:border-slate-700 pb-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-teal-600 dark:text-teal-400">View branch</p>
              <h2 class="font-display font-bold text-xl text-gray-900 dark:text-white mt-0.5"><?= htmlspecialchars($selectedBranch['name']) ?></h2>
            </div>
            <a href="/admin/branches?branch=<?= (int)$selectedBranch['id'] ?>&mode=edit" class="inline-flex items-center rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition shrink-0">
              Edit
            </a>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">City / municipality</p>
              <p class="text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($selectedBranch['city'] ?? '') ?: '—' ?></p>
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Phone</p>
              <p class="text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($selectedBranch['phone'] ?? '') ?: '—' ?></p>
            </div>
          </div>

          <div class="mt-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Street address</p>
            <p class="text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($selectedBranch['address'] ?? '') ?: '—' ?></p>
          </div>

          <div class="mt-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Opening hours</p>
            <p class="text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($selectedBranch['hours'] ?? '') ?: '—' ?></p>
          </div>

          <!-- Map pin location (non-interactive preview) -->
          <div class="mt-5">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Map pin location</span>
              <span class="font-mono text-xs text-teal-600 dark:text-teal-400 font-bold">
                <?= htmlspecialchars($selectedBranch['latitude'] ?? '') ?>, <?= htmlspecialchars($selectedBranch['longitude'] ?? '') ?>
              </span>
            </div>
            <div id="branch-pin-map-view" class="branch-pin-map-el overflow-hidden rounded-xl border border-gray-300 dark:border-slate-600 shadow-inner"
                 data-lat="<?= htmlspecialchars($selectedBranch['latitude'] ?? '14.5995') ?>"
                 data-lng="<?= htmlspecialchars($selectedBranch['longitude'] ?? '120.9842') ?>"></div>
          </div>

          <!-- Staff roster (read-only) -->
          <div class="mt-8">
            <div class="mb-3 flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pb-2">
              <h3 class="font-display font-semibold text-sm text-gray-900 dark:text-white">Staff assigned to this branch</h3>
              <a href="/admin/branch-managers" class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">Manage staff →</a>
            </div>

            <?php if (empty($branchStaff)): ?>
              <p class="rounded-xl bg-gray-50 dark:bg-slate-900 p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                No staff assigned to this branch yet.
              </p>
            <?php else: ?>
              <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mb-3">
                <select id="statusFilterStaff" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                  <option value="">All statuses</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="suspended">Suspended</option>
                </select>
                <div class="flex items-center gap-2 shrink-0">
                  <select id="staffEntriesSelect" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                  </select>
                  <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
                </div>
              </div>
              <table id="branchStaffTable" class="w-full text-sm">
                <thead>
                  <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
                    <th class="px-3 py-3 font-semibold">Pangalan</th>
                    <th class="px-3 py-3 font-semibold">Position</th>
                    <th class="px-3 py-3 font-semibold">Phone</th>
                    <th class="px-3 py-3 font-semibold text-center">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                  <?php
                    $staffStatusStyles = [
                      'active' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                      'inactive' => 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-slate-700',
                      'suspended' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                    ];
                    $positionLabels = Staff::POSITIONS ?? [];
                  ?>
                  <?php foreach ($branchStaff as $s): ?>
                    <tr data-status="<?= htmlspecialchars($s['status']) ?>">
                      <td class="px-3 py-3 font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars(trim($s['first_name'] . ' ' . $s['last_name'])) ?></td>
                      <td class="px-3 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($positionLabels[$s['position']] ?? ucwords(str_replace('_', ' ', $s['position']))) ?></td>
                      <td class="px-3 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($s['phone'] ?: '—') ?></td>
                      <td class="px-3 py-3 text-center">
                        <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[10px] font-bold <?= $staffStatusStyles[$s['status']] ?? $staffStatusStyles['inactive'] ?>">
                          <?= htmlspecialchars(ucfirst($s['status'])) ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                         </table>

              <!-- Footer & Pagination -->
              <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 mt-2 border-t border-gray-100 dark:border-slate-700/60">
                <div id="branch-staff-info" class="text-xs font-medium text-gray-500 dark:text-gray-400">Showing 0 to 0 of 0 entries</div>
                <div id="branch-staff-pagination" class="flex items-center gap-1"></div>
              </div>
            <?php endif; ?>
          </div>
        </section>

      <?php endif; ?>

    </div>
  </main>
</div>

<!-- Add Branch Modal -->
<div id="addBranchModal" class="fixed inset-0 z-[100] hidden">
  <div id="addBranchBackdrop" class="absolute inset-0 bg-black/50"></div>
  <div class="relative z-10 flex min-h-full items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-xl p-6 max-h-[90vh] overflow-y-auto">
      <div class="mb-5 flex items-start justify-between border-b border-gray-100 dark:border-slate-700 pb-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-teal-600 dark:text-teal-400">New branch</p>
          <h2 class="font-display font-bold text-xl text-gray-900 dark:text-white mt-0.5">Add a branch</h2>
        </div>
        <button type="button" id="closeAddBranchModalBtn" class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 dark:text-gray-500">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form method="POST" action="/admin/branches/store" class="space-y-4">

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Branch name</label>
            <input required name="name" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. TINDA Makati">
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">City / municipality</label>
            <input name="city" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Makati City">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Street address</label>
          <input required name="address" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Building, street, barangay">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Phone</label>
            <input
              type="tel"
              name="phone"
              id="phoneInput-add"
              value="+63"
              class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500"
              placeholder="+63 9XX XXX XXXX"
              maxlength="17"
              pattern="^\+63 9\d{2} \d{3} \d{4}$"
              title="Enter a valid PH mobile number (e.g. +63 917 123 4567)"
            >
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Opening hours</label>
            <input name="hours" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Mon–Sat, 9 AM–6 PM">
          </div>
        </div>

        <!-- Map Pin Picker (interactive, lazy-initialized on first modal open) -->
        <div>
          <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              Map pin <span class="font-normal text-gray-400 dark:text-gray-500">(Click the map to move it)</span>
            </span>
            <span id="coordinates-add" class="font-mono text-xs text-teal-600 dark:text-teal-400 font-bold"></span>
          </div>
          <div id="branch-pin-map-add" class="branch-pin-map-el overflow-hidden rounded-xl border border-gray-300 dark:border-slate-600 shadow-inner"></div>
          <input required type="hidden" name="latitude" id="latitude-add" value="14.5995">
          <input required type="hidden" name="longitude" id="longitude-add" value="120.9842">
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
          <button style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-bold shadow-md hover:opacity-90 transition cursor-pointer">
            Add branch
          </button>
          <button type="button" id="cancelAddBranchBtn" class="inline-flex items-center rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Leaflet Map Script -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<?php if ($selectedBranch && $mode !== 'edit' && !empty($branchStaff)): ?>
<!-- Staff table needs jQuery/DataTables loaded here, before footer.php loads them again -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
<?php endif; ?>

<script>
  // ---- Reusable: PH phone number formatter (unchanged logic, now parameterized) ----
  function attachPhoneFormatter(phoneInput) {
    if (!phoneInput) return;
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
      if (digitsOnly.startsWith('63')) digitsOnly = digitsOnly.slice(2);
      digitsOnly = digitsOnly.slice(0, 10);
      phoneInput.value = PREFIX + formatPhone(digitsOnly);
    }

    phoneInput.addEventListener('input', enforcePrefix);
    phoneInput.addEventListener('keydown', (e) => {
      const cursorPos = phoneInput.selectionStart;
      if ((e.key === 'Backspace' || e.key === 'Delete') && cursorPos <= PREFIX.length && phoneInput.selectionEnd <= PREFIX.length) {
        e.preventDefault();
      }
    });
    phoneInput.addEventListener('click', () => {
      if (phoneInput.selectionStart < PREFIX.length) phoneInput.setSelectionRange(PREFIX.length, PREFIX.length);
    });
    phoneInput.addEventListener('focus', () => {
      if (phoneInput.value.length < PREFIX.length) phoneInput.value = PREFIX;
    });
  }

  // ---- Reusable: Leaflet pin map (unchanged tile/marker logic, now parameterized) ----
  // interactive = true  -> click-to-move + draggable marker + writes into latInput/lngInput/coordLabel (edit panel, add modal)
  // interactive = false -> static preview only, no click/drag (view panel)
  function initBranchMap(containerId, lat, lng, options) {
    options = options || {};
    const map = L.map(containerId, {
      zoomControl: !!options.interactive,
      dragging: !!options.interactive,
      scrollWheelZoom: !!options.interactive,
      doubleClickZoom: !!options.interactive
    }).setView([lat, lng], 15);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    const marker = L.marker([lat, lng], { draggable: !!options.interactive }).addTo(map);

    if (options.interactive && options.latInput && options.lngInput) {
      function setPin(point) {
        marker.setLatLng(point);
        options.latInput.value = point.lat.toFixed(7);
        options.lngInput.value = point.lng.toFixed(7);
        if (options.coordLabel) options.coordLabel.textContent = options.latInput.value + ', ' + options.lngInput.value;
      }
      setPin(marker.getLatLng());
      map.on('click', (event) => setPin(event.latlng));
      marker.on('dragend', () => setPin(marker.getLatLng()));
    }

    return map;
  }

  <?php if ($selectedBranch && $mode === 'edit'): ?>
  // Edit panel: same ids/behavior as before, just moved into the reusable functions above
  attachPhoneFormatter(document.getElementById('phoneInput'));
  initBranchMap('branch-pin-map', Number(document.getElementById('latitude').value), Number(document.getElementById('longitude').value), {
    interactive: true,
    latInput: document.getElementById('latitude'),
    lngInput: document.getElementById('longitude'),
    coordLabel: document.getElementById('coordinates')
  });
  <?php endif; ?>

  <?php if ($selectedBranch && $mode !== 'edit'): ?>
  // View panel: static, non-interactive preview
  const viewMapEl = document.getElementById('branch-pin-map-view');
  if (viewMapEl) {
    initBranchMap('branch-pin-map-view', Number(viewMapEl.dataset.lat), Number(viewMapEl.dataset.lng), { interactive: false });
  }
  <?php endif; ?>

  <?php if ($selectedBranch && $mode !== 'edit' && !empty($branchStaff)): ?>
  $(document).ready(function () {
    // Staff table: manual init (not the generic data-datatable auto-init) so we can
    // wire a custom status filter + entries-per-page control, same pattern as stock-requests.
        const branchStaffDT = $('#branchStaffTable').DataTable({
      paging: true,
      pageLength: 5,
      lengthChange: false,
      searching: true,
      columnDefs: [{ orderable: false, targets: -1 }],
      layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
      drawCallback: function () {
        renderDtPillPagination(this.api(), 'branch-staff-pagination', 'branch-staff-info');
        if (typeof lucide !== 'undefined') lucide.createIcons();
      }
    });

    // Scoped by table id so this plugin never filters any other DataTable on the page.
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      if (settings.nTable.id !== 'branchStaffTable') return true;
      const status = $('#statusFilterStaff').val();
      if (status === '') return true;
      const row = branchStaffDT.row(dataIndex).node();
      return $(row).data('status') === status;
    });

    $('#statusFilterStaff').on('change', function () { branchStaffDT.draw(); });
    $('#staffEntriesSelect').on('change', function () { branchStaffDT.page.len(this.value).draw(); });
  });
  <?php endif; ?>

  // ---- Add Branch modal: open/close + lazy Leaflet init ----
  (function () {
    const modal = document.getElementById('addBranchModal');
    const openBtn = document.getElementById('openAddBranchModalBtn');
    const closeBtn = document.getElementById('closeAddBranchModalBtn');
    const cancelBtn = document.getElementById('cancelAddBranchBtn');
    const backdrop = document.getElementById('addBranchBackdrop');

    let addMap = null;
    let addMapInitialized = false;

    function openModal() {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';

      if (!addMapInitialized) {
        attachPhoneFormatter(document.getElementById('phoneInput-add'));
        addMap = initBranchMap('branch-pin-map-add', 14.5995, 120.9842, {
          interactive: true,
          latInput: document.getElementById('latitude-add'),
          lngInput: document.getElementById('longitude-add'),
          coordLabel: document.getElementById('coordinates-add')
        });
        addMapInitialized = true;
      } else if (addMap) {
        addMap.invalidateSize();
      }
    }

    function closeModal() {
      modal.classList.add('hidden');
      document.body.style.overflow = '';
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
  })();

  if (typeof lucide !== 'undefined') lucide.createIcons();
</script>