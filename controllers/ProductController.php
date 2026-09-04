<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/ProductVariant.php';
require_once __DIR__ . '/../models/RestockNotification.php';
require_once __DIR__ . '/../models/ProhibitedItem.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/StockRequest.php';
require_once __DIR__ . '/../models/SellerPosStock.php';

class ProductController extends Controller
{
    private Product $productModel;
    private ProductVariant $productVariantModel;
    private ProhibitedItem $prohibitedItemModel;
    private User $userModel;
    private StockRequest $stockRequestModel;

    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg'];
    private const ALLOWED_MIME_TYPES = ['image/png', 'image/jpeg'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->guardSeller();
        $this->productModel = new Product();
        $this->productVariantModel = new ProductVariant();
        $this->prohibitedItemModel = new ProhibitedItem();
        $this->userModel = new User();
        $this->stockRequestModel = new StockRequest();
    }

    private function guardSeller(): void
    {
        $this->requireApprovedSeller();
    }

    private function sellerId(): int
    {
        return (int) $_SESSION['user_id'];
    }

    public function index(): void
    {
        $view = ($_GET['view'] ?? '') === 'archived' ? 'archived' : 'active';
        $products = $view === 'archived'
            ? $this->productModel->archivedBySeller($this->sellerId())
            : $this->productModel->allBySeller($this->sellerId());
        $categories = $this->productModel->distinctCategoriesForSeller($this->sellerId());
        $this->view('admin/products/index', [
            'products' => $products,
            'categories' => $categories,
            'productsView' => $view,
            'active' => 'products',
            'error' => $_GET['error'] ?? null,
            'success' => $_GET['success'] ?? null,
        ]);
    }

    public function showCreate(): void
    {
        $this->view('admin/products/form', [
            'mode' => 'create',
            'product' => null,
            'error' => null,
            'availableStocks' => $this->productModel->inventorySourcesBySeller($this->sellerId()),
            'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST);
        $variants = $this->parseVariants($_POST['variants'] ?? []);
        $inventorySourceId = (int) ($_POST['inventory_source_product_id'] ?? 0);
        $sourceStock = $inventorySourceId > 0 ? $this->productModel->inventorySourceForSeller($inventorySourceId, $this->sellerId()) : null;

        if (isset($data['error']) || isset($variants['error']) || !$sourceStock) {
            $this->view('admin/products/form', [
                'mode' => 'create',
                'product' => $_POST,
                'error' => $data['error'] ?? $variants['error'] ?? 'Choose available Seller Inventory stock before adding a product.',
                'availableStocks' => $this->productModel->inventorySourcesBySeller($this->sellerId()),
                'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
            ]);
            return;
        }

        if (!empty($variants)) $data['stock'] = array_sum(array_column($variants, 'stock'));
        if ($data['stock'] > (int) $sourceStock['stock']) {
            $this->view('admin/products/form', [
                'mode' => 'create', 'product' => $_POST,
                'error' => 'Product stock cannot be more than the selected Seller Inventory stock (' . (int) $sourceStock['stock'] . ').',
                'availableStocks' => $this->productModel->inventorySourcesBySeller($this->sellerId()),
                'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
            ]);
            return;
        }
        $data['stock_request_id'] = null;
        $data['inventory_source_product_id'] = $inventorySourceId;

        // Image is required when creating a new product
        $imageResult = $this->handleImageUpload(required: true);

        if (isset($imageResult['error'])) {
            $this->view('admin/products/form', [
                'mode' => 'create',
                'product' => $_POST,
                'error' => $imageResult['error'],
                'availableStocks' => $this->productModel->inventorySourcesBySeller($this->sellerId()),
                'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
            ]);
            return;
        }

        $data['image_url'] = $imageResult['path'];
        $matches = $this->screenProduct($data);
        $data['status'] = 'pending_review';

        $productId = $this->productModel->create($this->sellerId(), $data);
        if ($matches) {
            $this->productModel->addModerationFlag($productId, $matches);
            $this->lockSellerForProhibitedListing();
        }
        $this->productModel->addImageModerationFlag($productId);
        $this->productVariantModel->replaceForProduct($productId, $variants);
        (new SellerPosStock())->createListingFromInventory($this->sellerId(), $inventorySourceId, $productId, $data['stock'], (int) $_SESSION['user_id']);
        $this->redirect('/products');
    }

    public function showEdit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->productModel->findByIdForSeller($id, $this->sellerId());

        if (!$product) {
            $this->redirect('/products');
            return;
        }

        $this->view('admin/products/form', [
            'mode' => 'edit',
            'product' => $product,
            'error' => null,
            'variants' => $this->productVariantModel->allByProduct($id),
            'sourceStockLimit' => !empty($product['stock_request_id'])
                ? ($this->stockRequestModel->findFulfilledForSeller((int) $product['stock_request_id'], $this->sellerId())['quantity_requested'] ?? null)
                : null,
            'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $existing = $this->productModel->findByIdForSeller($id, $this->sellerId());

        if (!$existing) {
            $this->redirect('/products');
            return;
        }

        $data = $this->validate($_POST);
        $variants = $this->parseVariants($_POST['variants'] ?? []);

        if (isset($data['error']) || isset($variants['error'])) {
            $this->view('admin/products/form', [
                'mode' => 'edit',
                'product' => $_POST,
                'error' => $data['error'] ?? $variants['error'],
                'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
            ]);
            return;
        }

        if (!empty($variants)) $data['stock'] = array_sum(array_column($variants, 'stock'));
        $sourceStock = !empty($existing['stock_request_id'])
            ? $this->stockRequestModel->findFulfilledForSeller((int) $existing['stock_request_id'], $this->sellerId())
            : null;
        if ($sourceStock && $data['stock'] > (int) $sourceStock['quantity_requested']) {
            $this->view('admin/products/form', [
                'mode' => 'edit', 'product' => $_POST,
                'error' => 'Product stock cannot be more than the selected Seller Inventory stock (' . (int) $sourceStock['quantity_requested'] . ').',
                'sourceStockLimit' => (int) $sourceStock['quantity_requested'],
                'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
            ]);
            return;
        }

        // This listing was created from a Seller Inventory item (see inventorySourcesBySeller /
        // createListingFromInventory). Raising its stock draws more from that same reserve, so cap
        // it at what's still unused there, plus whatever this listing already holds.
        if (!empty($existing['inventory_source_product_id'])) {
            $invSource = $this->productModel->inventorySourceForSeller((int) $existing['inventory_source_product_id'], $this->sellerId());
            $invCap = ((int) ($invSource['stock'] ?? 0)) + (int) $existing['stock'];
            if ($invSource && $data['stock'] > $invCap) {
                $this->view('admin/products/form', [
                    'mode' => 'edit', 'product' => $_POST,
                    'error' => 'Product stock cannot be more than your available Seller Inventory stock (' . $invCap . ').',
                    'sourceStockLimit' => $invCap,
                    'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
                ]);
                return;
            }
        }
        // Image is optional on edit — only replace it if a new file was uploaded
        $imageResult = $this->handleImageUpload(required: false);

        if (isset($imageResult['error'])) {
            $this->view('admin/products/form', [
                'mode' => 'edit',
                'product' => $_POST,
                'error' => $imageResult['error'],
                'active' => 'products',
                'categories' => $this->productModel->distinctCategoriesForSeller($this->sellerId()),
            ]);
            return;
        }

        $hasNewImage = $imageResult['path'] !== null;
        if ($hasNewImage) {
            $data['image_url'] = $imageResult['path'];
            // I-delete ang lumang image file para hindi mabuhusan ng basura ang uploads folder
            $this->deleteImageFile($existing['image_url']);
        } else {
            $data['image_url'] = $existing['image_url'];
        }

        $matches = $this->screenProduct($data);
        $this->productModel->clearPendingModerationFlags($id);
        if ($matches || $hasNewImage || $this->productModel->hasPendingModerationFlags($id)) {
            $data['status'] = 'pending_review';
        }

        $wasOutOfStock = (int) $existing['stock'] === 0;
        $this->productModel->update($id, $this->sellerId(), $data);
        // Keep the real sellable ledger (seller_pos_stock) in sync with the edited
        // stock — a no-op for raw Seller Inventory items that never had one.
        (new SellerPosStock())->syncListingStock($this->sellerId(), $id, (int) $data['stock']);
        if ($matches) {
            $this->productModel->addModerationFlag($id, $matches);
            $this->lockSellerForProhibitedListing();
        }
        if ($hasNewImage) {
            $this->productModel->clearPendingModerationFlags($id, 'image');
            $this->productModel->addImageModerationFlag($id);
        }
        $this->productVariantModel->replaceForProduct($id, $variants);
        if ($wasOutOfStock && (int) $data['stock'] > 0) (new RestockNotification())->markProductAvailable($id);
        $this->redirect('/products');
    }

    public function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $product = $this->productModel->findByIdForSeller($id, $this->sellerId());

        if ($product) {
            $archived = $this->productModel->delete($id, $this->sellerId());
            if (!$archived) {
                $this->redirect('/products?error=' . urlencode('This product could not be removed. Please try again.'));
                return;
            }
            // Soft delete: keep the image file since the product can still be viewed
            // under the "Archived" tab and may be restored later.
        }

        $this->redirect('/products?success=' . urlencode('Product moved to Archived.'));
    }

    // POST /products/restore
    public function restore(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $restored = $this->productModel->restore($id, $this->sellerId());

        $this->redirect($restored
            ? '/products?success=' . urlencode('Product restored.')
            : '/products?view=archived&error=' . urlencode('Unable to restore this product.'));
    }

    /**
     * Pinoproseso ang na-upload na image file mula sa $_FILES['image'].
     *
     * @param bool $required Kung true, kailangang may na-upload na file (para sa create).
     * @return array ['path' => string|null] kung successful, o ['error' => string] kung may mali.
     *               Kung hindi required at walang na-upload, ['path' => null] ang ibabalik
     *               (ibig sabihin, panatilihin ang existing image).
     */
    private function handleImageUpload(bool $required): array
    {
        $hasFile = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

        if (!$hasFile) {
            if ($required) {
                return ['error' => 'Please upload a product image (PNG or JPEG).'];
            }
            return ['path' => null];
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Something went wrong while uploading the image. Please try again.'];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['error' => 'Image must be 5MB or smaller.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true) || !in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            return ['error' => 'Only PNG or JPEG images are allowed.'];
        }

        $filename = uniqid('product_', true) . '.' . $extension;
        $uploadDir = __DIR__ . '/../public/uploads/products/';
        $destination = $uploadDir . $filename;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['error' => 'Failed to save the uploaded image. Please try again.'];
        }

        // Stored relative to /public — views should prefix with a leading slash
        return ['path' => 'uploads/products/' . $filename];
    }

    private function deleteImageFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $fullPath = __DIR__ . '/../public/' . $relativePath;

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    private function parseVariants(array $rawVariants): array
    {
        $variants = [];
        $seen = [];
        foreach ($rawVariants as $raw) {
            $size = trim($raw['size'] ?? ''); $color = trim($raw['color'] ?? ''); $sku = trim($raw['sku'] ?? ''); $stock = $raw['stock'] ?? '';
            if ($size === '' && $color === '' && $stock === '') continue;
            if (($size === '' && $color === '') || filter_var($stock, FILTER_VALIDATE_INT) === false || (int) $stock < 0) return ['error' => 'Each variant needs at least a size or color, plus a valid stock quantity.'];
            $size = $size ?: 'One size';
            $color = $color ?: 'N/A';
            $key = strtolower($size . '|' . $color);
            if (isset($seen[$key])) return ['error' => 'Each size/color combination must be unique.'];
            $seen[$key] = true;
            $variants[] = ['size' => $size, 'color' => $color, 'sku' => $sku, 'stock' => (int) $stock];
        }
        return $variants;
    }

    private function validate(array $input): array
    {
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price = $input['price'] ?? '';
        $stock = $input['stock'] ?? '';
        $category = trim($input['category'] ?? '');
        $status = $input['status'] ?? 'active';

        if ($name === '') {
            return ['error' => 'Product name is required.'];
        }

        if (!is_numeric($price) || (float) $price < 0) {
            return ['error' => 'Please enter a valid price.'];
        }

        if (!ctype_digit((string) $stock) && !is_int($stock)) {
            return ['error' => 'Please enter a valid stock quantity.'];
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        return [
            'name' => $name,
            'description' => $description,
            'size_guide' => trim($input['size_guide'] ?? ''),
            'fit_information' => trim($input['fit_information'] ?? ''),
            'price' => (float) $price,
            'stock' => (int) $stock,
            'category' => $category ?: null,
            'status' => $status,
        ];
    }

    private function screenProduct(array $data): array
    {
        return $this->prohibitedItemModel->findMatches(
            $data['name'] . "\n" . $data['description']
        );
    }

    /** Lock the seller account until Superadmin completes the safety review. */
    private function lockSellerForProhibitedListing(): void
    {
        $this->userModel->updateStatus($this->sellerId(), User::STATUS_SUSPENDED);
        $_SESSION['seller_access_error'] = 'Your account has been temporarily locked because a prohibited item was detected. A Superadmin will review it.';
    }
}