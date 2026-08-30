
<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="mb-6">
      <h1 class="font-display font-semibold text-2xl text-gray-900">Checkout</h1>
      <p class="text-sm text-gray-500">Review your order and confirm payment.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="bg-red-50 text-red-700 border border-red-200 rounded-lg px-4 py-3 text-sm mb-6">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-3 gap-6">

      <div class="col-span-2 bg-white border border-gray-200 rounded-lg divide-y divide-gray-100">
        <?php foreach ($items as $item): ?>
          <?php
            $product = $item['product'];
            $availableStock = (int) ($item['available'] ?? 0);
          ?>
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
              <?php if (!empty($item['variant'])): ?>
                <p class="text-xs text-gray-500 mt-1">Size: <?= htmlspecialchars($item['variant']['size']) ?><?= $item['variant']['color'] !== 'N/A' ? ' &middot; Color: ' . htmlspecialchars($item['variant']['color']) : '' ?></p>
              <?php endif; ?>
              <p class="text-xs text-gray-400">by <?= htmlspecialchars($product['seller_name']) ?></p>
              <?php if ($isBuyNow): ?>
                <div class="mt-2 flex items-center gap-2">
                  <span class="text-xs text-gray-500">Quantity</span>
                  <div class="flex items-center overflow-hidden rounded-lg border border-gray-200">
                    <button type="button" class="checkout-quantity-button px-2.5 py-1 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40" data-direction="-1" aria-label="Decrease quantity">−</button>
                    <input id="checkout-quantity" form="checkout-form" type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= $availableStock ?>" inputmode="numeric" class="w-12 border-x border-gray-200 py-1 text-center text-sm focus:outline-none" aria-label="Quantity">
                    <button type="button" class="checkout-quantity-button px-2.5 py-1 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40" data-direction="1" aria-label="Increase quantity">+</button>
                  </div>
                  <span class="text-xs text-gray-400"><?= $availableStock ?> available</span>
                </div>
              <?php else: ?>
                <p class="text-xs text-gray-400">Qty <?= (int) $item['quantity'] ?></p>
              <?php endif; ?>
            </div>
            <p id="checkout-line-subtotal" class="text-sm font-semibold text-gray-900 shrink-0" data-unit-price="<?= htmlspecialchars((string) (float) $product['price']) ?>">₱<?= number_format((float) $item['subtotal'], 2) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <form id="checkout-form" action="/checkout/place" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 h-fit">
        <?php if ($isBuyNow): ?>
          <input type="hidden" name="product_id" value="<?= (int) $buyNowProductId ?>">
          <input type="hidden" name="variant_id" value="<?= (int) $buyNowVariantId ?>">
          <input type="hidden" name="branch_id" value="<?= (int) ($items[0]['branch_id'] ?? 0) ?>">
        <?php endif; ?>
        <div class="mb-5 border-b border-gray-100 pb-5">
          <div class="flex items-center justify-between gap-3"><label class="block text-xs font-medium text-gray-500">Shipping address</label><a href="/addresses" class="text-xs font-semibold text-teal hover:underline">Manage addresses</a></div>
          <?php if (empty($addresses)): ?>
            <div class="mt-2 rounded-lg border border-amber/30 bg-amber/10 px-3 py-3 text-xs text-amber-700">You need a shipping address before you can place this order. <a href="/addresses" class="font-semibold underline">Add one now</a>.</div>
          <?php else: ?>
            <select required name="shipping_address_id" class="mt-1.5 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-teal">
              <option value="">Select delivery address</option>
              <?php foreach ($addresses as $address): ?>
                <option value="<?= (int) $address['id'] ?>" <?= (int) ($selectedAddressId ?? 0) === (int) $address['id'] ? 'selected' : '' ?>><?= htmlspecialchars($address['recipient_name']) ?> · <?= htmlspecialchars($address['address_line1']) ?>, <?= htmlspecialchars($address['city']) ?><?= $address['is_default'] ? ' (Default)' : '' ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>

        

        <label class="block text-xs font-medium text-gray-500 mb-1.5">Payment method</label>
        <select name="payment_method" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-4 focus:outline-none focus:border-teal">
          <option value="cash">Cash on delivery</option>
          <option value="gcash">GCash</option>
          <option value="card">Card</option>
          <option value="paymongo">PayMongo (GCash, Card, Maya)</option>
          <option value="other">Other</option>
        </select>

        <div class="flex items-center justify-between mb-4">
          <span class="text-sm font-medium text-gray-700">Items subtotal</span>
          <span id="checkout-items-subtotal" class="text-xl font-display font-semibold text-gray-900">₱<?= number_format((float) $total, 2) ?></span>
        </div>

        <div class="border-t border-gray-100 pt-4 space-y-4 mb-4">
          <?php $grandTotal = 0.0; foreach ($summary as $sellerId => $seller): $grandTotal += $seller['total']; ?>
            <div>
              <p class="text-xs font-semibold text-gray-700 mb-2"><?= htmlspecialchars($seller['seller_name']) ?></p>
              <label class="block text-xs text-gray-500 mb-1">Voucher code</label>
              <input name="voucher_codes[<?= (int) $sellerId ?>]" value="<?= htmlspecialchars($voucherCodes[$sellerId] ?? '') ?>" placeholder="Optional" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
              <div class="mt-2 space-y-1 text-xs text-gray-500">
                <div class="flex justify-between"><span>Items</span><span id="checkout-seller-items-<?= (int) $sellerId ?>">PHP <?= number_format($seller['subtotal'], 2) ?></span></div>
                <div class="flex justify-between"><span>Shipping<?= $seller['shipping'] == 0 ? ' (free)' : '' ?></span><span>PHP <?= number_format($seller['shipping'], 2) ?></span></div>
                <?php if (($seller['voucher']['discount'] ?? 0) > 0): ?><div class="flex justify-between text-teal"><span>Voucher discount</span><span>-PHP <?= number_format($seller['voucher']['discount'] ?? 0, 2) ?></span></div><?php endif; ?>
                <?php if (!empty($seller['voucher']['code'])): ?><p class="<?= $seller['voucher']['valid'] ? 'text-teal' : 'text-red-600' ?>"><?= htmlspecialchars($seller['voucher']['valid'] ? 'Voucher will be applied when you place the order.' : $seller['voucher']['message']) ?></p><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="flex items-center justify-between mb-4">
          <span class="text-sm font-medium text-gray-700">Order total</span>
          <span id="checkout-order-total" class="text-xl font-display font-semibold text-gray-900" data-extra-total="<?= htmlspecialchars((string) ($grandTotal - $total)) ?>">PHP <?= number_format($grandTotal, 2) ?></span>
        </div>
        <p class="text-xs text-gray-400 mb-4">Shipping and voucher discounts are finalized when you place the order.</p>

                <button type="submit" <?= empty($addresses) ? 'disabled' : '' ?> class="w-full bg-ink text-white font-semibold text-sm rounded-lg py-3 hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50">
          Place order
        </button>
      </form>

    </div>
  </main>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Buy-now checkout keeps the submitted quantity, item subtotal, and preview
  // in sync. The server still rechecks live stock before creating the order.
  const quantityInput = document.getElementById('checkout-quantity');
  if (quantityInput) {
    const max = Number(quantityInput.max);
    const lineSubtotal = document.getElementById('checkout-line-subtotal');
    const itemsSubtotal = document.getElementById('checkout-items-subtotal');
    const orderTotal = document.getElementById('checkout-order-total');
    const sellerItems = document.querySelector('[id^="checkout-seller-items-"]');
    const unitPrice = Number(lineSubtotal.dataset.unitPrice);
    const formatMoney = (amount, prefix) => prefix + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const updateQuantity = (value) => {
      const quantity = Math.min(max, Math.max(1, Number.parseInt(value, 10) || 1));
      const subtotal = unitPrice * quantity;
      quantityInput.value = quantity;
      lineSubtotal.textContent = formatMoney(subtotal, '₱');
      itemsSubtotal.textContent = formatMoney(subtotal, '₱');
      if (sellerItems) sellerItems.textContent = formatMoney(subtotal, 'PHP ');
      orderTotal.textContent = formatMoney(subtotal + Number(orderTotal.dataset.extraTotal), 'PHP ');
      document.querySelector('[data-direction="-1"]').disabled = quantity <= 1;
      document.querySelector('[data-direction="1"]').disabled = quantity >= max;
    };
    document.querySelectorAll('.checkout-quantity-button').forEach((button) => {
      button.addEventListener('click', () => updateQuantity(Number(quantityInput.value) + Number(button.dataset.direction)));
    });
    quantityInput.addEventListener('change', () => updateQuantity(quantityInput.value));
    updateQuantity(quantityInput.value);
  }

  document.getElementById('checkout-form').addEventListener('submit', function (event) {
    if (!this.checkValidity() || this.dataset.confirmed === 'yes') return;
    event.preventDefault();
    Swal.fire({ title: 'Place this order?', text: 'Please confirm that you want to proceed to checkout.', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, place order', cancelButtonText: 'Cancel', confirmButtonColor: '#2563EB' }).then((result) => {
      if (result.isConfirmed) { this.dataset.confirmed = 'yes'; this.requestSubmit(); }
    });
  });
</script>
