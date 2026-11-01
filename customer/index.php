<?php
require_once __DIR__ . '/../config/config.php';
require_login();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
$user = current_user();
$orders = (new Order())->forUser((int)$user['id']);
$wlCount = (new Wishlist())->count((int)$user['id']);
$pageTitle = 'My Account';
?>
<div class="container my-4">
  <div class="row g-4">
    <aside class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></aside>
    <div class="col-lg-9">
      <h1 class="h3">Welcome, <?= e(explode(' ', $user['name'])[0]) ?></h1>
      <div class="row g-3 mt-2">
        <div class="col-md-4"><div class="checkout-card text-center"><div class="display-6 text-primary"><?= count($orders) ?></div><div class="text-muted">Orders</div></div></div>
        <div class="col-md-4"><div class="checkout-card text-center"><div class="display-6 text-primary"><?= $wlCount ?></div><div class="text-muted">Wishlist items</div></div></div>
        <div class="col-md-4"><div class="checkout-card text-center"><div class="display-6 text-primary"><?= count(array_filter($orders, fn($o)=>in_array($o['status'],['confirmed','processing','packed','shipped']))) ?></div><div class="text-muted">Active orders</div></div></div>
      </div>
      <div class="checkout-card mt-4">
        <h5>Recent orders</h5>
        <?php if (!$orders): ?><p class="text-muted">No orders yet. <a href="<?= url('views/shop.php') ?>">Start shopping</a>.</p>
        <?php else: ?>
          <table class="table"><thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
            <?php foreach (array_slice($orders,0,5) as $o): ?>
              <tr><td><?= e($o['order_number']) ?></td><td><?= date('M d', strtotime($o['created_at'])) ?></td><td><?= money((float)$o['total']) ?></td><td><span class="badge-status <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td><td><a class="btn btn-sm btn-outline-primary" href="<?= url('customer/order.php?id=' . (int)$o['id']) ?>">View</a></td></tr>
            <?php endforeach; ?>
          </tbody></table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
