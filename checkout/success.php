<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';

$id = (int)($_GET['id'] ?? 0);
$order = (new Order())->find($id);
if (!$order) { flash('error', 'Order not found.'); redirect('/'); }
$items = (new Order())->items($id);

// Mark stripe orders as paid if we got back from Stripe with a session_id
if (!empty($_GET['session_id']) && $order['payment_method'] === 'stripe' && $order['payment_status'] !== 'paid') {
    (new Order())->setPayment($id, 'paid', $_GET['session_id'], 'stripe');
    (new Order())->setStatus($id, 'confirmed');
    (new Cart())->clear();
    unset($_SESSION['coupon_code'], $_SESSION['pending_order_id']);
    $order = (new Order())->find($id);
}
$pageTitle = 'Order placed';
?>
<div class="container my-5" style="max-width:720px">
  <div class="checkout-card text-center">
    <div class="display-1 text-success mb-3"><i class="bi bi-check-circle-fill"></i></div>
    <h1>Thank you for your order!</h1>
    <p class="text-muted">Order <strong><?= e($order['order_number']) ?></strong> · Total <strong><?= money((float)$order['total']) ?></strong></p>
    <p>We'll email you a confirmation. You can track the status anytime.</p>
    <div class="d-flex justify-content-center gap-2 mt-3">
      <a class="btn btn-primary" href="<?= url('views/track.php') ?>">Track order</a>
      <a class="btn btn-outline-primary" href="<?= url('views/shop.php') ?>">Continue shopping</a>
    </div>
  </div>
  <div class="checkout-card mt-3">
    <h6>Items</h6>
    <?php foreach ($items as $it): ?>
      <div class="d-flex justify-content-between py-1 small"><span><?= e($it['name']) ?> × <?= (int)$it['quantity'] ?></span><span><?= money((float)$it['line_total']) ?></span></div>
    <?php endforeach; ?>
    <hr>
    <div class="summary-row"><span>Subtotal</span><span><?= money((float)$order['subtotal']) ?></span></div>
    <?php if ($order['discount']>0): ?><div class="summary-row text-success"><span>Discount</span><span>-<?= money((float)$order['discount']) ?></span></div><?php endif; ?>
    <div class="summary-row"><span>Shipping</span><span><?= $order['shipping']>0 ? money((float)$order['shipping']) : 'Free' ?></span></div>
    <div class="summary-row"><span>Tax</span><span><?= money((float)$order['tax']) ?></span></div>
    <div class="summary-row total"><span>Total</span><span><?= money((float)$order['total']) ?></span></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
