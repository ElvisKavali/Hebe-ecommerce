<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$user = $token ? (new User())->findByResetToken($token) : null;
if (!$user) { flash('error', 'This reset link is invalid or expired.'); redirect('auth/forgot.php'); }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $p1 = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password2'] ?? '');
    if (strlen($p1) < 8 || $p1 !== $p2) {
        flash('error', 'Passwords must match and be at least 8 characters.');
    } else {
        (new User())->updatePassword((int)$user['id'], $p1);
        (new User())->clearResetToken((int)$user['id']);
        flash('success', 'Password updated. Please log in.');
        redirect('auth/login.php');
    }
}
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Reset password';
?>
<div class="container my-5" style="max-width:460px">
  <div class="checkout-card">
    <h1 class="h3">Choose a new password</h1>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div class="mb-3"><label class="form-label">New password</label><input type="password" name="password" class="form-control" minlength="8" required></div>
      <div class="mb-3"><label class="form-label">Confirm</label><input type="password" name="password2" class="form-control" minlength="8" required></div>
      <button class="btn btn-primary w-100">Update password</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
