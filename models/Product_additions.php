// ── Add this method inside your existing Product class (models/Product.php) ──

    /**
     * Get all sellable products (active + in stock) across all sellers,
     * for the customer-facing storefront. Optionally filter by search
     * term (matches product name) and/or category.
     */
    public function getAllActive(string $search = '', ?string $category = null): array
    {
        $sql = "SELECT p.*, u.name AS seller_name
                FROM products p
                INNER JOIN users u ON u.id = p.seller_id
                WHERE p.status = 'active' AND p.stock > 0";
        $params = [];

        if ($search !== '') {
            $sql .= " AND p.name LIKE :search";
            $params['search'] = "%{$search}%";
        }

        if ($category !== null && $category !== '') {
            $sql .= " AND p.category = :category";
            $params['category'] = $category;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Distinct list of categories currently in use, for the storefront
     * filter dropdown.
     */
    public function getActiveCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT category FROM products
             WHERE status = 'active' AND stock > 0 AND category IS NOT NULL
             ORDER BY category ASC"
        );

        return array_column($stmt->fetchAll(), 'category');
    }