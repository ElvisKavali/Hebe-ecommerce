<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Catalog.php';

$pm = new Product();
if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['id'])) {
    $pm->delete((int)$_GET['id']); flash('success','Product deleted.'); redirect('admin/products.php');
}
if (($_GET['action'] ?? '') === 'mark_sold_out' && !empty($_GET['id'])) {
    $pm->updateStock((int)$_GET['id'], 0); flash('success','Product marked as sold out.'); redirect('admin/products.php');
}
if (($_GET['action'] ?? '') === 'restock' && !empty($_GET['id'])) {
    $pm->updateStock((int)$_GET['id'], 1); flash('success','Product restocked.'); redirect('admin/products.php');
}
$res = $pm->search(['q'=>$_GET['q'] ?? '', 'page'=>(int)($_GET['page'] ?? 1), 'per_page'=>20, 'sort'=>'newest', 'include_inactive'=>true, 'include_out_of_stock'=>true]);
$pageTitle = 'Product Manager';
?>
<div class="alert alert-primary d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <strong>Admin product manager</strong>
    <div class="small">Use this page to add new products, edit prices and names, update stock, and mark items sold out.</div>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= url('admin/product_edit.php') ?>"><i class="bi bi-plus-circle"></i> Add product</a>
</div>
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2 align-items-center">
  <form class="d-flex gap-2"><input class="form-control form-control-sm" name="q" placeholder="Search..." value="<?= e($_GET['q'] ?? '') ?>"><button class="btn btn-sm btn-outline-primary">Search</button></form>
  <div class="d-flex gap-2">
    <a class="btn btn-success btn-sm" href="<?= url('admin/product_edit.php') ?>"><i class="bi bi-plus-circle"></i> Add product</a>
  </div>
</div>
<div class="admin-card">
  <table class="table table-clean mb-0">
    <thead><tr><th class="ps-3">Product</th><th>SKU</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($res['items'] as $p): ?>
        <tr>
          <td class="ps-3"><div class="d-flex gap-2 align-items-center"><img src="<?= e(product_image_url($p['image'])) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px"><div><div class="fw-semibold"><?= e($p['name']) ?></div><small class="text-muted"><?= e($p['category_name'] ?? '') ?></small></div></div></td>
          <td><?= e($p['sku']) ?></td>
          <td><?= money((float)($p['discount_price'] ?: $p['price'])) ?></td>
          <td><?= (int)$p['stock'] ?></td>
          <td><span class="badge text-bg-<?= $p['status']?'success':'secondary' ?>"><?= $p['status']?'Active':'Hidden' ?></span></td>
          <td>
            <div class="d-flex gap-2 flex-wrap">
              <a class="btn btn-sm btn-outline-primary" href="<?= url('admin/product_edit.php?id=' . (int)$p['id']) ?>"><i class="bi bi-pencil"></i> Edit</a>
              <?php if ((int)$p['stock'] > 0): ?>
                <a class="btn btn-sm btn-outline-warning" href="<?= url('admin/products.php?action=mark_sold_out&id=' . (int)$p['id']) ?>" onclick="return confirm('Mark this product as sold out?')"><i class="bi bi-bag-x"></i> Sold out</a>
              <?php else: ?>
                <a class="btn btn-sm btn-outline-success" href="<?= url('admin/products.php?action=restock&id=' . (int)$p['id']) ?>"><i class="bi bi-bag-check"></i> Restock</a>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-danger" href="<?= url('admin/products.php?action=delete&id=' . (int)$p['id']) ?>" onclick="return confirm('Delete this product?')"><i class="bi bi-trash"></i> Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php if ($res['pagination']['pages']>1): ?>
  <nav class="mt-3"><ul class="pagination">
  <?php for($i=1;$i<=$res['pagination']['pages'];$i++): ?>
    <li class="page-item <?= $i==$res['pagination']['current']?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
  <?php endfor; ?>
  </ul></nav>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
