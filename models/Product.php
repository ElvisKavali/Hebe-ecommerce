<?php
/**
 * Product Model — data access for products
 */
class Product
{
    public function find(int $id): ?array {
        $stmt = db()->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name
                               FROM products p
                               LEFT JOIN categories c ON c.id = p.category_id
                               LEFT JOIN brands b ON b.id = p.brand_id
                               WHERE p.id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?array {
        $stmt = db()->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name
                               FROM products p
                               LEFT JOIN categories c ON c.id = p.category_id
                               LEFT JOIN brands b ON b.id = p.brand_id
                               WHERE p.slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function gallery(int $productId): array {
        $stmt = db()->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC');
        $stmt->execute([$productId]);
        return array_column($stmt->fetchAll(), 'image');
    }

    public function featured(int $limit = 8): array {
        $stmt = db()->prepare('SELECT * FROM products WHERE status = 1 AND stock > 0 AND featured = 1 ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function bestsellers(int $limit = 8): array {
        $stmt = db()->prepare('SELECT * FROM products WHERE status = 1 AND stock > 0 AND bestseller = 1 ORDER BY rating DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function latest(int $limit = 8): array {
        $stmt = db()->prepare('SELECT * FROM products WHERE status = 1 AND stock > 0 ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function newArrivals(int $limit = 8): array {
        $stmt = db()->prepare('SELECT * FROM products WHERE status = 1 AND stock > 0 ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function related(int $productId, ?int $categoryId, int $limit = 4): array {
        $stmt = db()->prepare('SELECT * FROM products WHERE status = 1 AND stock > 0 AND id <> ? AND (category_id = ? OR ? IS NULL) ORDER BY RAND() LIMIT ?');
        $stmt->bindValue(1, $productId, PDO::PARAM_INT);
        $stmt->bindValue(2, $categoryId, $categoryId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(3, $categoryId, $categoryId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Filter + paginate.
     * @param array $f keys: q, category_id, brand_id, min_price, max_price, in_stock, sort, page, per_page
     */
    public function search(array $f): array {
        $where = [];
        $args  = [];

        if (empty($f['include_inactive'])) {
            $where[] = 'p.status = 1';
        }
        if (empty($f['include_out_of_stock'])) {
            $where[] = 'p.stock > 0';
        }

        if (!empty($f['q'])) {
            $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($args, $like, $like, $like);
        }
        if (!empty($f['category_id'])) { $where[] = 'p.category_id = ?'; $args[] = (int)$f['category_id']; }
        if (!empty($f['brand_id']))    { $where[] = 'p.brand_id = ?';    $args[] = (int)$f['brand_id']; }
        if (isset($f['min_price']) && $f['min_price'] !== '') { $where[] = 'COALESCE(p.discount_price, p.price) >= ?'; $args[] = (float)$f['min_price']; }
        if (isset($f['max_price']) && $f['max_price'] !== '') { $where[] = 'COALESCE(p.discount_price, p.price) <= ?'; $args[] = (float)$f['max_price']; }
        if (!empty($f['in_stock']))    { $where[] = 'p.stock > 0'; }
        if (!empty($f['min_rating']))  { $where[] = 'p.rating >= ?'; $args[] = (float)$f['min_rating']; }

        $orderMap = [
            'newest'    => 'p.created_at DESC',
            'price_asc' => 'COALESCE(p.discount_price, p.price) ASC',
            'price_desc'=> 'COALESCE(p.discount_price, p.price) DESC',
            'rating'    => 'p.rating DESC',
            'popular'   => 'p.reviews_count DESC',
        ];
        $order = $orderMap[$f['sort'] ?? 'newest'] ?? $orderMap['newest'];

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = db()->prepare("SELECT COUNT(*) FROM products p $whereSql");
        $countStmt->execute($args);
        $total = (int)$countStmt->fetchColumn();

        $perPage = max(1, min((int)($f['per_page'] ?? 12), 48));
        $page    = max(1, (int)($f['page'] ?? 1));
        $p       = paginate($total, $perPage, $page);

        $sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN brands b ON b.id = p.brand_id
                $whereSql
                ORDER BY $order
                LIMIT ? OFFSET ?";
        $stmt = db()->prepare($sql);
        $i = 1;
        foreach ($args as $a) {
            $stmt->bindValue($i++, $a, is_int($a) ? PDO::PARAM_INT : (is_float($a) ? PDO::PARAM_STR : PDO::PARAM_STR));
        }
        $stmt->bindValue($i++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($i++, $p['offset'], PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'pagination' => $p];
    }

    public function liveSearch(string $q, int $limit = 6): array {
        $like = '%' . $q . '%';
        $stmt = db()->prepare('SELECT id, name, slug, price, discount_price, image FROM products WHERE status = 1 AND (name LIKE ? OR description LIKE ? OR sku LIKE ?) ORDER BY rating DESC LIMIT ?');
        $stmt->bindValue(1, $like);
        $stmt->bindValue(2, $like);
        $stmt->bindValue(3, $like);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $d): int {
        $sql = 'INSERT INTO products (name, slug, sku, barcode, category_id, brand_id, description, specifications,
                price, discount_price, stock, weight, dimensions, image, tags, featured, bestseller, status)
                VALUES (:name,:slug,:sku,:barcode,:cat,:brand,:desc,:spec,:price,:dprice,:stock,:weight,:dims,:image,:tags,:feat,:best,:status)';
        $stmt = db()->prepare($sql);
        $stmt->execute([
            ':name'=>$d['name'], ':slug'=>$d['slug'], ':sku'=>$d['sku'], ':barcode'=>$d['barcode'] ?? null,
            ':cat'=>$d['category_id'] ?: null, ':brand'=>$d['brand_id'] ?: null,
            ':desc'=>$d['description'] ?? '', ':spec'=>$d['specifications'] ?? '',
            ':price'=>$d['price'], ':dprice'=>$d['discount_price'] ?: null,
            ':stock'=>$d['stock'] ?? 0,
            ':weight'=>$d['weight'] ?: null, ':dims'=>$d['dimensions'] ?? null,
            ':image'=>$d['image'] ?? null, ':tags'=>$d['tags'] ?? null,
            ':feat'=>!empty($d['featured']) ? 1 : 0, ':best'=>!empty($d['bestseller']) ? 1 : 0,
            ':status'=>isset($d['status']) ? (int)$d['status'] : 1,
        ]);
        return (int)db()->lastInsertId();
    }

    public function update(int $id, array $d): void {
        $sql = 'UPDATE products SET name=:name, slug=:slug, sku=:sku, barcode=:barcode, category_id=:cat,
                brand_id=:brand, description=:desc, specifications=:spec, price=:price, discount_price=:dprice,
                stock=:stock, weight=:weight, dimensions=:dims, tags=:tags, featured=:feat, bestseller=:best, status=:status'
                . (isset($d['image']) ? ', image=:image' : '') . ' WHERE id=:id';
        $params = [
            ':name'=>$d['name'], ':slug'=>$d['slug'], ':sku'=>$d['sku'], ':barcode'=>$d['barcode'] ?? null,
            ':cat'=>$d['category_id'] ?: null, ':brand'=>$d['brand_id'] ?: null,
            ':desc'=>$d['description'] ?? '', ':spec'=>$d['specifications'] ?? '',
            ':price'=>$d['price'], ':dprice'=>$d['discount_price'] ?: null,
            ':stock'=>$d['stock'] ?? 0,
            ':weight'=>$d['weight'] ?: null, ':dims'=>$d['dimensions'] ?? null,
            ':tags'=>$d['tags'] ?? null,
            ':feat'=>!empty($d['featured']) ? 1 : 0, ':best'=>!empty($d['bestseller']) ? 1 : 0,
            ':status'=>isset($d['status']) ? (int)$d['status'] : 1, ':id'=>$id,
        ];
        if (isset($d['image'])) $params[':image'] = $d['image'];
        db()->prepare($sql)->execute($params);
    }

    public function delete(int $id): void {
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    }

    public function updateStock(int $id, int $stock): void {
        db()->prepare('UPDATE products SET stock = ? WHERE id = ?')->execute([$stock, $id]);
    }

    public function lowStock(int $threshold = 10): array {
        $stmt = db()->prepare('SELECT id, name, sku, stock FROM products WHERE status = 1 AND stock <= ? ORDER BY stock ASC LIMIT 20');
        $stmt->bindValue(1, $threshold, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function recalcRating(int $productId): void {
        $stmt = db()->prepare('SELECT AVG(rating) avg_r, COUNT(*) c FROM reviews WHERE product_id = ? AND status = "approved"');
        $stmt->execute([$productId]);
        $row = $stmt->fetch();
        db()->prepare('UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?')
            ->execute([round((float)($row['avg_r'] ?? 0), 2), (int)$row['c'], $productId]);
    }
}
