<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/ProductVariant.php';
require_once __DIR__ . '/../models/BranchStock.php';

class CartController extends Controller
{
    private Product $productModel;
    private ProductVariant $variantModel;
    private BranchStock $branchStock;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireRole('customer');
        $this->productModel = new Product();
        $this->variantModel = new ProductVariant();
        $this->branchStock = new BranchStock();

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = []; // [cart_key => quantity]
        }
    }

    // POST /cart/add
    public function add(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $variantId = (int) ($_POST['variant_id'] ?? 0);
        $branchId = (int) ($_POST['branch_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        $product = $this->productModel->findById($productId);

        if ($product && $product['status'] === 'active' && $branchId > 0) {
            $variant = $variantId ? $this->variantModel->findForProduct($variantId, $productId) : null;
            if (($variantId && !$variant) || (!$variantId && $this->variantModel->hasForProduct($productId))) {
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/shop');
                return;
            }

            $key = $productId . ':' . ($variant ? $variantId : 0) . ':' . $branchId;
            $available = $this->branchStock->available($productId, $variant, $branchId);
            if ($available > 0) {
                $current = $_SESSION['cart'][$key] ?? 0;
                $_SESSION['cart'][$key] = min($current + $quantity, $available);
            }
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/shop');
    }

    // POST /cart/update
    public function update(): void
    {
        $cartKey = (string) ($_POST['cart_key'] ?? $_POST['product_id'] ?? '');
        [$productId, $variantId, $branchId] = array_pad(explode(':', $cartKey, 3), 3, null);
        $productId = (int) $productId;
        $variantId = $variantId !== null ? (int) $variantId : 0;
        $quantity = (int) ($_POST['quantity'] ?? 1);

        if ($cartKey === '') {
            $this->redirect('/cart');
            return;
        }

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cartKey]);
        } else {
            $product = $this->productModel->findById($productId);
            if ($product && $product['status'] === 'active') {
                $variant = $variantId ? $this->variantModel->findForProduct($variantId, $productId) : null;
                if ($variantId && !$variant) {
                    unset($_SESSION['cart'][$cartKey]);
                    $this->redirect('/cart');
                    return;
                }
                $_SESSION['cart'][$cartKey] = min($quantity, $this->branchStock->available($productId, $variant, (int) $branchId));
            }
        }

        $this->redirect('/cart');
    }

    // POST /cart/remove
    public function remove(): void
    {
        $cartKey = (string) ($_POST['cart_key'] ?? $_POST['product_id'] ?? '');
        unset($_SESSION['cart'][$cartKey]);
        $this->redirect('/cart');
    }

    // GET /cart
    public function index(): void
    {
        $items = [];
        $total = 0;

        foreach ($_SESSION['cart'] as $cartKey => $quantity) {
            [$productId, $variantId, $branchId] = array_pad(explode(':', (string) $cartKey, 3), 3, null);
            $product = $this->productModel->findById((int) $productId);

            if (!$product || $product['status'] !== 'active') {
                unset($_SESSION['cart'][$cartKey]); // product no longer available
                continue;
            }
            $variant = $variantId ? $this->variantModel->findForProduct((int) $variantId, (int) $productId) : null;
            if ($variantId && !$variant) { unset($_SESSION['cart'][$cartKey]); continue; }
            $available = $this->branchStock->available((int) $productId, $variant, (int) $branchId);
            $quantity = min($quantity, $available);
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;

            $items[] = [
                'product' => $product,
                'variant' => $variant,
                'cartKey' => $cartKey,
                'branchId' => (int) $branchId,
                'available' => $available,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        $this->view('customer/cart', [
            'name' => $_SESSION['user_name'],
            'items' => $items,
            'total' => $total,
            'active' => 'cart',
        ]);
    }
}
