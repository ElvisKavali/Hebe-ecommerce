<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

if (is_logged_in()) redirect('/');

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);
    $u = (new User())->findByEmail($email);
    if ($u && password_verify($pass, $u['password']) && $u['status'] === 'active') {
        login_user((int)$u['id'], $remember);
        $to = $_SESSION['_redirect_after_login'] ?? ($u['role']==='admin' ? url('admin/index.php') : url('customer/index.php'));
        unset($_SESSION['_redirect_after_login']);
        flash('success', 'Welcome back, ' . explode(' ', $u['name'])[0] . '!');
        redirect($to);
    }
    $err = 'Invalid email or password.';
}
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Login';
?>
<div class="container my-5" style="max-width:460px">
  <div class="checkout-card">
    <h1 class="h3 mb-3">Welcome back</h1>
    <?php if ($err): ?><div class="alert alert-danger small"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required autofocus></div>
      <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <div class="d-flex justify-content-between mb-3">
        <div class="form-check"><input class="form-check-input" type="checkbox" name="remember" id="rem"><label class="form-check-label small" for="rem">Remember me</label></div>
        <a href="<?= url('auth/forgot.php') ?>" class="small">Forgot password?</a>
      </div>
      <button class="btn btn-primary w-100">Login</button>
    </form>
    <p class="text-center text-muted mt-3 small">New here? <a href="<?= url('auth/register.php') ?>">Create an account</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
