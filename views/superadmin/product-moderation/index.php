<div class="flex min-h-screen bg-gray-50">
  <?php require __DIR__ . '/../../partials/sidebar.php'; ?>
  <main class="flex-1 px-8 py-8">
    <div class="mb-8">
      <h1 class="font-display font-semibold text-2xl text-gray-900">Product review queue</h1>
      <p class="text-sm text-gray-500">Review listings flagged by prohibited-keyword screening or new product images before they appear in the shop.</p>
    </div>
    <?php if (empty($queue)): ?>
      <div class="rounded-lg border border-gray-200 bg-white p-12 text-center text-gray-500">No flagged products are waiting for review.</div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($queue as $product): ?>
          <article class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex flex-col gap-5 lg:flex-row lg:justify-between">
              <div class="min-w-0">
                <div class="flex items-center gap-3">
                  <?php if (!empty($product['image_url'])): ?><img src="/<?= htmlspecialchars($product['image_url']) ?>" alt="" class="h-14 w-14 rounded-lg border border-gray-200 object-cover"><?php endif; ?>
                  <div><h2 class="font-semibold text-gray-900"><?= htmlspecialchars($product['name']) ?></h2><p class="text-sm text-gray-500">Seller: <?= htmlspecialchars($product['seller_name']) ?> (<?= htmlspecialchars($product['seller_email']) ?>)</p><?php if (($product['seller_status'] ?? '') === 'suspended'): ?><p class="mt-1 text-xs font-semibold text-red-600">Seller account is temporarily locked pending review.</p><?php endif; ?></div>
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
    <?php endif; ?>
  </main>
</div>

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