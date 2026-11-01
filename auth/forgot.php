<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
$sent = false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $u = (new User())->findByEmail($email);
    if ($u) {
        $token = bin2hex(random_bytes(32));
        (new User())->setResetToken((int)$u['id'], $token, 60);
        // In production: email this link. For demo: show it.
        $resetUrl = url('auth/reset.php?token=' . $token);
        flash('success', 'Password reset link generated. Demo link: <a href="' . $resetUrl . '">' . $resetUrl . '</a>');
    } else {
        flash('success', 'If that email exists, a reset link was sent.');
    }
    $sent = true;
}
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Forgot password';
?>
<div class="container my-5" style="max-width:460px">
  <div class="checkout-card">
    <h1 class="h3">Forgot password</h1>
    <p class="text-muted small">Enter your email to receive a reset link.</p>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3"><input type="email" class="form-control" name="email" placeholder="you@example.com" required></div>
      <button class="btn btn-primary w-100">Send reset link</button>
    </form>
    <p class="text-center small mt-3"><a href="<?= url('auth/login.php') ?>">Back to login</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
