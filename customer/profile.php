<?php
require_once __DIR__ . '/../config/config.php';
require_login();
require_once __DIR__ . '/../models/User.php';
$user = current_user();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $d = ['name'=>trim($_POST['name'] ?? ''),'email'=>strtolower(trim($_POST['email'] ?? '')),'phone'=>trim($_POST['phone'] ?? '')];
    if (!empty($_FILES['avatar']['name'])) {
        if ($path = upload_image($_FILES['avatar'], 'avatars')) $d['avatar'] = $path;
    }
    (new User())->update((int)$user['id'], $d);
    flash('success','Profile updated.');
    redirect('customer/profile.php');
}
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Profile';
?>
<div class="container my-4">
  <div class="row g-4">
    <aside class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></aside>
    <div class="col-lg-9">
      <h1 class="h3">Profile</h1>
      <form method="post" enctype="multipart/form-data" class="checkout-card mt-3">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-md-3 text-center">
            <img src="<?= e($user['avatar'] ? UPLOAD_URL.'/'.$user['avatar'] : asset('images/placeholder.svg')) ?>" class="rounded-circle" style="width:120px;height:120px;object-fit:cover">
            <input type="file" name="avatar" accept="image/*" class="form-control form-control-sm mt-2">
          </div>
          <div class="col-md-9">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= e($user['name']) ?>" required></div>
              <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e($user['email']) ?>" required></div>
              <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($user['phone'] ?? '') ?>"></div>
            </div>
            <button class="btn btn-primary mt-3">Save changes</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
