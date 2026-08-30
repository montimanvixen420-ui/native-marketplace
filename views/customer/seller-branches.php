<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>#seller-branch-map { height: min(62vh, 570px); min-height: 360px; } .leaflet-popup-content { min-width: 190px; }</style>

<div class="flex min-h-screen bg-surface dark:bg-ink">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="min-w-0 flex-1 px-5 py-6 sm:px-8 lg:px-10"><div class="mx-auto max-w-5xl">
    <a href="/shop" class="text-sm font-semibold text-brand hover:underline">← Back to products</a>
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-[10px] font-bold uppercase tracking-[.18em] text-brand">Seller locations</p><h1 class="mt-1 font-display text-3xl font-extrabold text-ink dark:text-white"><?= htmlspecialchars($seller['name']) ?> branches</h1><p class="mt-2 text-sm text-gray-500 dark:text-white/60">Browse this seller’s published store locations, contact details, and opening hours.</p></div><span class="rounded-full bg-brand-light px-3 py-1.5 text-xs font-bold text-brand"><?= count($branches) ?> <?= count($branches) === 1 ? 'branch' : 'branches' ?></span></div>
    <?php if (empty($branches)): ?><div class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500 dark:border-white/15 dark:bg-ink-2 dark:text-white/60">This seller has not published any branch locations yet.</div><?php else: ?>
      <section class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-ink-2"><div id="seller-branch-map" aria-label="Map of seller branches"></div></section>
      <section class="mt-5 grid gap-3 sm:grid-cols-2"><?php foreach ($branches as $branch): ?><article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-ink-2"><h2 class="font-display text-lg font-bold text-ink dark:text-white"><?= htmlspecialchars($branch['name']) ?></h2><p class="mt-2 text-sm leading-6 text-gray-600 dark:text-white/70"><?= htmlspecialchars($branch['address']) ?><?= $branch['city'] ? ', ' . htmlspecialchars($branch['city']) : '' ?></p><?php if ($branch['phone']): ?><p class="mt-3 text-sm text-gray-500">Phone: <span class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($branch['phone']) ?></span></p><?php endif; ?><?php if ($branch['hours']): ?><p class="mt-1 text-sm text-gray-500">Hours: <span class="font-semibold text-ink dark:text-white"><?= htmlspecialchars($branch['hours']) ?></span></p><?php endif; ?></article><?php endforeach; ?></section>
    <?php endif; ?>
  </div></main>
</div>
<?php if (!empty($branches)): ?><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script><script>
const sellerBranches = <?= json_encode($branches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const map = L.map('seller-branch-map').setView([12.8797, 121.7740], 5);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
const bounds = []; sellerBranches.forEach(function (branch) { const point = [Number(branch.latitude), Number(branch.longitude)]; bounds.push(point); const popup = document.createElement('div'); const title = document.createElement('strong'); title.textContent = branch.name; popup.appendChild(title); [['Address', branch.address], ['City', branch.city], ['Phone', branch.phone], ['Hours', branch.hours]].forEach(function (item) { if (!item[1]) return; const line = document.createElement('div'); line.className = 'mt-1 text-sm'; line.textContent = item[0] + ': ' + item[1]; popup.appendChild(line); }); L.marker(point).addTo(map).bindPopup(popup); });
if (bounds.length === 1) map.setView(bounds[0], 15); else map.fitBounds(bounds, { padding: [42, 42], maxZoom: 15 });
</script><?php endif; ?>
