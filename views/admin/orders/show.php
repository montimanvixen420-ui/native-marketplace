<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="max-w-4xl mx-auto">
      
      <!-- Back Link & Action Bar -->
      <div class="flex items-center justify-between mb-4">
        <a href="/admin/orders" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to orders
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 shadow-sm transition-all cursor-pointer">
          <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Order
        </button>
      </div>

      <!-- Page Header with Status Badge -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <h1 class="font-display font-bold text-2xl text-gray-900 dark:text-white">Order #<?= (int) $order['id'] ?></h1>
          <?php 
            $status = strtolower($order['status']);
            $badgeStyle = "background-color: #6b7280; color: #ffffff;"; // Default Gray
            if ($status === 'completed') {
              $badgeStyle = "background-color: #059669; color: #ffffff;"; // Emerald Green
            } elseif ($status === 'pending') {
              $badgeStyle = "background-color: #fbbf24; color: #451a03;"; // Amber/Yellow
            } elseif ($status === 'cancelled') {
              $badgeStyle = "background-color: #be123c; color: #ffffff;"; // Rose Red
            } elseif ($status === 'shipped' || $status === 'packed') {
              $badgeStyle = "background-color: #2563eb; color: #ffffff;"; // Blue
            }
          ?>
          <span style="<?= $badgeStyle ?>" class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
            <?= htmlspecialchars(ucfirst($order['status'])) ?>
          </span>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Placed on <?= date('M j, Y • g:i a', strtotime($order['created_at'])) ?></p>
      </div>

      <!-- Error Alert -->
      <?php if (!empty($error)): ?>
        <div class="mb-5 flex items-center gap-2 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/40 px-4 py-3 text-sm text-red-700 dark:text-red-300">
          <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- Order Info Grid -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Order Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
          
          <!-- Customer Info -->
          <div class="flex items-start gap-3">
            <div class="p-2 bg-gray-100 dark:bg-slate-700 rounded-lg text-gray-600 dark:text-gray-300 mt-0.5">
              <i data-lucide="user" class="w-4 h-4"></i>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-400 dark:text-gray-400">Customer</p>
              <p class="font-semibold text-gray-900 dark:text-gray-100 mt-0.5"><?= htmlspecialchars($order['linked_customer_name'] ?? $order['customer_name'] ?? 'Walk-in customer') ?></p>
            </div>
          </div>

          <!-- Payment Info -->
          <div class="flex items-start gap-3">
            <div class="p-2 bg-gray-100 dark:bg-slate-700 rounded-lg text-gray-600 dark:text-gray-300 mt-0.5">
              <i data-lucide="credit-card" class="w-4 h-4"></i>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-400 dark:text-gray-400">Payment Method</p>
              <p class="font-semibold text-gray-900 dark:text-gray-100 mt-0.5"><?= htmlspecialchars(ucfirst($order['payment_method'])) ?></p>
            </div>
          </div>

          <!-- Delivery Address -->
          <?php if (!empty($order['shipping_address_text'])): ?>
            <div class="flex items-start gap-3 md:col-span-2 pt-3 border-t border-gray-100 dark:border-slate-700">
              <div class="p-2 bg-gray-100 dark:bg-slate-700 rounded-lg text-gray-600 dark:text-gray-300 mt-0.5">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-400 dark:text-gray-400">Delivery Address</p>
                <p class="font-semibold text-gray-900 dark:text-gray-100 mt-0.5">
                  <?= htmlspecialchars($order['shipping_recipient_name']) ?> 
                  <span class="font-normal text-gray-500 dark:text-gray-400">· <?= htmlspecialchars($order['shipping_phone']) ?></span>
                </p>
                <p class="text-gray-600 dark:text-gray-300 mt-0.5 leading-relaxed"><?= nl2br(htmlspecialchars($order['shipping_address_text'])) ?></p>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Items Table Card -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700">
          <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Ordered Items</h2>
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
              <th class="px-6 py-3 font-semibold">Item</th>
              <th class="px-4 py-3 font-semibold text-center">Qty</th>
              <th class="px-4 py-3 font-semibold text-right">Unit Price</th>
              <th class="px-6 py-3 font-semibold text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
            <?php foreach ($items as $item): ?>
              <tr>
                <td class="px-6 py-4">
                  <p class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['product_name']) ?></p>
                  <?php if (!empty($item['variant_label'])): ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><?= htmlspecialchars($item['variant_label']) ?></p>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-4 text-center text-gray-700 dark:text-gray-300 font-medium"><?= (int) $item['quantity'] ?></td>
                <td class="px-4 py-4 text-right text-gray-600 dark:text-gray-400">₱<?= number_format((float) $item['unit_price'], 2) ?></td>
                <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-gray-100">₱<?= number_format((float) $item['subtotal'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Total Calculation Summary Section -->
        <div class="bg-gray-50/50 dark:bg-slate-900/40 px-6 py-4 border-t border-gray-100 dark:border-slate-700 flex justify-end">
          <div class="w-full max-w-xs space-y-2 text-sm">
            <div class="flex justify-between text-gray-500 dark:text-gray-400">
              <span>Subtotal</span>
              <span>₱<?= number_format((float) $order['total_amount'], 2) ?></span>
            </div>
            <div class="flex justify-between text-base font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-slate-700">
              <span>Total Amount</span>
              <span>₱<?= number_format((float) $order['total_amount'], 2) ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Branch View-Only Notice -->
      <?php if (!empty($order['branch_id'])): ?>
        <div class="bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-xl p-4 flex items-start gap-3 text-sm text-blue-900 dark:text-blue-300 shadow-sm">
          <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"></i>
          <div>
            <p class="font-semibold">View-only mode</p>
            <p class="text-blue-700 dark:text-blue-300 text-xs mt-0.5 leading-relaxed">
              This order was placed for <strong><?= htmlspecialchars($order['branch_name'] ?? 'a specific branch') ?></strong>. Only that branch's order staff can update its fulfillment status.
            </p>
          </div>
        </div>

      <!-- Fulfillment Update Form -->
      <?php elseif (!empty($nextStatuses)): ?>
        <form method="POST" action="/admin/orders/<?= (int) $order['id'] ?>/fulfillment" class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm">
          <h2 class="font-display font-bold text-base text-gray-900 dark:text-white">Update Fulfillment Status</h2>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Choose a courier when packing. TINDA will generate and retain one tracking number until delivery.</p>
          
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
              <select name="status" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-slate-400">
                <?php foreach ($nextStatuses as $status): ?>
                  <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Courier</label>
              <select name="courier" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-slate-400 <?= !empty($order['courier']) ? 'cursor-not-allowed opacity-60' : '' ?>" <?= !empty($order['courier']) ? 'disabled' : '' ?>>
                <option value="">Select courier<?= empty($order['courier']) ? ' (required for Packed)' : '' ?></option>
                <?php foreach (['J&T Express', 'LBC Express', 'Flash Express', 'Ninja Van', 'JRS Express', 'Other'] as $courier): ?>
                  <option value="<?= htmlspecialchars($courier) ?>" <?= ($order['courier'] ?? '') === $courier ? 'selected' : '' ?>><?= htmlspecialchars($courier) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tracking Number</label>
              <input value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" placeholder="Auto-generated when packed" readonly class="w-full cursor-not-allowed rounded-lg border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50 px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
            </div>
          </div>

          <div class="mt-5 text-right">
            <!-- Explicit solid styling na guaranteed na makikita sa parehong Light at Dark mode -->
            <button type="submit" style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-bold shadow-md hover:opacity-90 active:scale-95 transition-all cursor-pointer">
              <i data-lucide="check" class="w-4 h-4" style="color: #ffffff !important;"></i> 
              <span style="color: #ffffff !important;">Save Order Status</span>
            </button>
          </div>
        </form>
      <?php endif; ?>

    </div>
  </main>

</div>

<!-- Lucide icons initialization -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
  lucide.createIcons();
</script>