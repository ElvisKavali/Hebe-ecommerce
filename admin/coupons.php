<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Catalog.php';
$m = new Coupon();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    if (!empty($_POST['delete_id'])) { $m->delete((int)$_POST['delete_id']); flash('success','Coupon deleted.'); redirect('admin/coupons.php'); }
    $d = ['code'=>$_POST['code'],'type'=>$_POST['type'],'value'=>(float)$_POST['value'],'min_total'=>(float)($_POST['min_total'] ?? 0),'max_uses'=>$_POST['max_uses']!==''?(int)$_POST['max_uses']:null,'expires_at'=>$_POST['expires_at'] ?: null,'status'=>(int)($_POST['status'] ?? 1)];
    if (!empty($_POST['id'])) { $m->update((int)$_POST['id'], $d); flash('success','Updated.'); }
    else { $m->create($d); flash('success','Created.'); }
    redirect('admin/coupons.php');
}
$rows = $m->all();
$edit = !empty($_GET['edit']) ? (db()->prepare('SELECT * FROM coupons WHERE id=?'))->execute([(int)$_GET['edit']]) : null;
if (!empty($_GET['edit'])) { $s = db()->prepare('SELECT * FROM coupons WHERE id=?'); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$pageTitle = 'Coupons';
?>
<div class="row g-3">
  <div class="col-lg-7"><div class="admin-card"><table class="table table-clean mb-0">
    <thead><tr><th class="ps-3">Code</th><th>Type</th><th>Value</th><th>Used</th><th>Expires</th><th></th></tr></thead>
    <tbody><?php foreach ($rows as $r): ?>
      <tr><td class="ps-3"><strong><?= e($r['code']) ?></strong></td><td><?= e($r['type']) ?></td><td><?= $r['type']==='percent'?(int)$r['value'].'%':money((float)$r['value']) ?></td><td><?= (int)$r['used_count'] ?>/<?= $r['max_uses'] ?? '∞' ?></td><td><?= e($r['expires_at'] ?? '—') ?></td>
      <td><a class="btn btn-sm btn-outline-primary" href="?edit=<?= (int)$r['id'] ?>"><i class="bi bi-pencil"></i></a>
      <form method="post" class="d-inline" data-confirm="Delete?"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>
    <?php endforeach; ?></tbody>
  </table></div></div>
  <div class="col-lg-5"><div class="admin-card p-3"><h6><?= $edit?'Edit':'New' ?> coupon</h6>
    <form method="post"><?= csrf_field() ?>
      <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
      <div class="mb-2"><label class="form-label">Code</label><input class="form-control" name="code" required value="<?= e($edit['code'] ?? '') ?>"></div>
      <div class="row">
        <div class="col mb-2"><select class="form-select" name="type"><option value="percent" <?= ($edit['type'] ?? 'percent')==='percent'?'selected':'' ?>>Percent</option><option value="fixed" <?= ($edit['type'] ?? '')==='fixed'?'selected':'' ?>>Fixed amount</option></select></div>
        <div class="col mb-2"><input class="form-control" type="number" step="0.01" name="value" placeholder="Value" required value="<?= e($edit['value'] ?? '') ?>"></div>
      </div>
      <div class="row">
        <div class="col mb-2"><input class="form-control" type="number" step="0.01" name="min_total" placeholder="Min order $" value="<?= e($edit['min_total'] ?? 0) ?>"></div>
        <div class="col mb-2"><input class="form-control" type="number" name="max_uses" placeholder="Max uses" value="<?= e($edit['max_uses'] ?? '') ?>"></div>
      </div>
      <div class="mb-2"><input class="form-control" type="date" name="expires_at" value="<?= e($edit['expires_at'] ?? '') ?>"></div>
      <div class="mb-2"><select class="form-select" name="status"><option value="1" <?= ($edit['status'] ?? 1)?'selected':'' ?>>Active</option><option value="0">Disabled</option></select></div>
      <button class="btn btn-primary"><?= $edit?'Update':'Create' ?></button>
    </form>
  </div></div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
