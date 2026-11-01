<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Product.php';
$q = trim((string)($_GET['q'] ?? ''));
$result = $q ? (new Product())->search(['q'=>$q,'page'=>(int)($_GET['page']??1),'per_page'=>12,'sort'=>'newest']) : ['items'=>[],'pagination'=>['pages'=>0,'current'=>1,'total'=>0]];
$pageTitle = 'Search: ' . ($q ?: '');
?>
<div class="container my-4">
  <h1 class="h3">Search results for "<?= e($q) ?>"</h1>
  <p class="text-muted"><?= $result['pagination']['total'] ?> products found</p>
  <?php if (!$result['items']): ?>
    <div class="empty"><i class="bi bi-search"></i><h5>Nothing matched</h5><p>Try different keywords.</p></div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($result['items'] as $p): ?>
        <div class="col-6 col-md-4 col-lg-3"><?php include __DIR__ . '/../includes/product_card.php'; ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
