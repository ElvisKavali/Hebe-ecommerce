<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Catalog.php';

$__categories = (new Category())->all();
$__cartCount  = (new Cart())->count();
$__user       = current_user();
$__wishlistCount = $__user ? (new Wishlist())->count((int)$__user['id']) : 0;
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · HEBE Kitchen Store</title>
<meta name="description" content="<?= e($pageMeta ?? 'Premium kitchenware and small kitchen electronics. Curated, durable, and beautifully designed.') ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageMeta ?? 'Premium kitchenware and small kitchen electronics.') ?>">
<meta property="og:type" content="website">
<link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= asset('css/style.css') . '?v=' . filemtime(__DIR__ . '/../assets/css/style.css') ?>" rel="stylesheet">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<script>window.HEBE = { BASE_URL: <?= json_encode(BASE_URL) ?>, CSRF: <?= json_encode(csrf_token()) ?> };</script>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<?php if ($m = flash('success')): ?><div class="container mt-3"><div class="alert alert-success alert-dismissible fade show"><?= e($m) ?><button class="btn-close" data-bs-dismiss="alert"></button></div></div><?php endif; ?>
<?php if ($m = flash('error')):   ?><div class="container mt-3"><div class="alert alert-danger alert-dismissible fade show"><?= e($m) ?><button class="btn-close" data-bs-dismiss="alert"></button></div></div><?php endif; ?>
<main class="page-main" id="main-content">
<?php
$__currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$__isHomePage = ($__currentScript === 'index.php' && strpos($_SERVER['SCRIPT_NAME'] ?? '', '/customer/') === false && strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') === false);
if (!$__isHomePage) include __DIR__ . '/page_hero.php';
?>
