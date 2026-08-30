<main class="bg-[#0b1728] text-white">
  <section class="relative isolate min-h-screen overflow-hidden bg-[#0b1728]">
    <img src="https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&w=2000&q=90" alt="Denim clothing on a rack" class="absolute inset-0 -z-20 h-full w-full object-cover">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#07172b]/95 via-[#09213b]/70 to-[#07172b]/35">
    </div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-2/3 bg-gradient-to-t from-[#07172b] via-[#07172b]/20 to-transparent">
    </div>
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-7 sm:px-10 lg:px-14">
    <a href="/" class="font-display text-2xl font-extrabold tracking-tight">TINDA<span class="text-lime-300">.
    </span>
   </a>
   <div class="hidden items-center gap-8 text-sm font-medium text-white/85 md:flex">
    <a href="#shop" class="hover:text-lime-300">Shop</a><a href="/branches" class="hover:text-lime-300">Branches</a>
    <a href="#story" class="hover:text-lime-300">Our story</a>
    <a href="/sustainability" class="hover:text-lime-300">Sustainability</a>
  </div>
  <div class="flex items-center gap-3"><?php if ($isLoggedIn): ?><a href="<?= $userRole === 'superadmin' ? '/superadmin/dashboard' : ($userRole === 'admin' ? '/admin/dashboard' : ($userRole === 'supplier' ? '/supplier/dashboard' : '/customer/dashboard')) ?>" class="rounded-full border border-white/25 px-4 py-2 text-sm font-semibold transition hover:bg-white hover:text-[#0b1728]">My account</a>
    <?php else: ?>
      <a href="/login" class="hidden text-sm font-semibold hover:text-lime-300 sm:inline">Sign in</a>
      <a href="/register" class="rounded-full bg-lime-300 px-4 py-2 text-sm font-bold text-[#0a1a2d] transition hover:bg-lime-200">Join TINDA</a>
      <?php endif; ?>
    </div>
  </nav>
    <div class="mx-auto flex min-h-[calc(100vh-92px)] max-w-7xl items-center px-6 pb-20 pt-14 sm:px-10 lg:px-14">
      <div class="max-w-2xl"><p class="mb-5 text-xs font-bold uppercase tracking-[.25em] text-lime-300">Local style. Built to last.</p>
      <h1 class="font-display text-5xl font-extrabold leading-[.92] tracking-tight sm:text-7xl lg:text-8xl">Dress like it’s<br>
      <span class="text-lime-300">earned,</span> not bought.</h1>
      <p class="mt-7 max-w-xl text-base leading-7 text-white/80 sm:text-lg">Discover pre-loved pieces, independent sellers, and clothes with a story worth carrying forward. Good style should feel personal, not disposable.</p>
      <div class="mt-9 flex flex-wrap gap-4">
        <a id="shop" href="/shop" class="inline-flex items-center rounded-full bg-lime-300 px-7 py-3.5 text-sm font-extrabold text-[#0a1a2d] transition hover:-translate-y-0.5 hover:bg-lime-200">Shop now <span class="ml-2">→</span>
      </a>
      <a href="#story" class="inline-flex items-center rounded-full border border-white/35 px-7 py-3.5 text-sm font-bold transition hover:bg-white/10">Our story</a>
    </div>
  </div>
</div>
  </section>
  <section id="story" class="relative overflow-hidden bg-[#123c60]">
    <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=2000&q=85" alt="A clothing shop in the city" class="absolute inset-0 h-full w-full object-cover opacity-35">
    <div class="absolute inset-0 bg-[#092b4c]/90">
    </div>
    <div class="relative mx-auto grid max-w-7xl gap-12 px-6 py-24 sm:px-10 lg:grid-cols-[1.15fr_.85fr] lg:px-14 lg:py-32">
      <div>
        <p class="text-xs font-bold uppercase tracking-[.25em] text-lime-300">Our story</p>
        <h2 class="mt-4 max-w-3xl font-display text-4xl font-extrabold leading-tight sm:text-5xl">From one folding table to the front page.</h2>
      </div>
      <div class="text-sm leading-7 text-white/80 sm:text-base">
        <p>TINDA began with a simple belief: great clothing deserves more than one chapter. We created a marketplace where local sellers, thrift lovers, and conscious shoppers can find each other.</p>
        <p class="mt-5">Every listing helps keep usable clothing in circulation, supports small businesses, and makes style more accessible. The result is a better way to shop—one piece, one seller, one story at a time.</p>
      </div>
    </div>
  </section>
  <section id="impact" class="bg-[#0b1728] px-6 py-20 sm:px-10 lg:px-14">
    <div class="mx-auto max-w-7xl"><p class="text-xs font-bold uppercase tracking-[.25em] text-lime-300">Why TINDA</p>
    <div class="mt-8 grid gap-5 md:grid-cols-3">
      <article class="rounded-2xl border border-white/10 bg-white/5 p-7">
      <p class="text-3xl">♻</p>
      <h3 class="mt-7 font-display text-xl font-bold">Keep clothes moving</h3><p class="mt-3 text-sm leading-6 text-white/65">Give quality pieces a longer life instead of sending them to landfill.</p>
    </article>
    <article class="rounded-2xl border border-white/10 bg-white/5 p-7">
      <p class="text-3xl">⌂</p>
    <h3 class="mt-7 font-display text-xl font-bold">Back local sellers</h3>
    <p class="mt-3 text-sm leading-6 text-white/65">Buy directly from people building businesses in your community.</p>
  </article>
  <article class="rounded-2xl border border-white/10 bg-white/5 p-7"><p class="text-3xl">✦</p>
  <h3 class="mt-7 font-display text-xl font-bold">Find your next favorite</h3><p class="mt-3 text-sm leading-6 text-white/65">Discover individual pieces that make your wardrobe feel more like you.</p>
</article>
</div>
</div>
</section>
  <footer class="border-t border-white/10 bg-[#071221] px-6 py-8 sm:px-10 lg:px-14">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 text-sm text-white/55 sm:flex-row sm:items-center sm:justify-between">
      <p>© <?= date('Y') ?> TINDA Marketplace</p><div class="flex gap-5"><a href="/terms" class="hover:text-white">Terms</a>
      <a href="/privacy" class="hover:text-white">Privacy</a><a href="/help" class="hover:text-white">Help</a>
    </div>
    </div>
  </footer>
</main>
