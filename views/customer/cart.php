<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="mb-6">
      <h1 class="font-display font-semibold text-2xl text-gray-900">My cart</h1>
      <p class="text-sm text-gray-500">Review your items before checking out.</p>
    </div>

    <?php if (empty($items)): ?>
      <div class="bg-white border border-gray-200 rounded-lg p-10 text-center">
        <p class="text-gray-500 text-sm mb-4">Your cart is empty.</p>
        <a href="/shop" class="text-teal font-semibold text-sm hover:underline">Browse products →</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-3 gap-6">

        <div class="col-span-2 bg-white border border-gray-200 rounded-lg divide-y divide-gray-100">
          <?php foreach ($items as $item): ?>
            <?php $product = $item['product']; ?>
            <div class="flex items-center gap-4 px-5 py-4">
              <div class="w-16 h-16 bg-gray-100 rounded-md overflow-hidden flex items-center justify-center shrink-0">
                <?php if (!empty($product['image_url'])): ?>
                  <img src="/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                  <span class="text-gray-300 text-xs">No image</span>
                <?php endif; ?>
              </div>

              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($product['name']) ?></p>
                <p class="text-xs text-gray-400">by <?= htmlspecialchars($product['seller_name']) ?></p>
                <?php if (!empty($item['variant'])): ?><p class="text-xs text-gray-500 mt-1">Size: <?= htmlspecialchars($item['variant']['size']) ?><?= $item['variant']['color'] !== 'N/A' ? ' &middot; Color: ' . htmlspecialchars($item['variant']['color']) : '' ?></p><?php endif; ?>
                <p class="text-sm font-semibold text-teal mt-1">₱<?= number_format((float) $product['price'], 2) ?></p>
              </div>

              <form action="/cart/update" method="POST" class="cart-qty-form flex items-center gap-1">
                <input type="hidden" name="cart_key" value="<?= htmlspecialchars($item['cartKey']) ?>">
                <button
                  type="button"
                  class="qty-btn qty-minus w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-500 text-sm hover:bg-gray-50 disabled:opacity-40"
                >&minus;</button>
                <input
                  type="number"
                  name="quantity"
                  value="<?= (int) $item['quantity'] ?>"
                  min="1"
                  max="<?= (int) $item['available'] ?>"
                  class="qty-input w-12 rounded-lg border border-gray-200 px-1 py-1.5 text-sm text-center"
                  readonly
                >
                <button
                  type="button"
                  class="qty-btn qty-plus w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-500 text-sm hover:bg-gray-50 disabled:opacity-40"
                >&plus;</button>
              </form>

              <p class="text-sm font-semibold text-gray-900 w-20 text-right shrink-0">₱<?= number_format((float) $item['subtotal'], 2) ?></p>

              <form action="/cart/remove" method="POST">
                <input type="hidden" name="cart_key" value="<?= htmlspecialchars($item['cartKey']) ?>">
                <button type="submit" class="text-gray-300 hover:text-red-500 shrink-0">&times;</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-5 h-fit">
          <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-medium text-gray-700">Total</span>
            <span class="text-xl font-display font-semibold text-gray-900">₱<?= number_format((float) $total, 2) ?></span>
          </div>
          <a href="/checkout" class="block w-full text-center bg-ink text-white font-semibold text-sm rounded-lg py-3 hover:bg-gray-800">
            Proceed to checkout
          </a>
        </div>

      </div>
    <?php endif; ?>
  </main>

</div>

<script>
document.querySelectorAll('.cart-qty-form').forEach(function (form) {
  var input = form.querySelector('.qty-input');
  var minusBtn = form.querySelector('.qty-minus');
  var plusBtn = form.querySelector('.qty-plus');
  var min = parseInt(input.min, 10) || 1;
  var max = parseInt(input.max, 10) || Infinity;

  function syncDisabled() {
    var val = parseInt(input.value, 10);
    minusBtn.disabled = val <= min;
    plusBtn.disabled = val >= max;
  }

  function submitUpdate() {
    var formData = new FormData(form);
    var cartKey = formData.get('cart_key');

    fetch(form.action, { method: 'POST', body: formData })
      .then(function (res) { return res.text(); })
      .then(function (html) {
        var parsed = new DOMParser().parseFromString(html, 'text/html');

        // find the matching hidden cart_key input in the fresh HTML, then its row
        var matches = parsed.querySelectorAll('input[name="cart_key"]');
        var newInput = null;
        matches.forEach(function (el) {
          if (el.value === cartKey) newInput = el;
        });

        if (!newInput) {
          window.location.reload(); // item was removed server-side, fallback
          return;
        }

        var newRow = newInput.closest('.flex.items-center.gap-4.px-5.py-4');
        var newQtyInput = newRow.querySelector('.qty-input');
        var newSubtotalEl = newRow.querySelector('p.text-sm.font-semibold.text-gray-900.w-20.text-right.shrink-0');

        var currentRow = form.closest('.flex.items-center.gap-4.px-5.py-4');
        var currentSubtotalEl = currentRow.querySelector('p.text-sm.font-semibold.text-gray-900.w-20.text-right.shrink-0');

        input.value = newQtyInput.value;
        max = parseInt(newQtyInput.max, 10);
        currentSubtotalEl.textContent = newSubtotalEl.textContent;

        var newTotalEl = parsed.querySelector('.h-fit span.text-xl.font-display.font-semibold.text-gray-900');
        var currentTotalEl = document.querySelector('.h-fit span.text-xl.font-display.font-semibold.text-gray-900');
        if (newTotalEl && currentTotalEl) currentTotalEl.textContent = newTotalEl.textContent;

        syncDisabled();
      })
      .catch(function () {
        window.location.reload();
      });
  }

  minusBtn.addEventListener('click', function () {
    var val = parseInt(input.value, 10);
    if (val > min) {
      input.value = val - 1;
      syncDisabled();
      submitUpdate();
    }
  });

  plusBtn.addEventListener('click', function () {
    var val = parseInt(input.value, 10);
    if (val < max) {
      input.value = val + 1;
      syncDisabled();
      submitUpdate();
    }
  });

  syncDisabled();
});
</script>