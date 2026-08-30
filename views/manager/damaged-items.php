<div class="flex min-h-screen bg-gray-50"><?php require __DIR__.'/../partials/admin-sidebar.php'; ?>
<main class="flex-1 px-5 py-7 md:px-8">
  <header class="mb-6"><p class="text-sm font-semibold text-amber-600">Branch Manager review</p><h1 class="mt-1 font-display text-2xl font-semibold text-gray-900">Damaged items</h1><p class="mt-1 text-sm text-gray-500">Inventory Staff reports appear here. Approval permanently deducts Branch Inventory and keeps an audit record.</p></header>
  <?php if(!empty($error)): ?><div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <?php if(!empty($success)): ?><div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"><?=htmlspecialchars($success)?></div><?php endif; ?>
  <div class="overflow-hidden rounded-lg border bg-white"><table data-datatable class="w-full text-sm"><thead><tr class="border-b text-left text-xs uppercase text-gray-500"><th class="p-3">Product</th><th class="p-3">Variant</th><th class="p-3">Qty</th><th class="p-3">Reported by</th><th class="p-3">Note</th><th class="p-3">Action</th></tr></thead><tbody>
  <?php foreach($reports as $report): ?><tr class="border-b"><td class="p-3 font-medium"><?=htmlspecialchars($report['product_name'])?></td><td class="p-3"><?=htmlspecialchars(trim($report['variant_size'].' '.$report['variant_color']) ?: '—')?></td><td class="p-3"><span class="rounded-full bg-amber-50 px-2 py-1 font-semibold text-amber-700"><?= (int)$report['quantity'] ?></span></td><td class="p-3"><?=htmlspecialchars($report['reporter_name'])?></td><td class="p-3 text-gray-500"><?=htmlspecialchars($report['note'] ?: '—')?></td><td class="p-3"><form method="post" action="/manager/damaged-items/<?= (int)$report['id'] ?>/approve" class="js-confirm-form" data-title="Confirm damaged item?" data-text="This permanently removes the reported quantity from Branch Inventory." data-confirm-text="Yes, record damage"><button type="submit" class="confirm-damage-btn inline-flex items-center gap-1 rounded px-3 py-1.5"><i data-lucide="triangle-alert" class="h-4 w-4"></i>Confirm damage</button></form></td></tr><?php endforeach; ?>
  </tbody></table><?php if(empty($reports)): ?><p class="p-8 text-center text-sm text-gray-500">No damaged-item reports are waiting for approval.</p><?php endif; ?></div>
</main></div>
<style>
  .confirm-damage-btn { background: #f59e0b; color: #fff; font-weight: 600; border: 1px solid #d97706; }
  .confirm-damage-btn:hover { background: #d97706; }
</style>
