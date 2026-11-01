<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Catalog.php';
$__contact = new Contact();
$__pendingMessages = $__contact->unreadCount();
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Admin') ?> · HEBE Admin</title>
<link rel="icon" href="<?= asset('images/favicon.svg') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body class="admin-body">
<aside class="admin-sidebar">
  <div class="admin-brand"><a href="<?= url('admin/index.php') ?>">HEBE<span>.</span> Admin</a></div>
  <nav class="admin-nav">
    <a href="<?= url('admin/index.php') ?>" class="<?= $current==='index.php'?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="<?= url('admin/products.php') ?>" class="<?= $current==='products.php'?'active':'' ?>"><i class="bi bi-box-seam"></i> Products</a>
    <a href="<?= url('admin/categories.php') ?>" class="<?= $current==='categories.php'?'active':'' ?>"><i class="bi bi-grid"></i> Categories</a>
    <a href="<?= url('admin/brands.php') ?>" class="<?= $current==='brands.php'?'active':'' ?>"><i class="bi bi-tags"></i> Brands</a>
    <a href="<?= url('admin/orders.php') ?>" class="<?= $current==='orders.php'?'active':'' ?>"><i class="bi bi-receipt"></i> Orders</a>
    <a href="<?= url('admin/customers.php') ?>" class="<?= $current==='customers.php'?'active':'' ?>"><i class="bi bi-people"></i> Customers</a>
    <a href="<?= url('admin/coupons.php') ?>" class="<?= $current==='coupons.php'?'active':'' ?>"><i class="bi bi-ticket-perforated"></i> Coupons</a>
    <a href="<?= url('admin/reviews.php') ?>" class="<?= $current==='reviews.php'?'active':'' ?>"><i class="bi bi-star"></i> Reviews</a>
    <a href="<?= url('admin/messages.php') ?>" class="<?= $current==='messages.php'?'active':'' ?>"><i class="bi bi-chat-dots"></i> Messages <?php if ($__pendingMessages): ?><span class="badge bg-danger ms-1"><?= $__pendingMessages ?></span><?php endif; ?></a>
    <a href="<?= url('admin/users.php') ?>" class="<?= $current==='users.php'?'active':'' ?>"><i class="bi bi-person-gear"></i> Users</a>
    <a href="<?= url('admin/settings.php') ?>" class="<?= $current==='settings.php'?'active':'' ?>"><i class="bi bi-gear"></i> Settings</a>
    <a href="<?= url('/') ?>" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Store</a>
    <a href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </nav>
</aside>
<div class="admin-main">
  <header class="admin-topbar">
    <button class="btn btn-link d-lg-none" id="sidebarToggle"><i class="bi bi-list fs-3"></i></button>
    <h1 class="admin-title h5 mb-0"><?= e($pageTitle ?? 'Dashboard') ?></h1>
    <div class="ms-auto d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-outline-secondary" id="darkToggle"><i class="bi bi-moon-stars"></i></button>
      <span class="text-muted small d-none d-md-inline">Hi, <?= e(current_user()['name']) ?></span>
    </div>
  </header>
  <?php if ($m = flash('success')): ?><div class="alert alert-success m-3"><?= e($m) ?></div><?php endif; ?>
  <?php if ($m = flash('error')):   ?><div class="alert alert-danger m-3"><?= e($m) ?></div><?php endif; ?>
  <div class="admin-content p-3 p-md-4">
