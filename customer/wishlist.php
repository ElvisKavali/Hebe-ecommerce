<?php
require_once __DIR__ . '/../config/config.php';
require_login();
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../includes/header.php';
$user = current_user();
$items = (new Wishlist())->items((int)$user['id']);
$pageTitle = 'Wishlist';
?>
<div class="container my-4">
  <div class="row g-4">
    <aside class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></aside>
    <div class="col-lg-9">
      <h1 class="h3">My wishlist</h1>
      <?php if (!$items): ?><div class="empty"><i class="bi bi-heart"></i><h5>Your wishlist is empty</h5></div>
      <?php else: ?>
        <div class="row g-4 mt-1">
          <?php foreach ($items as $p): $p['category_name']=''; ?>
            <div class="col-md-6 col-lg-4"><?php include __DIR__ . '/../includes/product_card.php'; ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
