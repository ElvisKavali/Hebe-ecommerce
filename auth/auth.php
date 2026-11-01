<?php
/**
 * Hebe Store — Auth Helpers
 *
 * Usage:
 *   require_once __DIR__ . '/../auth/auth.php';
 *
 *   auth_require();           // redirect to login if not logged in
 *   auth_require('admin');    // redirect if not an admin
 *   $user = auth_user();      // current user row or null
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

/** Return the logged-in user array, or null if guest. */
function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** True if someone is logged in. */
function auth_check(): bool
{
    return isset($_SESSION['user']['id']);
}

/**
 * Guard: redirect to login if not authenticated (or not the required role).
 *
 * @param string|null $role  'admin' | 'customer' | null (any authenticated)
 */
function auth_require(?string $role = null): void
{
    if (!auth_check()) {
        header('Location: ' . APP_URL . '/auth/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if ($role !== null && ($_SESSION['user']['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Access denied.');
    }
}

/**
 * Attempt login.
 *
 * @return array|false  User row on success, false on bad credentials.
 */
function auth_login(string $email, string $password): array|false
{
    $pdo  = db_connect();
    $user = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    $user->execute([strtolower(trim($email))]);
    $row  = $user->fetch();

    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }

    // Rehash if PHP's cost factor has been updated
    if (password_needs_rehash($row['password_hash'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST])) {
        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
            ->execute([$newHash, $row['id']]);
    }

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'        => $row['id'],
        'full_name' => $row['full_name'],
        'email'     => $row['email'],
        'role'      => $row['role'],
        'avatar'    => $row['avatar'],
    ];

    return $_SESSION['user'];
}

/** Log the current user out and destroy the session. */
function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ── CSRF protection ───────────────────────────────────────────────────────────

/** Generate (or return existing) CSRF token for the current session. */
function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

/** Render a hidden CSRF input — paste into every HTML form. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validate the CSRF token on a POST request.
 * Call at the top of every POST handler.
 *
 * @throws RuntimeException on token mismatch.
 */
function csrf_verify(): void
{
    $token = $_POST[CSRF_TOKEN_KEY] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        throw new RuntimeException('CSRF token mismatch.');
    }
}
