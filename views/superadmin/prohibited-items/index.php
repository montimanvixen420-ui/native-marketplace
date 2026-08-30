<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="mb-6">
      <a href="/superadmin/settings" class="text-xs text-teal font-medium hover:underline">&larr; Back to System settings</a>
    </div>

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Prohibited items</h1>
        <p class="text-sm text-gray-500">Items sellers and suppliers are not allowed to list. Shown to applicants during registration.</p>
      </div>
      <a href="/superadmin/prohibited-items/create" class="text-sm font-medium px-4 py-2.5 rounded-lg bg-ink text-white hover:bg-ink/90">+ Add item</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <table data-datatable class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
            <th class="px-5 py-3 font-medium">Item</th>
            <th class="px-5 py-3 font-medium">Description</th>
            <th class="px-5 py-3 font-medium text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($items)): ?>
            <tr>
              <td colspan="3" class="px-5 py-8 text-center text-gray-400">No prohibited items yet. Click "Add item" to start the list.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <tr>
                <td class="px-5 py-3 font-medium text-gray-900"><?= htmlspecialchars($item['item_name']) ?></td>
                <td class="px-5 py-3 text-gray-600"><?= htmlspecialchars($item['description'] ?? '') ?></td>
                <td class="px-5 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <a href="/superadmin/prohibited-items/<?= (int) $item['id'] ?>/edit" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Edit</a>
                    <form method="POST" action="/superadmin/prohibited-items/<?= (int) $item['id'] ?>/delete" data-title="Remove this item from the prohibited list?" data-confirm-text="Yes, remove it">
                      <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>

</div>