<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="min-w-0 flex-1 px-5 py-7 sm:px-8">
    <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div><p class="text-xs font-semibold uppercase tracking-[.16em] text-brand">My dashboard</p><h1 class="mt-1 font-display text-2xl font-bold text-ink dark:text-white">Welcome, <?= htmlspecialchars($name) ?></h1><p class="mt-1 text-sm text-gray-500 dark:text-white/50">Your shopping activity in one place.</p></div>
      <div class="flex flex-wrap gap-2"><a href="/apply" class="w-fit rounded-lg border border-brand px-4 py-2.5 text-sm font-semibold text-brand hover:bg-brand-light">Apply as seller/supplier</a><a href="/shop" class="w-fit rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Browse products</a></div>
    </header>

    <?php if (!empty($banners)): ?>
      <section class="mb-5 space-y-3">
        <?php foreach ($banners as $banner): ?>
          <article class="overflow-hidden rounded-xl bg-ink text-white <?= !empty($banner['image_url']) ? 'relative min-h-40' : 'p-6' ?>">
            <?php if (!empty($banner['image_url'])): ?><img src="<?= htmlspecialchars($banner['image_url']) ?>" alt="<?= htmlspecialchars($banner['title']) ?>" class="absolute inset-0 h-full w-full object-cover opacity-40"><?php endif; ?>
            <div class="<?= !empty($banner['image_url']) ? 'relative p-6' : '' ?>"><h2 class="font-display text-xl font-semibold"><?= htmlspecialchars($banner['title']) ?></h2><?php if (!empty($banner['body'])): ?><p class="mt-2 max-w-2xl text-sm text-white/80"><?= nl2br(htmlspecialchars($banner['body'])) ?></p><?php endif; ?></div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
    <?php if (!empty($announcements)): ?>
      <section class="mb-5 space-y-2">
        <?php foreach ($announcements as $announcement): ?><article class="rounded-lg border border-brand/20 bg-brand-light px-4 py-3"><p class="text-sm font-semibold text-brand"><?= htmlspecialchars($announcement['title']) ?></p><?php if (!empty($announcement['body'])): ?><p class="mt-1 text-sm text-gray-600 dark:text-white/70"><?= nl2br(htmlspecialchars($announcement['body'])) ?></p><?php endif; ?></article><?php endforeach; ?>
      </section>
    <?php endif; ?>
    <?php if (!empty($siteTexts)): ?>
      <section class="mb-5 grid gap-3 sm:grid-cols-2">
        <?php foreach ($siteTexts as $siteText): ?><article class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-ink-2"><h2 class="text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($siteText['title']) ?></h2><?php if (!empty($siteText['body'])): ?><p class="mt-1 text-sm leading-6 text-gray-600 dark:text-white/70"><?= nl2br(htmlspecialchars($siteText['body'])) ?></p><?php endif; ?></article><?php endforeach; ?>
      </section>
    <?php endif; ?>

    <section class="grid gap-4 sm:grid-cols-3">
      <a href="/orders" class="dashboard-stat"><span class="stat-label">My orders</span><b>Track orders</b><small>Check delivery updates &rarr;</small></a>
      <a href="/wishlist" class="dashboard-stat"><span class="stat-label">Wishlist</span><b>Saved styles</b><small>See your favourites &rarr;</small></a>
      <a href="/cart" class="dashboard-stat"><span class="stat-label">Shopping cart</span><b>Ready to buy</b><small>Review your items &rarr;</small></a>
    </section>
    <section class="mt-5 grid gap-5 xl:grid-cols-[1.45fr_.8fr]">
      <div class="dashboard-panel"><div class="flex items-center justify-between"><div><p class="eyebrow">Shopping shortcuts</p><h2>Continue shopping</h2></div><a href="/shop" class="text-sm font-semibold text-brand hover:underline">View shop</a></div><div class="mt-5 grid gap-3 sm:grid-cols-3"><a href="/shop" class="action-card"><span>01</span><b>Browse products</b><p>Discover new clothing styles.</p></a><a href="/wishlist" class="action-card"><span>02</span><b>My wishlist</b><p>Return to saved favourites.</p></a><a href="/orders" class="action-card"><span>03</span><b>My orders</b><p>Track, review, or return items.</p></a></div></div>
      <aside class="dashboard-panel"><p class="eyebrow">Need help?</p><h2>We're here for you.</h2><p class="mt-3 text-sm leading-6 text-gray-500 dark:text-white/55">Need an update on an order or have a question about a product?</p><a href="/help" class="mt-5 inline-block rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white">Visit help centre</a></aside>
    </section>
  </main>
</div>
