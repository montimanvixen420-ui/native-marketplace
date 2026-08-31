<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="pos-workspace flex-1 flex overflow-hidden">

    <!-- Product grid / catalog -->
    <div class="pos-catalog flex-1 px-6 py-6 overflow-y-auto">
      <!-- Top bar -->
      <div class="flex items-center justify-between mb-5">
        <div>
          <p class="text-xs text-gray-400 dark:text-gray-400 mb-1">Menu <span class="mx-1">/</span> <span class="text-teal-600 dark:text-teal-400 font-medium">Point of Sale</span></p>
          <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 text-teal-600 dark:text-teal-400"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/></svg>
            Point of Sale
          </h1>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" class="w-9 h-9 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 hover:border-teal-600 transition-colors">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4"><path d="M4 12v7a1 1 0 001 1h14a1 1 0 001-1v-7"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></svg>
          </button>
          <button type="button" class="relative w-9 h-9 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 hover:border-teal-600 transition-colors">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
            <span id="pos-notif-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[9px] font-semibold items-center justify-center"></span>
          </button>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl p-4 mb-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-display font-semibold text-base text-gray-900 dark:text-white">List Product</h2>
          <div class="flex items-center gap-2">
            <button type="button" onclick="Swal.fire({icon:'info', title:'Coming soon', text:'Barcode scanning needs camera/hardware support — coming soon!'})" class="flex items-center gap-2 text-xs font-medium px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-gray-300 hover:border-teal-600 dark:hover:border-teal-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4"><path d="M3 7V5a2 2 0 012-2h2"/><path d="M17 3h2a2 2 0 012 2v2"/><path d="M21 17v2a2 2 0 01-2 2h-2"/><path d="M7 21H5a2 2 0 01-2-2v-2"/><line x1="7" y1="8" x2="7" y2="16"/><line x1="11" y1="8" x2="11" y2="16"/><line x1="14" y1="8" x2="14" y2="16"/><line x1="17" y1="8" x2="17" y2="16"/></svg>
              Scan Barcode
            </button>
            <div class="relative">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" id="pos-search-input" oninput="posApp_applyFilters()" placeholder="Search" class="pl-9 pr-4 py-2 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 w-48">
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between flex-wrap gap-2">
          <div id="pos-category-pills" class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="posApp_setCategory('', this)" data-category="" class="pos-pill text-xs font-semibold px-3 py-1.5 rounded-full bg-teal-600 text-white transition-colors">All Product</button>
            <?php foreach ($categories as $cat): ?>
              <button type="button" onclick="posApp_setCategory('<?= htmlspecialchars(addslashes($cat)) ?>', this)" data-category="<?= htmlspecialchars($cat) ?>" class="pos-pill text-xs font-medium px-3 py-1.5 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                <?= htmlspecialchars(ucfirst($cat)) ?>
              </button>
            <?php endforeach; ?>
          </div>

          <select id="pos-sort-select" onchange="posApp_applyFilters()" class="text-xs font-medium rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="newest">Newest</option>
            <option value="price-low">Price: Low to High</option>
            <option value="price-high">Price: High to Low</option>
            <option value="name">Name A–Z</option>
          </select>
        </div>
      </div>

      <?php if (empty($products)): ?>
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl p-8 text-center shadow-sm">
          <p class="text-gray-500 dark:text-gray-400 text-sm">No products are in Seller POS yet.</p>
          <?php if (empty($branchName)): ?>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Transfer an existing product from Seller Inventory—do not create it again.</p>
            <a href="/admin/inventory" style="background-color: #059669 !important; color: #ffffff !important;" class="mt-4 inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold shadow-md hover:opacity-90"><i data-lucide="package-plus" class="mr-1.5 h-4 w-4"></i>Add from Inventory</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div id="pos-product-grid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
          <?php foreach ($products as $product): ?>
            <button
              type="button"
              onclick='posApp_addToCart(<?= json_encode([
                  "id" => (int) $product["id"],
                  "name" => $product["name"],
                  "price" => (float) $product["price"],
                  "stock" => (int) $product["stock"],
                  "image" => $product["image_url"],
              ]) ?>)'
              class="pos-product-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl p-3 text-left shadow-sm hover:border-teal-500 dark:hover:border-teal-400 transition-colors cursor-pointer"
              data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
              data-category="<?= htmlspecialchars($product['category'] ?? '') ?>"
              data-price="<?= (float) $product['price'] ?>"
              data-created="<?= htmlspecialchars($product['created_at']) ?>"
            >
              <div class="aspect-square bg-gray-100 dark:bg-slate-900 rounded-xl mb-3 overflow-hidden flex items-center justify-center">
                <?php if (!empty($product['image_url'])): ?>
                  <img src="/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                  <span class="text-gray-400 dark:text-gray-500 text-xs">No image</span>
                <?php endif; ?>
              </div>
              <?php if (!empty($product['category'])): ?>
                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800 mb-1.5"><?= htmlspecialchars(ucfirst($product['category'])) ?></span>
              <?php endif; ?>
              <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($product['name']) ?></p>
              <div class="flex items-center justify-between mt-1">
                <span class="text-sm font-bold text-teal-600 dark:text-teal-400">₱<?= number_format((float) $product['price'], 2) ?></span>
                <span class="text-xs text-gray-500 dark:text-gray-400"><?= (int) $product['stock'] ?> left</span>
              </div>
            </button>
          <?php endforeach; ?>
        </div>
        <p id="pos-no-results" class="hidden text-center text-sm text-gray-500 dark:text-gray-400 py-10">No products match your search.</p>
      <?php endif; ?>
    </div>

    <!-- Cart panel -->
    <div class="pos-cart w-96 bg-white dark:bg-slate-800 border-l border-gray-200 dark:border-slate-700 flex flex-col shrink-0 transition-colors shadow-sm">

      <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
        <h2 class="font-display font-semibold text-base text-gray-900 dark:text-white">Cart details</h2>
        <span id="pos-item-count" class="text-xs font-semibold text-teal-600 dark:text-teal-400">0 items</span>
      </div>

      <!-- Customer -->
      <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 relative">
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">Customer</label>

        <div id="pos-customer-selected" class="hidden items-center justify-between bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 rounded-xl px-3 py-2 text-sm font-medium">
          <span id="pos-customer-selected-name"></span>
          <button type="button" onclick="posApp_clearCustomer()" class="text-teal-700 dark:text-teal-300 hover:opacity-75 font-bold">&times;</button>
        </div>

        <div id="pos-customer-search-wrap">
          <input
            type="text"
            id="pos-customer-search"
            placeholder="Search registered customer, or leave blank for walk-in"
            class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
            oninput="posApp_searchCustomers(this.value)"
          >
          <div id="pos-customer-results" class="hidden absolute left-5 right-5 mt-1 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl shadow-lg z-10 max-h-40 overflow-y-auto"></div>
        </div>

        <input
          type="text"
          id="pos-walkin-name"
          placeholder="Walk-in customer name (optional)"
          class="w-full mt-2 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
        >
      </div>

      <!-- Cart items -->
      <div id="pos-cart-items" class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-slate-700">
        <p id="pos-cart-empty" class="text-center text-sm text-gray-400 dark:text-gray-500 px-5 py-10">Cart is empty</p>
      </div>

      <!-- Payment + total -->
      <div class="border-t border-gray-200 dark:border-slate-700 px-5 py-4">
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">Payment method</label>
        <select id="pos-payment-method" class="w-full rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-gray-900 dark:text-white mb-4 focus:outline-none focus:ring-2 focus:ring-teal-500">
          <option value="cash">Cash</option>
          <option value="gcash">GCash</option>
          <option value="card">Card</option>
          <option value="other">Other</option>
        </select>

        <div class="flex items-center justify-between mb-4">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total</span>
          <span id="pos-total" class="text-xl font-display font-bold text-gray-900 dark:text-white">₱0.00</span>
        </div>

        <div id="pos-error" class="hidden bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-xl px-3 py-2 text-xs mb-3"></div>

        <!-- CHARGE BUTTON: Solid emerald green background and white text -->
        <button
          type="button"
          onclick="posApp_checkout()"
          id="pos-checkout-btn"
          style="background-color: #059669 !important; color: #ffffff !important;"
          class="w-full font-bold text-sm rounded-xl py-3 shadow-md hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer"
          disabled
        >
          Charge
        </button>
      </div>
    </div>

  </main>
</div>

<script>
let posCart = [];
let posSelectedCustomer = null;
let posSearchTimer = null;
let posActiveCategory = '';

function posApp_setCategory(category, btnEl) {
  posActiveCategory = category;

  document.querySelectorAll('.pos-pill').forEach(pill => {
    pill.classList.remove('bg-teal-600', 'text-white');
    pill.classList.add('text-gray-600', 'dark:text-gray-300');
  });
  btnEl.classList.add('bg-teal-600', 'text-white');
  btnEl.classList.remove('text-gray-600', 'dark:text-gray-300');

  posApp_applyFilters();
}

function posApp_applyFilters() {
  const searchTerm = (document.getElementById('pos-search-input')?.value || '').toLowerCase().trim();
  const sortBy = document.getElementById('pos-sort-select')?.value || 'newest';
  const grid = document.getElementById('pos-product-grid');
  if (!grid) return;

  const cards = Array.from(grid.querySelectorAll('.pos-product-card'));
  let visibleCount = 0;

  cards.forEach(card => {
    const matchesSearch = !searchTerm || card.dataset.name.includes(searchTerm);
    const matchesCategory = !posActiveCategory || card.dataset.category === posActiveCategory;
    const isVisible = matchesSearch && matchesCategory;
    card.style.display = isVisible ? '' : 'none';
    if (isVisible) visibleCount++;
  });

  document.getElementById('pos-no-results')?.classList.toggle('hidden', visibleCount > 0);

  const sorted = cards.sort((a, b) => {
    if (sortBy === 'price-low') return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
    if (sortBy === 'price-high') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
    if (sortBy === 'name') return a.dataset.name.localeCompare(b.dataset.name);
    return new Date(b.dataset.created) - new Date(a.dataset.created);
  });

  sorted.forEach(card => grid.appendChild(card));
}

function posApp_showStockLimitToast(productName, stock) {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'warning',
    title: `Only ${stock} left in stock — ${productName}`,
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true
  });
}

function posApp_addToCart(product) {
  const existing = posCart.find(item => item.id === product.id);

  if (existing) {
    if (existing.quantity < product.stock) {
      existing.quantity += 1;
    } else {
      posApp_showStockLimitToast(product.name, product.stock);
      return;
    }
  } else {
    posCart.push({ ...product, quantity: 1 });
  }

  posApp_renderCart();
}

function posApp_changeQty(productId, delta) {
  const item = posCart.find(i => i.id === productId);
  if (!item) return;

  if (delta > 0 && item.quantity >= item.stock) {
    posApp_showStockLimitToast(item.name, item.stock);
    return;
  }

  item.quantity += delta;

  if (item.quantity <= 0) {
    posCart = posCart.filter(i => i.id !== productId);
  } else if (item.quantity > item.stock) {
    item.quantity = item.stock;
  }

  posApp_renderCart();
}

function posApp_renderCart() {
  const container = document.getElementById('pos-cart-items');
  const totalEl = document.getElementById('pos-total');
  const checkoutBtn = document.getElementById('pos-checkout-btn');
  const countEl = document.getElementById('pos-item-count');

  container.innerHTML = '';

  const itemCount = posCart.reduce((sum, i) => sum + i.quantity, 0);
  countEl.textContent = itemCount + (itemCount === 1 ? ' item' : ' items');

  if (posCart.length === 0) {
    const emptyMsg = document.createElement('p');
    emptyMsg.id = 'pos-cart-empty';
    emptyMsg.className = 'text-center text-sm text-gray-400 dark:text-gray-500 px-5 py-10';
    emptyMsg.textContent = 'Cart is empty';
    container.appendChild(emptyMsg);
    checkoutBtn.disabled = true;
    totalEl.textContent = '₱0.00';
    return;
  }

  let total = 0;

  posCart.forEach(item => {
    const subtotal = item.price * item.quantity;
    total += subtotal;

    const row = document.createElement('div');
    row.className = 'px-5 py-3 flex items-center justify-between gap-3';
    row.innerHTML = `
      <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">${item.name}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">₱${item.price.toFixed(2)} each</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="posApp_changeQty(${item.id}, -1)" class="w-6 h-6 rounded-full border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer flex items-center justify-center text-xs font-bold">−</button>
        <span class="text-sm w-4 text-center text-gray-900 dark:text-white font-medium">${item.quantity}</span>
        <button onclick="posApp_changeQty(${item.id}, 1)" class="w-6 h-6 rounded-full border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer flex items-center justify-center text-xs font-bold">+</button>
      </div>
    `;
    container.appendChild(row);
  });

  totalEl.textContent = '₱' + total.toFixed(2);
  checkoutBtn.disabled = false;
}

function posApp_searchCustomers(query) {
  clearTimeout(posSearchTimer);
  const resultsBox = document.getElementById('pos-customer-results');

  if (query.trim() === '') {
    resultsBox.classList.add('hidden');
    return;
  }

  posSearchTimer = setTimeout(() => {
    fetch('/pos/customers?q=' + encodeURIComponent(query))
      .then(res => res.json())
      .then(customers => {
        if (customers.length === 0) {
          resultsBox.innerHTML = '<p class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500">No matches found</p>';
        } else {
          resultsBox.innerHTML = customers.map(c => `
            <button type="button" onclick='posApp_selectCustomer(${JSON.stringify(c)})' class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
              <span class="block font-medium text-gray-900 dark:text-white">${c.name}</span>
              <span class="block text-xs text-gray-500 dark:text-gray-400">${c.email}</span>
            </button>
          `).join('');
        }
        resultsBox.classList.remove('hidden');
      });
  }, 250);
}

function posApp_selectCustomer(customer) {
  posSelectedCustomer = customer;
  document.getElementById('pos-customer-search-wrap').classList.add('hidden');
  document.getElementById('pos-customer-selected').classList.remove('hidden');
  document.getElementById('pos-customer-selected').classList.add('flex');
  document.getElementById('pos-customer-selected-name').textContent = customer.name;
  document.getElementById('pos-customer-results').classList.add('hidden');
}

function posApp_clearCustomer() {
  posSelectedCustomer = null;
  document.getElementById('pos-customer-search-wrap').classList.remove('hidden');
  document.getElementById('pos-customer-selected').classList.add('hidden');
  document.getElementById('pos-customer-search').value = '';
}

function posApp_checkout() {
  const errorBox = document.getElementById('pos-error');
  errorBox.classList.add('hidden');

  if (posCart.length === 0) return;

  const payload = {
    items: posCart.map(i => ({ product_id: i.id, quantity: i.quantity })),
    payment_method: document.getElementById('pos-payment-method').value,
    customer_id: posSelectedCustomer ? posSelectedCustomer.id : null,
    customer_name: document.getElementById('pos-walkin-name').value.trim(),
  };

  const btn = document.getElementById('pos-checkout-btn');
  btn.disabled = true;
  btn.textContent = 'Processing...';

  fetch('/pos/checkout', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  })
    .then(res => res.json())
    .then(result => {
      if (result.success) {
        posCart = [];
        posApp_clearCustomer();
        document.getElementById('pos-walkin-name').value = '';
        posApp_renderCart();
        Swal.fire({ icon: 'success', title: 'Sale completed!', text: 'Order #' + result.order_id, timer: 3000, timerProgressBar: true });
      } else {
        errorBox.textContent = result.error || 'Something went wrong.';
        errorBox.classList.remove('hidden');
      }
    })
    .catch((err) => {
      console.error('POS checkout error:', err);
      errorBox.textContent = 'Network error. Please try again. (See console for details)';
      errorBox.classList.remove('hidden');
    })
    .finally(() => {
      btn.disabled = posCart.length === 0;
      btn.textContent = 'Charge';
    });
}
</script>