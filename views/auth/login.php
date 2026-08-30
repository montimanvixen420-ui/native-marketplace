<div class="min-h-screen bg-[#fafaf9] transition-colors dark:bg-ink lg:grid lg:grid-cols-[1.05fr_.95fr]">
  <section class="relative hidden overflow-hidden bg-brand px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
    <div class="absolute -left-24 top-24 h-80 w-80 rounded-full border border-white/15"></div>
    <div class="absolute -bottom-36 right-[-6rem] h-[34rem] w-[34rem] rounded-full bg-coral/90"></div>
    <div class="relative flex items-center gap-2 text-lg font-bold tracking-[.18em]">
      <span class="h-2.5 w-2.5 rounded-full bg-coral"></span>TINDA
    </div>

    <div class="relative max-w-xl">
      <p class="mb-5 text-xs font-bold uppercase tracking-[.25em] text-white/55">Filipino fashion, your way</p>
      <h1 class="font-display text-5xl font-bold leading-[1.04] xl:text-6xl">Wear what feels<br>like you.</h1>
      <p class="mt-6 max-w-sm text-sm leading-7 text-white/65">Find pieces from local clothing sellers, save your favourites, and track every order in one place.</p>
    </div>

    <div class="relative flex gap-7 border-t border-white/15 pt-5 text-xs text-white/55">
      <span><b class="block text-base text-white">Style-led</b>curated finds</span>
      <span><b class="block text-base text-white">Local</b>independent sellers</span>
      <span><b class="block text-base text-white">Easy</b>order tracking</span>
    </div>
  </section>

  <main class="flex min-h-screen items-center justify-center bg-[#fafaf9] px-5 py-10 transition-colors dark:bg-ink-2 sm:px-8">
    <div class="w-full max-w-md">
      <a href="/" class="mb-12 flex items-center gap-2 text-base font-bold tracking-[.16em] text-[#171717] dark:text-white lg:hidden"><span class="h-2.5 w-2.5 rounded-full bg-coral"></span>TINDA</a>
      <p class="mb-3 text-xs font-bold uppercase tracking-[.2em] text-coral">Welcome back</p>
      <h2 class="font-display text-4xl font-bold tracking-tight text-[#171717] dark:text-white">Sign in</h2>
      <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-white/55">Continue shopping your saved styles and orders.</p>

      <?php if (!empty($error)): ?>
        <div role="alert" class="mt-7 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="/login" class="mt-8 space-y-5">
        <div>
          <label for="email" class="mb-2 block text-sm font-semibold text-[#171717] dark:text-white">Email address</label>
          <input id="email" name="email" type="email" required autocomplete="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3.5 text-sm text-[#171717] outline-none transition placeholder:text-gray-400 focus:border-brand focus:ring-4 focus:ring-brand/15 dark:border-white/15 dark:bg-ink dark:text-white dark:placeholder:text-white/35">
        </div>
        <div>
          <div class="mb-2 flex items-center justify-between"><label for="password" class="block text-sm font-semibold text-[#171717] dark:text-white">Password</label><button type="button" data-password-toggle="password" class="text-xs font-semibold text-gray-500 hover:text-[#171717] dark:text-white/55 dark:hover:text-white">Show</button></div>
          <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3.5 text-sm text-[#171717] outline-none transition placeholder:text-gray-400 focus:border-brand focus:ring-4 focus:ring-brand/15 dark:border-white/15 dark:bg-ink dark:text-white dark:placeholder:text-white/35">
        </div>
        <div class="-mt-2 text-right"><a href="/forgot-password" class="text-xs font-semibold text-brand hover:underline">Forgot password?</a></div>
        <button type="submit" class="w-full rounded-lg bg-brand py-3.5 text-sm font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus:ring-4 focus:ring-brand/20">Sign in to TINDA</button>
      </form>
      <p class="mt-8 text-center text-sm text-gray-500 dark:text-white/55">New to TINDA? <a href="/register" class="font-bold text-[#171717] underline decoration-coral decoration-2 underline-offset-4 dark:text-white">Create an account</a></p>
      <p class="mt-5 text-center text-xs leading-5 text-gray-400 dark:text-white/40">By continuing, you agree to the <a href="/terms" class="underline hover:text-brand">Terms &amp; Conditions</a> and <a href="/privacy" class="underline hover:text-brand">Privacy Policy</a>.</p>
    </div>
  </main>
</div>
<script>
document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
  button.addEventListener('click', function () { var field = document.getElementById(button.dataset.passwordToggle); field.type = field.type === 'password' ? 'text' : 'password'; button.textContent = field.type === 'password' ? 'Show' : 'Hide'; });
});
</script>
