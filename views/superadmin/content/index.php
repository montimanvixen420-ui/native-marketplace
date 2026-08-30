<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/sidebar.php'; ?>
  <!-- TEST123 -->
  <main class="flex-1 px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="font-display font-semibold text-2xl text-gray-900">Content</h1>
        <p class="text-sm text-gray-500">Manage banners, announcements, and general site text.</p>
      </div>
      <a href="/superadmin/content/create" class="text-sm font-medium px-4 py-2.5 rounded-lg bg-ink text-white hover:bg-ink/90">+ New content</a>
    </div>

    <!-- Type filter -->
    <form method="GET" action="/superadmin/content" class="flex items-center gap-3 mb-5">
      <select name="type" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal">
        <option value="">All types</option>
        <option value="banner" <?= $typeFilter === 'banner' ? 'selected' : '' ?>>Banner</option>
        <option value="announcement" <?= $typeFilter === 'announcement' ? 'selected' : '' ?>>Announcement</option>
        <option value="site_text" <?= $typeFilter === 'site_text' ? 'selected' : '' ?>>Site text</option>
      </select>
      <button type="submit" class="text-xs font-medium px-4 py-2 rounded-lg bg-ink text-white hover:bg-ink/90">Filter</button>
      <?php if ($typeFilter !== ''): ?>
        <a href="/superadmin/content" class="text-xs text-gray-500 hover:underline">Clear</a>
      <?php endif; ?>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden p-5">
      <?php if (empty($items)): ?>
        <!-- Empty State (Nasa labas ng table para hindi mag-error ang DataTables) -->
        <div class="p-12 text-center text-gray-400">
          <p class="font-medium text-base text-gray-700 mb-1">No content found</p>
          <p class="text-sm">Click "New content" to add your first banner, announcement, or site text.</p>
        </div>
      <?php else: ?>
        <table data-datatable class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
              <th class="px-5 py-3 font-medium">Title</th>
              <th class="px-5 py-3 font-medium">Type</th>
              <th class="px-5 py-3 font-medium">Status</th>
              <th class="px-5 py-3 font-medium">Updated</th>
              <th class="px-5 py-3 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php
              $typeLabels = [
                  'banner' => 'Banner',
                  'announcement' => 'Announcement',
                  'site_text' => 'Site text',
              ];
              $typeStyles = [
                  'banner' => 'bg-amber/15 text-amber-700',
                  'announcement' => 'bg-teal/10 text-teal',
                  'site_text' => 'bg-gray-100 text-gray-600',
              ];
            ?>
            <?php foreach ($items as $item): ?>
              <tr>
                <td class="px-5 py-3 font-medium text-gray-900"><?= htmlspecialchars($item['title']) ?></td>
                <td class="px-5 py-3">
                  <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-full <?= $typeStyles[$item['type']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= $typeLabels[$item['type']] ?? $item['type'] ?>
                  </span>
                </td>
                <td class="px-5 py-3">
                  <?php if ((int) $item['is_active'] === 1): ?>
                    <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-full bg-teal/10 text-teal">Active</span>
                  <?php else: ?>
                    <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">Inactive</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-gray-500"><?= date('M j, Y', strtotime($item['updated_at'])) ?></td>
                <td class="px-5 py-3">
                  <div class="flex items-center justify-end gap-2 flex-wrap">
                    <a href="/superadmin/content/<?= (int) $item['id'] ?>/edit" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Edit</a>
                    <form method="POST" action="/superadmin/content/<?= (int) $item['id'] ?>/toggle">
                      <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                        <?= (int) $item['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                      </button>
                    </form>
                    <form method="POST" action="/superadmin/content/<?= (int) $item['id'] ?>/delete" data-title="Delete this content permanently?" data-confirm-text="Yes, delete it">
                      <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </main>

</div>