<?php
require_once __DIR__ . '/../config/config.php';
require_login();
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/header.php';

$user = current_user();
$addr = new Address();
$userId = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    if (isset($_POST['delete_id'])) {
        $addr->delete((int)$_POST['delete_id'], $userId);
        flash('success','Address removed.');
    } else {
        $d = [
            'label'=>trim($_POST['label'] ?? 'Home'), 'full_name'=>trim($_POST['full_name'] ?? ''),
            'phone'=>trim($_POST['phone'] ?? ''), 'line1'=>trim($_POST['line1'] ?? ''),
            'line2'=>trim($_POST['line2'] ?? ''), 'city'=>trim($_POST['city'] ?? ''),
            'state'=>trim($_POST['state'] ?? ''), 'postal_code'=>trim($_POST['postal_code'] ?? ''),
            'country'=>trim($_POST['country'] ?? 'Kenya'), 'is_default'=>!empty($_POST['is_default']),
        ];
        if ($d['full_name'] && $d['phone'] && $d['line1'] && $d['city']) {
            $addr->create($userId, $d);
            flash('success','Address saved.');
        } else flash('error','Please complete the address.');
    }
    redirect('customer/addresses.php');
}

$addresses = $addr->forUser($userId);
$pageTitle = 'Saved Addresses';
?>
<div class="container my-4">
  <div class="row g-4">
    <aside class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></aside>
    <div class="col-lg-9">
      <h1 class="h3">Saved addresses</h1>
      <div class="row g-3 mt-2">
        <?php foreach ($addresses as $a): ?>
          <div class="col-md-6">
            <div class="checkout-card h-100">
              <div class="d-flex justify-content-between"><strong><?= e($a['label']) ?></strong> <?php if($a['is_default']): ?><span class="badge bg-primary">Default</span><?php endif; ?></div>
              <p class="text-muted small mb-2"><?= e($a['full_name']) ?><br><?= e($a['line1']) ?><br><?= e($a['city']) ?>, <?= e($a['country']) ?><br><?= e($a['phone']) ?></p>
              <form method="post" data-confirm="Delete this address?"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= (int)$a['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="checkout-card mt-4">
        <h5>Add new address</h5>
        <form method="post" class="row g-3">
          <?= csrf_field() ?>
          <div class="col-md-3"><label class="form-label">Label</label><input class="form-control" name="label" value="Home"></div>
          <div class="col-md-9"><label class="form-label">Full name</label><input class="form-control" name="full_name" required></div>
          <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" required></div>
          <div class="col-md-6"><label class="form-label">Address line</label><input class="form-control" name="line1" required></div>
          <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="city" required></div>
          <div class="col-md-4"><label class="form-label">Country</label><input class="form-control" name="country" value="Kenya" required></div>
          <div class="col-md-4"><label class="form-label">Postal</label><input class="form-control" name="postal_code"></div>
          <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="def" name="is_default"><label class="form-check-label" for="def">Set as default</label></div></div>
          <div class="col-12"><button class="btn btn-primary">Save address</button></div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
