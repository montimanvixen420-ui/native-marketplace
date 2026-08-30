<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
    <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>
    <main class="min-w-0 flex-1 px-5 py-7 sm:px-8"><div class="mb-7">
        <p class="eyebrow">Supplier catalog</p><h1>My supplies</h1>
        <p class="mt-1 text-sm text-gray-500">Add supplies and stock that sellers can request.</p>
    </div><?php if ($error): ?>
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    <section class="dashboard-panel mb-6">
        <h2 class="mb-4">Add supply</h2>
        <form method="POST" action="/supplier/inventory/store" class="grid gap-4 md:grid-cols-2">
            <input name="item_name" required placeholder="Supply name" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
            <input name="unit_price" required min="0" step="0.01" type="number" placeholder="Price per unit" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
            <input name="quantity_available" required min="0" type="number" placeholder="Available quantity" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm"><input name="unit" value="piece" required placeholder="Unit (piece, kg, box)" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm"><textarea name="description" rows="2" placeholder="Description (optional)" class="md:col-span-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm"></textarea>
            <button class="w-fit rounded-xl bg-brand px-4 py-2.5 text-sm font-semibold text-white">Add to catalog</button>
        </form>
    </section>
    <section class="dashboard-panel overflow-hidden p-0">
        <div class="border-b border-gray-100 px-6 py-5">
            <h2>Your listed supplies</h2></div><?php if (empty($items)): ?>
                <div class="p-10 text-center text-sm text-gray-500">No supplies yet. Add one above to make it available to sellers.</div>
                <?php else: ?><div class="divide-y divide-gray-100"><?php foreach ($items as $item): ?>
                    <form method="POST" action="/supplier/inventory/update" class="grid gap-3 p-5 md:grid-cols-6 md:items-end">
                        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                        <label class="md:col-span-2 text-xs">Supply
                            <input name="item_name" required value="<?= htmlspecialchars($item['item_name']) ?>" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs">Price
                            <input name="unit_price" min="0" step="0.01" type="number" required value="<?= htmlspecialchars($item['unit_price']) ?>" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs">Stock
                            <input name="quantity_available" min="0" type="number" required value="<?= (int)$item['quantity_available'] ?>" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs">Unit
                            <input name="unit" required value="<?= htmlspecialchars($item['unit']) ?>" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        </label>
                        <div>
                            <label class="flex items-center gap-2 text-xs">
                                <input type="checkbox" name="is_active" <?= $item['is_active'] ? 'checked' : '' ?>> Available</label>
                                <button class="mt-2 rounded-lg border border-brand px-3 py-2 text-sm font-semibold text-brand">Save</button>
                            </div>
                            <textarea name="description" rows="1" placeholder="Description" class="md:col-span-5 rounded-lg border border-gray-200 px-3 py-2 text-sm"><?= htmlspecialchars($item['description'] ?? '') ?></textarea><
                            /form><?php endforeach; ?>
                        </div><?php endif; ?>
                    </section>
                </main>
               </div>
