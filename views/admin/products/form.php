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

<div class="flex min-h-screen bg-gray-50">

  <?php require __DIR__ . '/../../partials/admin-sidebar.php'; ?>

  <main class="flex-1 px-8 py-8">
    <div class="max-w-2xl">
      <a href="<?= htmlspecialchars($backHref) ?>" class="text-sm text-gray-400 hover:text-teal">
        &larr; Back to products
      </a>

      <h1 class="font-display font-semibold text-2xl text-gray-900 mt-2 mb-6">
        <?= htmlspecialchars($formTitle) ?>
      </h1>

      <div class="bg-white border border-gray-200 rounded-lg p-7">

        <?php if (!empty($error)): ?>
          <div class="mb-5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form
          id="productForm"
          method="POST"
          action="<?= htmlspecialchars($formAction) ?>"
          enctype="multipart/form-data"
          class="space-y-4"
        >

          <?php if ($isEdit): ?>
            <input
              type="hidden"
              name="id"
              value="<?= htmlspecialchars($product['id']) ?>"
            >
          <?php endif; ?>

          <?php if (!$isEdit): ?>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                <?= htmlspecialchars($sourceLabel) ?>
              </label>

              <select
                id="inventory_source_product_id"
                name="inventory_source_product_id"
                required
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm"
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

              <p class="mt-1 text-xs text-gray-400">
                <?= htmlspecialchars($sourceHelp) ?>
              </p>
              <?php if ($managerMode): ?><input type="hidden" name="branch_source_variant_size" id="branch_source_variant_size"><input type="hidden" name="branch_source_variant_color" id="branch_source_variant_color"><?php endif; ?>
            </div>
          <?php endif; ?>


          <!-- Product Name -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
              Product name
            </label>

            <input
              id="product_name"
              type="text"
              name="name"
              required
              value="<?= htmlspecialchars($product['name'] ?? '') ?>"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition"
            >
          </div>


          <!-- Description -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
              Description
            </label>

            <textarea
              name="description"
              rows="3"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition"
            ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
          </div>


          <!-- Fit Information -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
              Fit information
            </label>

            <input
              type="text"
              name="fit_information"
              value="<?= htmlspecialchars($product['fit_information'] ?? '') ?>"
              placeholder="e.g. True to size, stretchy fabric"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm"
            >
          </div>


          <!-- Size Guide -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
              Size guide
            </label>

            <textarea
              name="size_guide"
              rows="3"
              placeholder="e.g. S: Bust 84cm, Waist 66cm | M: Bust 88cm, Waist 70cm"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm"
            ><?= htmlspecialchars($product['size_guide'] ?? '') ?></textarea>

            <p class="text-xs text-gray-400 mt-1">
              Add measurements for each size in centimeters.
            </p>
          </div>


          <!-- Price / Stock -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Price (₱)
              </label>

              <input
                type="number"
                step="0.01"
                min="0"
                name="price"
                required
                value="<?= htmlspecialchars($product['price'] ?? '') ?>"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition"
              >
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Stock quantity
              </label>

              <input
                id="product_stock"
                type="number"
                min="0"
                name="stock"
                required
                value="<?= htmlspecialchars($product['stock'] ?? '') ?>"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition"
              >
            </div>

          </div>


          <!-- SIZE & COLOR VARIANTS -->
          <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">

            <div class="flex flex-wrap justify-between items-center gap-2 mb-3">

              <div>
                <p class="text-sm font-semibold text-gray-800">
                  Size &amp; color variants
                </p>

                <p class="text-xs text-gray-500">
                  Optional. Add only what you sell&mdash;one Small variant is valid.
                </p>
              </div>

              <button
                type="button"
                id="addVariant"
                class="text-xs font-semibold text-teal hover:underline"
              >
                + Add
              </button>

            </div>


            <div id="variantRows" class="space-y-2">

              <?php foreach ($variants as $i => $variant): ?>

                <div class="variant-row grid grid-cols-1 sm:grid-cols-[100px_minmax(0,1fr)_minmax(0,1fr)_90px_auto] gap-2 items-center">

                  <!-- SIZE -->
                  <select
                    name="variants[<?= $i ?>][size]"
                    class="w-full min-w-0 border border-gray-300 rounded-lg px-2.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
                  >
                    <option value="">
                      Select Size
                    </option>

                    <option
                      value="Small"
                      <?= (($variant['size'] ?? '') === 'Small') ? 'selected' : '' ?>
                    >
                      Small
                    </option>

                    <option
                      value="Medium"
                      <?= (($variant['size'] ?? '') === 'Medium') ? 'selected' : '' ?>
                    >
                      Medium
                    </option>

                    <option
                      value="Large"
                      <?= (($variant['size'] ?? '') === 'Large') ? 'selected' : '' ?>
                    >
                      Large
                    </option>

                    <option
                      value="XL"
                      <?= (($variant['size'] ?? '') === 'XL') ? 'selected' : '' ?>
                    >
                      XL
                    </option>

                    <option
                      value="XXL"
                      <?= (($variant['size'] ?? '') === 'XXL') ? 'selected' : '' ?>
                    >
                      XXL
                    </option>
                  </select>


                  <!-- COLOR -->
                  <input
                    type="text"
                    name="variants[<?= $i ?>][color]"
                    value="<?= htmlspecialchars(($variant['color'] ?? '') === 'N/A' ? '' : ($variant['color'] ?? '')) ?>"
                    placeholder="Color (optional)"
                    class="w-full min-w-0 border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
                  >


                  <!-- SKU -->
                  <input
                    type="text"
                    name="variants[<?= $i ?>][sku]"
                    value="<?= htmlspecialchars($variant['sku'] ?? '') ?>"
                    placeholder="SKU (optional)"
                    class="w-full min-w-0 border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
                  >


                  <!-- STOCK -->
                  <input
                    type="number"
                    min="0"
                    name="variants[<?= $i ?>][stock]"
                    value="<?= htmlspecialchars($variant['stock'] ?? '') ?>"
                    placeholder="Stock"
                    class="variant-stock w-full min-w-0 border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
                  >


                  <!-- REMOVE -->
                  <button
                    type="button"
                    class="remove-variant whitespace-nowrap text-xs font-semibold text-red-600 hover:text-red-800 px-1"
                  >
                    Remove
                  </button>

                </div>

              <?php endforeach; ?>

            </div>


            <p id="variantStockHelp" class="mt-2 text-xs text-gray-500">
              The total of all variant stocks must not exceed the product stock.
            </p>

          </div>


          <!-- Category / Status -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Category
              </label>

              <input
                type="text"
                name="category"
                value="<?= htmlspecialchars($product['category'] ?? '') ?>"
                placeholder="e.g. Electronics"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition"
              >
            </div>


            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Status
              </label>

              <?php $currentStatus = $product['status'] ?? 'active'; ?>

              <select
                name="status"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition"
              >
                <option
                  value="active"
                  <?= $currentStatus === 'active' ? 'selected' : '' ?>
                >
                  Available
                </option>

                <option
                  value="inactive"
                  <?= $currentStatus === 'inactive' ? 'selected' : '' ?>
                >
                  Hidden
                </option>
              </select>

            </div>

          </div>


          <!-- Product Image -->
          <div>

            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
              Product image (PNG or JPEG)
            </label>

            <?php if ($isEdit && !empty($product['image_url'])): ?>

              <div class="flex items-center gap-3 mb-2">

                <img
                  src="/<?= htmlspecialchars($product['image_url']) ?>"
                  alt=""
                  class="w-14 h-14 rounded-lg object-cover border border-gray-200"
                >

                <p class="text-xs text-gray-500">
                  Current image — upload a new file below to replace it.
                </p>

              </div>

            <?php endif; ?>


            <input
              type="file"
              name="image"
              accept=".png,.jpg,.jpeg,image/png,image/jpeg"
              <?= $isEdit ? '' : 'required' ?>
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-teal-light file:text-teal focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition"
            >

            <p class="text-xs text-gray-400 mt-1">
              PNG or JPEG only, max 5MB.
            </p>

          </div>


          <!-- Submit -->
          <button
            type="submit"
            class="w-full bg-amber text-ink rounded-lg py-2.5 text-sm font-semibold hover:opacity-90 transition"
          >
            <?= $isEdit ? 'Save changes' : 'Add product' ?>
          </button>

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

const deliveredStockLimit = Number(
    <?= json_encode($sourceStockLimit ?? 0) ?>
);


function sourceLimit() {

    const option = stockSource && stockSource.selectedOptions[0];

    return option && option.value
        ? Number(option.dataset.quantity)
        : (
            deliveredStockLimit ||
            Number(productStock.value || 0)
        );
}


function enforceProductStockLimit() {
    const limit = sourceLimit();
    const entered = Number(productStock.value || 0);
    if (limit > 0 && entered > limit) productStock.value = limit;
}

function updateVariantLimit() {

    const limit = sourceLimit();

    const stocks = [
        ...variantRows.querySelectorAll('.variant-stock')
    ];

    const total = stocks.reduce(
        (sum, input) => sum + (Number(input.value) || 0),
        0
    );


    stocks.forEach(input => {

        if (limit > 0) {
            input.max = limit;
        } else {
            input.removeAttribute('max');
        }

    });


    const tooHigh =
        limit >= 0 &&
        stocks.length &&
        total > limit;


    if (stocks.length) {

        variantHelp.textContent =
            `Variant total: ${total}. Available product stock: ${limit}.`;

    } else {

        variantHelp.textContent =
            'The total of all variant stocks must not exceed the product stock.';

    }


    variantHelp.className =
        'mt-2 text-xs ' +
        (
            tooHigh
                ? 'text-red-600'
                : 'text-gray-500'
        );


    if (stocks.length) {
        productStock.value = total;
    }


    productStock.setCustomValidity(
        tooHigh
            ? 'Total variant stock cannot exceed the delivered stock.'
            : ''
    );
}


let variantIndex = <?= count($variants) ?>;


function addVariantRow() {

    const idx = variantIndex++;


    variantRows.insertAdjacentHTML(
        'beforeend',
        `
        <div class="variant-row grid grid-cols-1 sm:grid-cols-[100px_minmax(0,1fr)_minmax(0,1fr)_90px_auto] gap-2 items-center">

            <!-- SIZE -->
            <select
                name="variants[${idx}][size]"
                class="w-full min-w-0 border border-gray-300 rounded-lg px-2.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
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
                class="w-full min-w-0 border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
            >


            <!-- SKU -->
            <input
                type="text"
                name="variants[${idx}][sku]"
                placeholder="SKU (optional)"
                class="w-full min-w-0 border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
            >


            <!-- STOCK -->
            <input
                type="number"
                min="0"
                name="variants[${idx}][stock]"
                placeholder="Stock"
                class="variant-stock w-full min-w-0 border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
            >


            <!-- REMOVE -->
            <button
                type="button"
                class="remove-variant whitespace-nowrap text-xs font-semibold text-red-600 hover:text-red-800 px-1"
            >
                Remove
            </button>

        </div>
        `
    );


    updateVariantLimit();
}


document.getElementById('addVariant').onclick = addVariantRow;


variantRows.addEventListener(
    'click',
    event => {

        if (
            event.target.classList.contains('remove-variant')
        ) {

            event.target
                .closest('.variant-row')
                .remove();

            updateVariantLimit();
        }

    }
);


variantRows.addEventListener(
    'input',
    updateVariantLimit
);

productStock.addEventListener('input', function () {
    enforceProductStockLimit();
    updateVariantLimit();
});


if (stockSource) {

    stockSource.addEventListener(
        'change',
        function () {

            const option =
                this.selectedOptions[0];


            if (!option.value) {

                productName.value = '';
                productStock.value = '';

                productStock.removeAttribute('max');
                if (branchSourceSize) branchSourceSize.value = '';
                if (branchSourceColor) branchSourceColor.value = '';

                updateVariantLimit();

                return;
            }


            productName.value =
                option.dataset.name;


            productStock.value =
                option.dataset.quantity;


            productStock.max =
                option.dataset.quantity;

            if (branchSourceSize) branchSourceSize.value = option.dataset.size || '';
            if (branchSourceColor) branchSourceColor.value = option.dataset.color || '';


            updateVariantLimit();

        }
    );

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
      if (form.dataset.confirmed === '1') return; // already confirmed, let it go through
      if (!form.checkValidity()) return; // let native "required" validation show first

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
        confirmButtonColor: '#0d9488',
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
