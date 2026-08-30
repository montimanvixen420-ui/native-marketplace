<main class="flex min-h-screen items-center justify-center bg-[#fafaf9] px-5 py-10 dark:bg-ink-2">
  <div class="w-full max-w-md rounded-2xl bg-white p-7 shadow-sm dark:bg-ink">
    <a href="/login" class="text-sm font-semibold text-brand hover:underline">&larr; Back to sign in</a>
    <p class="mt-8 text-xs font-bold uppercase tracking-[.2em] text-coral">Account recovery</p>
    <h1 class="mt-2 font-display text-3xl font-bold text-[#171717] dark:text-white">Choose a new password</h1>
    <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-white/55">Your reset link expires after 15 minutes.</p>
    <?php if (!empty($error)): ?><div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" action="/reset-password" class="mt-7 space-y-5">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
      <div><label for="password" class="mb-2 block text-sm font-semibold text-[#171717] dark:text-white">New password</label><input id="password" name="password" type="password" minlength="8" required autocomplete="new-password" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3.5 text-sm text-[#171717] outline-none focus:border-brand focus:ring-4 focus:ring-brand/15"></div>
      <div><label for="confirm_password" class="mb-2 block text-sm font-semibold text-[#171717] dark:text-white">Confirm new password</label><input id="confirm_password" name="confirm_password" type="password" minlength="8" required autocomplete="new-password" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3.5 text-sm text-[#171717] outline-none focus:border-brand focus:ring-4 focus:ring-brand/15"></div>
      <button class="w-full rounded-lg bg-brand py-3.5 text-sm font-semibold text-white hover:bg-brand-dark">Save new password</button>
    </form>
  </div>
</main>
