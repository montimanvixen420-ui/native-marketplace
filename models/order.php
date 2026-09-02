<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BranchAllocation.php';

class Order
{
    private PDO $db;
    private BranchAllocation $allocations;

    public const PAYMENT_METHODS = ['cash', 'gcash', 'card', 'paymongo', 'other'];
    public const FULFILLMENT_TRANSITIONS = [
        'pending' => ['packed'],
        'packed' => ['shipped'],
        'shipped' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->allocations = new BranchAllocation();
    }

    public function checkout(
        int $sellerId,
        ?int $customerId,
        ?string $customerName,
        array $items,
        string $paymentMethod,
        string $orderType = 'pos',
        string $status = 'completed',
        float $shippingFee = 0.0,
        float $discount = 0.0,
        ?array $shippingAddress = null,
        ?int $branchId = null,
        ?int $processedByUserId = null,
        ?string $processedByRole = null
    ): array {
        if (empty($items)) {
            return ['success' => false, 'order_id' => null, 'error' => 'Cart is empty.'];
        }

        if (!in_array($paymentMethod, self::PAYMENT_METHODS, true)) {
            return ['success' => false, 'order_id' => null, 'error' => 'Invalid payment method.'];
        }

        if ($branchId !== null) {
            $branchCheck = $this->db->prepare('SELECT 1 FROM branches WHERE id = :id AND seller_id = :seller_id LIMIT 1');
            $branchCheck->execute(['id' => $branchId, 'seller_id' => $sellerId]);
            if (!$branchCheck->fetchColumn()) {
                $branchId = null;
            }
        }

        $this->db->beginTransaction();

        try {
            $total = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw new RuntimeException('Invalid quantity for one of the items.');
                }

                $stmt = $this->db->prepare(
                    "SELECT id, name, price, stock, seller_id FROM products
                     WHERE id = :id AND seller_id = :seller_id
                     FOR UPDATE"
                );
                $stmt->execute(['id' => $productId, 'seller_id' => $sellerId]);
                $product = $stmt->fetch();

                if (!$product) {
                    throw new RuntimeException("Product not found or doesn't belong to this seller.");
                }

                $variant = null;
                if ($variantId) {
                    $variantStmt = $this->db->prepare(
                        "SELECT id, size, color, sku, stock FROM product_variants
                         WHERE id = :id AND product_id = :product_id FOR UPDATE"
                    );
                    $variantStmt->execute(['id' => $variantId, 'product_id' => $productId]);
                    $variant = $variantStmt->fetch();

                    if (!$variant) {
                        throw new RuntimeException("Selected variant is no longer available for \"{$product['name']}\".");
                    }
                }

                $branchStockRow = null;
                if ($branchId !== null) {
                    $branchStockStmt = $this->db->prepare(
                        "SELECT stock FROM branch_pos_stock
                         WHERE product_id = :product_id AND variant_size = :size AND variant_color = :color AND branch_id = :branch_id
                         FOR UPDATE"
                    );
                    $branchStockStmt->execute([
                        'product_id' => $productId,
                        'size' => $variant['size'] ?? '',
                        'color' => $variant['color'] ?? '',
                        'branch_id' => $branchId,
                    ]);
                    $branchStockRow = $branchStockStmt->fetch();
                    $branchAvailable = $branchStockRow ? (int) $branchStockRow['stock'] : 0;

                    if ($branchAvailable < $quantity) {
                        $label = $variant ? " ({$variant['size']} / {$variant['color']})" : '';
                        throw new RuntimeException("Not enough stock for \"{$product['name']}\"{$label} at this branch (only {$branchAvailable} left).");
                    }
                } else {
                    $posStock = $this->db->prepare('SELECT stock FROM seller_pos_stock WHERE seller_id=:seller_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color FOR UPDATE');
                    $posStock->execute(['seller_id'=>$sellerId,'product_id'=>$productId,'size'=>$variant['size']??'','color'=>$variant['color']??'']);
                    $available=(int)$posStock->fetchColumn();
                    if ($available < $quantity) throw new RuntimeException("Not enough Seller POS stock for \"{$product['name']}\" (only {$available} left).");
                }

                $subtotal = $product['price'] * $quantity;
                $total += $subtotal;

                $lineItems[] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'variant_size' => $variant['size'] ?? '',
                    'variant_color' => $variant['color'] ?? '',
                    'product_name' => $product['name'],
                    'variant_label' => $variant ? ($variant['color'] === 'N/A' ? $variant['size'] : $variant['size'] . ' / ' . $variant['color']) : null,
                    'unit_price' => $product['price'],
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }

            $shippingFee = max(0.0, $shippingFee);
            $discount = min(max(0.0, $discount), $total);
            $total = $total + $shippingFee - $discount;

            // FIX: Added processed_by_user_id column saving
            $stmt = $this->db->prepare(
                "INSERT INTO orders (seller_id, branch_id, customer_id, customer_name, processed_by_user_id, shipping_address_id, shipping_recipient_name, shipping_phone, shipping_address_text, total_amount, payment_method, order_type, status, created_at)
                 VALUES (:seller_id, :branch_id, :customer_id, :customer_name, :processed_by_user_id, :shipping_address_id, :shipping_recipient_name, :shipping_phone, :shipping_address_text, :total_amount, :payment_method, :order_type, :status, NOW())"
            );
            $stmt->execute([
                'seller_id' => $sellerId,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'customer_name' => $customerId ? null : $customerName,
                'processed_by_user_id' => $processedByUserId,
                'shipping_address_id' => $shippingAddress['id'] ?? null,
                'shipping_recipient_name' => $shippingAddress['recipient_name'] ?? null,
                'shipping_phone' => $shippingAddress['phone'] ?? null,
                'shipping_address_text' => $shippingAddress ? $this->formatShippingAddress($shippingAddress) : null,
                'total_amount' => $total,
                'payment_method' => $paymentMethod,
                'order_type' => $orderType,
                'status' => $status,
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                "INSERT INTO order_items (order_id, product_id, variant_id, product_name, variant_label, variant_size, variant_color, unit_price, quantity, subtotal)
                 VALUES (:order_id, :product_id, :variant_id, :product_name, :variant_label, :variant_size, :variant_color, :unit_price, :quantity, :subtotal)"
            );
            $stockStmt = $this->db->prepare(
                "UPDATE products SET stock = stock - :quantity WHERE id = :id"
            );
            $variantStockStmt = $this->db->prepare(
                "UPDATE product_variants SET stock = stock - :quantity WHERE id = :id"
            );
            $sellerPosDeductStmt = $this->db->prepare('UPDATE seller_pos_stock SET stock=stock-:quantity WHERE seller_id=:seller_id AND product_id=:product_id AND variant_size=:size AND variant_color=:color');
            $branchStockDeductStmt = $this->db->prepare(
                "UPDATE branch_pos_stock SET stock = stock - :quantity
                 WHERE product_id = :product_id AND variant_size = :size AND variant_color = :color AND branch_id = :branch_id"
            );
            $branchStockLogStmt = $this->db->prepare(
                "INSERT INTO branch_stock_adjustments
                    (product_id, variant_size, variant_color, branch_id, adjusted_by_user_id, adjusted_by_role, previous_stock, new_stock, change_amount, reason, note, created_at)
                 VALUES (:product_id, :size, :color, :branch_id, :user_id, :role, :previous, :new, :change, 'sale', :note, NOW())"
            );

            foreach ($lineItems as $line) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $line['product_id'],
                    'variant_id' => $line['variant_id'],
                    'product_name' => $line['product_name'],
                    'variant_label' => $line['variant_label'],
                    'variant_size' => $line['variant_size'],
                    'variant_color' => $line['variant_color'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['subtotal'],
                ]);

                if ($branchId !== null) {
                    $previousBranchStock = $this->db->prepare(
                        "SELECT stock FROM branch_pos_stock WHERE product_id = :product_id AND variant_size = :size AND variant_color = :color AND branch_id = :branch_id"
                    );
                    $previousBranchStock->execute([
                        'product_id' => $line['product_id'], 'size' => $line['variant_size'],
                        'color' => $line['variant_color'], 'branch_id' => $branchId,
                    ]);
                    $previous = (int) $previousBranchStock->fetchColumn();

                    $branchStockDeductStmt->execute([
                        'quantity' => $line['quantity'], 'product_id' => $line['product_id'],
                        'size' => $line['variant_size'], 'color' => $line['variant_color'], 'branch_id' => $branchId,
                    ]);
                    $branchStockLogStmt->execute([
                        'product_id' => $line['product_id'], 'size' => $line['variant_size'], 'color' => $line['variant_color'],
                        'branch_id' => $branchId,
                        'user_id' => $processedByUserId ?? $customerId ?? 0,
                        'role' => $processedByRole ?? ($orderType === 'online' ? 'customer' : 'system'),
                        'previous' => $previous, 'new' => $previous - $line['quantity'], 'change' => -$line['quantity'],
                        'note' => 'Order #' . $orderId,
                    ]);
                } else {
                    $sellerPosDeductStmt->execute(['quantity'=>$line['quantity'],'seller_id'=>$sellerId,'product_id'=>$line['product_id'],'size'=>$line['variant_size'],'color'=>$line['variant_color']]);
                }
            }

            $this->db->commit();

            return ['success' => true, 'order_id' => $orderId, 'error' => null];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'order_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function createPosOrder(array $data): array
    {
        $sellerStmt = $this->db->prepare('SELECT seller_id FROM branches WHERE id = :id LIMIT 1');
        $sellerStmt->execute(['id' => $data['branch_id']]);
        $sellerId = (int) $sellerStmt->fetchColumn();

        return $this->checkout(
            $sellerId,
            $data['customer_id'] ?? null,
            $data['customer_name'] ?? null,
            $data['items'] ?? [],
            $data['payment_method'] ?? 'cash',
            'pos',
            'completed',
            0.0,
            0.0,
            null,
            (int) $data['branch_id'],
            (int) ($data['processed_by'] ?? 0),
            'cashier'
        );
    }

    public function allBySeller(int $sellerId, string $branchFilter = ''): array
    {
        $sql = "SELECT o.*, u.name AS linked_customer_name, b.name AS branch_name
             FROM orders o
             LEFT JOIN users u ON u.id = o.customer_id
             LEFT JOIN branches b ON b.id = o.branch_id
             WHERE o.seller_id = :seller_id";
        $params = ['seller_id' => $sellerId];
        if ($branchFilter === 'none') {
            $sql .= ' AND o.branch_id IS NULL';
        } elseif ($branchFilter !== '') {
            $sql .= ' AND o.branch_id = :branch_id';
            $params['branch_id'] = (int) $branchFilter;
        }
        $sql .= ' ORDER BY o.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findByIdForSeller(int $id, int $sellerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.name AS linked_customer_name, u.email AS customer_email, b.name AS branch_name
             FROM orders o
             LEFT JOIN users u ON u.id = o.customer_id
             LEFT JOIN branches b ON b.id = o.branch_id
             WHERE o.id = :id AND o.seller_id = :seller_id LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'seller_id' => $sellerId]);
        $order = $stmt->fetch();

        return $order ?: null;
    }

    public function allByBranch(int $branchId): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.name AS linked_customer_name
             FROM orders o
             LEFT JOIN users u ON u.id = o.customer_id
             WHERE o.branch_id = :branch_id
             ORDER BY o.created_at DESC"
        );
        $stmt->execute(['branch_id' => $branchId]);
        return $stmt->fetchAll();
    }

    public function findByIdForBranch(int $id, int $branchId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.name AS linked_customer_name, u.email AS customer_email
             FROM orders o
             LEFT JOIN users u ON u.id = o.customer_id
             WHERE o.id = :id AND o.branch_id = :branch_id LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'branch_id' => $branchId]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public function countPendingOnlineByBranch(int $branchId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE branch_id = :branch_id AND order_type = 'online' AND status = 'pending'");
        $stmt->execute(['branch_id' => $branchId]);
        return (int) $stmt->fetchColumn();
    }

    public function countTodayByBranch(int $branchId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE branch_id = :branch_id AND DATE(created_at) = CURDATE()");
        $stmt->execute(['branch_id' => $branchId]);
        return (int) $stmt->fetchColumn();
    }

    public function todaysRevenueByBranch(int $branchId): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE branch_id = :branch_id AND DATE(created_at) = CURDATE() AND status != 'cancelled'");
        $stmt->execute(['branch_id' => $branchId]);
        return (float) $stmt->fetchColumn();
    }

    public function updateFulfillmentForBranch(int $orderId, int $branchId, string $newStatus, ?string $courier, ?string $trackingNumber): array
    {
        $order = $this->findByIdForBranch($orderId, $branchId);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }
        return $this->applyFulfillmentTransition($order, 'branch_id', $branchId, $newStatus, $courier);
    }

    public function findForNotification(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT o.*, u.name AS customer_name_for_email, u.email AS customer_email, s.name AS seller_name FROM orders o INNER JOIN users u ON u.id = o.customer_id INNER JOIN users s ON s.id = o.seller_id WHERE o.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    private function formatShippingAddress(array $address): string
    {
        return implode(', ', array_filter([
            $address['address_line1'] ?? '', $address['address_line2'] ?? '', $address['barangay'] ?? '',
            $address['city'] ?? '', $address['province'] ?? '', $address['postal_code'] ?? '',
        ]));
    }

    public function nextStatuses(string $currentStatus): array
    {
        return self::FULFILLMENT_TRANSITIONS[$currentStatus] ?? [];
    }

    public function updateFulfillment(int $orderId, int $sellerId, string $newStatus, ?string $courier, ?string $trackingNumber): array
    {
        $order = $this->findByIdForSeller($orderId, $sellerId);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }
        if (!empty($order['branch_id'])) {
            return ['success' => false, 'error' => 'This order was placed for a specific branch. Only that branch\'s order staff can process it.'];
        }
        return $this->applyFulfillmentTransition($order, 'seller_id', $sellerId, $newStatus, $courier);
    }

    private function applyFulfillmentTransition(array $order, string $scopeColumn, int $scopeValue, string $newStatus, ?string $courier): array
    {
        $orderId = (int) $order['id'];
        $courier = $courier ?? '';

        if (!in_array($newStatus, $this->nextStatuses($order['status']), true)) {
            return ['success' => false, 'error' => 'That status change is not allowed.'];
        }
        if ($newStatus === 'packed' && $courier === '') {
            return ['success' => false, 'error' => 'Choose a courier before marking this order as packed.'];
        }

        $generatedTracking = null;
        if ($newStatus === 'packed') {
            $generatedTracking = $this->generateTrackingNumber($courier);
        } elseif ($newStatus === 'shipped' && empty($order['tracking_number'])) {
            if (empty($order['courier'])) return ['success' => false, 'error' => 'This order needs a courier before it can be shipped.'];
            $generatedTracking = $this->generateTrackingNumber($order['courier']);
        }

        $timestamps = [
            'packed' => 'packed_at = NOW()',
            'shipped' => 'shipped_at = NOW()',
            'completed' => 'delivered_at = NOW()',
            'cancelled' => 'cancelled_at = NOW()',
        ];
        $sql = "UPDATE orders SET status = :status";
        if ($newStatus === 'packed') {
            $sql .= ', courier = :courier, tracking_number = :tracking_number';
        } elseif ($generatedTracking !== null) {
            $sql .= ', tracking_number = :tracking_number';
        }
        $sql .= ', ' . $timestamps[$newStatus] . " WHERE id = :id AND {$scopeColumn} = :scope_value AND status = :current_status";

        $stmt = $this->db->prepare($sql);
        $params = ['status' => $newStatus, 'id' => $orderId, 'scope_value' => $scopeValue, 'current_status' => $order['status']];
        if ($newStatus === 'packed') {
            $params['courier'] = $courier;
            $params['tracking_number'] = $generatedTracking;
        } elseif ($generatedTracking !== null) {
            $params['tracking_number'] = $generatedTracking;
        }
        $stmt->execute($params);
        if ($stmt->rowCount() !== 1) {
            return ['success' => false, 'error' => 'This order was updated by the customer. Please refresh and try again.'];
        }
        return ['success' => true, 'error' => null];
    }

    private function generateTrackingNumber(string $courier): string
    {
        $normalizedCourier = strtolower(trim($courier));
        $prefix = str_contains($normalizedCourier, 'j&t') || str_contains($normalizedCourier, 'jnt') ? 'JNT'
            : (str_contains($normalizedCourier, 'lbc') ? 'LBC' : strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $courier), 0, 3)));
        $prefix = $prefix !== '' ? $prefix : 'TND';
        do {
            $trackingNumber = $prefix . '-' . random_int(100000000000, 999999999999);
            $check = $this->db->prepare('SELECT 1 FROM orders WHERE tracking_number = :tracking_number LIMIT 1');
            $check->execute(['tracking_number' => $trackingNumber]);
        } while ($check->fetchColumn());
        return $trackingNumber;
    }

    public function cancelByCustomer(int $orderId, int $customerId): array
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT id, status, branch_id FROM orders WHERE id = :id AND customer_id = :customer_id FOR UPDATE");
            $stmt->execute(['id' => $orderId, 'customer_id' => $customerId]);
            $order = $stmt->fetch();
            if (!$order) throw new RuntimeException('Order not found.');
            if ($order['status'] !== 'pending') throw new RuntimeException('Only pending orders can be cancelled.');

            $branchId = $order['branch_id'] !== null ? (int) $order['branch_id'] : null;
            $productStock = $this->db->prepare("UPDATE products SET stock = stock + :quantity WHERE id = :id");
            $variantStock = $this->db->prepare("UPDATE product_variants SET stock = stock + :quantity WHERE id = :id");
            $branchStockRestore = $this->db->prepare(
                "UPDATE branch_pos_stock SET stock = stock + :quantity
                 WHERE product_id = :product_id AND variant_size = :size AND variant_color = :color AND branch_id = :branch_id"
            );
            $branchStockLog = $this->db->prepare(
                "INSERT INTO branch_stock_adjustments
                    (product_id, variant_size, variant_color, branch_id, adjusted_by_user_id, adjusted_by_role, previous_stock, new_stock, change_amount, reason, note, created_at)
                 VALUES (:product_id, :size, :color, :branch_id, :user_id, 'customer', :previous, :new, :change, 'restock', :note, NOW())"
            );

            foreach ($this->getItems($orderId) as $item) {
                if ($branchId !== null) {
                    $size = $item['variant_size'] ?? '';
                    $color = $item['variant_color'] ?? '';
                    $prevStmt = $this->db->prepare(
                        "SELECT stock FROM branch_pos_stock WHERE product_id = :product_id AND variant_size = :size AND variant_color = :color AND branch_id = :branch_id"
                    );
                    $prevStmt->execute(['product_id' => (int) $item['product_id'], 'size' => $size, 'color' => $color, 'branch_id' => $branchId]);
                    $previous = (int) $prevStmt->fetchColumn();

                    $branchStockRestore->execute([
                        'quantity' => (int) $item['quantity'], 'product_id' => (int) $item['product_id'],
                        'size' => $size, 'color' => $color, 'branch_id' => $branchId,
                    ]);
                    $branchStockLog->execute([
                        'product_id' => (int) $item['product_id'], 'size' => $size, 'color' => $color, 'branch_id' => $branchId,
                        'user_id' => $customerId, 'previous' => $previous, 'new' => $previous + (int) $item['quantity'],
                        'change' => (int) $item['quantity'], 'note' => 'Order #' . $orderId . ' cancelled',
                    ]);
                } else {
                    $productStock->execute(['quantity' => (int) $item['quantity'], 'id' => (int) $item['product_id']]);
                    if (!empty($item['variant_id'])) $variantStock->execute(['quantity' => (int) $item['quantity'], 'id' => (int) $item['variant_id']]);
                }
            }
            $stmt = $this->db->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $orderId]);
            $this->db->commit();
            return ['success' => true, 'error' => null];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function countTodayBySeller(int $sellerId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM orders
             WHERE seller_id = :seller_id AND DATE(created_at) = CURDATE()"
        );
        $stmt->execute(['seller_id' => $sellerId]);

        return (int) $stmt->fetchColumn();
    }

    public function countPendingOnlineBySeller(int $sellerId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE seller_id = :seller_id AND order_type = 'online' AND status = 'pending'");
        $stmt->execute(['seller_id' => $sellerId]);
        return (int) $stmt->fetchColumn();
    }

    public function todaysRevenueBySeller(int $sellerId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) FROM orders
             WHERE seller_id = :seller_id AND DATE(created_at) = CURDATE() AND status IN ('completed')"
        );
        $stmt->execute(['seller_id' => $sellerId]);

        return (float) $stmt->fetchColumn();
    }

    public function getSalesByBranchesForSeller(int $sellerId, int $days = 30): array
    {
        $startDate = (new DateTimeImmutable('today'))->modify('-' . $days . ' days')->format('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT b.id AS branch_id, b.name AS branch_name,
                    COALESCE(SUM(CASE WHEN o.status = 'completed' AND o.created_at >= :start_date1 THEN o.total_amount ELSE 0 END), 0) AS revenue,
                    COALESCE(SUM(CASE WHEN o.status = 'completed' AND o.created_at >= :start_date2 THEN 1 ELSE 0 END), 0) AS orders
             FROM branches b
             LEFT JOIN orders o ON o.branch_id = b.id
             WHERE b.seller_id = :seller_id
             GROUP BY b.id, b.name
             ORDER BY revenue DESC"
        );
        $stmt->bindValue(':seller_id', $sellerId, PDO::PARAM_INT);
        $stmt->bindValue(':start_date1', $startDate);
        $stmt->bindValue(':start_date2', $startDate);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSalesAnalyticsBySeller(int $sellerId, ?int $branchId = null, bool $ownOnly = false): array
    {
        $sql = "SELECT
                COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) AS lifetime_revenue,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) AS completed_orders,
                COALESCE(SUM(CASE WHEN status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN total_amount ELSE 0 END), 0) AS revenue_30_days,
                COALESCE(SUM(CASE WHEN status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END), 0) AS orders_30_days
             FROM orders WHERE seller_id = :seller_id";
        $params = ['seller_id' => $sellerId];
        if ($branchId !== null) {
            $sql .= ' AND branch_id = :branch_id';
            $params['branch_id'] = $branchId;
        } elseif ($ownOnly) {
            $sql .= ' AND branch_id IS NULL';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }

    public function getDailySalesBySeller(int $sellerId, int $days = 7, ?int $branchId = null, bool $ownOnly = false): array
    {
        $startDate = (new DateTimeImmutable('today'))->modify('-' . $days . ' days')->format('Y-m-d');
        $sql = "SELECT DATE(created_at) AS sale_date, COUNT(*) AS orders, COALESCE(SUM(total_amount), 0) AS revenue
             FROM orders
             WHERE seller_id = :seller_id AND status = 'completed'
               AND created_at >= :start_date";
        if ($branchId !== null) {
            $sql .= ' AND branch_id = :branch_id';
        } elseif ($ownOnly) {
            $sql .= ' AND branch_id IS NULL';
        }
        $sql .= ' GROUP BY DATE(created_at) ORDER BY sale_date DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':seller_id', $sellerId, PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $startDate);
        if ($branchId !== null) {
            $stmt->bindValue(':branch_id', $branchId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDailySalesByBranch(int $branchId, int $days = 7): array
    {
        $startDate = (new DateTimeImmutable('today'))->modify('-' . $days . ' days')->format('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT DATE(created_at) AS sale_date, COUNT(*) AS orders, COALESCE(SUM(total_amount), 0) AS revenue
             FROM orders
             WHERE branch_id = :branch_id AND status = 'completed' AND created_at >= :start_date
             GROUP BY DATE(created_at) ORDER BY sale_date DESC"
        );
        $stmt->execute(['branch_id' => $branchId, 'start_date' => $startDate]);
        return $stmt->fetchAll();
    }

    public function getTopProductsBySeller(int $sellerId, int $limit = 5, ?int $branchId = null, bool $ownOnly = false): array
    {
        $sql = "SELECT oi.product_id, oi.product_name, oi.variant_label,
                    SUM(oi.quantity) AS units_sold, SUM(oi.subtotal) AS revenue
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             WHERE o.seller_id = :seller_id AND o.status = 'completed'";
        if ($branchId !== null) {
            $sql .= ' AND o.branch_id = :branch_id';
        } elseif ($ownOnly) {
            $sql .= ' AND o.branch_id IS NULL';
        }
        $sql .= ' GROUP BY oi.product_id, oi.product_name, oi.variant_label
             ORDER BY units_sold DESC, revenue DESC
             LIMIT :limit';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':seller_id', $sellerId, PDO::PARAM_INT);
        if ($branchId !== null) {
            $stmt->bindValue(':branch_id', $branchId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopProductsByBranch(int $branchId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT oi.product_id, oi.product_name, oi.variant_label,
                    SUM(oi.quantity) AS units_sold, SUM(oi.subtotal) AS revenue
             FROM order_items oi INNER JOIN orders o ON o.id = oi.order_id
             WHERE o.branch_id = :branch_id AND o.status = 'completed'
             GROUP BY oi.product_id, oi.product_name, oi.variant_label
             ORDER BY units_sold DESC, revenue DESC LIMIT :limit"
        );
        $stmt->bindValue(':branch_id', $branchId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function allByCustomer(int $customerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.name AS seller_name
             FROM orders o
             INNER JOIN users u ON u.id = o.seller_id
             WHERE o.customer_id = :customer_id
             ORDER BY o.created_at DESC"
        );
        $stmt->execute(['customer_id' => $customerId]);

        return $stmt->fetchAll();
    }

    public function findByIdForCustomer(int $id, int $customerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.name AS seller_name
             FROM orders o
             INNER JOIN users u ON u.id = o.seller_id
             WHERE o.id = :id AND o.customer_id = :customer_id LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'customer_id' => $customerId]);
        $order = $stmt->fetch();

        return $order ?: null;
    }

    public function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM order_items WHERE order_id = :order_id"
        );
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll();
    }

    public function getPlatformSalesSummary(): array
    {
        $sql = "SELECT
                COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) AS lifetime_revenue,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) AS completed_orders,
                COALESCE(SUM(CASE WHEN status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN total_amount ELSE 0 END), 0) AS revenue_30_days,
                COALESCE(SUM(CASE WHEN status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END), 0) AS orders_30_days
             FROM orders";
        return $this->db->query($sql)->fetch() ?: [];
    }

    public function getPlatformDailySales(int $days = 14): array
    {
        $startDate = (new DateTimeImmutable('today'))->modify('-' . $days . ' days')->format('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT DATE(created_at) AS sale_date, COUNT(*) AS orders, COALESCE(SUM(total_amount), 0) AS revenue
             FROM orders
             WHERE status = 'completed' AND created_at >= :start_date
             GROUP BY DATE(created_at) ORDER BY sale_date ASC"
        );
        $stmt->execute(['start_date' => $startDate]);
        return $stmt->fetchAll();
    }

    public function getTopSellersByRevenue(int $limit = 5, int $days = 30): array
    {
        $startDate = (new DateTimeImmutable('today'))->modify('-' . $days . ' days')->format('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT u.id AS seller_id, u.name AS seller_name,
                    COALESCE(SUM(o.total_amount), 0) AS revenue,
                    COUNT(o.id) AS orders
             FROM orders o
             INNER JOIN users u ON u.id = o.seller_id
             WHERE o.status = 'completed' AND o.created_at >= :start_date
             GROUP BY u.id, u.name
             ORDER BY revenue DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}