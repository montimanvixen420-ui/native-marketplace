<div class="flex min-h-screen bg-gray-50">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="flex-1 px-8 py-8"><div class="max-w-3xl">
    <h1 class="font-display text-2xl font-semibold text-gray-900">Shipping addresses</h1>
    <p class="mt-1 text-sm text-gray-500">Add a delivery address before checking out.</p>
    <?php if (!empty($error)): ?>
      <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="mt-5 rounded-lg border border-teal/30 bg-teal-light px-4 py-3 text-sm text-teal">
        <?= htmlspecialchars($success) ?>
      </div>
      <?php endif; ?>
    <?php if (!empty($addresses)): ?>
      <div class="mt-5 space-y-3"><?php foreach ($addresses as $address): ?>
        <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700">
          <p class="font-semibold text-gray-900">
            <?= htmlspecialchars($address['recipient_name']) ?> · <?= htmlspecialchars($address['phone']) ?>
            <?= $address['is_default'] ? ' <span class="ml-2 rounded bg-teal-light px-2 py-0.5 text-xs text-teal">Default</span>' : '' ?></p>
            <p class="mt-1"><?= htmlspecialchars($address['address_line1']) ?>
            <?= $address['address_line2'] ? ', ' . htmlspecialchars($address['address_line2']) : '' ?>, <?= htmlspecialchars($address['barangay']) ?>,
             <?= htmlspecialchars($address['city']) ?>,
              <?= htmlspecialchars($address['province']) ?>
               <?= htmlspecialchars($address['postal_code']) ?>
              </p>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
    <form method="POST" action="/addresses" class="mt-6 rounded-lg border border-gray-200 bg-white p-5">
      <h2 class="font-display text-lg font-semibold text-gray-900">Add shipping address</h2>
      <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <input required name="recipient_name" placeholder="Recipient name" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm">
        <input required name="phone" placeholder="Mobile number" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm">
        <input required name="address_line1" placeholder="House no. / street" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm sm:col-span-2">
        <input name="address_line2" placeholder="Building / unit (optional)" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm sm:col-span-2">
        <input required name="barangay" placeholder="Barangay" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm">
        <input required name="city" placeholder="City / municipality" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm">
        <input required name="province" placeholder="Province" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm">
        <input required name="postal_code" placeholder="Postal code" class="rounded-lg border border-gray-200 px-3 py-2.5 text-sm"></div>
        <button class="mt-4 rounded-lg bg-ink px-4 py-2.5 text-sm font-semibold text-white">Save address</button>
      </form>
  </div>
</main>
</div>
