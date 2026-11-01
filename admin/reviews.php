<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Catalog.php';
$rm = new Review();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    if (!empty($_POST['delete_id'])) $rm->delete((int)$_POST['delete_id']);
    elseif (!empty($_POST['id']) && !empty($_POST['status'])) $rm->setStatus((int)$_POST['id'], $_POST['status']);
    flash('success','Updated.'); redirect('admin/reviews.php');
}
$rows = $rm->all();
$pageTitle = 'Reviews';
?>
<div class="admin-card"><table class="table table-clean mb-0">
  <thead><tr><th class="ps-3">Product</th><th>Author</th><th>Rating</th><th>Review</th><th>Status</th><th></th></tr></thead>
  <tbody><?php foreach ($rows as $r): ?>
    <tr>
      <td class="ps-3"><?= e($r['product_name']) ?></td>
      <td><?= e($r['author_name']) ?></td>
      <td><?= str_repeat('★', (int)$r['rating']) ?></td>
      <td style="max-width:340px"><strong><?= e($r['title']) ?></strong><br><small class="text-muted"><?= e(mb_strimwidth($r['body'], 0, 120, '…')) ?></small></td>
      <td><span class="badge text-bg-<?= $r['status']==='approved'?'success':($r['status']==='pending'?'warning':'secondary') ?>"><?= e($r['status']) ?></span></td>
      <td>
        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-outline-success" title="Approve"><i class="bi bi-check"></i></button></form>
        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-outline-warning"><i class="bi bi-x"></i></button></form>
        <form method="post" class="d-inline" data-confirm="Delete?"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
      </td>
    </tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
