<?php
/**
 * Generic simple CRUD models for categories, brands, coupons, contacts.
 */

class Category {
    public function all(bool $activeOnly = true): array {
        $sql = 'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 1) AS product_count
                FROM categories c' . ($activeOnly ? ' WHERE c.status = 1' : '') . ' ORDER BY c.name ASC';
        return db()->query($sql)->fetchAll();
    }
    public function find(int $id): ?array { $s = db()->prepare('SELECT * FROM categories WHERE id=?'); $s->execute([$id]); return $s->fetch() ?: null; }
    public function findBySlug(string $slug): ?array { $s = db()->prepare('SELECT * FROM categories WHERE slug=?'); $s->execute([$slug]); return $s->fetch() ?: null; }
    public function create(array $d): int {
        db()->prepare('INSERT INTO categories(name,slug,description,image,status) VALUES(?,?,?,?,?)')
            ->execute([$d['name'], $d['slug'], $d['description'] ?? '', $d['image'] ?? null, (int)($d['status'] ?? 1)]);
        return (int)db()->lastInsertId();
    }
    public function update(int $id, array $d): void {
        $hasImage = array_key_exists('image', $d);
        $sql = 'UPDATE categories SET name=?, slug=?, description=?, status=?' . ($hasImage ? ', image=?' : '') . ' WHERE id=?';
        $args = [$d['name'], $d['slug'], $d['description'] ?? '', (int)($d['status'] ?? 1)];
        if ($hasImage) $args[] = $d['image'];
        $args[] = $id;
        db()->prepare($sql)->execute($args);
    }
    public function delete(int $id): void { db()->prepare('DELETE FROM categories WHERE id=?')->execute([$id]); }
}

class Brand {
    public function all(bool $activeOnly = true): array {
        $sql = 'SELECT * FROM brands' . ($activeOnly ? ' WHERE status = 1' : '') . ' ORDER BY name ASC';
        return db()->query($sql)->fetchAll();
    }
    public function find(int $id): ?array { $s = db()->prepare('SELECT * FROM brands WHERE id=?'); $s->execute([$id]); return $s->fetch() ?: null; }
    public function create(array $d): int {
        db()->prepare('INSERT INTO brands(name,slug,logo,status) VALUES(?,?,?,?)')
            ->execute([$d['name'], $d['slug'], $d['logo'] ?? null, (int)($d['status'] ?? 1)]);
        return (int)db()->lastInsertId();
    }
    public function update(int $id, array $d): void {
        $hasLogo = array_key_exists('logo', $d);
        $sql = 'UPDATE brands SET name=?, slug=?, status=?' . ($hasLogo ? ', logo=?' : '') . ' WHERE id=?';
        $args = [$d['name'], $d['slug'], (int)($d['status'] ?? 1)];
        if ($hasLogo) $args[] = $d['logo'];
        $args[] = $id;
        db()->prepare($sql)->execute($args);
    }
    public function delete(int $id): void { db()->prepare('DELETE FROM brands WHERE id=?')->execute([$id]); }
}

class Coupon {
    public function all(): array { return db()->query('SELECT * FROM coupons ORDER BY created_at DESC')->fetchAll(); }
    public function findByCode(string $code): ?array {
        $s = db()->prepare('SELECT * FROM coupons WHERE code=? AND status=1 LIMIT 1');
        $s->execute([strtoupper(trim($code))]);
        return $s->fetch() ?: null;
    }
    public function apply(string $code, float $subtotal): array {
        $c = $this->findByCode($code);
        if (!$c) return ['ok' => false, 'message' => 'Invalid coupon code.'];
        if ($c['expires_at'] && strtotime($c['expires_at']) < time()) return ['ok' => false, 'message' => 'Coupon expired.'];
        if ($c['max_uses'] !== null && $c['used_count'] >= $c['max_uses']) return ['ok' => false, 'message' => 'Coupon limit reached.'];
        if ($subtotal < (float)$c['min_total']) return ['ok' => false, 'message' => 'Minimum order ' . money((float)$c['min_total']) . ' not met.'];
        $discount = $c['type'] === 'percent' ? round($subtotal * ((float)$c['value'] / 100), 2) : (float)$c['value'];
        $discount = min($discount, $subtotal);
        return ['ok' => true, 'coupon' => $c, 'discount' => $discount, 'message' => 'Coupon applied.'];
    }
    public function create(array $d): int {
        db()->prepare('INSERT INTO coupons(code,type,value,min_total,max_uses,expires_at,status) VALUES(?,?,?,?,?,?,?)')
            ->execute([strtoupper($d['code']), $d['type'], $d['value'], $d['min_total'] ?? 0, $d['max_uses'] ?: null, $d['expires_at'] ?: null, (int)($d['status'] ?? 1)]);
        return (int)db()->lastInsertId();
    }
    public function update(int $id, array $d): void {
        db()->prepare('UPDATE coupons SET code=?, type=?, value=?, min_total=?, max_uses=?, expires_at=?, status=? WHERE id=?')
            ->execute([strtoupper($d['code']), $d['type'], $d['value'], $d['min_total'] ?? 0, $d['max_uses'] ?: null, $d['expires_at'] ?: null, (int)($d['status'] ?? 1), $id]);
    }
    public function delete(int $id): void { db()->prepare('DELETE FROM coupons WHERE id=?')->execute([$id]); }
    public function incrementUse(int $id): void { db()->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id=?')->execute([$id]); }
}

class Contact {
    public function create(array $d): int {
        db()->prepare('INSERT INTO contacts(name,email,subject,message) VALUES(?,?,?,?)')
            ->execute([$d['name'], $d['email'], $d['subject'], $d['message']]);
        @mail('hebeappliances@gmail.com', 'HEBE contact: ' . $d['subject'], $d['message'], 'Reply-To: ' . $d['email']);
        return (int)db()->lastInsertId();
    }
    public function all(): array { return db()->query('SELECT * FROM contacts ORDER BY created_at DESC')->fetchAll(); }
    public function markRead(int $id): void { db()->prepare('UPDATE contacts SET read_flag = 1 WHERE id=?')->execute([$id]); }
    public function delete(int $id): void { db()->prepare('DELETE FROM contacts WHERE id=?')->execute([$id]); }
    public function unreadCount(): int { return (int)db()->query('SELECT COUNT(*) FROM contacts WHERE read_flag = 0')->fetchColumn(); }
}

class Review {
    public function forProduct(int $productId): array {
        $s = db()->prepare('SELECT * FROM reviews WHERE product_id=? AND status="approved" ORDER BY created_at DESC');
        $s->execute([$productId]);
        return $s->fetchAll();
    }
    public function create(array $d): int {
        db()->prepare('INSERT INTO reviews(product_id,user_id,author_name,rating,title,body,status) VALUES(?,?,?,?,?,?,?)')
            ->execute([$d['product_id'], $d['user_id'] ?? null, $d['author_name'], (int)$d['rating'], $d['title'] ?? null, $d['body'] ?? '', $d['status'] ?? 'approved']);
        $id = (int)db()->lastInsertId();
        (new Product())->recalcRating((int)$d['product_id']);
        return $id;
    }
    public function all(): array {
        return db()->query('SELECT r.*, p.name AS product_name FROM reviews r LEFT JOIN products p ON p.id=r.product_id ORDER BY r.created_at DESC')->fetchAll();
    }
    public function setStatus(int $id, string $status): void {
        db()->prepare('UPDATE reviews SET status=? WHERE id=?')->execute([$status, $id]);
        $r = db()->prepare('SELECT product_id FROM reviews WHERE id=?'); $r->execute([$id]);
        if ($pid = $r->fetchColumn()) (new Product())->recalcRating((int)$pid);
    }
    public function delete(int $id): void {
        $r = db()->prepare('SELECT product_id FROM reviews WHERE id=?'); $r->execute([$id]);
        $pid = $r->fetchColumn();
        db()->prepare('DELETE FROM reviews WHERE id=?')->execute([$id]);
        if ($pid) (new Product())->recalcRating((int)$pid);
    }
}
