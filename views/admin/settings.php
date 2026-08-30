<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
  
  <main class="flex-1 px-8 py-8">
    
    <!-- Page Header -->
    <header class="mb-8">
      <p class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400 mb-1">Preferences</p>
      <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white">Seller Settings</h1>
      <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Manage your account credentials and the public details of your shop.</p>
    </header>

    <!-- Alerts Section -->
    <div class="max-w-3xl space-y-3 mb-6">
      <?php if ($success): ?>
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
          <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
          <span>Settings saved successfully.</span>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 px-4 py-3 text-sm text-rose-700 dark:text-rose-300 flex items-center gap-2">
          <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($isInitialSetup): ?>
        <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-800 dark:text-amber-200 flex items-center gap-2">
          <i data-lucide="info" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0"></i>
          <span>Complete your shop profile and submit your business proof. This is required for seller verification.</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Main Settings Form -->
    <form method="POST" action="/admin/settings/update" enctype="multipart/form-data" class="max-w-3xl space-y-6">
      
      <!-- Account Details Card -->
      <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm">
        <h2 class="font-display font-semibold text-base text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-slate-700 pb-3">
          <i data-lucide="user" class="w-4 h-4 text-teal-600 dark:text-teal-400"></i> Account Details
        </h2>
        
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Full Name</label>
            <input required name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Email Address</label>
            <input required type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
          </div>
        </div>
      </section>

      <!-- Shop Profile Card -->
      <section class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm">
        <h2 class="font-display font-semibold text-base text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-slate-700 pb-3">
          <i data-lucide="store" class="w-4 h-4 text-teal-600 dark:text-teal-400"></i> Shop Profile
        </h2>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Shop Name</label>
            <input required name="business_name" value="<?= htmlspecialchars($application['business_name'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. TINDA Official Store">
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">About Your Shop</label>
            <textarea required name="business_description" rows="4" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Describe what products your shop sells..."><?= htmlspecialchars($application['business_description'] ?? '') ?></textarea>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Mobile Number</label>
              <input required name="phone" value="<?= htmlspecialchars($application['phone'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="+63 9XX XXX XXXX">
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Business Address</label>
              <input required name="business_address" value="<?= htmlspecialchars($application['business_address'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Street, City, Province">
            </div>
          </div>

          <!-- Business Logo Upload Box -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Business Logo</label>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-gray-200 dark:border-slate-700 rounded-xl bg-gray-50/50 dark:bg-slate-900/50">
              <?php if (!empty($application['logo_path'])): ?>
                <img src="/<?= htmlspecialchars($application['logo_path']) ?>" alt="Current shop logo" class="w-16 h-16 rounded-xl object-cover border border-gray-200 dark:border-slate-600 shrink-0">
              <?php else: ?>
                <div class="w-16 h-16 rounded-xl bg-gray-200 dark:bg-slate-700 flex items-center justify-center shrink-0 text-gray-400">
                  <i data-lucide="image" class="w-6 h-6"></i>
                </div>
              <?php endif; ?>
              
              <div class="space-y-1">
                <input type="file" name="business_logo" accept="image/png,image/jpeg" <?= $isInitialSetup ? 'required' : '' ?> class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 dark:file:bg-teal-950/60 dark:file:text-teal-300 hover:file:bg-teal-100 transition">
                <p class="text-xs text-gray-400 dark:text-gray-500"><?= $isInitialSetup ? 'Required: PNG or JPG, 5MB max.' : 'Optional: PNG or JPG, 5MB max.' ?></p>
              </div>
            </div>
          </div>

          <!-- Business Document Upload (Initial Setup Only) -->
          <?php if ($isInitialSetup): ?>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Business Registration or Valid Proof</label>
              <div class="p-4 border border-gray-200 dark:border-slate-700 rounded-xl bg-gray-50/50 dark:bg-slate-900/50 space-y-2">
                <input required type="file" name="business_document" accept="application/pdf,image/png,image/jpeg" class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 dark:file:bg-teal-950/60 dark:file:text-teal-300 hover:file:bg-teal-100 transition">
                <p class="text-xs text-gray-400 dark:text-gray-500">DTI/SEC registration, barangay permit, or valid proof. Accepted formats: PDF, PNG, or JPEG; 5MB max.</p>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </section>

      <!-- Save Button -->
      <div class="flex justify-end pt-2">
        <button style="background-color: #059669 !important; color: #ffffff !important;" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold shadow-md hover:opacity-90 transition cursor-pointer">
          <i data-lucide="save" class="w-4 h-4"></i> Save Changes
        </button>
      </div>

    </form>
  </main>
</div>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
  if (typeof lucide !== 'undefined') lucide.createIcons();
</script>