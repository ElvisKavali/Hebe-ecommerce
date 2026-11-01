<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Catalog.php';
$m = new Brand();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    if (!empty($_POST['delete_id'])) { $m->delete((int)$_POST['delete_id']); flash('success','Brand deleted.'); redirect('admin/brands.php'); }
    $d = ['name'=>trim($_POST['name']),'slug'=>slugify($_POST['slug'] ?: $_POST['name']),'status'=>(int)($_POST['status'] ?? 1)];
    if (!empty($_POST['id'])) { $m->update((int)$_POST['id'], $d); flash('success','Updated.'); }
    else { $m->create($d); flash('success','Created.'); }
    redirect('admin/brands.php');
}
$rows = $m->all(false);
$edit = !empty($_GET['edit']) ? $m->find((int)$_GET['edit']) : null;
$pageTitle = 'Brands';
?>
<div class="row g-3">
  <div class="col-lg-7"><div class="admin-card"><table class="table table-clean mb-0">
    <thead><tr><th class="ps-3">Name</th><th>Slug</th><th>Status</th><th></th></tr></thead>
    <tbody><?php foreach ($rows as $r): ?>
      <tr><td class="ps-3"><?= e($r['name']) ?></td><td><?= e($r['slug']) ?></td><td><?= $r['status']?'Active':'Hidden' ?></td>
      <td><a class="btn btn-sm btn-outline-primary" href="?edit=<?= (int)$r['id'] ?>"><i class="bi bi-pencil"></i></a>
          <form method="post" class="d-inline" data-confirm="Delete?"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>
    <?php endforeach; ?></tbody>
  </table></div></div>
  <div class="col-lg-5"><div class="admin-card p-3"><h6><?= $edit?'Edit':'New' ?> brand</h6>
    <form method="post"><?= csrf_field() ?>
      <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
      <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" required value="<?= e($edit['name'] ?? '') ?>"></div>
      <div class="mb-2"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?= e($edit['slug'] ?? '') ?>"></div>
      <div class="mb-2"><select class="form-select" name="status"><option value="1" <?= ($edit['status'] ?? 1)?'selected':'' ?>>Active</option><option value="0">Hidden</option></select></div>
      <button class="btn btn-primary"><?= $edit?'Update':'Create' ?></button>
    </form>
  </div></div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
