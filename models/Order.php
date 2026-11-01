<?php
/**
 * Order Model
 */
class Order
{
    public function create(array $d, array $items, array $totals, string $paymentMethod): int {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $orderNumber = 'HEBE-' . str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $pdo->prepare('INSERT INTO orders
                (order_number,user_id,guest_email,full_name,phone,address_line,city,country,postal_code,notes,
                 subtotal,shipping,tax,discount,total,coupon_code,payment_method,payment_status,status)
                 VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([
                    $orderNumber,
                    $d['user_id'] ?? null,
                    $d['guest_email'] ?? null,
                    $d['full_name'], $d['phone'], $d['address_line'], $d['city'],
                    $d['country'] ?? 'Kenya', $d['postal_code'] ?? null, $d['notes'] ?? null,
                    $totals['subtotal'], $totals['shipping'], $totals['tax'], $totals['discount'], $totals['total'],
                    $d['coupon_code'] ?? null,
                    $paymentMethod,
                    $paymentMethod === 'cod' ? 'pending' : 'pending',
                    'pending',
                ]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items(order_id,product_id,name,sku,price,quantity,line_total) VALUES(?,?,?,?,?,?,?)');
            $stockStmt = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
            foreach ($items as $it) {
                $price = (float)($it['discount_price'] ?: $it['price']);
                $qty   = (int)$it['quantity'];
                $itemStmt->execute([$orderId, $it['product_id'], $it['name'], $it['sku'] ?? null, $price, $qty, round($price * $qty, 2)]);
                $stockStmt->execute([$qty, $it['product_id']]);
            }
            $pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function find(int $id): ?array {
        $s = db()->prepare('SELECT * FROM orders WHERE id=?'); $s->execute([$id]);
        return $s->fetch() ?: null;
    }
    public function findByNumber(string $num): ?array {
        $s = db()->prepare('SELECT * FROM orders WHERE order_number=?'); $s->execute([$num]);
        return $s->fetch() ?: null;
    }
    public function items(int $orderId): array {
        $s = db()->prepare('SELECT * FROM order_items WHERE order_id=?'); $s->execute([$orderId]);
        return $s->fetchAll();
    }
    public function forUser(int $userId): array {
        $s = db()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
        $s->execute([$userId]); return $s->fetchAll();
    }
    public function all(array $filters = []): array {
        $where = []; $args = [];
        if (!empty($filters['status']))         { $where[] = 'status = ?';         $args[] = $filters['status']; }
        if (!empty($filters['payment_status'])) { $where[] = 'payment_status = ?'; $args[] = $filters['payment_status']; }
        if (!empty($filters['q'])) {
            $where[] = '(order_number LIKE ? OR full_name LIKE ? OR phone LIKE ?)';
            $like = '%' . $filters['q'] . '%'; array_push($args, $like, $like, $like);
        }
        $sql = 'SELECT * FROM orders' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC LIMIT 200';
        $s = db()->prepare($sql); $s->execute($args);
        return $s->fetchAll();
    }
    public function setStatus(int $id, string $status): void {
        db()->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$status, $id]);
    }
    public function setPayment(int $id, string $status, ?string $reference = null, string $method = 'stripe', ?string $payload = null): void {
        db()->prepare('UPDATE orders SET payment_status=?, payment_method=? WHERE id=?')->execute([$status, $method, $id]);
        $pdo = db();
        $row = $pdo->prepare('SELECT id FROM payments WHERE order_id = ? LIMIT 1');
        $row->execute([$id]);
        $paymentId = $row->fetchColumn();
        if ($paymentId) {
            $pdo->prepare('UPDATE payments SET method=?, amount=(SELECT total FROM orders WHERE id=?), reference=?, status=?, payload=? WHERE id=?')
                ->execute([$method, $id, $reference, $status, $payload, $paymentId]);
        } else {
            $pdo->prepare('INSERT INTO payments(order_id,method,amount,reference,status,payload) SELECT id,?,total,?,?,? FROM orders WHERE id=?')
                ->execute([$method, $reference, $status, $payload, $id]);
        }
    }
    public function setTracking(int $id, string $number): void {
        db()->prepare('UPDATE orders SET tracking_number=? WHERE id=?')->execute([$number, $id]);
    }
    public function stats(): array {
        $pdo = db();
        return [
            'orders_total'   => (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'orders_pending' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
            'revenue_total'  => (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid'")->fetchColumn(),
            'revenue_month'  => (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
            'customers'      => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
            'products'       => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        ];
    }
    public function recent(int $limit = 8): array {
        $s = db()->prepare('SELECT id, order_number, full_name, total, status, payment_status, created_at FROM orders ORDER BY created_at DESC LIMIT ?');
        $s->bindValue(1, $limit, PDO::PARAM_INT);
        $s->execute(); return $s->fetchAll();
    }
    public function salesLast30Days(): array {
        $rows = db()->query("SELECT DATE(created_at) d, COALESCE(SUM(total),0) revenue
                             FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
                             GROUP BY DATE(created_at)")->fetchAll();
        $map = [];
        foreach ($rows as $r) $map[$r['d']] = (float)$r['revenue'];
        $out = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $out[] = ['date' => $d, 'revenue' => $map[$d] ?? 0];
        }
        return $out;
    }
}
