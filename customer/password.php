<?php
require_once __DIR__ . '/../config/config.php';
require_login();
require_once __DIR__ . '/../models/User.php';
$user = current_user();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $cur = (string)($_POST['current'] ?? '');
    $new = (string)($_POST['new'] ?? '');
    $cf  = (string)($_POST['confirm'] ?? '');
    $full = (new User())->find((int)$user['id']);
    if (!password_verify($cur, $full['password'])) flash('error','Current password incorrect.');
    elseif (strlen($new) < 8 || $new !== $cf)      flash('error','New passwords must match and be at least 8 chars.');
    else { (new User())->updatePassword((int)$user['id'], $new); flash('success','Password updated.'); }
    redirect('customer/password.php');
}
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Change password';
?>
<div class="container my-4">
  <div class="row g-4">
    <aside class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></aside>
    <div class="col-lg-9">
      <h1 class="h3">Change password</h1>
      <form method="post" class="checkout-card mt-3" style="max-width:480px">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Current password</label><input type="password" class="form-control" name="current" required></div>
        <div class="mb-3"><label class="form-label">New password</label><input type="password" class="form-control" name="new" minlength="8" required></div>
        <div class="mb-3"><label class="form-label">Confirm</label><input type="password" class="form-control" name="confirm" minlength="8" required></div>
        <button class="btn btn-primary">Update password</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
