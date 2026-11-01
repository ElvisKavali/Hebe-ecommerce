<?php
require_once __DIR__ . '/../config/config.php';
require_login();
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../includes/header.php';
$orders = (new Order())->forUser((int)current_user()['id']);
$pageTitle = 'My Orders';
?>
<div class="container my-4">
  <div class="row g-4">
    <aside class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></aside>
    <div class="col-lg-9">
      <h1 class="h3">My orders</h1>
      <div class="checkout-card mt-3">
        <?php if (!$orders): ?><p class="text-muted">No orders yet.</p>
        <?php else: ?>
        <table class="table"><thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead><tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><?= e($o['order_number']) ?></td>
              <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
              <td><?= money((float)$o['total']) ?></td>
              <td><span class="badge-status <?= e($o['payment_status']) ?>"><?= e(ucfirst($o['payment_status'])) ?></span></td>
              <td><span class="badge-status <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
              <td><a class="btn btn-sm btn-outline-primary" href="<?= url('customer/order.php?id=' . (int)$o['id']) ?>">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody></table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
