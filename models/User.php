<?php
/**
 * User Model
 */
class User {
    public function find(int $id): ?array {
        $s = db()->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$id]);
        return $s->fetch() ?: null;
    }
    public function findByEmail(string $email): ?array {
        $s = db()->prepare('SELECT * FROM users WHERE email=?'); $s->execute([strtolower(trim($email))]);
        return $s->fetch() ?: null;
    }
    public function create(array $d): int {
        $hash = password_hash($d['password'], PASSWORD_BCRYPT);
        db()->prepare('INSERT INTO users(name,email,password,phone,role,email_verified,verify_token) VALUES(?,?,?,?,?,?,?)')
            ->execute([
                $d['name'], strtolower(trim($d['email'])), $hash, $d['phone'] ?? null,
                $d['role'] ?? 'customer', (int)($d['email_verified'] ?? 1), $d['verify_token'] ?? null,
            ]);
        return (int)db()->lastInsertId();
    }
    public function update(int $id, array $d): void {
        $fields = []; $args = [];
        foreach (['name','email','phone','role','status','avatar'] as $k) {
            if (array_key_exists($k, $d)) { $fields[] = "$k = ?"; $args[] = $d[$k]; }
        }
        if (!$fields) return;
        $args[] = $id;
        db()->prepare('UPDATE users SET ' . implode(',', $fields) . ' WHERE id=?')->execute($args);
    }
    public function updatePassword(int $id, string $newPassword): void {
        db()->prepare('UPDATE users SET password=? WHERE id=?')
            ->execute([password_hash($newPassword, PASSWORD_BCRYPT), $id]);
    }
    public function delete(int $id): void { db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]); }
    public function all(string $role = 'customer'): array {
        $s = db()->prepare('SELECT id,name,email,phone,role,status,created_at FROM users WHERE role=? ORDER BY created_at DESC');
        $s->execute([$role]);
        return $s->fetchAll();
    }
    public function setResetToken(int $id, string $token, int $minutes = 60): void {
        $expires = (new DateTime("+$minutes minutes"))->format('Y-m-d H:i:s');
        db()->prepare('UPDATE users SET reset_token=?, reset_expires=? WHERE id=?')->execute([hash('sha256', $token), $expires, $id]);
    }
    public function findByResetToken(string $token): ?array {
        $s = db()->prepare('SELECT * FROM users WHERE reset_token=? AND reset_expires > NOW()');
        $s->execute([hash('sha256', $token)]);
        return $s->fetch() ?: null;
    }
    public function clearResetToken(int $id): void {
        db()->prepare('UPDATE users SET reset_token=NULL, reset_expires=NULL WHERE id=?')->execute([$id]);
    }
}

class Address {
    public function forUser(int $userId): array {
        $s = db()->prepare('SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC, id DESC');
        $s->execute([$userId]); return $s->fetchAll();
    }
    public function find(int $id, int $userId): ?array {
        $s = db()->prepare('SELECT * FROM addresses WHERE id=? AND user_id=?');
        $s->execute([$id, $userId]); return $s->fetch() ?: null;
    }
    public function create(int $userId, array $d): int {
        if (!empty($d['is_default'])) {
            db()->prepare('UPDATE addresses SET is_default=0 WHERE user_id=?')->execute([$userId]);
        }
        db()->prepare('INSERT INTO addresses(user_id,label,full_name,phone,line1,line2,city,state,postal_code,country,is_default) VALUES(?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$userId, $d['label'] ?? 'Home', $d['full_name'], $d['phone'], $d['line1'], $d['line2'] ?? null, $d['city'], $d['state'] ?? null, $d['postal_code'] ?? null, $d['country'] ?? 'Kenya', !empty($d['is_default']) ? 1 : 0]);
        return (int)db()->lastInsertId();
    }
    public function delete(int $id, int $userId): void {
        db()->prepare('DELETE FROM addresses WHERE id=? AND user_id=?')->execute([$id, $userId]);
    }
    public function defaultFor(int $userId): ?array {
        $s = db()->prepare('SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC, id ASC LIMIT 1');
        $s->execute([$userId]); return $s->fetch() ?: null;
    }
}
