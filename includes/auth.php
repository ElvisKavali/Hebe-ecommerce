<?php
/**
 * Auth helpers & middleware
 */

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null && $cache['id'] == $_SESSION['user_id']) return $cache;
    $stmt = db()->prepare('SELECT id, name, email, role, avatar, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $cache = $stmt->fetch() ?: null;
    if (!$cache) { unset($_SESSION['user_id']); }
    return $cache;
}

function is_logged_in(): bool { return current_user() !== null; }
function is_admin(): bool { $u = current_user(); return $u && $u['role'] === 'admin'; }

function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['_redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
        flash('error', 'Please log in to continue.');
        redirect('auth/login.php');
    }
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Forbidden.');
    }
}

function login_user(int $userId, bool $remember = false): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('hebe_remember', $userId . ':' . $token, time() + 60*60*24*30, '/', '', isset($_SERVER['HTTPS']), true);
        db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?')->execute([hash('sha256', $token), $userId]);
    }
    // Merge guest cart into DB cart
    if (!empty($_SESSION['cart'])) {
        require_once __DIR__ . '/../models/Cart.php';
        (new Cart())->mergeSessionCart($userId, $_SESSION['cart']);
        unset($_SESSION['cart']);
    }
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    setcookie('hebe_remember', '', time() - 3600, '/');
    session_destroy();
}
