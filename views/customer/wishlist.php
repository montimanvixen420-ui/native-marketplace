<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="mb-6">
      <h1 class="font-display font-semibold text-2xl text-ink dark:text-white">My wishlist</h1>
      <p class="text-sm text-gray-500 dark:text-white/50">Items you're keeping an eye on.</p>
    </div>

    <?php if (empty($items)): ?>
      <div class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl p-10 text-center shadow-sm">
        <p class="text-gray-500 dark:text-white/50 text-sm mb-4">Your wishlist is empty.</p>
        <a href="/shop" class="text-brand font-semibold text-sm hover:underline">Browse products →</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 gap-4">
        <?php foreach ($items as $item): ?>
          <?php
            $priorityStyles = [
                'high' => 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400',
                'medium' => 'bg-brand-light dark:bg-brand/15 text-brand',
                'low' => 'bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-white/50',
            ];
            $style = $priorityStyles[$item['priority']] ?? $priorityStyles['medium'];
          ?>
          <div class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl p-4 shadow-sm">
            <div class="flex items-start gap-4">
              <div class="w-16 h-16 bg-surface dark:bg-white/5 rounded-xl overflow-hidden flex items-center justify-center shrink-0">
                <?php if (!empty($item['image_url'])): ?>
                  <img src="/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                  <span class="text-gray-300 dark:text-white/20 text-xs">No image</span>
                <?php endif; ?>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <p class="text-sm font-medium text-ink dark:text-white truncate"><?= htmlspecialchars($item['product_name']) ?></p>
                  <?php if ($item['product_status'] !== 'active' || (int) $item['stock'] <= 0): ?>
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/40">Unavailable</span>
                  <?php endif; ?>
                </div>
                <p class="text-xs text-gray-400 dark:text-white/30 mb-2">by <?= htmlspecialchars($item['seller_name']) ?></p>
                <p class="text-sm font-semibold text-brand mb-3">₱<?= number_format((float) $item['price'], 2) ?></p>

                <form action="/wishlist/update" method="POST" class="flex items-start gap-2">
                  <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                  <textarea name="notes" rows="1" placeholder="Add a note (e.g. size, color)..." class="flex-1 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-ink px-3 py-1.5 text-xs text-ink dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-brand resize-none"><?= htmlspecialchars($item['notes'] ?? '') ?></textarea>
                  <select name="priority" class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-ink px-2 py-1.5 text-xs text-ink dark:text-white focus:outline-none focus:border-brand">
                    <option value="low" <?= $item['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $item['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $item['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                  </select>
                  <button type="submit" class="text-xs font-medium text-brand hover:underline shrink-0 py-1.5">Save</button>
                </form>
              </div>

              <div class="flex flex-col items-end gap-2 shrink-0">
                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full <?= $style ?>"><?= ucfirst($item['priority']) ?> priority</span>
                <form action="/wishlist/remove" method="POST">
                  <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                  <button type="submit" class="text-gray-300 dark:text-white/20 hover:text-red-500 text-xs">Remove</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

</div>