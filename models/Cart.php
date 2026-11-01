<?php
/**
 * Cart Model — session for guests, DB for logged-in users
 */
class Cart
{
    public function isUserCart(): bool { return is_logged_in(); }

    public function items(): array {
        if ($this->isUserCart()) {
            $uid = $_SESSION['user_id'];
            $s = db()->prepare('SELECT c.product_id, c.quantity, p.name, p.slug, p.price, p.discount_price, p.image, p.stock
                                FROM cart c JOIN products p ON p.id = c.product_id
                                WHERE c.user_id = ? ORDER BY c.id DESC');
            $s->execute([$uid]);
            return $s->fetchAll();
        }
        $items = [];
        foreach ($_SESSION['cart'] ?? [] as $pid => $qty) {
            $s = db()->prepare('SELECT id AS product_id, name, slug, price, discount_price, image, stock FROM products WHERE id = ? AND status = 1');
            $s->execute([(int)$pid]);
            if ($row = $s->fetch()) { $row['quantity'] = (int)$qty; $items[] = $row; }
        }
        return $items;
    }

    public function add(int $productId, int $qty = 1): void {
        $qty = max(1, $qty);
        if ($this->isUserCart()) {
            $uid = $_SESSION['user_id'];
            db()->prepare('INSERT INTO cart(user_id,product_id,quantity) VALUES(?,?,?)
                          ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)')
                ->execute([$uid, $productId, $qty]);
        } else {
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
        }
    }

    public function update(int $productId, int $qty): void {
        if ($qty <= 0) { $this->remove($productId); return; }
        if ($this->isUserCart()) {
            db()->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?')
                ->execute([$qty, $_SESSION['user_id'], $productId]);
        } else {
            $_SESSION['cart'][$productId] = $qty;
        }
    }

    public function remove(int $productId): void {
        if ($this->isUserCart()) {
            db()->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?')->execute([$_SESSION['user_id'], $productId]);
        } else {
            unset($_SESSION['cart'][$productId]);
        }
    }

    public function clear(): void {
        if ($this->isUserCart()) db()->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$_SESSION['user_id']]);
        else $_SESSION['cart'] = [];
    }

    public function count(): int {
        $n = 0;
        foreach ($this->items() as $it) $n += (int)$it['quantity'];
        return $n;
    }

    public function subtotal(): float {
        $t = 0.0;
        foreach ($this->items() as $it) {
            $price = (float)($it['discount_price'] ?: $it['price']);
            $t += $price * (int)$it['quantity'];
        }
        return round($t, 2);
    }

    public function totals(?array $couponResult = null): array {
        $subtotal = $this->subtotal();
        $shipping = $subtotal >= FREE_SHIPPING_THRESHOLD || $subtotal === 0.0 ? 0.0 : SHIPPING_FLAT_FEE;
        $discount = $couponResult && !empty($couponResult['ok']) ? (float)$couponResult['discount'] : 0.0;
        $taxable  = max(0, $subtotal - $discount);
        $tax      = round($taxable * TAX_RATE, 2);
        $total    = round($taxable + $shipping + $tax, 2);
        return compact('subtotal','shipping','discount','tax','total');
    }

    public function mergeSessionCart(int $userId, array $sessionCart): void {
        foreach ($sessionCart as $pid => $qty) {
            db()->prepare('INSERT INTO cart(user_id,product_id,quantity) VALUES(?,?,?)
                          ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)')
                ->execute([$userId, (int)$pid, (int)$qty]);
        }
    }
}

class Wishlist {
    public function items(int $userId): array {
        $s = db()->prepare('SELECT p.* FROM wishlist w JOIN products p ON p.id = w.product_id WHERE w.user_id = ? ORDER BY w.id DESC');
        $s->execute([$userId]);
        return $s->fetchAll();
    }
    public function add(int $userId, int $productId): void {
        db()->prepare('INSERT IGNORE INTO wishlist(user_id,product_id) VALUES(?,?)')->execute([$userId, $productId]);
    }
    public function remove(int $userId, int $productId): void {
        db()->prepare('DELETE FROM wishlist WHERE user_id=? AND product_id=?')->execute([$userId, $productId]);
    }
    public function has(int $userId, int $productId): bool {
        $s = db()->prepare('SELECT 1 FROM wishlist WHERE user_id=? AND product_id=?');
        $s->execute([$userId, $productId]);
        return (bool)$s->fetchColumn();
    }
    public function count(int $userId): int {
        $s = db()->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id=?');
        $s->execute([$userId]);
        return (int)$s->fetchColumn();
    }
}
