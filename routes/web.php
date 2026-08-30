<?php

/** @var Router $router */

$router->get('/', ['LandingController', 'index']);
$router->get('/sustainability', ['SustainabilityController', 'index']);
$router->get('/branches', ['BranchController', 'locator']);

$router->get('/login', ['AuthController', 'showLogin']);
$router->post('/login', ['AuthController', 'login']);
$router->get('/forgot-password', ['AuthController', 'showForgotPassword']);
$router->post('/forgot-password', ['AuthController', 'sendPasswordReset']);
$router->get('/reset-password', ['AuthController', 'showResetPassword']);
$router->post('/reset-password', ['AuthController', 'resetPassword']);

$router->get('/register', ['AuthController', 'showRegister']);
$router->post('/register', ['AuthController', 'register']);

$router->get('/logout', ['AuthController', 'logout']);
$router->get('/terms', ['LegalController', 'terms']);
$router->get('/privacy', ['LegalController', 'privacy']);

$router->get('/superadmin/dashboard', ['DashboardController', 'superadmin']);
$router->get('/admin/dashboard', ['DashboardController', 'admin']);
$router->get('/admin/analytics', ['SellerAnalyticsController', 'index']);
$router->get('/admin/settings', ['SellerSettingsController', 'index']);
$router->post('/admin/settings/update', ['SellerSettingsController', 'update']);
$router->get('/admin/branches', ['BranchController', 'index']);
$router->get('/admin/branch-managers', ['BranchManagerController', 'index']);
$router->post('/admin/branch-managers/store', ['BranchManagerController', 'store']);
$router->post('/admin/branch-managers/{id}/update', ['BranchManagerController', 'update']);
$router->post('/admin/branch-managers/{id}/reset-password', ['BranchManagerController', 'resetPassword']);
$router->post('/admin/branch-managers/{id}/branch', ['BranchManagerController', 'changeBranch']);
$router->post('/admin/branch-managers/{id}/status', ['BranchManagerController', 'setStatus']);
$router->post('/admin/branches/store', ['BranchController', 'store']);
$router->post('/admin/branches/{id}/update', ['BranchController', 'update']);
$router->post('/admin/branches/{id}/toggle', ['BranchController', 'toggle']);
$router->post('/admin/branches/{id}/delete', ['BranchController', 'delete']);
$router->get('/supplier/dashboard', ['DashboardController', 'supplier']);
$router->get('/supplier/inventory', ['SupplierInventoryController', 'index']);
$router->post('/supplier/inventory/store', ['SupplierInventoryController', 'store']);
$router->post('/supplier/inventory/update', ['SupplierInventoryController', 'update']);
$router->get('/supplier/requests', ['SupplierRequestsController', 'index']);
$router->post('/supplier/requests/update-status', ['SupplierRequestsController', 'updateStatus']);
$router->get('/customer/dashboard', ['DashboardController', 'customer']);
$router->get('/apply', ['ApplicationController', 'create']);
$router->post('/apply', ['ApplicationController', 'store']);

// ── Superadmin: User management ─────────────────────────────
$router->get('/superadmin/users', ['UserManagementController', 'index']);
$router->get('/superadmin/sellers', ['UserManagementController', 'sellers']);
$router->get('/superadmin/organization', ['OrganizationController', 'index']);
$router->get('/superadmin/suppliers', ['UserManagementController', 'suppliers']);
$router->get('/superadmin/applications', ['UserManagementController', 'reviewApplications']);

// ── Superadmin: Reports ─────────────────────────────
$router->get('/superadmin/reports', ['ReportsController', 'index']);

// ── Superadmin: Content management ─────────────────────────────
$router->get('/superadmin/content', ['ContentController', 'index']);
$router->get('/superadmin/content/create', ['ContentController', 'create']);
$router->post('/superadmin/content/store', ['ContentController', 'store']);
$router->get('/superadmin/content/{id}/edit', ['ContentController', 'edit']);
$router->post('/superadmin/content/{id}/update', ['ContentController', 'update']);
$router->post('/superadmin/content/{id}/toggle', ['ContentController', 'toggle']);
$router->post('/superadmin/content/{id}/delete', ['ContentController', 'delete']);

// ── Superadmin: System settings ─────────────────────────────
$router->get('/superadmin/settings', ['SystemSettingsController', 'index']);
$router->post('/superadmin/settings/update', ['SystemSettingsController', 'update']);

// ── Superadmin: Prohibited items ─────────────────────────────
$router->get('/superadmin/prohibited-items', ['ProhibitedItemController', 'index']);
$router->get('/superadmin/prohibited-items/create', ['ProhibitedItemController', 'create']);
$router->post('/superadmin/prohibited-items/store', ['ProhibitedItemController', 'store']);
$router->get('/superadmin/prohibited-items/{id}/edit', ['ProhibitedItemController', 'edit']);
$router->post('/superadmin/prohibited-items/{id}/update', ['ProhibitedItemController', 'update']);
$router->post('/superadmin/prohibited-items/{id}/delete', ['ProhibitedItemController', 'delete']);
$router->get('/superadmin/product-moderation', ['ProductModerationController', 'index']);
$router->post('/superadmin/product-moderation/{id}/review', ['ProductModerationController', 'review']);
$router->get('/superadmin/users/{id}/edit', ['UserManagementController', 'edit']);
$router->post('/superadmin/users/{id}/update', ['UserManagementController', 'update']);
$router->post('/superadmin/users/{id}/approve', ['UserManagementController', 'approve']);
$router->post('/superadmin/users/{id}/reject', ['UserManagementController', 'reject']);
$router->post('/superadmin/users/{id}/suspend', ['UserManagementController', 'suspend']);
$router->post('/superadmin/users/{id}/reactivate', ['UserManagementController', 'reactivate']);
$router->post('/superadmin/users/{id}/delete', ['UserManagementController', 'delete']);
$router->get('/superadmin/users/{id}/verification/{type}', ['ApplicationController', 'downloadVerification']);

$router->get('/products', ['ProductController', 'index']);
$router->get('/products/create', ['ProductController', 'showCreate']);
$router->post('/products/create', ['ProductController', 'store']);
$router->get('/products/edit', ['ProductController', 'showEdit']);
$router->post('/products/update', ['ProductController', 'update']);
$router->post('/products/delete', ['ProductController', 'delete']);
$router->get('/admin/vouchers', ['VoucherController', 'index']);
$router->post('/admin/vouchers', ['VoucherController', 'store']);
$router->post('/admin/vouchers/toggle', ['VoucherController', 'toggle']);

$router->get('/pos', ['PosController', 'index']);
$router->get('/pos/customers', ['PosController', 'searchCustomers']);
$router->post('/pos/checkout', ['PosController', 'checkout']);

// ── Seller: Orders (POS sales + customer checkout orders) ─────────────────────────────
$router->get('/admin/orders', ['AdminOrdersController', 'index']);
$router->get('/admin/orders/{id}', ['AdminOrdersController', 'show']);
$router->post('/admin/orders/{id}/fulfillment', ['AdminOrdersController', 'updateFulfillment']);

// ── Seller: Stock Adjustments (audit log across all branches) ──
$router->get('/admin/stock-adjustments', ['StockAdjustmentsController', 'index']);
$router->post('/admin/stock-adjustments/adjust', ['StockAdjustmentsController', 'adjust']);
$router->get('/admin/allocations', ['BranchAllocationController', 'index']);
$router->post('/admin/allocations/store', ['BranchAllocationController', 'store']);
$router->get('/admin/damaged-products', ['DamagedProductsController', 'index']);
$router->get('/admin/inventory', ['SellerInventoryController', 'index']);
$router->post('/admin/inventory/transfer', ['SellerInventoryController', 'transfer']);

$router->get('/admin/returns', ['AdminReturnsController', 'index']);
$router->post('/admin/returns/{id}', ['AdminReturnsController', 'update']);

// ── Seller: Stock requests to suppliers ─────────────────────────────
$router->get('/stock-requests', ['StockRequestController', 'index']);
$router->get('/stock-requests/create', ['StockRequestController', 'create']);
$router->post('/stock-requests/store', ['StockRequestController', 'store']);
 
$router->get('/shop', ['StorefrontController', 'browse']);
$router->get('/reports', ['CustomerReportController', 'index']);
$router->get('/reports/create', ['CustomerReportController', 'create']);
$router->post('/reports', ['CustomerReportController', 'store']);
$router->post('/superadmin/reports/{id}', ['ReportsController', 'updateReport']);
$router->get('/seller/{id}/branches', ['BranchController', 'sellerBranches']);
$router->post('/restock-notifications', ['RestockNotificationController', 'subscribe']);

 
$router->get('/cart', ['CartController', 'index']);
$router->post('/cart/add', ['CartController', 'add']);
$router->post('/cart/update', ['CartController', 'update']);
$router->post('/cart/remove', ['CartController', 'remove']);
 
$router->get('/checkout', ['CheckoutController', 'index']);
$router->get('/checkout/check-availability', ['CheckoutController', 'checkAvailability']);
$router->post('/checkout/place', ['CheckoutController', 'place']);
$router->get('/checkout/paymongo/success', ['CheckoutController', 'paymongoSuccess']);
$router->get('/checkout/paymongo/cancel', ['CheckoutController', 'paymongoCancel']);
$router->get('/addresses', ['ShippingAddressController', 'index']);
$router->post('/addresses', ['ShippingAddressController', 'store']);
 
$router->get('/orders', ['OrdersController', 'index']);
$router->post('/orders/{id}/cancel', ['OrdersController', 'cancel']);
$router->get('/orders/reviews', ['PostPurchaseController', 'reviewsIndex']);
$router->get('/orders/returns', ['PostPurchaseController', 'returnsIndex']);
$router->get('/orders/item/{id}/return', ['PostPurchaseController', 'returnForm']);
$router->post('/orders/item/{id}/return', ['PostPurchaseController', 'submitReturn']);
$router->get('/orders/item/{id}/review', ['PostPurchaseController', 'reviewForm']);
$router->post('/orders/item/{id}/review', ['PostPurchaseController', 'submitReview']);
 
$router->get('/wishlist', ['WishlistController', 'index']);
$router->post('/wishlist/add', ['WishlistController', 'add']);
$router->post('/wishlist/update', ['WishlistController', 'update']);
$router->post('/wishlist/remove', ['WishlistController', 'remove']);
 
$router->get('/feedback', ['FeedbackController', 'showForm']);
$router->post('/feedback/submit', ['FeedbackController', 'submit']);
$router->get('/superadmin/feedback', ['FeedbackController', 'indexForSuperadmin']);
$router->post('/superadmin/feedback/mark-reviewed', ['FeedbackController', 'markReviewed']);
 
$router->get('/help', ['HelpController', 'index']);
// ── Branch Manager: own dashboard (separate from generic Staff dashboard) ──
$router->get('/manager/dashboard', ['ManagerDashboardController', 'index']);

// ── Branch Manager: per-branch stock ──
$router->get('/manager/stock', ['ManagerStockController', 'index']);
$router->post('/manager/stock/adjust', ['ManagerStockController', 'adjust']);
$router->get('/manager/inventory', ['BranchInventoryController', 'index']);
$router->post('/manager/inventory/transfer', ['BranchInventoryController', 'transfer']);
$router->post('/manager/damage-reports', ['BranchDamageReportController', 'report']);
$router->get('/manager/damaged-items', ['BranchDamageReportController', 'index']);
$router->post('/manager/damaged-items/{id}/approve', ['BranchDamageReportController', 'approve']);
$router->get('/manager/products', ['ManagerProductsController', 'index']);
$router->get('/manager/products/create', ['ManagerProductsController', 'showCreate']);
$router->post('/manager/products/create', ['ManagerProductsController', 'store']);
$router->post('/manager/products/add', ['ManagerProductsController', 'add']);
$router->post('/manager/products/return', ['ManagerProductsController', 'returnToInventory']);

// ── Branch manager: staff is always created in the manager's own branch ──
$router->get('/staff/manage', ['StaffController', 'index']);
$router->post('/staff/manage/store', ['StaffController', 'store']);
$router->post('/staff/manage/{id}/update', ['StaffController', 'update']);
$router->post('/staff/manage/{id}/status', ['StaffController', 'setStatus']);
$router->post('/staff/manage/{id}/archive', ['StaffController', 'archive']);
$router->post('/staff/manage/{id}/reset-password', ['StaffController', 'resetPassword']);

// ── Staff: self-service ─────────────────────────────
$router->get('/staff/dashboard', ['StaffController', 'dashboard']);

// ── Branch Manager + Staff: orders scoped to their one branch (spec 34-35) ──
$router->get('/staff/orders', ['StaffOrdersController', 'index']);
$router->get('/staff/orders/{id}', ['StaffOrdersController', 'show']);
$router->post('/staff/orders/{id}/fulfillment', ['StaffOrdersController', 'updateFulfillment']);

// ── Branch Manager + Staff: returns/refunds scoped to their one branch ──
$router->get('/staff/returns', ['StaffReturnsController', 'index']);
$router->post('/staff/returns/{id}', ['StaffReturnsController', 'update']);
 
 
// ── Seller: view-only reports filed against their account/products ──
$router->get('/admin/reports', ['SellerReportsController', 'index']);

// ── Branch Manager: view-only awareness of product reports for their branch ──
$router->get('/staff/reports', ['StaffReportsController', 'index']);

// ── Cashier: branch-scoped Point of Sale ──
$router->get('/staff/pos', ['StaffPosController', 'index']);
$router->get('/staff/pos/customers', ['StaffPosController', 'searchCustomers']);
$router->post('/staff/pos/checkout', ['StaffPosController', 'checkout']);
 