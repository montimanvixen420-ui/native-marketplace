<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>#branch-map { height: min(68vh, 650px); min-height: 390px; } .leaflet-popup-content { min-width: 190px; }</style>

<main class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div><p class="eyebrow text-brand">Store locator</p><h1 class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">Find a TINDA branch</h1><p class="mt-2 text-sm text-gray-500 dark:text-white/60">Choose a pin to view the branch address, contact details, and opening hours.</p></div>
    <a href="/" class="text-sm font-semibold text-brand hover:underline">← Back to TINDA</a>
  </div>
  <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-ink-2">
    <div id="branch-map" aria-label="Map of TINDA branches"></div>
  </section>
  <?php if (empty($branches)): ?><p class="mt-5 rounded-xl bg-brand-light px-4 py-3 text-sm text-brand">No branches have been published yet. Please check back soon.</p><?php endif; ?>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
  const branches = <?= json_encode($branches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const map = L.map('branch-map').setView([12.8797, 121.7740], 5);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
  const bounds = [];
  branches.forEach(function (branch) {
    const point = [Number(branch.latitude), Number(branch.longitude)]; bounds.push(point);
    const popup = document.createElement('div');
    const heading = document.createElement('strong'); heading.textContent = branch.name; popup.appendChild(heading);
    [['Seller', branch.seller_name], ['Address', branch.address], ['City', branch.city], ['Phone', branch.phone], ['Hours', branch.hours]].forEach(function (item) {
      if (!item[1]) return; const line = document.createElement('div'); line.className = 'mt-1 text-sm'; line.textContent = item[0] + ': ' + item[1]; popup.appendChild(line);
    });
    L.marker(point).addTo(map).bindPopup(popup);
  });
  if (bounds.length === 1) map.setView(bounds[0], 15); else if (bounds.length > 1) map.fitBounds(bounds, { padding: [42, 42], maxZoom: 15 });
</script>
