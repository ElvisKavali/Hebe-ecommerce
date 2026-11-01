<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

if (is_logged_in()) redirect('/');

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $name  = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password2'] ?? '');
    $userModel = new User();
    if (!$name || strlen($name) < 2)                    $err = 'Please enter your full name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Please enter a valid email.';
    elseif (strlen($pass) < 8)                          $err = 'Password must be at least 8 characters.';
    elseif ($pass !== $pass2)                           $err = 'Passwords do not match.';
    elseif ($userModel->findByEmail($email))            $err = 'An account with this email already exists.';
    else {
        $id = $userModel->create(['name'=>$name,'email'=>$email,'phone'=>$phone,'password'=>$pass,'role'=>'customer','email_verified'=>1]);
        login_user($id);
        flash('success', 'Welcome to HEBE, ' . explode(' ', $name)[0] . '!');
        redirect('customer/index.php');
    }
    set_old($_POST);
}
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Create account';
?>
<div class="container my-5" style="max-width:500px">
  <div class="checkout-card">
    <h1 class="h3 mb-3">Create your HEBE account</h1>
    <?php if ($err): ?><div class="alert alert-danger small"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="name" value="<?= old('name') ?>" required></div>
      <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= old('email') ?>" required></div>
      <div class="mb-3"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= old('phone') ?>" placeholder="+254 ..."></div>
      <div class="row g-3">
        <div class="col"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required minlength="8"></div>
        <div class="col"><label class="form-label">Confirm</label><input type="password" class="form-control" name="password2" required minlength="8"></div>
      </div>
      <button class="btn btn-primary w-100 mt-3">Create account</button>
    </form>
    <p class="text-center text-muted mt-3 small">Already have an account? <a href="<?= url('auth/login.php') ?>">Login</a></p>
  </div>
</div>
<?php clear_old(); require_once __DIR__ . '/../includes/footer.php'; ?>
