<?php
/**
 * Hebe Store — App Configuration
 *
 * Include this once from your bootstrap / index.php:
 *   require_once __DIR__ . '/config/app.php';
 *
 * Sensitive credentials (M-Pesa keys, SMTP password, etc.) should live in
 * a .env file or environment variables — never committed to Git.
 * Install vlucas/phpdotenv (composer) or use getenv() directly.
 */

// ── Environment ──────────────────────────────────────────────────────────────
define('APP_ENV',   getenv('APP_ENV') ?: 'development');   // 'production' on live
define('APP_DEBUG', APP_ENV !== 'production');

// ── URLs & Paths ─────────────────────────────────────────────────────────────
define('APP_URL',      rtrim(getenv('APP_URL') ?: 'http://localhost/hebe-store', '/'));
define('ROOT_PATH',    dirname(__DIR__));
define('UPLOAD_PATH',  ROOT_PATH . '/assets/uploads');
define('PRODUCT_UPLOAD_PATH', UPLOAD_PATH . '/products');
define('AVATAR_UPLOAD_PATH',  UPLOAD_PATH . '/avatars');

// ── Upload limits ────────────────────────────────────────────────────────────
define('MAX_UPLOAD_SIZE',      5 * 1024 * 1024);    // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// ── Session ──────────────────────────────────────────────────────────────────
define('SESSION_NAME',     'hebe_session');
define('SESSION_LIFETIME', 60 * 60 * 2);            // 2 hours

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode',  '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (APP_ENV === 'production') {
        ini_set('session.cookie_secure', '1');       // HTTPS only on live
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Security ─────────────────────────────────────────────────────────────────
define('BCRYPT_COST', 12);                           // password_hash() cost factor
define('CSRF_TOKEN_KEY', '_csrf_token');

// ── M-Pesa / Daraja ──────────────────────────────────────────────────────────
// Get these from https://developer.safaricom.co.ke
// Use SANDBOX keys in development, LIVE keys on production.
define('MPESA_ENV',              getenv('MPESA_ENV')              ?: 'sandbox');
define('MPESA_CONSUMER_KEY',     getenv('MPESA_CONSUMER_KEY')     ?: '');
define('MPESA_CONSUMER_SECRET',  getenv('MPESA_CONSUMER_SECRET')  ?: '');
define('MPESA_SHORTCODE',        getenv('MPESA_SHORTCODE')        ?: '174379');   // sandbox till
define('MPESA_PASSKEY',          getenv('MPESA_PASSKEY')          ?: '');
define('MPESA_CALLBACK_URL',     APP_URL . '/api/mpesa-callback.php');
// Base URLs (auto-selected by MPESA_ENV)
define('MPESA_BASE_URL', MPESA_ENV === 'production'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke');

// ── Email (SMTP via PHPMailer) ────────────────────────────────────────────────
define('MAIL_HOST',     getenv('MAIL_HOST')     ?: 'smtp.gmail.com');
define('MAIL_PORT',     getenv('MAIL_PORT')     ?: 587);
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');
define('MAIL_FROM',     getenv('MAIL_FROM')     ?: 'no-reply@hebe-store.co.ke');
define('MAIL_FROM_NAME', 'Hebe Store');

// ── Error handling ───────────────────────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_PATH . '/storage/logs/error.log');
}
