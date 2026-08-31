<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="min-w-0 flex-1 px-5 py-7 sm:px-8">

    <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[.16em] text-brand">Platform overview</p>
        <h1 class="mt-1 font-display text-2xl font-bold text-ink dark:text-white">Good day, <?= htmlspecialchars($name) ?></h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-white/50">Monitor users, applications, and platform activity.</p>
      </div>
      <div class="flex gap-2">
        <a href="/superadmin/reports" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-ink dark:border-white/10 dark:bg-ink-2 dark:text-white">
          <i data-lucide="bar-chart-3" class="h-4 w-4"></i>
          View reports
        </a>
      </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <div class="dashboard-stat">
        <div class="flex items-center gap-2">
          <i data-lucide="users" class="h-4 w-4 text-brand"></i>
          <span class="stat-label">Total users</span>
        </div>
        <b><?= (int) $stats['total'] ?></b>
        <small>All accounts</small>
      </div>

      <div class="dashboard-stat">
        <div class="flex items-center gap-2">
          <i data-lucide="store" class="h-4 w-4 text-brand"></i>
          <span class="stat-label">Sellers</span>
        </div>
        <b><?= (int) $stats['sellers'] ?></b>
        <small>Shop operators</small>
      </div>

      <div class="dashboard-stat">
        <div class="flex items-center gap-2">
          <i data-lucide="package" class="h-4 w-4 text-brand"></i>
          <span class="stat-label">Suppliers</span>
        </div>
        <b><?= (int) $stats['suppliers'] ?></b>
        <small>Inventory partners</small>
      </div>

      <div class="dashboard-stat">
        <div class="flex items-center gap-2">
          <i data-lucide="shopping-bag" class="h-4 w-4 text-brand"></i>
          <span class="stat-label">Customers</span>
        </div>
        <b><?= (int) $stats['customers'] ?></b>
        <small>Registered shoppers</small>
      </div>

      <a href="/superadmin/applications"
         class="dashboard-stat transition hover:-translate-y-0.5 hover:shadow-md <?= (int) $stats['pending'] === 0 ? 'opacity-60' : '' ?>">
        <div class="flex items-center gap-2">
          <i data-lucide="clock" class="h-4 w-4 text-brand"></i>
          <span class="stat-label">Pending approvals</span>
        </div>
        <b><?= (int) $stats['pending'] ?></b>
        <small class="<?= (int) $stats['pending'] > 0 ? 'text-brand font-semibold' : '' ?>">
          <?= (int) $stats['pending'] > 0 ? 'See applications below' : 'All caught up' ?>
        </small>
      </a>
    </section>

    <!-- Platform Revenue -->
    <section class="dashboard-panel mt-5 p-6">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 dark:border-white/10 pb-4">
        <div>
          <p class="eyebrow">Platform revenue</p>
          <h2 class="font-display text-xl font-bold text-ink dark:text-white">Gross Merchandise Value</h2>
        </div>
        <span class="rounded-full bg-brand-light dark:bg-brand/20 px-3 py-1 text-xs font-bold text-brand">Completed orders, all sellers</span>
      </div>

      <div class="mt-5 grid gap-4 sm:grid-cols-3">
        <div class="dashboard-stat">
          <div class="flex items-center gap-2">
            <i data-lucide="wallet" class="h-4 w-4 text-brand"></i>
            <span class="stat-label">Lifetime GMV</span>
          </div>
          <b>&#8369;<?= number_format((float) $salesSummary['lifetime_revenue'], 2) ?></b>
          <small><?= (int) $salesSummary['completed_orders'] ?> completed orders</small>
        </div>
        <div class="dashboard-stat">
          <div class="flex items-center gap-2">
            <i data-lucide="trending-up" class="h-4 w-4 text-brand"></i>
            <span class="stat-label">GMV (last 30 days)</span>
          </div>
          <b>&#8369;<?= number_format((float) $salesSummary['revenue_30_days'], 2) ?></b>
          <small><?= (int) $salesSummary['orders_30_days'] ?> orders</small>
        </div>
        <div class="dashboard-stat">
          <div class="flex items-center gap-2">
            <i data-lucide="calculator" class="h-4 w-4 text-brand"></i>
            <span class="stat-label">Avg. order value (30d)</span>
          </div>
          <b>&#8369;<?= (int) $salesSummary['orders_30_days'] > 0 ? number_format((float) $salesSummary['revenue_30_days'] / (int) $salesSummary['orders_30_days'], 2) : '0.00' ?></b>
          <small>Per completed order</small>
        </div>
      </div>

      <?php if (empty($dailySales)): ?>
        <p class="mt-6 py-8 text-center text-sm text-gray-500 dark:text-white/50">No completed orders in the last 14 days yet.</p>
      <?php else: ?>
        <div class="mt-6 relative h-52">
          <canvas id="platformRevenueChart" role="img" aria-label="Line chart of daily platform-wide completed sales revenue">
            <?php foreach ($dailySales as $day): ?><?= date('M j', strtotime($day['sale_date'])) ?>: &#8369;<?= number_format((float) $day['revenue'], 2) ?> (<?= (int) $day['orders'] ?> orders). <?php endforeach; ?>
          </canvas>
        </div>
      <?php endif; ?>

      <?php if (!empty($topSellers)): ?>
        <div class="mt-7 border-t border-gray-100 dark:border-white/10 pt-5">
          <p class="mb-3 text-sm font-semibold text-ink dark:text-white">Top sellers (last 30 days)</p>
          <div class="divide-y divide-gray-100 dark:divide-white/10">
            <?php foreach ($topSellers as $i => $seller): ?>
              <div class="flex items-center justify-between py-2.5 text-sm">
                <div class="flex items-center gap-2.5">
                  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-light dark:bg-brand/20 text-xs font-bold text-brand"><?= $i + 1 ?></span>
                  <span class="font-medium text-ink dark:text-white"><?= htmlspecialchars($seller['seller_name']) ?></span>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-ink dark:text-white">&#8369;<?= number_format((float) $seller['revenue'], 2) ?></p>
                  <p class="text-xs text-gray-500 dark:text-white/50"><?= (int) $seller['orders'] ?> orders</p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </section>

    <section class="dashboard-panel mt-5 overflow-hidden p-0">
      <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-white/10">
        <div>
          <p class="eyebrow">Applications</p>
          <h2>Pending approvals</h2>
        </div>
        <a href="/superadmin/applications" class="text-sm font-semibold text-brand">View all applications</a>
      </div>

      <?php if (empty($pendingApprovals)): ?>
        <div class="flex flex-col items-center gap-2 p-12 text-center text-sm text-gray-500">
          <i data-lucide="inbox" class="h-8 w-8 text-gray-300"></i>
          No pending approvals right now.
        </div>
      <?php else: ?>
        <div class="divide-y divide-gray-100 dark:divide-white/10">
          <?php foreach ($pendingApprovals as $applicant): ?>
            <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                 data-applicant-id="<?= (int) $applicant['id'] ?>">
              <div>
                <div class="flex items-center gap-2">
                  <p class="text-sm font-semibold text-ink dark:text-white"><?= htmlspecialchars($applicant['name']) ?></p>
                  <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-700 dark:bg-amber-400/10 dark:text-amber-400">
                    Pending
                  </span>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                  <?= $applicant['role'] === 'admin' ? 'Seller' : ucfirst($applicant['role']) ?> · <?= htmlspecialchars($applicant['email']) ?>
                  <?php if (!empty($applicant['created_at'])): ?>
                    · <span title="<?= htmlspecialchars($applicant['created_at']) ?>"><?= htmlspecialchars(time_ago($applicant['created_at'])) ?></span>
                  <?php endif; ?>
                </p>
              </div>
              <div class="flex items-center gap-2">
                <a href="/superadmin/applications/<?= (int) $applicant['id'] ?>"
                   class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 dark:border-white/10 dark:text-white">
                  <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                  View
                </a>
                <button type="button"
                        class="js-approve-btn inline-flex items-center gap-1 rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-white"
                        data-id="<?= (int) $applicant['id'] ?>"
                        data-name="<?= htmlspecialchars($applicant['name'], ENT_QUOTES) ?>">
                  <i data-lucide="check" class="h-3.5 w-3.5"></i>
                  Approve
                </button>
                <button type="button"
                        class="js-reject-btn inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 dark:border-white/10 dark:text-white"
                        data-id="<?= (int) $applicant['id'] ?>"
                        data-name="<?= htmlspecialchars($applicant['name'], ENT_QUOTES) ?>">
                  <i data-lucide="x" class="h-3.5 w-3.5"></i>
                  Reject
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.lucide) lucide.createIcons();

  function setButtonLoading(btn, loadingText) {
    btn.dataset.originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block h-3 w-3 animate-spin rounded-full border-2 border-current border-t-transparent"></span> ' + loadingText;
  }

  function resetButton(btn) {
    btn.disabled = false;
    if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
  }

  document.querySelectorAll('.js-approve-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = btn.dataset.id;
      const name = btn.dataset.name;

      Swal.fire({
        title: 'Approve this application?',
        html: 'This will grant <strong>' + name + '</strong> access as an approved account.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, approve',
        confirmButtonColor: '#16a34a',
        cancelButtonText: 'Cancel'
      }).then(function (result) {
        if (!result.isConfirmed) return;

        setButtonLoading(btn, 'Approving...');

        fetch('/superadmin/users/' + id + '/approve', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (res) { if (!res.ok) throw new Error('Request failed'); return res; })
          .then(function () {
            Swal.fire({ title: 'Approved', text: name + ' has been approved.', icon: 'success', timer: 1500, showConfirmButton: false })
              .then(function () { window.location.reload(); });
          })
          .catch(function () {
            resetButton(btn);
            Swal.fire('Something went wrong', 'Please try again.', 'error');
          });
      });
    });
  });

  document.querySelectorAll('.js-reject-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = btn.dataset.id;
      const name = btn.dataset.name;

      Swal.fire({
        title: 'Reject this application?',
        input: 'textarea',
        inputLabel: 'Reason for rejection',
        inputPlaceholder: 'Let ' + name + ' know why the application was rejected...',
        inputAttributes: { 'aria-label': 'Reason for rejection' },
        showCancelButton: true,
        confirmButtonText: 'Reject application',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Cancel',
        inputValidator: function (value) {
          if (!value || !value.trim()) return 'Please provide a reason before rejecting.';
        }
      }).then(function (result) {
        if (!result.isConfirmed) return;

        setButtonLoading(btn, 'Rejecting...');

        fetch('/superadmin/users/' + id + '/reject', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({ reason: result.value })
        })
          .then(function (res) { if (!res.ok) throw new Error('Request failed'); return res; })
          .then(function () {
            Swal.fire({ title: 'Rejected', text: name + ' has been notified.', icon: 'success', timer: 1500, showConfirmButton: false })
              .then(function () { window.location.reload(); });
          })
          .catch(function () {
            resetButton(btn);
            Swal.fire('Something went wrong', 'Please try again.', 'error');
          });
      });
    });
  });
});
</script>

<?php if (!empty($dailySales)): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
(function () {
  var daily = <?= json_encode($dailySales) ?>;
  var labels = daily.map(function (d) {
    return new Date(d.sale_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  });
  var revenue = daily.map(function (d) { return parseFloat(d.revenue); });
  var orders = daily.map(function (d) { return parseInt(d.orders, 10); });

  new Chart(document.getElementById('platformRevenueChart'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Platform revenue',
        data: revenue,
        borderColor: '#2563EB',
        backgroundColor: 'rgba(37,99,235,0.08)',
        fill: true,
        tension: 0.35,
        pointRadius: 3,
        pointBackgroundColor: '#2563EB',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (ctx) {
              var i = ctx.dataIndex;
              return '\u20b1' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 }) + '  (' + orders[i] + ' orders)';
            }
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#9CA3AF', font: { size: 11 } } },
        y: {
          grid: { color: '#F3F4F6' },
          ticks: { color: '#9CA3AF', font: { size: 11 }, callback: function (v) { return '\u20b1' + Number(v).toLocaleString('en-PH'); } }
        }
      }
    }
  });
})();
</script>
<?php endif; ?>