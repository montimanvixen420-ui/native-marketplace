<?php
$isEdit = $mode === 'edit';
$product = $product ?? [];
$variants = $variants ?? ($product['variants'] ?? []);
$branches = $branches ?? [];
$selectedBranchIds = $selectedBranchIds ?? [];
$managerMode = $managerMode ?? false;
$formAction = $formAction ?? ($isEdit ? '/products/update' : '/products/create');
$backHref = $backHref ?? '/products';
$formTitle = $formTitle ?? ($isEdit ? 'Edit product' : 'Add a new product');
$sourceLabel = $sourceLabel ?? 'Seller inventory stock';
$sourceHelp = $sourceHelp ?? 'Choose stock already received into your Seller Inventory. The product details below will be used for your Seller POS listing.';
?>

<div class="flex min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="max-w-3xl mx-auto">
      
      <!-- Back Link -->
      <a href="<?= htmlspecialchars($backHref) ?>" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors mb-3">
        &larr; Back to products
      </a>

      <!-- Page Header -->
      <h1 class="font-display font-semibold text-2xl text-gray-900 dark:text-white mb-6">
        <?= htmlspecialchars($formTitle) ?>
      </h1>

      <!-- Main Form Card -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 sm:p-8 shadow-sm">

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
          <div class="mb-6 text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <form
          id="productForm"
          method="POST"
          action="<?= htmlspecialchars($formAction) ?>"
          enctype="multipart/form-data"
          class="space-y-5"
        >

          <?php if ($isEdit): ?>
            <input
              type="hidden"
              name="id"
              value="<?= htmlspecialchars($product['id']) ?>"
            >
          <?php endif; ?>

          <!-- Inventory Source Select (Add Mode Only) -->
          <?php if (!$isEdit): ?>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                <?= htmlspecialchars($sourceLabel) ?>
              </label>

              <select
                id="inventory_source_product_id"
                name="inventory_source_product_id"
                required
                class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
              >
                <option value="">Select inventory stock...</option>

                <?php foreach (($availableStocks ?? []) as $source): ?>
                  <option
                    value="<?= (int)$source['id'] ?>"
                    data-name="<?= htmlspecialchars($source['name'], ENT_QUOTES) ?>"
                    data-quantity="<?= (int)$source['stock'] ?>"
                    data-size="<?= htmlspecialchars($source['size'] ?? '', ENT_QUOTES) ?>"
                    data-color="<?= htmlspecialchars($source['color'] ?? '', ENT_QUOTES) ?>"
                    <?= (int)($product['inventory_source_product_id'] ?? 0) === (int)$source['id'] ? 'selected' : '' ?>
                  >
                    <?= htmlspecialchars($source['name']) ?><?= (!empty($source['size']) || !empty($source['color'])) ? ' — '.htmlspecialchars(trim(($source['size'] ?? '').' / '.($source['color'] ?? ''),' /')) : '' ?>
                    — <?= (int)$source['stock'] ?> available in inventory
                  </option>
                <?php endforeach; ?>
              </select>

              <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                <?= htmlspecialchars($sourceHelp) ?>
              </p>
              <?php if ($managerMode): ?><input type="hidden" name="branch_source_variant_size" id="branch_source_variant_size"><input type="hidden" name="branch_source_variant_color" id="branch_source_variant_color"><?php endif; ?>
            </div>
          <?php endif; ?>


          <!-- Product Name -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
              Product name
            </label>

            <input
              id="product_name"
              type="text"
              name="name"
              required
              value="<?= htmlspecialchars($product['name'] ?? '') ?>"
              class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"
            >
          </div>


          <!-- Description -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
              Description
            </label>

            <textarea
              name="description"
              rows="3"
              class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"
            ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
          </div>


          <!-- Fit Information & Size Guide -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                Fit information
              </label>

              <input
                type="text"
                name="fit_information"
                value="<?= htmlspecialchars($product['fit_information'] ?? '') ?>"
                placeholder="e.g. True to size, stretchy fabric"
                class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
              >
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                Size guide
              </label>

              <textarea
                name="size_guide"
                rows="2"
                placeholder="e.g. S: Bust 84cm | M: Bust 88cm"
                class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
              ><?= htmlspecialchars($product['size_guide'] ?? '') ?></textarea>
            </div>
          </div>


          <!-- Price & Stock Quantity -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                Price (₱)
              </label>

              <input
                type="number"
                step="0.01"
                min="0"
                name="price"
                required
                value="<?= htmlspecialchars($product['price'] ?? '') ?>"
                class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"
              >
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                Stock quantity
              </label>

              <input
                id="product_stock"
                type="number"
                min="0"
                name="stock"
                required
                value="<?= htmlspecialchars($product['stock'] ?? '') ?>"
                class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"
              >
            </div>

          </div>


          <!-- SIZE & COLOR VARIANTS CARD -->
          <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-4 bg-gray-50/50 dark:bg-slate-900/50">

            <div class="flex flex-wrap justify-between items-center gap-2 mb-3">

              <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                  Size &amp; color variants
                </p>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Optional. Add only what you sell&mdash;one Small variant is valid.
                </p>
              </div>

              <button
                type="button"
                id="addVariant"
                class="text-xs font-bold text-teal-600 dark:text-teal-400 hover:underline cursor-pointer"
              >
                + Add Variant
              </button>

            </div>


            <div id="variantRows" class="space-y-2.5">

              <?php foreach ($variants as $i => $variant): ?>

                <div class="variant-row grid grid-cols-1 sm:grid-cols-[110px_minmax(0,1fr)_minmax(0,1fr)_90px_auto] gap-2 items-center">

                  <!-- SIZE -->
                  <select
                    name="variants[<?= $i ?>][size]"
                    class="w-full min-w-0 border border-gray-300 dark:border-slate-600 rounded-lg px-2.5 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
                  >
                    <option value="">Select Size</option>
                    <option value="Small" <?= (($variant['size'] ?? '') === 'Small') ? 'selected' : '' ?>>Small</option>
                    <option value="Medium" <?= (($variant['size'] ?? '') === 'Medium') ? 'selected' : '' ?>>Medium</option>
                    <option value="Large" <?= (($variant['size'] ?? '') === 'Large') ? 'selected' : '' ?>>Large</option>
                    <option value="XL" <?= (($variant['size'] ?? '') === 'XL') ? 'selected' : '' ?>>XL</option>
                    <option value="XXL" <?= (($variant['size'] ?? '') === 'XXL') ? 'selected' : '' ?>>XXL</option>
                  </select>

                  <!-- COLOR -->
                  <input
                    type="text"
                    name="variants[<?= $i ?>][color]"
                    value="<?= htmlspecialchars(($variant['color'] ?? '') === 'N/A' ? '' : ($variant['color'] ?? '')) ?>"
                    placeholder="Color (optional)"
                    class="w-full min-w-0 border border-gray-300 dark:border-slate-600 rounded-lg px-2.5 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
                  >

                  <!-- SKU -->
                  <input
                    type="text"
                    name="variants[<?= $i ?>][sku]"
                    value="<?= htmlspecialchars($variant['sku'] ?? '') ?>"
                    placeholder="SKU (optional)"
                    class="w-full min-w-0 border border-gray-300 dark:border-slate-600 rounded-lg px-2.5 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
                  >

                  <!-- STOCK -->
                  <input
                    type="number"
                    min="0"
                    name="variants[<?= $i ?>][stock]"
                    value="<?= htmlspecialchars($variant['stock'] ?? '') ?>"
                    placeholder="Stock"
                    class="variant-stock w-full min-w-0 border border-gray-300 dark:border-slate-600 rounded-lg px-2.5 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
                  >

                  <!-- REMOVE -->
                  <button
                    type="button"
                    class="remove-variant whitespace-nowrap text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline px-1 cursor-pointer"
                  >
                    Remove
                  </button>

                </div>

              <?php endforeach; ?>

            </div>

            <p id="variantStockHelp" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
              The total of all variant stocks must not exceed the product stock.
            </p>

          </div>


          <!-- Category & Status -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                Category
              </label>

              <?php
                $existingCategories = $categories ?? [];
                $currentCategory = $product['category'] ?? '';
                $isNewCategory = $currentCategory !== '' && !in_array($currentCategory, $existingCategories, true);
              ?>
              <select
                id="category_select"
                onchange="handleCategorySelect(this)"
                class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"
              >
                <option value="">No category</option>
                <?php foreach ($existingCategories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>" <?= $currentCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
                <option value="__new__" <?= $isNewCategory ? 'selected' : '' ?>>+ Add new category…</option>
              </select>

              <input
                type="text"
                id="category_new_input"
                placeholder="Type the new category name"
                value="<?= $isNewCategory ? htmlspecialchars($currentCategory) : '' ?>"
                class="w-full mt-2 border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"
                style="<?= $isNewCategory ? '' : 'display:none' ?>"
              >

              <script>
                function handleCategorySelect(sel) {
                  var newInput = document.getElementById('category_new_input');
                  if (sel.value === '__new__') {
                    newInput.style.display = 'block';
                    newInput.name = 'category';
                    sel.removeAttribute('name');
                  } else {
                    newInput.style.display = 'none';
                    newInput.removeAttribute('name');
                    sel.name = 'category';
                  }
                }
                // Set the correct initial name attribute on load (edit mode may start on "+ Add new category…").
                handleCategorySelect(document.getElementById('category_select'));
              </script>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                Status
              </label>

              <?php $currentStatus = $product['status'] ?? 'active'; ?>

              <select
                name="status"
                class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"
              >
                <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>
                  Available
                </option>
                <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>
                  Hidden
                </option>
              </select>

            </div>

          </div>


          <!-- Product Image -->
          <div>

            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
              Product image (PNG or JPEG)
            </label>

            <?php if ($isEdit && !empty($product['image_url'])): ?>
              <div class="flex items-center gap-3 mb-2">
                <img
                  src="/<?= htmlspecialchars($product['image_url']) ?>"
                  alt=""
                  class="w-14 h-14 rounded-lg object-cover border border-gray-200 dark:border-slate-700"
                >
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Current image — upload a new file below to replace it.
                </p>
              </div>
            <?php endif; ?>

            <input
              type="file"
              name="image"
              accept=".png,.jpg,.jpeg,image/png,image/jpeg"
              <?= $isEdit ? '' : 'required' ?>
              class="w-full border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-lg px-3.5 py-2 text-sm text-gray-900 dark:text-gray-100 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 dark:file:bg-slate-800 dark:file:text-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 transition"
            >

            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
              PNG or JPEG only, max 5MB.
            </p>

          </div>


          <!-- Submit Action Button (Explicit Solid Green Color) -->
          <div class="pt-2">
            <button
              type="submit"
              style="background-color: #059669 !important; color: #ffffff !important;"
              class="w-full rounded-lg py-3 text-sm font-bold shadow-md hover:opacity-90 active:scale-95 transition-all cursor-pointer"
            >
              <?= $isEdit ? 'Save changes' : 'Add product' ?>
            </button>
          </div>

        </form>

      </div>
    </div>
  </main>

</div>


<script>
const stockSource = document.getElementById('inventory_source_product_id');
const productStock = document.getElementById('product_stock');
const productName = document.getElementById('product_name');
const variantRows = document.getElementById('variantRows');
const variantHelp = document.getElementById('variantStockHelp');
const branchSourceSize = document.getElementById('branch_source_variant_size');
const branchSourceColor = document.getElementById('branch_source_variant_color');

const deliveredStockLimit = Number(<?= json_encode($sourceStockLimit ?? 0) ?>);

function sourceLimit() {
    const option = stockSource && stockSource.selectedOptions[0];
    return option && option.value
        ? Number(option.dataset.quantity)
        : (deliveredStockLimit || Number(productStock.value || 0));
}

function enforceProductStockLimit() {
    const limit = sourceLimit();
    const entered = Number(productStock.value || 0);
    if (limit > 0 && entered > limit) productStock.value = limit;
}

function updateVariantLimit() {
    const limit = sourceLimit();
    const stocks = [...variantRows.querySelectorAll('.variant-stock')];
    const total = stocks.reduce((sum, input) => sum + (Number(input.value) || 0), 0);

    stocks.forEach(input => {
        if (limit > 0) {
            input.max = limit;
        } else {
            input.removeAttribute('max');
        }
    });

    const tooHigh = limit >= 0 && stocks.length && total > limit;

    if (stocks.length) {
        variantHelp.textContent = `Variant total: ${total}. Available product stock: ${limit}.`;
    } else {
        variantHelp.textContent = 'The total of all variant stocks must not exceed the product stock.';
    }

    variantHelp.className = 'mt-2 text-xs ' + (tooHigh ? 'text-red-600 font-bold' : 'text-gray-500 dark:text-gray-400');

    if (stocks.length) {
        productStock.value = total;
    }

    productStock.setCustomValidity(
        tooHigh ? 'Total variant stock cannot exceed the delivered stock.' : ''
    );
}

let variantIndex = <?= count($variants) ?>;

function addVariantRow() {
    const idx = variantIndex++;
    variantRows.insertAdjacentHTML(
        'beforeend',
        `
        <div class="variant-row grid grid-cols-1 sm:grid-cols-[110px_minmax(0,1fr)_minmax(0,1fr)_90px_auto] gap-2 items-center">
            <!-- SIZE -->
            <select
                name="variants[${idx}][size]"
                class="w-full min-w-0 border border-gray-300 dark:border-slate-600 rounded-lg px-2.5 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
            >
                <option value="">Select Size</option>
                <option value="Small">Small</option>
                <option value="Medium">Medium</option>
                <option value="Large">Large</option>
                <option value="XL">XL</option>
                <option value="XXL">XXL</option>
            </select>

            <!-- COLOR -->
            <input
                type="text"
                name="variants[${idx}][color]"
                placeholder="Color (optional)"
                class="w-full min-w-0 border border-gray-300 dark:border-slate-600 rounded-lg px-2.5 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
            >

            <!-- SKU -->
            <input
                type="text"
                name="variants[${idx}][sku]"
                placeholder="SKU (optional)"
                class="w-full min-w-0 border border-gray-300 dark:border-slate-600 rounded-lg px-2.5 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
            >

            <!-- STOCK -->
            <input
                type="number"
                min="0"
                name="variants[${idx}][stock]"
                placeholder="Stock"
                class="variant-stock w-full min-w-0 border border-gray-300 dark:border-slate-600 rounded-lg px-2.5 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
            >

            <!-- REMOVE -->
            <button
                type="button"
                class="remove-variant whitespace-nowrap text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline px-1 cursor-pointer"
            >
                Remove
            </button>
        </div>
        `
    );
    updateVariantLimit();
}

document.getElementById('addVariant').onclick = addVariantRow;

variantRows.addEventListener('click', event => {
    if (event.target.classList.contains('remove-variant')) {
        event.target.closest('.variant-row').remove();
        updateVariantLimit();
    }
});

variantRows.addEventListener('input', updateVariantLimit);

productStock.addEventListener('input', function () {
    enforceProductStockLimit();
    updateVariantLimit();
});

if (stockSource) {
    stockSource.addEventListener('change', function () {
        const option = this.selectedOptions[0];

        if (!option.value) {
            productName.value = '';
            productStock.value = '';
            productStock.removeAttribute('max');
            if (branchSourceSize) branchSourceSize.value = '';
            if (branchSourceColor) branchSourceColor.value = '';
            updateVariantLimit();
            return;
        }

        productName.value = option.dataset.name;
        productStock.value = option.dataset.quantity;
        productStock.max = option.dataset.quantity;

        if (branchSourceSize) branchSourceSize.value = option.dataset.size || '';
        if (branchSourceColor) branchSourceColor.value = option.dataset.color || '';

        updateVariantLimit();
    });
}

if (stockSource && stockSource.value) {
    const initialSource = stockSource.selectedOptions[0];
    productStock.max = initialSource.dataset.quantity;
    if (branchSourceSize) branchSourceSize.value = initialSource.dataset.size || '';
    if (branchSourceColor) branchSourceColor.value = initialSource.dataset.color || '';
}

enforceProductStockLimit();
updateVariantLimit();
</script>

<script>
(function () {
  function bindProductFormConfirm() {
    var form = document.getElementById('productForm');
    if (!form || form.dataset.swalBound) return;
    form.dataset.swalBound = '1';

    form.addEventListener('submit', function (e) {
      if (form.dataset.confirmed === '1') return;
      if (!form.checkValidity()) return;

      e.preventDefault();

      var isEdit = <?= json_encode($isEdit) ?>;

      Swal.fire({
        title: isEdit ? 'Save changes to this product?' : 'Add this product?',
        text: isEdit
          ? 'This will update the listing your customers see.'
          : 'This will be submitted for moderation review before it goes live.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: isEdit ? 'Yes, save' : 'Yes, add product',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
      }).then(function (result) {
        if (result.isConfirmed) {
          form.dataset.confirmed = '1';
          form.submit();
        }
      });
    });
  }

  if (typeof Swal === 'undefined') {
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    s.onload = bindProductFormConfirm;
    document.head.appendChild(s);
  } else {
    bindProductFormConfirm();
  }
})();
</script>