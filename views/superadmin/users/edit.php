<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

  <main class="flex-1 px-8 py-8 max-w-2xl">
    <div class="mb-6">
      <a href="/superadmin/users" class="text-xs text-teal font-medium hover:underline">&larr; Back to Users</a>
      <h1 class="font-display font-semibold text-2xl text-gray-900 mt-2">Edit user</h1>
      <p class="text-sm text-gray-500">Update this account's basic details.</p>
    </div>

    <?php if ($error): ?>
      <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-4 py-3">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/superadmin/users/<?= (int) $user['id'] ?>/update" class="bg-white border border-gray-200 rounded-lg p-6 space-y-5">

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Full name</label>
        <input
          type="text"
          name="name"
          value="<?= htmlspecialchars($user['name']) ?>"
          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal"
          required
        >
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
        <input
          type="email"
          name="email"
          value="<?= htmlspecialchars($user['email']) ?>"
          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal"
          required
        >
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Role</label>
        <select name="role" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-teal">
          <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
          <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin (seller)</option>
          <option value="supplier" <?= $user['role'] === 'supplier' ? 'selected' : '' ?>>Supplier</option>
          <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Status</label>
        <p class="text-sm text-gray-500 px-3 py-2.5 bg-gray-50 rounded-lg border border-gray-100">
          <?= ucfirst($user['status']) ?> — change status from the Users list (Approve / Suspend / Reactivate / Delete).
        </p>
      </div>

      <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="text-sm font-medium px-5 py-2.5 rounded-lg bg-ink text-white hover:bg-ink/90">Save changes</button>
        <a href="/superadmin/users" class="text-sm text-gray-500 hover:underline">Cancel</a>
      </div>

    </form>
  </main>

</div>