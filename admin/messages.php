<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Catalog.php';
$c = new Contact();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    if (!empty($_POST['mark_read'])) $c->markRead((int)$_POST['mark_read']);
    if (!empty($_POST['delete_id'])) $c->delete((int)$_POST['delete_id']);
    redirect('admin/messages.php');
}
$rows = $c->all();
$pageTitle = 'Messages';
?>
<div class="admin-card"><table class="table table-clean mb-0">
  <thead><tr><th class="ps-3">From</th><th>Subject</th><th>Message</th><th>Status</th><th>Date</th><th></th></tr></thead>
  <tbody><?php foreach ($rows as $r): ?>
    <tr class="<?= $r['read_flag']?'':'table-warning' ?>">
      <td class="ps-3"><strong><?= e($r['name']) ?></strong><br><small class="text-muted"><?= e($r['email']) ?></small></td>
      <td><?= e($r['subject']) ?></td>
      <td style="max-width:340px"><small class="text-muted"><?= e(mb_strimwidth($r['message'], 0, 160, '…')) ?></small></td>
      <td><?= $r['read_flag']?'Read':'<span class="badge bg-warning">New</span>' ?></td>
      <td><?= date('M d, H:i', strtotime($r['created_at'])) ?></td>
      <td>
        <?php if(!$r['read_flag']): ?><form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="mark_read" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-primary">Mark read</button></form><?php endif; ?>
        <form method="post" class="d-inline" data-confirm="Delete?"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
      </td>
    </tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
