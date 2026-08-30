<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/ProductVariant.php';
require_once __DIR__ . '/../models/ProductBranch.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/BranchStock.php';
require_once __DIR__ . '/../models/Voucher.php';
require_once __DIR__ . '/../models/SystemSettings.php';
require_once __DIR__ . '/../models/ShippingAddress.php';
require_once __DIR__ . '/../core/OrderNotificationMailer.php';
require_once __DIR__ . '/../core/PayMongoCheckout.php';

class CheckoutController extends Controller
{
    private Product $productModel;
    private Order $orderModel;
    private ProductVariant $variantModel;
    private ProductBranch $productBranchModel;
    private Branch $branchModel;
    private Voucher $voucherModel;
    private SystemSettings $settingsModel;
    private ShippingAddress $addressModel;
    private BranchStock $branchStock;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireRole('customer');
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->variantModel = new ProductVariant();
        $this->productBranchModel = new ProductBranch();
        $this->branchModel = new Branch();
        $this->voucherModel = new Voucher();
        $this->settingsModel = new SystemSettings();
        $this->addressModel = new ShippingAddress();
        $this->branchStock = new BranchStock();
    }

    /**
     * One order = one branch, decided HERE at checkout (like shipping
     * address / payment method) rather than earlier while shopping.
     *
     * A branch only qualifies if it carries EVERY item in this checkout
     * (products with zero branches assigned are branch-agnostic and don't
     * narrow the set). Returns:
     *   - options: candidate branches the customer can choose from (each
     *     ['id'=>,'name'=>,'city'=>,'address'=>])
     *   - conflict: true when the items in this order don't share ANY
     *     common branch (e.g. two products only sold at different branches)
     */
    private function resolveBranchOptions(array $items): array
    {
        $candidateIds = null; // null = "no constraint yet"

        foreach ($items as $item) {
            $branchIds = $this->productBranchModel->activeBranchIdsForProduct((int) $item['product']['id']);
            if (empty($branchIds)) continue; // branch-agnostic product, doesn't narrow the set

            $candidateIds = $candidateIds === null ? $branchIds : array_intersect($candidateIds, $branchIds);
        }

        if ($candidateIds === null) {
            return ['options' => [], 'conflict' => false]; // no item cares about branch
        }
        if (empty($candidateIds)) {
            return ['options' => [], 'conflict' => true]; // items don't share any branch
        }

        return ['options' => $this->branchModel->findManyByIds($candidateIds), 'conflict' => false];
    }

    /**
     * Figures out what's being checked out:
     * - "Buy now": ?product_id=X&quantity=Y in the query string
     * - "Cart checkout": no product_id, uses the session cart
     *
     * @return array Each entry: ['product' => array, 'variant' => array|null, 'quantity' => int, 'subtotal' => float]
     */
    private function resolveItems(): array
    {
        $items = [];

        $buyNowProductId = (int) ($_REQUEST['product_id'] ?? 0);

        if ($buyNowProductId > 0) {
            $variantId = (int) ($_REQUEST['variant_id'] ?? 0);
            $quantity = max(1, (int) ($_REQUEST['quantity'] ?? 1));
            $branchId = (int) ($_REQUEST['branch_id'] ?? 0);
            $product = $this->productModel->findById($buyNowProductId);

            if ($product && $product['status'] === 'active' && $branchId > 0) {
                $variant = $variantId ? $this->variantModel->findForProduct($variantId, $buyNowProductId) : null;
                if (($variantId && !$variant) || (!$variantId && $this->variantModel->hasForProduct($buyNowProductId))) {
                    return $items;
                }
                $quantity = min($quantity, $this->branchStock->available($buyNowProductId, $variant, $branchId));
                if ($quantity < 1) {
                    return $items;
                }
                $items[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'subtotal' => $product['price'] * $quantity,
                    'branch_id' => $branchId,
                    'available' => $this->branchStock->available($buyNowProductId, $variant, $branchId),
                ];
            }

            return $items;
        }

        // Cart checkout
        foreach ($_SESSION['cart'] ?? [] as $cartKey => $quantity) {
            [$productId, $variantId, $branchId] = array_pad(explode(':', (string) $cartKey, 3), 3, null);
            $product = $this->productModel->findById((int) $productId);

            if (!$product || $product['status'] !== 'active') {
                continue;
            }

            $variant = $variantId ? $this->variantModel->findForProduct((int) $variantId, (int) $productId) : null;
            if ($variantId && !$variant) continue;
            $quantity = min((int) $quantity, $this->branchStock->available((int) $productId, $variant, (int) $branchId));
            if ($quantity < 1) continue;
            $items[] = [
                'product' => $product,
                'variant' => $variant,
                'quantity' => $quantity,
                'subtotal' => $product['price'] * $quantity,
                'branch_id' => (int) $branchId,
                'available' => $this->branchStock->available((int) $productId, $variant, (int) $branchId),
            ];
        }

        return $items;
    }

    // GET /checkout  or  GET /checkout?product_id=X&quantity=Y
    public function index(): void
    {
        $items = $this->resolveItems();
        $total = array_sum(array_column($items, 'subtotal'));

        $isBuyNow = (int) ($_GET['product_id'] ?? 0) > 0;

        if (empty($items)) {
            $message = $isBuyNow
                ? 'This product is no longer available. Please choose another item.'
                : 'Your cart has no available items to check out.';
            $this->redirect(($isBuyNow ? '/shop' : '/cart') . '?error=' . urlencode($message));
            return;
        }

        $summary = $this->buildSummary($items, []);
        $branchInfo = ['conflict' => false, 'options' => []]; // branch is already attached to each cart item
        $this->view('customer/checkout', [
            'name' => $_SESSION['user_name'],
            'items' => $items,
            'total' => $total,
            'summary' => $summary,
            'voucherCodes' => [],
            'isBuyNow' => $isBuyNow,
            'buyNowProductId' => $isBuyNow ? (int) $_GET['product_id'] : null,
            'buyNowQuantity' => $isBuyNow ? (int) ($_GET['quantity'] ?? 1) : null,
            'buyNowVariantId' => $isBuyNow ? (int) ($_GET['variant_id'] ?? 0) : null,
            'error' => null,
            'addresses' => $this->addressModel->allByCustomer((int) $_SESSION['user_id']),
            'selectedAddressId' => null,
            'branchOptions' => $branchInfo['options'],
            'branchConflict' => $branchInfo['conflict'],
            'selectedBranchId' => null,
            'active' => 'browse',
        ]);
    }

    // GET /checkout/check-availability?product_id=X&variant_id=Y&quantity=Z
    // Used by the shop page's "Buy now" button to verify the product is still
    // available WITHOUT navigating away — avoids the full page redirect/refresh
    // that used to happen when checkout/index() bounced back to /shop?error=...
    public function checkAvailability(): void
    {
        header('Content-Type: application/json');

        $productId = (int) ($_GET['product_id'] ?? 0);
        $variantId = (int) ($_GET['variant_id'] ?? 0);
        $branchId = (int) ($_GET['branch_id'] ?? 0);
        $quantity = max(1, (int) ($_GET['quantity'] ?? 1));

        $product = $productId ? $this->productModel->findById($productId) : null;

        if (!$product || $product['status'] !== 'active') {
            echo json_encode(['available' => false, 'message' => 'This product is no longer available. Please choose another item.']);
            return;
        }

        $variant = $variantId ? $this->variantModel->findForProduct($variantId, $productId) : null;
        if (($variantId && !$variant) || (!$variantId && $this->variantModel->hasForProduct($productId))) {
            echo json_encode(['available' => false, 'message' => 'This product is no longer available. Please choose another item.']);
            return;
        }

        $available = $branchId > 0 ? $this->branchStock->available($productId, $variant, $branchId) : 0;
        if ($available < $quantity) {
            echo json_encode(['available' => false, 'message' => 'This product is no longer available. Please choose another item.']);
            return;
        }

        echo json_encode(['available' => true]);
    }

    // POST /checkout/place
    public function place(): void
    {
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $customerId = (int) $_SESSION['user_id'];

        $items = $this->resolveItems();
        $voucherCodes = is_array($_POST['voucher_codes'] ?? null) ? $_POST['voucher_codes'] : [];

        if (empty($items)) {
            $this->redirect('/shop');
            return;
        }

        // Group items by seller — each seller gets their own order,
        // same as a real marketplace checkout would split a multi-seller cart.
        $bySeller = [];
        foreach ($items as $item) {
            $sellerId = (int) $item['product']['seller_id'];
            $branchId = (int) $item['branch_id'];
            $key = $sellerId . ':' . $branchId;
            if (!isset($bySeller[$key])) $bySeller[$key] = ['seller_id' => $sellerId, 'branch_id' => $branchId, 'items' => []];
            $bySeller[$key]['items'][] = [
                'product_id' => (int) $item['product']['id'],
                'variant_id' => !empty($item['variant']) ? (int) $item['variant']['id'] : null,
                'quantity' => $item['quantity'],
            ];
        }

        $addressId = (int) ($_POST['shipping_address_id'] ?? 0);
        $shippingAddress = $this->addressModel->findForCustomer($addressId, $customerId);
        if (!$shippingAddress) {
            $this->renderCheckoutError($items, 'Please add and select a valid shipping address before placing your order.', $voucherCodes, $this->buildSummary($items, $voucherCodes));
            return;
        }

        // Branch selection happened when the customer chose the listing.
        $branchInfo = ['conflict' => false, 'options' => []];
        if ($branchInfo['conflict']) {
            $this->renderCheckoutError($items, 'These items are not sold at any common branch. Please check out the conflicting items separately.', $voucherCodes, $this->buildSummary($items, $voucherCodes));
            return;
        }
        $branchId = null;
        if (count($branchInfo['options']) === 1) {
            $branchId = (int) $branchInfo['options'][0]['id'];
        } elseif (count($branchInfo['options']) > 1) {
            $submittedBranchId = (int) ($_POST['branch_id'] ?? 0);
            $validIds = array_column($branchInfo['options'], 'id');
            if (!in_array($submittedBranchId, $validIds, true)) {
                $this->renderCheckoutError($items, 'Please select a branch for this order.', $voucherCodes, $this->buildSummary($items, $voucherCodes));
                return;
            }
            $branchId = $submittedBranchId;
        }

        $summary = $this->buildSummary($items, $voucherCodes);
        foreach ($summary as $sellerSummary) {
            if (!$sellerSummary['voucher']['valid']) {
                $this->renderCheckoutError($items, $sellerSummary['voucher']['message'], $voucherCodes, $summary);
                return;
            }
        }

        if ($paymentMethod === 'paymongo' && empty($_SESSION['paymongo_verified_token'])) {
            $this->startPayMongoCheckout($items, $summary, $voucherCodes, $addressId);
            return;
        }
        unset($_SESSION['paymongo_verified_token']);

        $createdOrderIds = [];
        $errors = [];
        $sellerChargesApplied = [];

        foreach ($bySeller as $group) {
            $sellerId = $group['seller_id'];
            $sellerItems = $group['items'];
            $branchId = $group['branch_id'];
            $sellerSummary = $summary[$sellerId];
            $applyCharges = empty($sellerChargesApplied[$sellerId]);
            $result = $this->orderModel->checkout(
                $sellerId,
                $customerId,
                null,
                $sellerItems,
                $paymentMethod,
                'online',
                'pending',
                $applyCharges ? $sellerSummary['shipping'] : 0.0,
                $applyCharges ? $sellerSummary['voucher']['discount'] : 0.0,
                $shippingAddress,
                $branchId
            );

            if ($result['success']) {
                $sellerChargesApplied[$sellerId] = true;
                $createdOrderIds[] = $result['order_id'];
                if ($applyCharges && !empty($sellerSummary['voucher']['voucher']['id'])) {
                    $this->voucherModel->recordUse((int) $sellerSummary['voucher']['voucher']['id']);
                }
                (new OrderNotificationMailer())->sendPlaced($result['order_id'], $this->orderModel);
            } else {
                $errors[] = $result['error'];
            }
        }

        if (!empty($errors)) {
            $itemsForView = $this->resolveItems();
            $this->renderCheckoutError($itemsForView, implode(' ', $errors), $voucherCodes, $this->buildSummary($itemsForView, $voucherCodes));
            return;
        }

        // Clear the cart only if this was a cart checkout (not buy-now)
        if (empty($_POST['product_id'])) {
            $_SESSION['cart'] = [];
        }

        $this->redirect('/orders?placed=1');
    }

    public function paymongoSuccess(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        $checkout = $_SESSION['paymongo_checkout'] ?? null;
        if (!$checkout || !hash_equals($checkout['token'], $token) || $checkout['expires_at'] < time()) {
            $this->redirect('/checkout?error=' . urlencode('This payment session has expired. Please try again.'));
            return;
        }
        try {
            if (!(new PayMongoCheckout())->isPaid($checkout['session_id'])) {
                $this->redirect('/checkout?error=' . urlencode('PayMongo has not confirmed the payment yet. Please wait a moment and try again.'));
                return;
            }
        } catch (Throwable $e) {
            $this->redirect('/checkout?error=' . urlencode($e->getMessage()));
            return;
        }
        $_POST = $checkout['post'];
        // PHP builds $_REQUEST only once at request start. Restore the saved
        // Buy-now/variant fields here too, otherwise resolveItems() cannot
        // see them after returning from the hosted PayMongo page.
        $_REQUEST = array_replace($_REQUEST, $_POST);
        $_SESSION['paymongo_verified_token'] = $token;
        unset($_SESSION['paymongo_checkout']);
        $this->place();
    }

    public function paymongoCancel(): void
    {
        unset($_SESSION['paymongo_checkout']);
        $this->redirect('/checkout?error=' . urlencode('Payment was cancelled. Your order was not placed.'));
    }

    private function startPayMongoCheckout(array $items, array $summary, array $voucherCodes, int $addressId): void
    {
        $token = bin2hex(random_bytes(24));
        try {
            $result = (new PayMongoCheckout())->createSession($items, $summary, $token);
            $attributes = $result['data']['attributes'] ?? [];
            $sessionId = (string) ($result['data']['id'] ?? '');
            $checkoutUrl = (string) ($attributes['checkout_url'] ?? '');
            if ($sessionId === '' || $checkoutUrl === '') throw new RuntimeException('PayMongo did not return a checkout link.');
            $_SESSION['paymongo_checkout'] = [
                'token' => $token, 'session_id' => $sessionId, 'expires_at' => time() + 3600,
                'post' => [
                    'payment_method' => 'paymongo', 'shipping_address_id' => $addressId,
                    'voucher_codes' => $voucherCodes,
                    'product_id' => $_POST['product_id'] ?? null,
                    'quantity' => $_POST['quantity'] ?? null,
                    'variant_id' => $_POST['variant_id'] ?? null,
                    'branch_id' => $_POST['branch_id'] ?? null,
                ],
            ];
            header('Location: ' . $checkoutUrl);
            exit;
        } catch (Throwable $e) {
            $this->renderCheckoutError($items, $e->getMessage(), $voucherCodes, $summary);
        }
    }

    private function buildSummary(array $items, array $voucherCodes): array
    {
        $settings = $this->settingsModel->get();
        $shippingFee = max(0, (float) ($settings['shipping_fee'] ?? 0));
        $threshold = $settings['free_shipping_threshold'] ?? null;
        $bySeller = [];
        foreach ($items as $item) {
            $sellerId = (int) $item['product']['seller_id'];
            if (!isset($bySeller[$sellerId])) {
                $bySeller[$sellerId] = ['seller_name' => $item['product']['seller_name'], 'subtotal' => 0.0];
            }
            $bySeller[$sellerId]['subtotal'] += (float) $item['subtotal'];
        }
        foreach ($bySeller as $sellerId => &$seller) {
            $shipping = ($threshold !== null && $threshold !== '' && $seller['subtotal'] >= (float) $threshold) ? 0.0 : $shippingFee;
            $seller['voucher'] = $this->voucherModel->apply((string) ($voucherCodes[$sellerId] ?? ''), $sellerId, $seller['subtotal'], $shipping);
            $seller['shipping'] = $seller['voucher']['shipping'] ?? $shipping;
            $seller['total'] = $seller['subtotal'] + $seller['shipping'] - ($seller['voucher']['discount'] ?? 0);
        }
        unset($seller);
        return $bySeller;
    }

    private function renderCheckoutError(array $items, string $error, array $voucherCodes, array $summary): void
    {
        $branchInfo = ['conflict' => false, 'options' => []];
        $this->view('customer/checkout', [
            'name' => $_SESSION['user_name'], 'items' => $items,
            'total' => array_sum(array_column($items, 'subtotal')),
            'summary' => $summary, 'voucherCodes' => $voucherCodes,
            'isBuyNow' => (int) ($_POST['product_id'] ?? 0) > 0,
            'buyNowProductId' => (int) ($_POST['product_id'] ?? 0) ?: null,
            'buyNowQuantity' => (int) ($_POST['quantity'] ?? 1),
            'buyNowVariantId' => (int) ($_POST['variant_id'] ?? 0) ?: null,
            'error' => $error, 'active' => 'browse',
            'addresses' => $this->addressModel->allByCustomer((int) $_SESSION['user_id']),
            'selectedAddressId' => (int) ($_POST['shipping_address_id'] ?? 0),
            'branchOptions' => $branchInfo['options'],
            'branchConflict' => $branchInfo['conflict'],
            'selectedBranchId' => (int) ($_POST['branch_id'] ?? 0) ?: null,
        ]);
    }
}
