<?php
require_once __DIR__ . '/../includes/admin_header.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    foreach ($_POST['settings'] ?? [] as $k => $v) {
        db()->prepare('INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)')->execute([$k, (string)$v]);
    }
    flash('success','Settings saved.'); redirect('admin/settings.php');
}
$rows = db()->query('SELECT * FROM settings')->fetchAll();
$s = []; foreach ($rows as $r) $s[$r['key']] = $r['value'];
$pageTitle = 'Settings';
?>
<form method="post" class="admin-card p-3" style="max-width:720px">
  <?= csrf_field() ?>
  <h5 class="mb-3">Site settings</h5>
  <div class="mb-2"><label class="form-label">Site name</label><input class="form-control" name="settings[site_name]" value="<?= e($s['site_name'] ?? '') ?>"></div>
  <div class="mb-2"><label class="form-label">Site email</label><input class="form-control" name="settings[site_email]" value="<?= e($s['site_email'] ?? '') ?>"></div>
  <div class="mb-2"><label class="form-label">Phone</label><input class="form-control" name="settings[site_phone]" value="<?= e($s['site_phone'] ?? '') ?>"></div>
  <div class="mb-2"><label class="form-label">Address</label><input class="form-control" name="settings[site_address]" value="<?= e($s['site_address'] ?? '') ?>"></div>
  <div class="mb-2"><label class="form-label">About</label><textarea class="form-control" name="settings[about]" rows="3"><?= e($s['about'] ?? '') ?></textarea></div>
  <button class="btn btn-primary">Save settings</button>
</form>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
