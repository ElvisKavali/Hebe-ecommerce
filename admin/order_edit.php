<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Order.php';
$om = new Order();
$id = (int)($_GET['id'] ?? 0);
$o = $om->find($id);
if (!$o) { flash('error','Order not found.'); redirect('admin/orders.php'); }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    if (isset($_POST['status']))         $om->setStatus($id, $_POST['status']);
    if (isset($_POST['payment_status'])) $om->setPayment($id, $_POST['payment_status'], null, $o['payment_method']);
    if (!empty($_POST['tracking_number']))$om->setTracking($id, trim($_POST['tracking_number']));
    flash('success','Order updated.'); redirect('admin/order_edit.php?id=' . $id);
}
$items = $om->items($id);
$pageTitle = 'Order ' . $o['order_number'];
?>
<a class="btn btn-link" href="<?= url('admin/orders.php') ?>"><i class="bi bi-arrow-left"></i> Back</a>
<div class="row g-3 mt-2">
  <div class="col-lg-8"><div class="admin-card p-3">
    <h5>Order <?= e($o['order_number']) ?></h5>
    <p class="text-muted small">Placed <?= date('M d, Y H:i', strtotime($o['created_at'])) ?></p>
    <table class="table"><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>
      <?php foreach ($items as $it): ?>
        <tr><td><?= e($it['name']) ?> <small class="text-muted"><?= e($it['sku']) ?></small></td><td><?= (int)$it['quantity'] ?></td><td><?= money((float)$it['price']) ?></td><td><?= money((float)$it['line_total']) ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
    <div class="row">
      <div class="col-md-6">
        <h6>Customer</h6>
        <p class="small"><?= e($o['full_name']) ?><br><?= e($o['phone']) ?><br><?= e($o['guest_email'] ?? '') ?></p>
        <h6>Shipping</h6>
        <p class="small"><?= e($o['address_line']) ?><br><?= e($o['city']) ?>, <?= e($o['country']) ?> <?= e($o['postal_code']) ?></p>
      </div>
      <div class="col-md-6">
        <div class="summary-row"><span>Subtotal</span><span><?= money((float)$o['subtotal']) ?></span></div>
        <?php if ($o['discount']>0): ?><div class="summary-row text-success"><span>Discount</span><span>-<?= money((float)$o['discount']) ?></span></div><?php endif; ?>
        <div class="summary-row"><span>Shipping</span><span><?= money((float)$o['shipping']) ?></span></div>
        <div class="summary-row"><span>Tax</span><span><?= money((float)$o['tax']) ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?= money((float)$o['total']) ?></span></div>
      </div>
    </div>
  </div></div>
  <div class="col-lg-4">
    <form method="post" class="admin-card p-3"><?= csrf_field() ?>
      <h6>Update status</h6>
      <select name="status" class="form-select mb-2">
        <?php foreach (['pending','confirmed','processing','packed','shipped','delivered','cancelled','returned','refunded'] as $s): ?>
          <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="payment_status" class="form-select mb-2">
        <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
          <option value="<?= $s ?>" <?= $o['payment_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <input class="form-control mb-2" name="tracking_number" placeholder="Tracking number" value="<?= e($o['tracking_number'] ?? '') ?>">
      <button class="btn btn-primary w-100">Save</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
