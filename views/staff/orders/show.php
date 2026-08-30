<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="max-w-2xl">
      <a href="/staff/orders" class="text-sm text-gray-400 hover:text-teal">&larr; Back to orders</a>

      <div class="flex items-center justify-between mt-2 mb-6">
        <h1 class="font-display font-semibold text-2xl text-gray-900">Order #<?= (int) $order['id'] ?></h1>
        <?php
          $statusStyles = [
              'completed' => 'bg-teal-light text-teal',
              'pending' => 'bg-amber/15 text-amber-700',
              'packed' => 'bg-blue-50 text-blue-700',
              'shipped' => 'bg-indigo-50 text-indigo-700',
              'cancelled' => 'bg-gray-100 text-gray-500',
              'refunded' => 'bg-red-100 text-red-600',
          ];
        ?>
        <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full <?= $statusStyles[$order['status']] ?? 'bg-gray-100 text-gray-500' ?>">
          <?= htmlspecialchars(ucfirst($order['status'])) ?>
        </span>
      </div>

      <?php if (!empty($error)): ?>
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-xs text-gray-500 mb-1">Customer</p>
            <p class="font-medium text-gray-900"><?= htmlspecialchars($order['linked_customer_name'] ?? $order['customer_name'] ?? 'Walk-in') ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500 mb-1">Payment method</p>
            <p class="font-medium text-gray-900"><?= htmlspecialchars(ucfirst($order['payment_method'])) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500 mb-1">Date</p>
            <p class="font-medium text-gray-900"><?= date('M j, Y g:ia', strtotime($order['created_at'])) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500 mb-1">Total</p>
            <p class="font-medium text-gray-900">₱<?= number_format((float) $order['total_amount'], 2) ?></p>
          </div>
          <?php if (!empty($order['shipping_address_text'])): ?>
            <div class="col-span-2">
              <p class="text-xs text-gray-500 mb-1">Delivery address</p>
              <p class="font-medium text-gray-900"><?= htmlspecialchars($order['shipping_recipient_name']) ?> · <?= htmlspecialchars($order['shipping_phone']) ?></p>
              <p class="text-gray-700"><?= nl2br(htmlspecialchars($order['shipping_address_text'])) ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
              <th class="px-5 py-3 font-semibold">Item</th>
              <th class="px-3 py-3 font-semibold text-right">Qty</th>
              <th class="px-3 py-3 font-semibold text-right">Unit price</th>
              <th class="px-5 py-3 font-semibold text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr class="border-b border-gray-100 last:border-0">
                <td class="px-5 py-3.5 font-medium text-gray-900">
                  <?= htmlspecialchars($item['product_name']) ?>
                  <?php if (!empty($item['variant_label'])): ?>
                    <p class="text-xs font-normal text-gray-500 mt-0.5"><?= htmlspecialchars($item['variant_label']) ?></p>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-3.5 text-right text-gray-700"><?= (int) $item['quantity'] ?></td>
                <td class="px-3 py-3.5 text-right text-gray-500">₱<?= number_format((float) $item['unit_price'], 2) ?></td>
                <td class="px-5 py-3.5 text-right font-medium text-gray-900">₱<?= number_format((float) $item['subtotal'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($profile['position'] !== 'order_staff'): ?>
        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-5 text-sm text-amber-800">
          <p class="font-semibold">View only</p>
          <p class="mt-1">Only this branch's order staff can update an order's fulfillment status. Your role (<?= htmlspecialchars(ucfirst(str_replace('_', ' ', $profile['position']))) ?>) has view access only.</p>
        </div>
      <?php elseif (!empty($nextStatuses)): ?>
        <form method="POST" action="/staff/orders/<?= (int) $order['id'] ?>/fulfillment" class="mt-6 bg-white border border-gray-200 rounded-lg p-5">
          <h2 class="font-display font-semibold text-gray-900">Update fulfillment</h2>
          <p class="text-xs text-gray-500 mt-1">Choose a courier when packing. TINDA will generate and retain one tracking number until delivery.</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
            <select name="status" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm">
              <?php foreach ($nextStatuses as $status): ?><option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></option><?php endforeach; ?>
            </select>
            <select name="courier" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm <?= !empty($order['courier']) ? 'cursor-not-allowed bg-gray-50 text-gray-500' : '' ?>" <?= !empty($order['courier']) ? 'disabled' : '' ?>>
              <option value="">Select courier<?= empty($order['courier']) ? ' (required for Packed)' : '' ?></option>
              <?php foreach (['J&T Express', 'LBC Express', 'Flash Express', 'Ninja Van', 'JRS Express', 'Other'] as $courier): ?><option value="<?= htmlspecialchars($courier) ?>" <?= ($order['courier'] ?? '') === $courier ? 'selected' : '' ?>><?= htmlspecialchars($courier) ?></option><?php endforeach; ?>
            </select>
            <input value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" placeholder="Auto-generated when packed" readonly class="cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-500">
          </div>
          <button type="submit" class="mt-3 rounded-lg bg-teal px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90">Save order status</button>
        </form>
      <?php endif; ?>
    </div>
  </main>

</div>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>lucide.createIcons();</script>