<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 min-w-0 px-8 py-8">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="font-display font-semibold text-2xl text-ink dark:text-white">Feedback</h1>
        <p class="text-sm text-gray-500 dark:text-white/50">Messages submitted by customers.</p>
      </div>
      <?php if (!empty($feedbackList)): ?>
      <div class="flex gap-2 text-xs">
        <span class="rounded-full bg-gray-900 px-3 py-1.5 font-semibold text-white"><?= count($feedbackList) ?> total</span>
        <span class="rounded-full bg-blue-50 px-3 py-1.5 font-semibold text-blue-700"><?= count(array_filter($feedbackList, fn($f) => $f['status'] === 'new')) ?> new</span>
      </div>
      <?php endif; ?>
    </div>

    <?php if (empty($feedbackList)): ?>
      <div class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl p-10 text-center shadow-sm">
        <p class="text-gray-500 dark:text-white/50 text-sm">No feedback yet.</p>
      </div>
    <?php else: ?>
      <div class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl p-5 shadow-sm mb-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="relative w-full max-w-xs">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="fb-search" placeholder="Search subject, sender, message..." class="w-full rounded-lg border border-gray-200 dark:border-white/15 dark:bg-ink py-2 pl-9 pr-3 text-sm focus:outline-none focus:border-blue-500 dark:text-white">
          </div>
          <div class="flex items-center gap-2">
            <select id="fb-status-filter" class="text-sm border border-gray-200 dark:border-white/15 dark:bg-ink dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
              <option value="">All statuses</option>
              <option value="new">New</option>
              <option value="reviewed">Reviewed</option>
            </select>
            <select id="fb-perpage" class="text-sm border border-gray-200 dark:border-white/15 dark:bg-ink dark:text-white rounded-lg px-2 py-1.5 focus:outline-none focus:border-blue-500">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
            </select>
            <span class="text-sm text-gray-500 dark:text-white/50">per page</span>
          </div>
        </div>
      </div>

      <div class="space-y-3" id="fb-list">
        <?php foreach ($feedbackList as $fb): ?>
          <div class="fb-card bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl p-5 shadow-sm"
               data-status="<?= htmlspecialchars($fb['status']) ?>"
               data-search="<?= htmlspecialchars(strtolower($fb['subject'] . ' ' . ($fb['sender_name'] ?? '') . ' ' . $fb['message'])) ?>">
            <div class="flex items-start justify-between mb-2">
              <div>
                <p class="text-sm font-medium text-ink dark:text-white"><?= htmlspecialchars($fb['subject']) ?></p>
                <p class="text-xs text-gray-400 dark:text-white/30">
                  <?= htmlspecialchars($fb['sender_name'] ?? 'Unknown user') ?> ·
                  <?= date('M j, Y g:i A', strtotime($fb['created_at'])) ?>
                </p>
              </div>
              <?php if ($fb['status'] === 'new'): ?>
                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-brand-light dark:bg-brand/15 text-brand shrink-0">New</span>
              <?php else: ?>
                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/40 shrink-0">Reviewed</span>
              <?php endif; ?>
            </div>
            <p class="text-sm text-gray-600 dark:text-white/60 leading-relaxed mb-3"><?= nl2br(htmlspecialchars($fb['message'])) ?></p>
            <?php if ($fb['status'] === 'new'): ?>
              <form action="/superadmin/feedback/mark-reviewed" method="POST">
                <input type="hidden" name="id" value="<?= (int) $fb['id'] ?>">
                <button type="submit" class="text-xs font-medium text-brand hover:underline">Mark as reviewed</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4 rounded-2xl border border-gray-100 dark:border-white/10 bg-white dark:bg-ink-2 px-5 py-4 shadow-sm">
        <div id="fb-info" class="text-xs font-medium text-gray-500 dark:text-white/50">Showing 0 to 0 of 0 entries</div>
        <div id="fb-pagination" class="flex items-center gap-1"></div>
      </div>
    <?php endif; ?>
  </main>

</div>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
let fbCurrentPage = 1;

function fbUpdate(resetPage = false) {
  if (resetPage) fbCurrentPage = 1;

  const searchValue = (document.getElementById('fb-search')?.value || '').toLowerCase().trim();
  const statusValue = document.getElementById('fb-status-filter')?.value || '';
  const perPage = parseInt(document.getElementById('fb-perpage')?.value || '5');

  const allCards = Array.from(document.querySelectorAll('#fb-list .fb-card'));
  const matching = allCards.filter(card => {
    const matchesSearch = !searchValue || card.dataset.search.includes(searchValue);
    const matchesStatus = !statusValue || card.dataset.status === statusValue;
    return matchesSearch && matchesStatus;
  });

  const totalEntries = matching.length;
  const totalPages = Math.ceil(totalEntries / perPage) || 1;
  if (fbCurrentPage > totalPages) fbCurrentPage = totalPages;

  const startIndex = (fbCurrentPage - 1) * perPage;
  const endIndex = startIndex + perPage;

  allCards.forEach(c => c.style.display = 'none');
  matching.slice(startIndex, endIndex).forEach(c => c.style.display = '');

  const infoEl = document.getElementById('fb-info');
  if (infoEl) {
    infoEl.textContent = totalEntries === 0
      ? 'Showing 0 to 0 of 0 entries'
      : `Showing ${startIndex + 1} to ${Math.min(endIndex, totalEntries)} of ${totalEntries} entries`;
  }

  const pagEl = document.getElementById('fb-pagination');
  if (pagEl) {
    pagEl.innerHTML = '';
    const mkBtn = (label, disabled, onClick) => {
      const btn = document.createElement('button');
      btn.className = `px-2.5 py-1 text-xs font-semibold rounded-lg border border-gray-200 dark:border-white/15 transition-colors ${disabled ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-100 dark:hover:bg-white/10 text-gray-700 dark:text-white'}`;
      btn.textContent = label;
      btn.disabled = disabled;
      btn.onclick = onClick;
      return btn;
    };
    pagEl.appendChild(mkBtn('«', fbCurrentPage === 1, () => { fbCurrentPage = 1; fbUpdate(); }));
    pagEl.appendChild(mkBtn('‹', fbCurrentPage === 1, () => { if (fbCurrentPage > 1) { fbCurrentPage--; fbUpdate(); } }));
    for (let p = 1; p <= totalPages; p++) {
      const isActive = p === fbCurrentPage;
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 text-xs font-bold rounded-lg transition-colors ${isActive ? 'bg-blue-600 text-white border border-blue-600' : 'border border-gray-200 dark:border-white/15 hover:bg-gray-100 dark:hover:bg-white/10 text-gray-700 dark:text-white'}`;
      pageBtn.textContent = p;
      pageBtn.onclick = () => { fbCurrentPage = p; fbUpdate(); };
      pagEl.appendChild(pageBtn);
    }
    pagEl.appendChild(mkBtn('›', fbCurrentPage === totalPages || totalEntries === 0, () => { if (fbCurrentPage < totalPages) { fbCurrentPage++; fbUpdate(); } }));
    pagEl.appendChild(mkBtn('»', fbCurrentPage === totalPages || totalEntries === 0, () => { fbCurrentPage = totalPages; fbUpdate(); }));
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('fb-list')) {
    fbUpdate(true);
    document.getElementById('fb-search')?.addEventListener('keyup', () => fbUpdate(true));
    document.getElementById('fb-status-filter')?.addEventListener('change', () => fbUpdate(true));
    document.getElementById('fb-perpage')?.addEventListener('change', () => fbUpdate(true));
  }
  if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>