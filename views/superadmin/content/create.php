<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8 max-w-2xl">
    <div class="mb-6">
      <a href="/superadmin/content" class="text-xs text-teal font-medium hover:underline">&larr; Back to Content</a>
      <h1 class="font-display font-semibold text-2xl text-gray-900 mt-2">New content</h1>
      <p class="text-sm text-gray-500">Add a banner, announcement, or page of site text.</p>
    </div>

    <?php if ($error): ?>
      <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-4 py-3">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/superadmin/content/store" class="bg-white border border-gray-200 rounded-lg p-6 space-y-5">

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Type</label>
        <select name="type" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal">
          <option value="banner">Banner</option>
          <option value="announcement">Announcement</option>
          <option value="site_text">Site text</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Title</label>
        <input
          type="text"
          name="title"
          placeholder="e.g. Summer Sale Banner"
          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal"
          required
        >
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Body / message</label>
        <textarea
          name="body"
          rows="4"
          placeholder="Text shown to visitors…"
          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal"
        ></textarea>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Image URL (optional, for banners)</label>
        <input
          type="text"
          name="image_url"
          placeholder="https://…"
          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal"
        >
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" checked class="rounded border-gray-300 text-teal focus:ring-teal">
        <label for="is_active" class="text-sm text-gray-700">Active (visible on the site)</label>
      </div>

      <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="text-sm font-medium px-5 py-2.5 rounded-lg bg-ink text-white hover:bg-ink/90">Create</button>
        <a href="/superadmin/content" class="text-sm text-gray-500 hover:underline">Cancel</a>
      </div>

    </form>
  </main>

</div>