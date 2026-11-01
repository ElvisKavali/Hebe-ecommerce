<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/User.php';
$um = new User();
if (!empty($_GET['delete'])) { $um->delete((int)$_GET['delete']); flash('success','Customer removed.'); redirect('admin/customers.php'); }
$rows = $um->all('customer');
$pageTitle = 'Customers';
?>
<div class="admin-card"><table class="table table-clean mb-0">
  <thead><tr><th class="ps-3">Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th><th></th></tr></thead>
  <tbody><?php foreach ($rows as $r): ?>
    <tr><td class="ps-3"><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['phone'] ?? '') ?></td>
    <td><span class="badge text-bg-<?= $r['status']==='active'?'success':'secondary' ?>"><?= e($r['status']) ?></span></td>
    <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
    <td><a class="btn btn-sm btn-outline-danger" href="?delete=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this customer?')"><i class="bi bi-trash"></i></a></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
