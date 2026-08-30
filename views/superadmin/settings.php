<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8 max-w-2xl">
    <div class="mb-6">
      <h1 class="font-display font-semibold text-2xl text-gray-900">System settings</h1>
      <p class="text-sm text-gray-500">Commission rate, payment methods, and shipping options for the whole platform.</p>
    </div>

    <?php if ($saved): ?>
      <div class="mb-4 text-sm text-teal bg-teal-light border border-teal/20 rounded-lg px-4 py-3">
        Settings saved successfully.
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-4 py-3">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/superadmin/settings/update" class="space-y-6">

      <!-- Commission -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="font-display font-semibold text-base text-gray-900 mb-1">Commission</h2>
        <p class="text-xs text-gray-500 mb-4">Percentage the platform keeps from every seller's sale.</p>

        <label class="block text-xs font-medium text-gray-600 mb-1.5">Commission rate (%)</label>
        <input
          type="number"
          name="commission_rate"
          value="<?= htmlspecialchars($settings['commission_rate']) ?>"
          step="0.01" min="0" max="100"
          class="w-full max-w-xs text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal"
          required
        >
      </div>

      <!-- Payment methods -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="font-display font-semibold text-base text-gray-900 mb-1">Payment methods</h2>
        <p class="text-xs text-gray-500 mb-4">Choose which payment options customers can use at checkout.</p>

        <div class="space-y-2">
          <?php foreach ($paymentOptions as $key => $label): ?>
            <label class="flex items-center gap-2 text-sm text-gray-700">
              <input
                type="checkbox"
                name="payment_methods[]"
                value="<?= htmlspecialchars($key) ?>"
                <?= in_array($key, $enabledMethods, true) ? 'checked' : '' ?>
                class="rounded border-gray-300 text-teal focus:ring-teal"
              >
              <?= htmlspecialchars($label) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Shipping -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="font-display font-semibold text-base text-gray-900 mb-1">Shipping</h2>
        <p class="text-xs text-gray-500 mb-4">Default shipping fee and optional free-shipping threshold.</p>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1.5">Shipping fee (₱)</label>
            <input
              type="number"
              name="shipping_fee"
              value="<?= htmlspecialchars($settings['shipping_fee']) ?>"
              step="0.01" min="0"
              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal"
              required
            >
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1.5">Free shipping over (₱, optional)</label>
            <input
              type="number"
              name="free_shipping_threshold"
              value="<?= htmlspecialchars($settings['free_shipping_threshold'] ?? '') ?>"
              step="0.01" min="0"
              placeholder="Leave blank to disable"
              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal"
            >
          </div>
        </div>
      </div>

      <!-- Prohibited items -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="font-display font-semibold text-base text-gray-900 mb-1">Prohibited items</h2>
        <p class="text-xs text-gray-500 mb-4">Manage the list of items sellers/suppliers are not allowed to sell. This list is shown to applicants during registration.</p>
        <a href="/superadmin/prohibited-items" class="inline-block text-sm font-medium px-4 py-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50">Manage prohibited items &rarr;</a>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="text-sm font-medium px-5 py-2.5 rounded-lg bg-ink text-white hover:bg-ink/90">Save settings</button>
      </div>

    </form>
  </main>

</div>