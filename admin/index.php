<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';

$o = new Order();
$stats = $o->stats();
$recent = $o->recent(8);
$lowStock = (new Product())->lowStock(10);
$sales = $o->salesLast30Days();
$pageTitle = 'Dashboard';
?>
<div class="row g-3">
  <div class="col-md-3"><div class="stat-card"><div class="label">Revenue (total)</div><div class="value text-primary"><?= money($stats['revenue_total']) ?></div><div class="delta"><?= money($stats['revenue_month']) ?> last 30d</div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="label">Orders</div><div class="value"><?= $stats['orders_total'] ?></div><div class="delta"><?= $stats['orders_pending'] ?> pending</div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="label">Customers</div><div class="value"><?= $stats['customers'] ?></div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="label">Products</div><div class="value"><?= $stats['products'] ?></div></div></div>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-8">
    <div class="admin-card mb-3">
      <div class="card-head"><strong>Store management</strong></div>
      <div class="card-body-p">
        <p class="mb-3">Use the product manager to add new items, update product names and prices, and manage stock from the website itself.</p>
        <a class="btn btn-primary" href="<?= url('admin/products.php') ?>"><i class="bi bi-box-seam"></i> Open product manager</a>
      </div>
    </div>
    <div class="admin-card"><div class="card-head"><strong>Revenue · last 30 days</strong></div><div class="card-body-p"><canvas id="salesChart" height="100"></canvas></div></div>
  </div>
  <div class="col-lg-4">
    <div class="admin-card"><div class="card-head"><strong>Low stock alert</strong></div><div class="card-body-p p-0">
      <table class="table table-clean mb-0"><thead><tr><th class="ps-3">Product</th><th>Stock</th></tr></thead><tbody>
        <?php foreach ($lowStock as $p): ?>
          <tr><td class="ps-3"><a href="<?= url('admin/product_edit.php?id=' . (int)$p['id']) ?>"><?= e($p['name']) ?></a></td><td><span class="badge text-bg-<?= $p['stock']<=2?'danger':'warning' ?>"><?= (int)$p['stock'] ?></span></td></tr>
        <?php endforeach; ?>
        <?php if (!$lowStock): ?><tr><td colspan="2" class="text-muted ps-3">All good!</td></tr><?php endif; ?>
      </tbody></table>
    </div></div>
  </div>
</div>

<div class="admin-card mt-3"><div class="card-head"><strong>Recent orders</strong><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/orders.php') ?>">All orders</a></div>
  <table class="table table-clean mb-0">
    <thead><tr><th class="ps-3">Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td class="ps-3"><strong><?= e($r['order_number']) ?></strong></td>
          <td><?= e($r['full_name']) ?></td>
          <td><?= money((float)$r['total']) ?></td>
          <td><span class="badge-status <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td><?= date('M d, H:i', strtotime($r['created_at'])) ?></td>
          <td><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/order_edit.php?id=' . (int)$r['id']) ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
const ctx = document.getElementById('salesChart');
new Chart(ctx, {
  type:'line',
  data:{ labels: <?= json_encode(array_column($sales, 'date')) ?>,
         datasets:[{ label:'Revenue', data: <?= json_encode(array_column($sales, 'revenue')) ?>, borderColor:'#0D6EFD', backgroundColor:'rgba(13,110,253,.1)', fill:true, tension:.3 }]},
  options:{ plugins:{ legend:{ display:false }}, scales:{ y:{ beginAtZero:true } } }
});
</script>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
