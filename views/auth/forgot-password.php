<main class="flex min-h-screen items-center justify-center bg-[#fafaf9] px-5 py-10 dark:bg-ink-2">
  <div class="w-full max-w-md rounded-2xl bg-white p-7 shadow-sm dark:bg-ink">
    <a href="/login" class="text-sm font-semibold text-brand hover:underline">&larr; Back to sign in</a>
    <p class="mt-8 text-xs font-bold uppercase tracking-[.2em] text-coral">Account recovery</p>
    <h1 class="mt-2 font-display text-3xl font-bold text-[#171717] dark:text-white">Reset your password</h1>
    <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-white/55">Enter your account email and we will send a password-reset link.</p>
    <?php if (!empty($error)): ?><div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($message)): ?><div role="status" class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="POST" action="/forgot-password" class="mt-7 space-y-5">
      <div><label for="email" class="mb-2 block text-sm font-semibold text-[#171717] dark:text-white">Email address</label><input id="email" name="email" type="email" required autocomplete="email" value="<?= htmlspecialchars($email ?? '') ?>" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3.5 text-sm text-[#171717] outline-none focus:border-brand focus:ring-4 focus:ring-brand/15"></div>
      <button class="w-full rounded-lg bg-brand py-3.5 text-sm font-semibold text-white hover:bg-brand-dark">Send reset link</button>
    </form>
  </div>
</main>
