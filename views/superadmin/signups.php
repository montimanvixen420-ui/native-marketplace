<?php
// Work out chart scale. Guard against an all-zero series so we don't divide by zero.
$maxCount = max(array_column($signups, 'count'));
$chartMax = $maxCount > 0 ? $maxCount : 1;

$barWidth = 48;
$barGap = 24;
$chartHeight = 220;
$chartWidth = count($signups) * ($barWidth + $barGap) + $barGap;
?>
<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Platform Sign-ups</h1>
        <p class="text-sm text-gray-500">Sign-ups over time. Sales and seller analytics will appear here once orders are tracked.</p>
      </div>

      <div class="flex items-center gap-2">
  <a href="/superadmin/signups?range=6" class="text-xs font-medium px-3 py-1.5 rounded-lg <?= $range === 6 ? 'bg-ink text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">6 months</a>
  <a href="/superadmin/signups?range=12" class="text-xs font-medium px-3 py-1.5 rounded-lg <?= $range === 12 ? 'bg-ink text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">12 months</a>
</div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
      <h2 class="font-display font-semibold text-base text-gray-900 mb-1">New sign-ups per month</h2>
      <p class="text-xs text-gray-500 mb-6">All roles combined (superadmin, sellers, suppliers, customers).</p>

      <?php if ($maxCount === 0): ?>
        <div class="py-12 text-center text-sm text-gray-400">No sign-ups recorded in this period yet.</div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <svg
            viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight + 50 ?>"
            width="<?= $chartWidth ?>"
            height="<?= $chartHeight + 50 ?>"
            xmlns="http://www.w3.org/2000/svg"
          >
            <line
              x1="0" y1="<?= $chartHeight ?>"
              x2="<?= $chartWidth ?>" y2="<?= $chartHeight ?>"
              stroke="#E5E7EB" stroke-width="1"
            />

            <?php foreach ($signups as $i => $point): ?>
              <?php
                $barHeight = $point['count'] > 0
                    ? max(4, (int) round(($point['count'] / $chartMax) * ($chartHeight - 30)))
                    : 0;
                $x = $barGap + $i * ($barWidth + $barGap);
                $y = $chartHeight - $barHeight;
              ?>
              <text
                x="<?= $x + $barWidth / 2 ?>" y="<?= $y - 8 ?>"
                text-anchor="middle" font-size="12" fill="#12151A" font-family="Inter, sans-serif"
              ><?= $point['count'] ?></text>

              <rect
                x="<?= $x ?>" y="<?= $y ?>"
                width="<?= $barWidth ?>" height="<?= $barHeight ?>"
                rx="4" fill="#12A594"
              />

              <text
                x="<?= $x + $barWidth / 2 ?>" y="<?= $chartHeight + 22 ?>"
                text-anchor="middle" font-size="12" fill="#6B7280" font-family="Inter, sans-serif"
              ><?= htmlspecialchars($point['label']) ?></text>
            <?php endforeach; ?>
          </svg>
        </div>
      <?php endif; ?>
    </div>

    <div class="grid grid-cols-3 gap-4 mt-6">
      <div class="bg-white border border-gray-200 rounded-lg p-5">
        <p class="text-xs text-gray-500 mb-1">Total sign-ups (<?= $range ?> mo.)</p>
        <p class="text-2xl font-semibold text-gray-900"><?= array_sum(array_column($signups, 'count')) ?></p>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg p-5">
        <p class="text-xs text-gray-500 mb-1">Best month</p>
        <?php
          $best = $signups[0] ?? null;
          foreach ($signups as $point) {
              if ($best === null || $point['count'] > $best['count']) {
                  $best = $point;
              }
          }
        ?>
        <p class="text-2xl font-semibold text-gray-900"><?= $best ? (int) $best['count'] : 0 ?></p>
        <p class="text-xs text-gray-400"><?= $best ? htmlspecialchars($best['label']) : '—' ?></p>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg p-5">
        <p class="text-xs text-gray-500 mb-1">Average per month</p>
        <p class="text-2xl font-semibold text-gray-900">
          <?= count($signups) > 0 ? round(array_sum(array_column($signups, 'count')) / count($signups), 1) : 0 ?>
        </p>
      </div>
    </div>

    <div class="mt-6 bg-amber/5 border border-amber/30 rounded-lg p-5">
      <p class="text-sm font-medium text-gray-900 mb-1">Coming soon</p>
      <p class="text-xs text-gray-600">Sales totals, top sellers, and order analytics will show up here once your <code class="font-mono bg-white px-1 py-0.5 rounded border border-gray-200">orders</code>/<code class="font-mono bg-white px-1 py-0.5 rounded border border-gray-200">products</code> tables are in place.</p>
    </div>
  </main>

</div>