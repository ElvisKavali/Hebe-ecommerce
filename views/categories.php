<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Catalog.php';
$cats = (new Category())->all();
$pageTitle = 'Categories';
?>
<div class="container my-4">
  <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li><li class="breadcrumb-item active">Categories</li></ol></nav>
  <h1 class="mb-4">All Categories</h1>
  <div class="row g-4">
    <?php foreach ($cats as $c): ?>
      <div class="col-md-6 col-lg-4">
        <a href="<?= url('views/shop.php?category=' . e($c['slug'])) ?>" class="card-product d-block">
          <div class="img-wrap" style="aspect-ratio:16/9;display:flex;align-items:center;justify-content:center">
            <i class="bi bi-grid-3x3-gap" style="font-size:4rem;color:var(--hebe-blue)"></i>
          </div>
          <div class="body">
            <div class="title"><?= e($c['name']) ?></div>
            <p class="text-muted small mb-0"><?= e($c['description'] ?? '') ?></p>
            <span class="stock-pill"><?= $c['product_count'] ?> products</span>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
