<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
$pageTitle = 'Track Order';
$order = null; $items = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $num = trim($_POST['order_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $order = (new Order())->findByNumber($num);
    if (!$order || (strcasecmp($order['guest_email'] ?? '', $email) !== 0 && (!$order['user_id'] || strcasecmp((new User())->find((int)$order['user_id'])['email'] ?? '', $email) !== 0))) {
        flash('error', 'Order not found.');
        $order = null;
    } else {
        $items = (new Order())->items((int)$order['id']);
    }
}
  require_once __DIR__ . '/../includes/header.php';
?>
<div class="container my-5" style="max-width:720px">
  <h1>Track your order</h1>
  <form method="post" class="checkout-card mt-3">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Order number</label><input class="form-control" name="order_number" placeholder="HEBE-100001" required></div>
      <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
      <div class="col-12"><button class="btn btn-primary">Track</button></div>
    </div>
  </form>
  <?php if ($order): ?>
    <div class="checkout-card mt-4">
      <div class="d-flex justify-content-between"><h5>Order <?= e($order['order_number']) ?></h5><span class="badge-status <?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></div>
      <p class="text-muted">Placed on <?= e(date('M d, Y', strtotime($order['created_at']))) ?> · Total <?= money((float)$order['total']) ?></p>
      <?php $steps=['pending','confirmed','processing','packed','shipped','delivered']; $current=array_search($order['status'],$steps); $current = $current === false ? 0 : $current; ?>
      <div class="d-flex justify-content-between mb-2 small">
        <?php foreach ($steps as $i=>$s): ?>
          <div class="<?= $i<=$current?'text-primary fw-semibold':'text-muted' ?>"><?= ucfirst($s) ?></div>
        <?php endforeach; ?>
      </div>
      <div class="progress"><div class="progress-bar" style="width: <?= round(($current+1)/count($steps)*100) ?>%"></div></div>
      <?php if ($order['tracking_number']): ?><p class="mt-2 small">Tracking #: <strong><?= e($order['tracking_number']) ?></strong></p><?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
