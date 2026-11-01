<?php
/**
 * Global helpers: escape, csrf, flash, redirect, money, url
 */

function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

function url(string $path = ''): string {
    $path = ltrim($path, '/');
    if ($path === '') return rtrim(BASE_URL, '/');
    return rtrim(BASE_URL, '/') . '/' . $path;
}

function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void {
    $target = $path;
    if (!str_starts_with($path, 'http')) {
        $target = url($path);
    }
    header('Location: ' . $target);
    exit;
}

function money(float $amount): string {
    // Display amounts in Kenyan Shillings
    return 'KSh ' . number_format($amount, 2);
}

function csrf_token(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool {
    $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($token) && hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
}

function csrf_require(): void {
    if (!csrf_verify()) {
        http_response_code(419);
        die('CSRF token mismatch.');
    }
}

function flash(string $key, ?string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['_flash'][$key] = $msg; return null; }
    $m = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $m;
}

function old(string $key, $default = ''): string {
    return e($_SESSION['_old'][$key] ?? $default);
}

function set_old(array $data): void { $_SESSION['_old'] = $data; }
function clear_old(): void { unset($_SESSION['_old']); }

function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower(trim($text, '-'));
    return preg_replace('~[^-\w]+~', '', $text) ?: 'item';
}

function paginate(int $total, int $perPage, int $current): array {
    $pages = max(1, (int)ceil($total / $perPage));
    $current = max(1, min($current, $pages));
    return ['total' => $total, 'per_page' => $perPage, 'pages' => $pages, 'current' => $current, 'offset' => ($current - 1) * $perPage];
}

function upload_image(array $file, string $subdir = 'products'): ?string {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null; // 5MB max
    $dir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) return null;
    return $subdir . '/' . $name;
}

function product_image_url(?string $path): string {
    if (!$path) return asset('images/placeholder.svg');
    if (str_starts_with($path, 'http')) return $path;
    return UPLOAD_URL . '/' . $path;
}
