<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="mb-6">
      <h1 class="font-display font-semibold text-2xl text-ink dark:text-white">Feedback</h1>
      <p class="text-sm text-gray-500 dark:text-white/50">Messages submitted by customers.</p>
    </div>

    <?php if (empty($feedbackList)): ?>
      <div class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl p-10 text-center shadow-sm">
        <p class="text-gray-500 dark:text-white/50 text-sm">No feedback yet.</p>
      </div>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($feedbackList as $fb): ?>
          <div class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl p-5 shadow-sm">
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
    <?php endif; ?>
  </main>

</div>