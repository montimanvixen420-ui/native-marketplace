<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/ProductVariant.php';
require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/ProductBranch.php';
require_once __DIR__ . '/../models/Wishlist.php';

class StorefrontController extends Controller
{
    private Product $productModel;
    private ProductVariant $variantModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireRole('customer');
        $this->productModel = new Product();
        $this->variantModel = new ProductVariant();
    }

    // GET /shop
    public function browse(): void
    {
        $search = trim($_GET['q'] ?? '');
        $category = $_GET['category'] ?? null;

        // Render the search result once, then let the category dropdown filter
        // the existing cards in the browser without a page reload.
        $productBranches = new ProductBranch();
        $products = $productBranches->customerListings($search);
        $categories = $this->productModel->getActiveCategories();
        $variantsByProduct = [];
        $productIds = [];
        foreach ($products as $product) {
            $variantsByProduct[(int) $product['id'] . ':' . (int) $product['branch_id']] = $productBranches->variantsForBranch((int) $product['id'], (int) $product['branch_id']);
        }

        $wishlistedProductIds = (new Wishlist())->productIdsByCustomer((int) $_SESSION['user_id']);

        $this->view('customer/browse', [
            'name' => $_SESSION['user_name'],
            'products' => $products,
            'variantsByProduct' => $variantsByProduct,
            'wishlistedProductIds' => $wishlistedProductIds,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $category,
            'banners' => (new Content())->getActive(Content::TYPE_BANNER),
            'announcements' => (new Content())->getActive(Content::TYPE_ANNOUNCEMENT),
            'siteTexts' => (new Content())->getActive(Content::TYPE_SITE_TEXT),
            'active' => 'browse',
        ]);
    }
}
