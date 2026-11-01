<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Order.php';
$o = new Order();
$rows = $o->all(['status'=>$_GET['status'] ?? '', 'q'=>$_GET['q'] ?? '']);
$statuses = ['pending','confirmed','processing','packed','shipped','delivered','cancelled','returned','refunded'];
$pageTitle = 'Orders';
?>
<form class="d-flex gap-2 mb-3">
  <input class="form-control form-control-sm" name="q" placeholder="Order # or name" value="<?= e($_GET['q'] ?? '') ?>" style="max-width:260px">
  <select class="form-select form-select-sm" name="status" style="max-width:180px" onchange="this.form.submit()">
    <option value="">All statuses</option>
    <?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= ($_GET['status'] ?? '')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
  </select>
  <button class="btn btn-sm btn-outline-primary">Filter</button>
</form>
<div class="admin-card"><table class="table table-clean mb-0">
  <thead><tr><th class="ps-3">Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr><td class="ps-3"><strong><?= e($r['order_number']) ?></strong></td>
        <td><?= e($r['full_name']) ?><br><small class="text-muted"><?= e($r['phone']) ?></small></td>
        <td><?= money((float)$r['total']) ?></td>
        <td><span class="badge-status <?= e($r['payment_status']) ?>"><?= e($r['payment_status']) ?></span></td>
        <td><span class="badge-status <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
        <td><?= date('M d, H:i', strtotime($r['created_at'])) ?></td>
        <td><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/order_edit.php?id=' . (int)$r['id']) ?>">Open</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table></div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
