<?php
  // NOTE: every variable here is prefixed with __sidebar to avoid clashing
  // with variables the page itself uses. This partial is require()'d
  // directly into the page's scope.

  $__sidebarRole = $_SESSION['user_role'] ?? '';
  $__sidebarName = $name ?? ($_SESSION['user_name'] ?? '');

  // Smart Active URL Detection
  $__currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
  $__sidebarActive = $active ?? '';

  // Pinalitang py-2.5 sa py-1.5 at text-sm sa text-xs para mas compact at kasya agad sa screen
  $__sidebarNavBase = 'app-side-link flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200';
  $__sidebarNavActive = "{$__sidebarNavBase} app-side-active";
  $__sidebarNavInactive = $__sidebarNavBase;

  $__sidebarBadges = [
      'superadmin' => 'Superadmin',
      'admin'      => 'Owner · Seller',
      'supplier'   => 'Supplier',
      'customer'   => 'Customer',
      'staff'      => 'Staff',
      'manager'    => 'Branch Manager',
  ];

  // Each role: array of groups. Each group: optional 'label' + 'items'.
  $__sidebarGroupsByRole = [
      'superadmin' => [
          ['label' => 'Menu', 'items' => [
              ['key' => 'overview', 'href' => '/superadmin/dashboard', 'label' => 'Overview',
               'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
              ['key' => 'signups', 'href' => '/superadmin/signups', 'label' => 'Platform Sign-ups',
               'icon' => '<path d="M3 3v18h18"/><path d="M7 15l4-6 3 4 4-7"/>'],
              ['key' => 'reports', 'href' => '/superadmin/reports', 'label' => 'Customer Reports',
               'icon' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/>'],
          ]],
          ['label' => 'Management', 'items' => [
              ['key' => 'users', 'href' => '/superadmin/users', 'label' => 'Users',
               'icon' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/>'],
              ['key' => 'sellers', 'href' => '/superadmin/sellers', 'label' => 'Sellers',
               'icon' => '<path d="M3 9l1.5-5h15L21 9"/><path d="M4 9h16v10H4z"/><path d="M9 13h6"/>'],
              ['key' => 'organization', 'href' => '/superadmin/organization', 'label' => 'Organization',
               'icon' => '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/><path d="M9 10h.01M15 10h.01"/>'],
              ['key' => 'suppliers', 'href' => '/superadmin/suppliers', 'label' => 'Suppliers',
               'icon' => '<path d="M16.5 9.4L7.5 4.21"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/>'],
              ['key' => 'applications', 'href' => '/superadmin/applications', 'label' => 'Review applications',
               'icon' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>'],
              ['key' => 'product-moderation', 'href' => '/superadmin/product-moderation', 'label' => 'Product reviews',
               'icon' => '<path d="M12 9v4l2.5 1.5"/><circle cx="12" cy="12" r="9"/>'],
          ]],
          ['label' => 'Others', 'items' => [
              ['key' => 'content', 'href' => '/superadmin/content', 'label' => 'Content',
               'icon' => '<rect x="3" y="4" width="18" height="14" rx="1"/><path d="M7 21h10M9 18v3M15 18v3"/>'],
              ['key' => 'feedback', 'href' => '/superadmin/feedback', 'label' => 'Feedback',
               'icon' => '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>'],
              ['key' => 'settings', 'href' => '/superadmin/settings', 'label' => 'System settings',
               'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
          ]],
      ],
      'admin' => [
          ['label' => 'Menu', 'items' => [
              ['key' => 'overview', 'href' => '/admin/dashboard', 'label' => 'Overview',
               'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
              ['key' => 'pos', 'href' => '/pos', 'label' => 'Point of Sale',
               'icon' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/>'],
              ['key' => 'orders', 'href' => '/admin/orders', 'label' => 'Orders',
               'icon' => '<path d="M6 2l1.5 5H21l-3 8H8L4 4H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>'],
              ['key' => 'analytics', 'href' => '/admin/analytics', 'label' => 'Analytics',
                'icon' => '<path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="6"/><rect x="12" y="8" width="3" height="10"/><rect x="17" y="5" width="3" height="13"/>'],
          ]],
          ['label' => 'Inventory', 'items' => [
              ['key' => 'inventory', 'href' => '/admin/inventory', 'label' => ' Inventory',
               'icon' => '<path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-4.4a2 2 0 0 1 1.48 0l8 4.4A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/><rect x="6" y="10" width="12" height="1"/>'],
              ['key' => 'products', 'href' => '/products', 'label' => 'Products',
               'icon' => '<path d="M20 7l-8-4-8 4v10l8 4 8-4V7z"/><path d="M4 7l8 4 8-4"/><path d="M12 11v10"/>'],
              ['key' => 'allocations', 'href' => '/admin/allocations', 'label' => 'Branch allocations',
               'icon' => '<path d="M12 3v18"/><path d="m7 8 5-5 5 5"/><path d="m17 16-5 5-5-5"/>'],
              ['key' => 'damaged-products', 'href' => '/admin/damaged-products', 'label' => 'Damaged products',
               'icon' => '<path d="M12 2v6"/><path d="M12 16v6"/><path d="M4.93 4.93l4.24 4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="M2 12h6"/><path d="M16 12h6"/>'],
              ['key' => 'stock-requests', 'href' => '/stock-requests', 'label' => 'Stock requests',
               'icon' => '<rect x="3" y="7" width="13" height="11"/><path d="M16 11h3l2 3v4h-5z"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="17.5" cy="18.5" r="1.5"/>'],
              ['key' => 'branches', 'href' => '/admin/branches', 'label' => 'Maps & Branches',
               'icon' => '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/><path d="M9 10h.01M15 10h.01"/>'],
          ]],
          ['label' => 'Team', 'items' => [
              ['key' => 'branch-managers', 'href' => '/admin/branch-managers', 'label' => 'Branch Managers',
               'icon' => '<circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/><path d="M16 3.13a4 4 0 010 7.75"/><path d="M22 21v-2a4 4 0 00-3-3.87"/>'],
          ]],
          ['label' => 'Others', 'items' => [
              ['key' => 'reports', 'href' => '/admin/reports', 'label' => 'Complaints Against Store',
               'icon' => '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>'],
                ['key' => 'returns', 'href' => '/admin/returns', 'label' => 'Returns',
               'icon' => '<path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/><path d="M3 3v5h5"/>'],
              ['key' => 'settings', 'href' => '/admin/settings', 'label' => 'Settings',
               'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06-.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
          ]],
      ],
      'supplier' => [
          ['label' => 'Menu', 'items' => [
              ['key' => 'overview', 'href' => '/supplier/dashboard', 'label' => 'Overview',
               'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
              ['key' => 'requests', 'href' => '/supplier/requests', 'label' => 'Incoming requests',
               'icon' => '<rect x="3" y="7" width="13" height="11"/><path d="M16 11h3l2 3v4h-5z"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="17.5" cy="18.5" r="1.5"/>'],
          ]],
          ['label' => 'Catalog', 'items' => [
              ['key' => 'catalog', 'href' => '/supplier/inventory', 'label' => 'My catalog',
               'icon' => '<path d="M20 7l-8-4-8 4v10l8 4 8-4V7z"/><path d="M4 7l8 4 8-4"/><path d="M12 11v10"/>'],
          ]],
      ],
      'customer' => [
          ['label' => 'Menu', 'items' => [
              ['key' => 'overview', 'href' => '/customer/dashboard', 'label' => 'Overview',
               'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
              ['key' => 'browse', 'href' => '/shop', 'label' => 'Browse products',
               'icon' => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/>'],
          ]],
          ['label' => 'Shopping', 'items' => [
              ['key' => 'cart', 'href' => '/cart', 'label' => 'Cart',
               'icon' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>'],
              ['key' => 'my-orders', 'href' => '/orders', 'label' => 'My orders',
               'icon' => '<path d="M6 2l1.5 5H21l-3 8H8L4 4H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>'],
              ['key' => 'reviews', 'href' => '/orders/reviews', 'label' => 'Write reviews',
               'icon' => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1z"/>'],
              ['key' => 'returns', 'href' => '/orders/returns', 'label' => 'Return / refund',
               'icon' => '<path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/><path d="M3 3v5h5"/>'],
              ['key' => 'wishlist', 'href' => '/wishlist', 'label' => 'Wishlist',
               'icon' => '<path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 000-7.6z"/>'],
          ]],
      ],
      'staff' => [
          ['label' => 'Menu', 'items' => [
              ['key' => 'dashboard', 'href' => '/staff/dashboard', 'label' => 'Dashboard',
               'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
              ['key' => 'pos', 'href' => '/staff/pos', 'label' => 'Point of Sale', 'only' => 'cashier',
               'icon' => '<rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/>'],
              ['key' => 'orders', 'href' => '/staff/orders', 'label' => 'Orders',
               'icon' => '<path d="M6 2l1.5 5H21l-3 8H8L4 4H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>'],
              ['key' => 'returns', 'href' => '/staff/returns', 'label' => 'Returns',
               'icon' => '<path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/><path d="M3 3v5h5"/>'],
              ['key' => 'stock', 'href' => '/manager/stock', 'label' => 'Stock', 'only' => 'inventory_staff',
               'icon' => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/>'],
              ['key' => 'inventory', 'href' => '/manager/inventory', 'label' => 'Branch Inventory', 'only' => 'inventory_staff',
               'icon' => '<rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>'],
              ['key' => 'reports', 'href' => '/staff/reports', 'label' => 'Branch Product Reports',
               'icon' => '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>'],
          ]],
      ],
      'manager' => [
          ['label' => 'Menu', 'items' => [
              ['key' => 'dashboard', 'href' => '/manager/dashboard', 'label' => 'Dashboard',
               'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
              ['key' => 'orders', 'href' => '/staff/orders', 'label' => 'Orders',
               'icon' => '<path d="M6 2l1.5 5H21l-3 8H8L4 4H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>'],
              ['key' => 'returns', 'href' => '/staff/returns', 'label' => 'Returns',
               'icon' => '<path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/><path d="M3 3v5h5"/>'],
              ['key' => 'stock', 'href' => '/manager/stock', 'label' => 'Stock',
               'icon' => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/>',],
              ['key' => 'products', 'href' => '/manager/products', 'label' => 'Branch POS Products',
               'icon' => '<path d="M20 7l-8-4-8 4v10l8 4 8-4V7z"/><path d="M4 7l8 4 8-4"/><path d="M12 11v10"/>'],
              ['key' => 'inventory', 'href' => '/manager/inventory', 'label' => 'Branch Inventory',
               'icon' => '<rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>'],
              ['key' => 'damaged-items', 'href' => '/manager/damaged-items', 'label' => 'Damaged Items',
               'icon' => '<path d="M12 2v6"/><path d="M12 16v6"/><path d="M4.93 4.93l4.24 4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="M2 12h6"/><path d="M16 12h6"/>'],
          ]],
          ['label' => 'Team', 'items' => [
              ['key' => 'staff', 'href' => '/staff/manage', 'label' => 'Staff Management',
               'icon' => '<circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/><path d="M16 3.13a4 4 0 010 7.75"/><path d="M22 21v-2a4 4 0 00-3-3.87"/>'],
          ]],
          ['label' => 'Others', 'items' => [
              ['key' => 'reports', 'href' => '/staff/reports', 'label' => 'Branch Product Reports',
               'icon' => '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>'],
          ]],
      ],
  ];

  $__sidebarBadgeLabel = $__sidebarBadges[$__sidebarRole] ?? ucfirst($__sidebarRole);
  if ($__sidebarRole === 'staff' && !empty($profile['position'])) {
      $__sidebarPositionLabels = ['cashier' => 'Cashier', 'inventory_staff' => 'Inventory Staff', 'order_staff' => 'Order Staff', 'customer_service' => 'Customer Service Staff'];
      $__sidebarBadgeLabel = $__sidebarPositionLabels[$profile['position']] ?? $__sidebarBadgeLabel;
  }
  $__sidebarGroups = $__sidebarGroupsByRole[$__sidebarRole] ?? [];
  if ($__sidebarRole === 'staff') {
      foreach ($__sidebarGroups as &$__sidebarGroup) {
          $__sidebarGroup['items'] = array_values(array_filter($__sidebarGroup['items'], function ($__item) use ($profile) {
              return empty($__item['only']) || ($profile['position'] ?? null) === $__item['only'];
          }));
      }
      unset($__sidebarGroup);
  }

  // Initials for the avatar circle (e.g. "Brent Asuncion" -> "BA")
  $__sidebarInitials = '';
  foreach (explode(' ', trim($__sidebarName)) as $__sidebarWord) {
      if ($__sidebarWord !== '') {
          $__sidebarInitials .= strtoupper($__sidebarWord[0]);
      }
      if (strlen($__sidebarInitials) >= 2) break;
  }
?>
<nav class="md:hidden sticky top-0 z-30 bg-white/95 dark:bg-ink-2/95 backdrop-blur border-b border-gray-100 dark:border-white/10 px-4 py-3">
  <div class="flex items-center justify-between gap-3">
    <a href="/customer/dashboard" class="font-display font-bold text-base text-ink dark:text-white"><span class="inline-block w-2.5 h-2.5 rounded-full bg-brand mr-1.5"></span>TINDA</a>
    <details class="relative">
      <summary class="list-none cursor-pointer rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-white select-none">Menu <span aria-hidden="true">☰</span></summary>
      <div class="absolute right-0 top-11 w-64 max-h-[70vh] overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-white/10 dark:bg-ink-2">
        <p class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-white/60"><?= htmlspecialchars($__sidebarName) ?></p>
        <?php foreach ($__sidebarGroups as $__mobileGroup): ?>
          <p class="px-3 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400"><?= htmlspecialchars($__mobileGroup['label']) ?></p>
          <?php foreach ($__mobileGroup['items'] as $__mobileItem): ?>
            <?php 
              $__mobileUrlMatches = ($__mobileItem['href'] === '/') 
                ? ($__currentUri === '/') 
                : str_starts_with($__currentUri, $__mobileItem['href']);

              $__isMobileActive = $__mobileUrlMatches || (!empty($__sidebarActive) && $__sidebarActive === $__mobileItem['key']);
            ?>
            <a href="<?= htmlspecialchars($__mobileItem['href']) ?>" class="block rounded-lg px-3 py-2 text-sm font-medium <?= $__isMobileActive ? 'bg-brand-light text-brand' : 'text-gray-700 dark:text-white/80' ?>"><?= $__mobileItem['label'] ?></a>
          <?php endforeach; ?>
        <?php endforeach; ?>
        <div class="mt-2 border-t border-gray-100 pt-2 dark:border-white/10"><a href="/logout" class="js-logout-link block rounded-lg px-3 py-2 text-sm font-semibold text-red-500">Log out</a></div>
      </div>
    </details>
  </div>
</nav>

<!-- Dinagdagan ng [scrollbar-width:none] at [&::-webkit-scrollbar]:hidden para siguradong tago ang scrollbar sa browser -->
<aside class="app-sidebar hidden md:flex min-h-screen sticky top-0 h-screen flex-col justify-between shrink-0 transition-colors [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
    <!-- Top & Scrollable Nav Section -->
    <div class="flex-1 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
      <!-- Binuwasan ang py-6 to py-4 -->
      <div class="app-brand flex items-center gap-3 font-display font-extrabold text-xl px-6 py-4">
        <span class="app-brand-mark">T</span>
        TINDA
      </div>

      <!-- Binuwasan ang pb-4 to pb-2 -->
      <div class="px-6 pb-2">
        <span class="app-role inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-lg"><span class="h-1.5 w-1.5 rounded-full bg-blue-300"></span><?= htmlspecialchars($__sidebarBadgeLabel) ?></span>
      </div>

      <nav class="px-4 space-y-0.5 mt-2">
        <?php foreach ($__sidebarGroups as $__sidebarGroup): ?>
          <?php foreach ($__sidebarGroup['items'] as $__sidebarNavItem): ?>
            <?php 
              // Direct URL priority match
              $__urlMatches = ($__sidebarNavItem['href'] === '/') 
                ? ($__currentUri === '/') 
                : str_starts_with($__currentUri, $__sidebarNavItem['href']);

              $__isItemActive = $__urlMatches || (!empty($__sidebarActive) && $__sidebarActive === $__sidebarNavItem['key']);
            ?>
            <a href="<?= htmlspecialchars($__sidebarNavItem['href']) ?>" class="<?= $__isItemActive ? $__sidebarNavActive : $__sidebarNavInactive ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 shrink-0"><?= $__sidebarNavItem['icon'] ?></svg>
              <?= $__sidebarNavItem['label'] ?>
            </a>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </nav>
    </div><!-- End of flex-1 scrollable container -->

    <!-- Pinned Bottom Section (Profile, Links, & Footer) -->
    <div class="shrink-0">
      <?php if ($__sidebarRole !== 'staff'): ?>
      <div class="app-footer-rule px-4 pt-2 pb-1 space-y-0.5 border-t">
        <?php if ($__sidebarRole !== 'superadmin'): ?>
          <?php 
            $__isFeedbackActive = str_starts_with($__currentUri, '/feedback') || (!empty($__sidebarActive) && $__sidebarActive === 'feedback');
          ?>
          <a href="/feedback" class="<?= $__isFeedbackActive ? $__sidebarNavActive : $__sidebarNavInactive ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 shrink-0"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
            Feedback
          </a>
        <?php endif; ?>
        <?php 
          $__isHelpActive = str_starts_with($__currentUri, '/help') || (!empty($__sidebarActive) && $__sidebarActive === 'help');
        ?>
        <a href="/help" class="<?= $__isHelpActive ? $__sidebarNavActive : $__sidebarNavInactive ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 shrink-0"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 2-3 5"/><path d="M12 17h.01"/></svg>
          Help
        </a>
      </div>
      <?php endif; ?>

      <!-- Binuwasan ang py-4 to py-2 -->
      <div class="app-footer-rule px-4 pb-2 pt-2 border-t">
        <div class="app-account flex items-center gap-2.5 px-3 py-2 rounded-xl transition-colors">
          <div class="w-7 h-7 rounded-full bg-brand text-white text-xs font-semibold flex items-center justify-center shrink-0">
            <?= htmlspecialchars($__sidebarInitials ?: '?') ?>
          </div>
          <div class="min-w-0 flex-1">
            <p class="app-account-name text-xs font-bold truncate"><?= htmlspecialchars($__sidebarName) ?></p>
            <a href="/logout" class="js-logout-link text-[11px] text-gray-400 hover:text-white">Log out</a>
          </div>
        </div>
        <p class="text-[9px] text-gray-300 dark:text-white/20 text-center mt-1.5">© <?= date('Y') ?> TINDA Marketplace</p>
      </div>
    </div>
</aside>

<script>
(function () {
  function initLogoutConfirm() {
    document.querySelectorAll('.js-logout-link').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        var href = this.getAttribute('href');
        Swal.fire({
          title: 'Log out?',
          text: 'You will need to sign in again to access your account.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, log out',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#0d9488',
          cancelButtonColor: '#6b7280',
          reverseButtons: true
        }).then(function (result) {
          if (result.isConfirmed) {
            window.location.href = href;
          }
        });
      });
    });
  }

  if (typeof Swal === 'undefined') {
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    s.onload = initLogoutConfirm;
    document.head.appendChild(s);
  } else {
    initLogoutConfirm();
  }
})();
</script>