<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/User.php';
$um = new User();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    if (!empty($_POST['delete_id'])) { $um->delete((int)$_POST['delete_id']); flash('success','User deleted.'); redirect('admin/users.php'); }
    $d = ['name'=>trim($_POST['name']),'email'=>strtolower(trim($_POST['email'])),'phone'=>trim($_POST['phone'] ?? ''),'role'=>$_POST['role'] ?? 'customer','password'=>$_POST['password'] ?? bin2hex(random_bytes(6)),'email_verified'=>1];
    $id = $um->create($d);
    flash('success','User created.'); redirect('admin/users.php');
}
$admins = $um->all('admin');
$customers = $um->all('customer');
$pageTitle = 'Users';
?>
<div class="row g-3">
  <div class="col-lg-7"><div class="admin-card"><div class="card-head"><strong>All users</strong></div><table class="table table-clean mb-0">
    <thead><tr><th class="ps-3">Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
    <tbody><?php foreach (array_merge($admins, $customers) as $r): ?>
      <tr><td class="ps-3"><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><span class="badge text-bg-<?= $r['role']==='admin'?'primary':'secondary' ?>"><?= e($r['role']) ?></span></td>
      <td><form method="post" class="d-inline" data-confirm="Delete?"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>
    <?php endforeach; ?></tbody></table></div></div>
  <div class="col-lg-5"><div class="admin-card p-3"><h6>Add user</h6>
    <form method="post"><?= csrf_field() ?>
      <input class="form-control mb-2" name="name" placeholder="Name" required>
      <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
      <input class="form-control mb-2" name="phone" placeholder="Phone">
      <input class="form-control mb-2" type="password" name="password" placeholder="Password" minlength="8" required>
      <select class="form-select mb-2" name="role"><option value="customer">Customer</option><option value="admin">Admin</option></select>
      <button class="btn btn-primary">Create user</button>
    </form>
  </div></div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
