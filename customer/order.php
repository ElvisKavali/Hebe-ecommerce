<?php
require_once __DIR__ . '/../config/config.php';
require_login();
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$o = (new Order())->find($id);
$user = current_user();
if (!$o || (int)$o['user_id'] !== (int)$user['id']) { http_response_code(404); echo '<div class="container my-5">Order not found.</div>'; require_once __DIR__ . '/../includes/footer.php'; exit; }
$items = (new Order())->items($id);
$pageTitle = 'Order ' . $o['order_number'];
?>
<div class="container my-4">
  <a href="<?= url('customer/orders.php') ?>" class="btn btn-link"><i class="bi bi-arrow-left"></i> Back</a>
  <div class="checkout-card mt-3">
    <div class="d-flex justify-content-between flex-wrap">
      <div><h2 class="h4">Order <?= e($o['order_number']) ?></h2><p class="text-muted small">Placed <?= date('M d, Y H:i', strtotime($o['created_at'])) ?></p></div>
      <div class="text-end"><span class="badge-status <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span><br><small class="text-muted">Payment: <?= e($o['payment_status']) ?> via <?= e($o['payment_method']) ?></small></div>
    </div>
    <div class="row g-3 mt-2">
      <div class="col-md-6"><h6>Shipping to</h6><p class="small text-muted"><?= e($o['full_name']) ?><br><?= e($o['address_line']) ?><br><?= e($o['city']) ?>, <?= e($o['country']) ?> <?= e($o['postal_code']) ?><br><?= e($o['phone']) ?></p></div>
      <div class="col-md-6">
        <h6>Items</h6>
        <?php foreach ($items as $it): ?>
          <div class="d-flex justify-content-between small py-1"><span><?= e($it['name']) ?> × <?= (int)$it['quantity'] ?></span><span><?= money((float)$it['line_total']) ?></span></div>
        <?php endforeach; ?>
        <hr>
        <div class="summary-row"><span>Subtotal</span><span><?= money((float)$o['subtotal']) ?></span></div>
        <?php if ($o['discount']>0): ?><div class="summary-row text-success"><span>Discount</span><span>-<?= money((float)$o['discount']) ?></span></div><?php endif; ?>
        <div class="summary-row"><span>Shipping</span><span><?= $o['shipping']>0 ? money((float)$o['shipping']) : 'Free' ?></span></div>
        <div class="summary-row"><span>Tax</span><span><?= money((float)$o['tax']) ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?= money((float)$o['total']) ?></span></div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
