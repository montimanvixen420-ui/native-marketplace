<div class="flex min-h-screen bg-surface dark:bg-ink transition-colors">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="min-w-0 flex-1 px-5 py-6 sm:px-8 lg:px-10">
    <header class="mb-6 flex items-center justify-between gap-4 border-b border-slate-200 pb-5 dark:border-white/10">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-[.2em] text-brand">The style edit</p>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-ink dark:text-white">Discover your next look</h1>
      </div>
      <div class="hidden items-center gap-2 text-xs font-semibold text-gray-500 sm:flex">
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-light text-brand">&#10022;</span> Curated for you
      </div>
    </header>
    <?php if (!empty($_SESSION['restock_success'])): ?>
      <div class="mb-5 rounded-xl border border-brand/20 bg-brand-light px-4 py-3 text-sm text-brand">
        <?= htmlspecialchars($_SESSION['restock_success']); unset($_SESSION['restock_success']); ?>
      </div>
      <?php endif; ?>
    <?php if (!empty($_GET['report_success'])): ?>
      <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        <?= htmlspecialchars($_GET['report_success']) ?>
      </div><?php endif; ?>
    <?php if (!empty($_GET['report_error'])): ?>
      <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
        <?= htmlspecialchars($_GET['report_error']) ?>
      </div>
      <?php endif; ?>

    <section class="relative mb-8 overflow-hidden rounded-[1.5rem] bg-[#111827] px-6 py-9 text-white sm:px-10 sm:py-12">
      <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#2563eb] opacity-50 blur-2xl">
      </div>
      <div class="absolute bottom-0 right-1/4 h-32 w-32 rounded-full bg-[#f59e0b] opacity-25 blur-xl">
      </div>
      <div class="relative max-w-xl">
        <p class="mb-3 text-[11px] font-bold uppercase tracking-[.22em] text-blue-200">New season · 2026</p>
        <h2 class="font-display text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Everyday pieces,
           <em class="font-serif font-normal text-blue-200">extraordinary</em> you.
          </h2>
          <p class="mt-4 max-w-md text-sm leading-6 text-white/70">Thoughtful styles from independent local sellers, ready for your wardrobe.</p>
          <a href="#collection" class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold text-ink transition hover:bg-blue-50">Shop the edit 
            <span aria-hidden="true">&#8594;
            </span>
          </a>
        </div>
      <div class="absolute bottom-5 right-6 hidden rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-right backdrop-blur sm:block">
        <p class="text-[10px] uppercase tracking-wider text-white/60">Style promise</p>
        <p class="mt-1 text-sm font-bold">Fresh picks weekly</p>
      </div>
    </section>
    <?php if (!empty($banners)): ?>
      <section class="mb-6 grid gap-3 lg:grid-cols-2"><?php foreach ($banners as $banner): ?>
        <article class="relative min-h-32 overflow-hidden rounded-2xl border border-rose-100 bg-white p-5 dark:border-white/10 dark:bg-ink-2"><?php if (!empty($banner['image_url'])): ?>
          <img src="<?= htmlspecialchars($banner['image_url']) ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-15"><?php endif; ?>
          <div class="relative">
            <p class="text-[10px] font-bold uppercase tracking-widest text-brand">Spotlight</p>
          <h2 class="mt-1 font-display text-lg font-bold text-ink dark:text-white"><?= htmlspecialchars($banner['title']) ?></h2>
          <?php if (!empty($banner['body'])): ?>
            <p class="mt-1 text-sm text-gray-500 dark:text-white/60"><?= nl2br(htmlspecialchars($banner['body'])) ?></p>
            <?php endif; ?>
          </div>
        </article><?php endforeach; ?>
      </section><?php endif; ?>
    <?php if (!empty($announcements)): ?>
      <section class="mb-5 space-y-2"><?php foreach ($announcements as $announcement): ?>
        <article class="rounded-xl border border-brand/20 bg-brand-light px-4 py-3">
          <p class="text-sm font-bold text-brand"><?= htmlspecialchars($announcement['title']) ?></p>
          <?php if (!empty($announcement['body'])): ?>
            <p class="mt-1 text-sm text-gray-600"><?= nl2br(htmlspecialchars($announcement['body'])) ?>
          </p><?php endif; ?>
        </article>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

    <section id="collection" class="mb-6">
      <div class="mb-4 flex flex-wrap items-end justify-between gap-3"><div>
        <p class="text-[10px] font-bold uppercase tracking-[.18em] text-brand">Curated collection</p>
        <h2 class="mt-1 font-display text-xl font-extrabold text-ink dark:text-white">Shop all styles</h2>
      </div>
      <p id="product-count" class="text-sm text-gray-400"><?= count($products) ?> pieces waiting for you</p>
    </div>
      <form action="/shop" method="GET" class="flex flex-col gap-2 rounded-2xl border border-rose-100 bg-white p-2 shadow-sm sm:flex-row dark:border-white/10 dark:bg-ink-2">
        <div class="flex flex-1 items-center gap-2 px-3">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-brand">
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-4-4"/>
          </svg>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search dresses, tops, bags..." class="w-full bg-transparent py-2 text-sm text-ink outline-none placeholder:text-gray-400 dark:text-white"></div>
          <select id="category-filter" name="category" class="rounded-xl bg-rose-50 px-3 py-2.5 text-sm font-medium text-gray-600 outline-none dark:bg-white/5 dark:text-white">
            <option value="">All categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $selectedCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($cat)) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="rounded-xl bg-brand px-5 py-2.5 text-sm font-bold text-white transition hover:bg-brand-dark">Search</button>
        </form>
    </section>
    <?php if (empty($products)): ?>
      <div class="rounded-2xl border border-dashed border-rose-200 bg-white p-12 text-center text-sm text-gray-500">No styles found. Try another search or category.</div>
      <?php else: ?>
      <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
        <?php foreach ($products as $product): $variants = $variantsByProduct[(int) $product['id'] . ':' . (int) $product['branch_id']] ?? []; $isWishlisted = in_array((int) $product['id'], $wishlistedProductIds, true); ?>
          <article class="product-card group min-w-0" data-category="<?= htmlspecialchars((string) ($product['category'] ?? ''), ENT_QUOTES) ?>">
            <div class="relative aspect-[.82] overflow-hidden rounded-2xl bg-rose-100">
              <?php if (!empty($product['image_url'])): ?>
                <img src="/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105"><?php else: ?>
                  <span class="flex h-full items-center justify-center text-xs text-rose-300">No image</span><?php endif; ?><span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-brand shadow-sm">New</span>
                  <button
                    type="button"
                    title="<?= $isWishlisted ? 'Remove from wishlist' : 'Save to wishlist' ?>"
                    class="wishlist-btn absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full shadow-sm transition <?= $isWishlisted ? 'bg-brand text-white' : 'bg-white/90 text-ink hover:bg-brand hover:text-white' ?>"
                    data-product-id="<?= (int) $product['id'] ?>"
                    data-in-wishlist="<?= $isWishlisted ? '1' : '0' ?>"
                  >
                    <svg viewBox="0 0 24 24" fill="<?= $isWishlisted ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                      <path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 000-7.6z"/>
                    </svg>
                  </button>
                </div>
            <div class="pt-3">
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                  <p class="truncate text-sm font-bold text-ink dark:text-white"><?= htmlspecialchars($product['name']) ?></p>
                  <p class="mt-1 truncate text-xs text-gray-400">by <?= htmlspecialchars($product['seller_name']) ?><?php if (($product['seller_status'] ?? '') === 'approved'): ?> · Verified<?php endif; ?></p>
                  </div>
                  <p class="shrink-0 text-sm font-extrabold text-brand">₱<?= number_format((float) $product['price'], 2) ?></p>
                </div>

                <?php $productBranches = [['name' => $product['branch_name'], 'address' => $product['branch_address'], 'city' => $product['branch_city'], 'phone' => $product['branch_phone'], 'hours' => $product['branch_hours']]]; ?>
                <?php if (!empty($productBranches)): ?>
                  <div class="branch-list mt-2 text-xs text-gray-500">
                    <?php foreach ($productBranches as $i => $branch): ?>
                      <div class="space-y-1 <?= $i > 0 ? 'branch-extra hidden mt-2' : '' ?>">
                        <p class="flex items-center gap-1.5">
                          <span class="flex h-3.5 w-3.5 shrink-0 items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5 text-brand"><path d="M12 21s7-5.1 7-11a7 7 0 10-14 0c0 5.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                          </span>
                          <span><?= htmlspecialchars($branch['address']) ?>, <?= htmlspecialchars($branch['city']) ?></span>
                        </p>
                        <p class="flex items-center gap-1.5">
                          <span class="flex h-3.5 w-3.5 shrink-0 items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5 text-brand"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                          </span>
                          <span><?= !empty($branch['phone']) ? htmlspecialchars($branch['phone']) : 'No contact number' ?></span>
                        </p>
                        <p class="flex items-center gap-1.5">
                          <span class="flex h-3.5 w-3.5 shrink-0 items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5 text-brand"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                          </span>
                          <span><?= !empty($branch['hours']) ? htmlspecialchars($branch['hours']) : 'Hours not set' ?></span>
                        </p>
                        
                      </div>
                    <?php endforeach; ?>
                    <?php if (count($productBranches) > 1): ?>
                      <button type="button" class="branch-toggle mt-1.5 text-[11px] font-bold text-brand hover:underline" data-more-label="See <?= count($productBranches) - 1 ?> more branch<?= count($productBranches) - 1 > 1 ? 'es' : '' ?>" data-expanded="0">See <?= count($productBranches) - 1 ?> more branch<?= count($productBranches) - 1 > 1 ? 'es' : '' ?></button>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <p class="mt-2 text-xs text-gray-400">No branch location listed</p>
                <?php endif; ?>

                <div class="mt-2 flex gap-3 text-xs font-semibold">
                  <a href="/reports/create?type=product&id=<?= (int)$product['id'] ?>" class="text-gray-400 hover:text-red-600">Report item</a>
                  <a href="/reports/create?type=seller&id=<?= (int)$product['seller_id'] ?>" class="text-gray-400 hover:text-red-600">Report seller</a>
                </div><?php if (!empty($product['fit_information'])): ?>
                  <p class="mt-2 text-xs text-gray-500">
                    <b>Fit:</b> 
                    <?= htmlspecialchars($product['fit_information']) ?>
                  </p>
                  <?php endif; ?>
              <div class="mt-3">
                <form action="/cart/add" method="POST" class="cart-add-form flex-1" data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>">
                  <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                  <input type="hidden" name="branch_id" value="<?= (int) $product['branch_id'] ?>">
                  <input type="hidden" name="quantity" value="1"><?php if (!empty($variants)): ?>
                    <select required name="variant_id" class="mb-2 w-full rounded-lg border border-rose-100 px-2 py-1.5 text-xs">
                      <option value="">Select size/color</option>
                      <?php foreach ($variants as $variant): ?>
                        <option value="<?= (int) $variant['id'] ?>" <?= (int) $variant['stock'] === 0 ? 'disabled' : '' ?>><?= htmlspecialchars($variant['size'] . ' · ' . $variant['color']) ?><?= (int) $variant['stock'] === 0 ? ' (Sold out)' : '' ?>
                      </option>
                      <?php endforeach; ?>
                    </select><?php endif; ?>
                    <div class="flex gap-2">
                      <button type="submit" class="flex-1 rounded-xl border border-brand py-2 text-xs font-bold text-brand transition hover:bg-brand hover:text-white">Add to bag</button>
                      <button type="submit" formaction="/checkout" formmethod="GET" class="buy-now-btn flex-1 rounded-xl bg-ink py-2 text-xs font-bold text-white transition hover:bg-brand-dark">Buy now</button>
                    </div>
                  </form>
                  </div>
              <?php $soldOutVariants = !empty($variants) && !array_filter($variants, fn($v) => (int) $v['stock'] > 0) ? $variants : []; ?><?php if ((!empty($variants) && !empty($soldOutVariants)) || (empty($variants) && (int)$product['stock'] === 0)): ?>
                <form action="/restock-notifications" method="POST" class="mt-2 flex gap-2">
                  <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><?php if (!empty($soldOutVariants)): ?>
                    <select name="variant_id" class="min-w-0 flex-1 rounded-lg border border-rose-100 px-2 py-1.5 text-xs"><?php foreach($soldOutVariants as $variant): ?>
                      <option value="<?= (int)$variant['id'] ?>"><?= htmlspecialchars($variant['size'] . ' · ' . $variant['color']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    <button class="shrink-0 rounded-lg bg-rose-100 px-2 py-1.5 text-xs font-bold text-brand">Notify me</button>
                  </form>
                  <?php endif; ?>
                </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div id="category-empty-state" class="hidden rounded-2xl border border-dashed border-rose-200 bg-white p-12 text-center text-sm text-gray-500">No styles found in this category.</div>
    <?php endif; ?>
  </main>
</div>

<script>
(function () {
  function bindCartAddForms() {
    document.querySelectorAll('.cart-add-form').forEach(function (form) {
      if (form.dataset.bound) return;
      form.dataset.bound = '1';

      form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        var submitter = e.submitter;
        if (submitter && submitter.classList.contains('buy-now-btn')) {
          var buyNowOriginalText = submitter.textContent;
          submitter.disabled = true;
          submitter.textContent = 'Checking...';
          var buyNowParams = new URLSearchParams(new FormData(form));

          fetch('/checkout/check-availability?' + buyNowParams.toString())
            .then(function (res) { return res.json(); })
            .then(function (data) {
              if (data.available) {
                window.location = '/checkout?' + buyNowParams.toString();
              } else {
                showToast(data.message || 'This item is no longer available.', 'error');
                submitter.disabled = false;
                submitter.textContent = buyNowOriginalText;
              }
            })
            .catch(function () {
              showToast('Could not verify item availability. Please try again.', 'error');
              submitter.disabled = false;
              submitter.textContent = buyNowOriginalText;
            });
          return;
        }

        var btn = form.querySelector('button[type="submit"]');
        var originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Adding...';

        fetch(form.action, { method: 'POST', body: new FormData(form) })
          .then(function (res) {
            if (!res.ok) throw new Error('Request failed');
            showToast('Added "' + form.dataset.productName + '" to bag');
          })
          .catch(function () {
            showToast('Could not add item. Please try again.', 'error');
          })
          .finally(function () {
            btn.disabled = false;
            btn.textContent = originalText;
          });
      });
    });
  }

  function bindBuyNowForms() {
    document.querySelectorAll('.buynow-form').forEach(function (form) {
      if (form.dataset.bound) return;
      form.dataset.bound = '1';

      form.addEventListener('submit', function (e) {
        e.preventDefault();

        var btn = form.querySelector('button[type="submit"]');
        var originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Checking...';

        var params = new URLSearchParams(new FormData(form));

        fetch('/checkout/check-availability?' + params.toString())
          .then(function (res) { return res.json(); })
          .then(function (data) {
            if (data.available) {
              window.location = form.action + '?' + params.toString();
            } else {
              showToast(data.message || 'This item is no longer available.', 'error');
              btn.disabled = false;
              btn.textContent = originalText;
            }
          })
          .catch(function () {
            showToast('Could not verify item availability. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = originalText;
          });
      });
    });
  }

  function bindBranchToggles() {
    document.querySelectorAll('.branch-toggle').forEach(function (btn) {
      if (btn.dataset.bound) return;
      btn.dataset.bound = '1';

      btn.addEventListener('click', function () {
        var list = btn.closest('.branch-list');
        var extras = list.querySelectorAll('.branch-extra');
        var expanded = btn.dataset.expanded === '1';

        extras.forEach(function (el) {
          el.classList.toggle('hidden', expanded);
        });

        btn.dataset.expanded = expanded ? '0' : '1';
        btn.textContent = expanded ? btn.dataset.moreLabel : 'See less';
      });
    });
  }

  function bindCategoryFilter() {
    var select = document.getElementById('category-filter');
    if (!select || select.dataset.bound) return;
    select.dataset.bound = '1';

    var cards = Array.prototype.slice.call(document.querySelectorAll('.product-card'));
    var count = document.getElementById('product-count');
    var emptyState = document.getElementById('category-empty-state');

    function applyFilter() {
      var category = select.value;
      var visible = 0;
      cards.forEach(function (card) {
        var matches = !category || card.dataset.category === category;
        card.classList.toggle('hidden', !matches);
        if (matches) visible++;
      });
      if (count) count.textContent = visible + ' piece' + (visible === 1 ? '' : 's') + ' waiting for you';
      if (emptyState) emptyState.classList.toggle('hidden', visible > 0);
    }

    select.addEventListener('change', applyFilter);
    applyFilter();
  }

  function bindWishlistButtons() {
    document.querySelectorAll('.wishlist-btn').forEach(function (btn) {
      if (btn.dataset.bound) return;
      btn.dataset.bound = '1';

      btn.addEventListener('click', function () {
        var svg = btn.querySelector('svg');
        var inWishlist = btn.dataset.inWishlist === '1';
        var url = inWishlist ? '/wishlist/remove' : '/wishlist/add';

        var body = new URLSearchParams();
        body.set('product_id', btn.dataset.productId);
        body.set('ajax', '1');

        fetch(url, { method: 'POST', body: body })
          .then(function (res) {
            if (!res.ok) throw new Error('Request failed');
            return res.json();
          })
          .then(function (data) {
            var nowInWishlist = !!data.in_wishlist;
            btn.dataset.inWishlist = nowInWishlist ? '1' : '0';

            if (nowInWishlist) {
              btn.classList.remove('bg-white/90', 'text-ink');
              btn.classList.add('bg-brand', 'text-white');
              svg.setAttribute('fill', 'currentColor');
              btn.title = 'Remove from wishlist';
              showToast('Saved to wishlist');
            } else {
              btn.classList.remove('bg-brand', 'text-white');
              btn.classList.add('bg-white/90', 'text-ink');
              svg.setAttribute('fill', 'none');
              btn.title = 'Save to wishlist';
              showToast('Removed from wishlist');
            }
          })
          .catch(function () {
            showToast('Could not update wishlist. Please try again.', 'error');
          });
      });
    });
  }

  function showToast(title, icon) {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: icon || 'success',
      title: title,
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
    });
  }

  if (typeof Swal === 'undefined') {
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    s.onload = function () {
      bindWishlistButtons();
      bindCartAddForms();
      bindBuyNowForms();
      bindBranchToggles();
      bindCategoryFilter();
    };
    document.head.appendChild(s);
  } else {
    bindWishlistButtons();
    bindCartAddForms();
    bindBuyNowForms();
    bindBranchToggles();
    bindCategoryFilter();
  }
})();
</script>
