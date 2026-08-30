<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="min-w-0 flex-1 px-5 py-7 sm:px-8">
    <header class="mb-7">
      <p class="text-xs font-semibold uppercase tracking-[.16em] text-brand">Submit a report</p>
      <h1 class="mt-1 font-display text-2xl font-bold text-ink dark:text-white">
        Report <?= htmlspecialchars(ucfirst($type)) ?>: <?= htmlspecialchars($target['label'] ?? 'this listing') ?>
      </h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-white/50">Our moderation team will review your report and follow up.</p>
    </header>

    <div class="dashboard-panel max-w-xl px-6 py-6">
      <?php if (!empty($error)): ?>
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-400">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/reports" class="space-y-5">
        <input type="hidden" name="target_type" value="<?= htmlspecialchars($type) ?>">
        <input type="hidden" name="target_id" value="<?= htmlspecialchars($target['id']) ?>">

        <div>
          <label for="reason" class="mb-1.5 block text-sm font-semibold text-ink dark:text-white">Reason</label>
          <select id="reason" name="reason" required
                  class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-brand focus:outline-none dark:border-white/10 dark:bg-ink-2 dark:text-white">
            <option value="" disabled selected>Select a reason</option>
            <?php foreach ($reasons as $reasonOption): ?>
              <option value="<?= htmlspecialchars($reasonOption) ?>"><?= htmlspecialchars($reasonOption) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="details" class="mb-1.5 block text-sm font-semibold text-ink dark:text-white">Details</label>
          <textarea id="details" name="details" rows="5" minlength="10" maxlength="1000" required
                    placeholder="Tell us what happened (10–1,000 characters)"
                    class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-brand focus:outline-none dark:border-white/10 dark:bg-ink-2 dark:text-white"></textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
          <button type="submit" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
            Submit report
          </button>
          <a href="/shop" class="text-sm font-semibold text-gray-500 hover:text-ink dark:text-white/50 dark:hover:text-white">Cancel</a>
        </div>
      </form>
    </div>
  </main>
</div>