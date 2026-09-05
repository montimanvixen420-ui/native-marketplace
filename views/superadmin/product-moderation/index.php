<div class="flex min-h-screen bg-gray-50">
  <?php require __DIR__ . '/../../partials/sidebar.php'; ?>
  <main class="flex-1 min-w-0 px-8 py-8">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Product review queue</h1>
        <p class="text-sm text-gray-500">Review listings flagged by prohibited-keyword screening or new product images before they appear in the shop.</p>
      </div>
      <?php if (!empty($queue)): ?>
      <div class="flex gap-2 text-xs">
        <span class="rounded-full bg-gray-900 px-3 py-1.5 font-semibold text-white"><?= count($queue) ?> pending</span>
        <span class="rounded-full bg-red-50 px-3 py-1.5 font-semibold text-red-700"><?= count(array_filter($queue, fn($p) => $p['flag_type'] === 'keyword')) ?> keyword</span>
        <span class="rounded-full bg-amber-50 px-3 py-1.5 font-semibold text-amber-700"><?= count(array_filter($queue, fn($p) => $p['flag_type'] === 'image')) ?> image</span>
        <?php
          $oldestHours = null;
          if (!empty($queue)) { $oldestHours = floor((time() - strtotime($queue[0]['flagged_at'])) / 3600); }
        ?>
        <?php if ($oldestHours !== null): ?>
          <span class="rounded-full bg-gray-100 px-3 py-1.5 font-semibold text-gray-600">Oldest: <?= $oldestHours < 24 ? $oldestHours . 'h' : floor($oldestHours / 24) . 'd' ?> waiting</span>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if (empty($queue)): ?>
      <div class="rounded-lg border border-gray-200 bg-white p-12 text-center text-gray-500">No flagged products are waiting for review.</div>
    <?php else: ?>
      <div class="rounded-lg border border-gray-200 bg-white p-5 mb-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="relative w-full max-w-xs">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="pm-search" placeholder="Search product or seller..." class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm focus:outline-none focus:border-blue-500">
          </div>
          <div class="flex items-center gap-2">
            <select id="pm-type-filter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
              <option value="">All flag types</option>
              <option value="keyword">Keyword</option>
              <option value="image">Image</option>
            </select>
            <select id="pm-perpage" class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-blue-500">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
            </select>
            <span class="text-sm text-gray-500">per page</span>
          </div>
        </div>
      </div>

      <div class="space-y-4" id="pm-list">
        <?php foreach ($queue as $product): ?>
          <?php
            $hoursWaiting = floor((time() - strtotime($product['flagged_at'])) / 3600);
            if ($hoursWaiting < 24) { $waitClass = 'bg-emerald-50 text-emerald-700'; $waitLabel = $hoursWaiting . 'h waiting'; }
            elseif ($hoursWaiting < 72) { $waitClass = 'bg-amber-50 text-amber-700'; $waitLabel = floor($hoursWaiting / 24) . 'd waiting'; }
            else { $waitClass = 'bg-red-50 text-red-700'; $waitLabel = floor($hoursWaiting / 24) . 'd waiting'; }
          ?>
          <article class="pm-card rounded-lg border border-gray-200 bg-white p-5"
                    data-type="<?= htmlspecialchars($product['flag_type']) ?>"
                    data-search="<?= htmlspecialchars(strtolower($product['name'] . ' ' . $product['seller_name'])) ?>">
            <div class="flex flex-col gap-5 lg:flex-row lg:justify-between">
              <div class="min-w-0">
                <div class="flex items-center gap-3">
                  <?php if (!empty($product['image_url'])): ?><img src="/<?= htmlspecialchars($product['image_url']) ?>" alt="" class="h-14 w-14 rounded-lg border border-gray-200 object-cover"><?php endif; ?>
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <h2 class="font-semibold text-gray-900"><?= htmlspecialchars($product['name']) ?></h2>
                      <span class="rounded-full px-2 py-0.5 text-[11px] font-bold <?= $waitClass ?>"><?= $waitLabel ?></span>
                    </div>
                    <p class="text-sm text-gray-500">Seller: <?= htmlspecialchars($product['seller_name']) ?> (<?= htmlspecialchars($product['seller_email']) ?>)</p>
                    <?php if (($product['seller_status'] ?? '') === 'suspended'): ?><p class="mt-1 text-xs font-semibold text-red-600">Seller account is temporarily locked pending review.</p><?php endif; ?>
                  </div>
                </div>
                <p class="mt-4 whitespace-pre-line text-sm text-gray-600"><?= htmlspecialchars($product['description'] ?: 'No description provided.') ?></p>
                <p class="mt-3 text-sm"><span class="font-medium <?= $product['flag_type'] === 'image' ? 'text-amber-700' : 'text-red-600' ?>"><?= $product['flag_type'] === 'image' ? 'Image review:' : 'Flagged keywords:' ?></span> <?= htmlspecialchars($product['matched_keywords']) ?></p>
              </div>
              <form method="POST" action="/superadmin/product-moderation/<?= (int) $product['flag_id'] ?>/review" class="moderation-form w-full shrink-0 lg:w-72">
                <label class="mb-1 block text-xs font-medium text-gray-600" for="note-<?= (int) $product['flag_id'] ?>">Review note (optional)</label>
                <textarea id="note-<?= (int) $product['flag_id'] ?>" name="review_note" rows="3" class="mb-3 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Reason for your decision"></textarea>
                <div class="flex gap-2">
                  <button type="button" class="decision-btn rounded-lg bg-teal px-3 py-2 text-sm font-medium text-white" data-decision="approved" data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>">Approve</button>
                  <button type="button" class="decision-btn rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600" data-decision="rejected" data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>">Reject</button>
                </div>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4 rounded-lg border border-gray-200 bg-white px-5 py-4">
        <div id="pm-info" class="text-xs font-medium text-gray-500">Showing 0 to 0 of 0 entries</div>
        <div id="pm-pagination" class="flex items-center gap-1"></div>
      </div>
    <?php endif; ?>
  </main>
</div>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
let pmCurrentPage = 1;

function pmUpdate(resetPage = false) {
  if (resetPage) pmCurrentPage = 1;

  const searchValue = (document.getElementById('pm-search')?.value || '').toLowerCase().trim();
  const typeValue = document.getElementById('pm-type-filter')?.value || '';
  const perPage = parseInt(document.getElementById('pm-perpage')?.value || '5');

  const allCards = Array.from(document.querySelectorAll('#pm-list .pm-card'));
  const matching = allCards.filter(card => {
    const matchesSearch = !searchValue || card.dataset.search.includes(searchValue);
    const matchesType = !typeValue || card.dataset.type === typeValue;
    return matchesSearch && matchesType;
  });

  const totalEntries = matching.length;
  const totalPages = Math.ceil(totalEntries / perPage) || 1;
  if (pmCurrentPage > totalPages) pmCurrentPage = totalPages;

  const startIndex = (pmCurrentPage - 1) * perPage;
  const endIndex = startIndex + perPage;

  allCards.forEach(c => c.style.display = 'none');
  matching.slice(startIndex, endIndex).forEach(c => c.style.display = '');

  const infoEl = document.getElementById('pm-info');
  if (infoEl) {
    infoEl.textContent = totalEntries === 0
      ? 'Showing 0 to 0 of 0 entries'
      : `Showing ${startIndex + 1} to ${Math.min(endIndex, totalEntries)} of ${totalEntries} entries`;
  }

  const pagEl = document.getElementById('pm-pagination');
  if (pagEl) {
    pagEl.innerHTML = '';
    const mkBtn = (label, disabled, onClick) => {
      const btn = document.createElement('button');
      btn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-gray-200 transition-colors ${disabled ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-100 text-gray-700'}`;
      btn.textContent = label;
      btn.disabled = disabled;
      btn.onclick = onClick;
      return btn;
    };
    pagEl.appendChild(mkBtn('«', pmCurrentPage === 1, () => { pmCurrentPage = 1; pmUpdate(); }));
    pagEl.appendChild(mkBtn('‹', pmCurrentPage === 1, () => { if (pmCurrentPage > 1) { pmCurrentPage--; pmUpdate(); } }));
    for (let p = 1; p <= totalPages; p++) {
      const isActive = p === pmCurrentPage;
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 text-xs font-bold rounded-lg transition-colors ${isActive ? 'bg-blue-600 text-white border border-blue-600' : 'border border-gray-200 hover:bg-gray-100 text-gray-700'}`;
      pageBtn.textContent = p;
      pageBtn.onclick = () => { pmCurrentPage = p; pmUpdate(); };
      pagEl.appendChild(pageBtn);
    }
    pagEl.appendChild(mkBtn('›', pmCurrentPage === totalPages || totalEntries === 0, () => { if (pmCurrentPage < totalPages) { pmCurrentPage++; pmUpdate(); } }));
    pagEl.appendChild(mkBtn('»', pmCurrentPage === totalPages || totalEntries === 0, () => { pmCurrentPage = totalPages; pmUpdate(); }));
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('pm-list')) {
    pmUpdate(true);
    document.getElementById('pm-search')?.addEventListener('keyup', () => pmUpdate(true));
    document.getElementById('pm-type-filter')?.addEventListener('change', () => pmUpdate(true));
    document.getElementById('pm-perpage')?.addEventListener('change', () => pmUpdate(true));
  }
  if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

<script>
(function () {
  function bindDecisionButtons() {
    document.querySelectorAll('.decision-btn').forEach(function (btn) {
      if (btn.dataset.bound) return;
      btn.dataset.bound = '1';

      btn.addEventListener('click', function () {
        var form = btn.closest('.moderation-form');
        var decision = btn.dataset.decision;
        var productName = btn.dataset.productName;
        var isApprove = decision === 'approved';

        Swal.fire({
          title: isApprove ? 'Approve this product?' : 'Reject this product?',
          text: isApprove
            ? '"' + productName + '" will become visible in the shop.'
            : '"' + productName + '" will be rejected and hidden from the shop.',
          icon: isApprove ? 'question' : 'warning',
          showCancelButton: true,
          confirmButtonText: isApprove ? 'Yes, approve' : 'Yes, reject',
          cancelButtonText: 'Cancel',
          confirmButtonColor: isApprove ? '#0d9488' : '#dc2626',
          cancelButtonColor: '#6b7280',
          reverseButtons: true,
        }).then(function (result) {
          if (!result.isConfirmed) return;

          var hiddenDecision = document.createElement('input');
          hiddenDecision.type = 'hidden';
          hiddenDecision.name = 'decision';
          hiddenDecision.value = decision;
          form.appendChild(hiddenDecision);
          form.submit();
        });
      });
    });
  }

  if (typeof Swal === 'undefined') {
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    s.onload = bindDecisionButtons;
    document.head.appendChild(s);
  } else {
    bindDecisionButtons();
  }
})();
</script>