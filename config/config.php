<?php
/**
 * HEBE Kitchen Store — Global Configuration
 */

// Environment: 'development' | 'production'
define('APP_ENV', 'development');

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ---------- App ----------
define('APP_NAME', 'hebe_Store');
$host = !empty($_SERVER['HTTP_HOST']) ? (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : 'http://localhost';
$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? 'D:/xampp/htdocs') ?: 'D:/xampp/htdocs');
$projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: __DIR__ . '/..');
$basePath = '';
if ($projectRoot && $documentRoot && str_starts_with($projectRoot, $documentRoot . '/')) {
    $basePath = '/' . ltrim(substr($projectRoot, strlen($documentRoot)), '/');
} elseif (!empty($_SERVER['SCRIPT_NAME'])) {
    $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
}
$basePath = $basePath && $basePath !== '/' ? rtrim($basePath, '/') : '/';
define('BASE_URL', rtrim(getenv('APP_URL') ?: $host . $basePath, '/'));
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads');
define('UPLOAD_URL', BASE_URL . '/assets/uploads');

// ---------- Database ----------
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hebe_store');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---------- Session / Security ----------
define('SESSION_LIFETIME', 60 * 60 * 2); // 2 hours
define('CSRF_TOKEN_NAME', '_csrf');

// Secure session cookie params
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_name('HEBESESSID');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Payments ----------
// Stripe TEST keys — replace with your own from https://dashboard.stripe.com/test/apikeys
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_REPLACE_ME');
define('STRIPE_SECRET_KEY', 'sk_test_REPLACE_ME');
// Use Kenyan Shillings as the default currency for payments/display
define('STRIPE_CURRENCY', 'kes');

// M-Pesa Daraja (stub)
define('MPESA_CONSUMER_KEY', '');
define('MPESA_CONSUMER_SECRET', '');
define('MPESA_SHORTCODE', '174379');
define('MPESA_PASSKEY', '');
define('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke');
define('MPESA_CALLBACK_URL', BASE_URL . '/api/mpesa-callback.php');

// PayPal (stub)
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_SECRET', '');

// ---------- Shipping / Tax ----------
define('SHIPPING_FLAT_FEE', 5.00);
define('FREE_SHIPPING_THRESHOLD', 100.00);
define('TAX_RATE', 0.08); // 8%

// Load DB + helpers
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
