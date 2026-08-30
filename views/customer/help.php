<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">

  <?php if (($_SESSION['user_role'] ?? '') === 'customer'): ?>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <?php else: ?>
    <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <?php endif; ?>

  <main class="flex-1 px-8 py-8">
    <div class="mb-6 max-w-2xl">
      <h1 class="font-display font-semibold text-2xl text-ink dark:text-white">Help &amp; FAQ</h1>
      <p class="text-sm text-gray-500 dark:text-white/50">Helpful answers for using TINDA Marketplace.</p>
    </div>

    <div class="max-w-2xl space-y-3 mb-6">
      <?php
        $faqs = [
            ['q' => 'How do I place an order?', 'a' => 'Browse products, add an item to your cart, then follow the checkout steps to confirm payment and place your order.'],
            ['q' => 'What payment methods are accepted?', 'a' => 'Cash on delivery, GCash, card, and other methods depending on what the seller supports.'],
            ['q' => 'How do I track my order?', 'a' => 'Go to "My orders" in the sidebar. Each order shows its current status — Pending, Completed, Cancelled, or Refunded.'],
            ['q' => 'Can I cancel an order after placing it?', 'a' => 'Orders can only be cancelled while still "Pending" (before the seller starts fulfilling it). Contact the seller directly for cancellations.'],
            ['q' => 'What is the wishlist for?', 'a' => 'Save products you\'re interested in, with optional notes and a priority level, so you can find them again later.'],
            ['q' => 'How do I respond to a stock request?', 'a' => 'Suppliers can open Incoming requests from the sidebar, then mark each request as fulfilled or rejected.'],
        ];
      ?>
      <?php foreach ($faqs as $i => $faq): ?>
        <details class="bg-white dark:bg-ink-2 border border-gray-100 dark:border-white/10 rounded-2xl px-5 py-4 shadow-sm group">
          <summary class="text-sm font-medium text-ink dark:text-white cursor-pointer list-none flex items-center justify-between">
            <?= htmlspecialchars($faq['q']) ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform shrink-0"><path d="M6 9l6 6 6-6"/></svg>
          </summary>
          <p class="text-sm text-gray-500 dark:text-white/50 mt-2 leading-relaxed"><?= htmlspecialchars($faq['a']) ?></p>
        </details>
      <?php endforeach; ?>
    </div>

    <div class="bg-brand-light dark:bg-brand/15 rounded-2xl p-5 max-w-2xl flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-brand mb-0.5">Still need help?</p>
        <p class="text-xs text-brand/70">Send us a message and we'll get back to you.</p>
      </div>
      <a href="/feedback" class="shrink-0 bg-brand text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-brand-dark transition-colors">
        Contact us
      </a>
    </div>
  </main>

</div>
