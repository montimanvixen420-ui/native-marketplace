<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">

  <?php if (($_SESSION['user_role'] ?? '') === 'customer'): ?>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <?php else: ?>
    <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <?php endif; ?>

  <main class="flex-1 px-8 py-8">
    <div class="mb-6 max-w-lg">
      <h1 class="font-display font-semibold text-2xl text-ink dark:text-white">Feedback</h1>
      <p class="text-sm text-gray-500 dark:text-white/50">Tell us what's working, or what isn't.</p>
    </div>

    <?php if ($success): ?>
      <div class="bg-brand-light dark:bg-brand/15 text-brand rounded-2xl px-4 py-3 text-sm mb-6 max-w-lg">
        Thanks! Your feedback has been sent.
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20 rounded-2xl px-4 py-3 text-sm mb-6 max-w-lg">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="/feedback/submit" method="POST" class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl p-5 shadow-sm max-w-lg space-y-4">
      <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-white/40 mb-1.5">Subject</label>
        <input type="text" name="subject" placeholder="e.g. Suggestion, bug report, question..."
               class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-ink px-3.5 py-2.5 text-sm text-ink dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-brand" required>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-white/40 mb-1.5">Message</label>
        <textarea name="message" rows="5" placeholder="Tell us more..."
                  class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-ink px-3.5 py-2.5 text-sm text-ink dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-brand resize-none" required></textarea>
      </div>

      <button type="submit" class="w-full bg-brand text-white font-semibold text-sm rounded-xl py-3 hover:bg-brand-dark transition-colors">
        Send feedback
      </button>
    </form>
  </main>

</div>
