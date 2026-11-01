<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<div class="checkout-card p-0">
  <div class="list-group list-group-flush rounded">
    <a class="list-group-item list-group-item-action <?= $cur==='index.php'?'active':'' ?>" href="<?= url('customer/index.php') ?>"><i class="bi bi-grid"></i> Dashboard</a>
    <a class="list-group-item list-group-item-action <?= $cur==='orders.php'?'active':'' ?>" href="<?= url('customer/orders.php') ?>"><i class="bi bi-receipt"></i> Orders</a>
    <a class="list-group-item list-group-item-action <?= $cur==='addresses.php'?'active':'' ?>" href="<?= url('customer/addresses.php') ?>"><i class="bi bi-geo-alt"></i> Addresses</a>
    <a class="list-group-item list-group-item-action <?= $cur==='wishlist.php'?'active':'' ?>" href="<?= url('customer/wishlist.php') ?>"><i class="bi bi-heart"></i> Wishlist</a>
    <a class="list-group-item list-group-item-action <?= $cur==='profile.php'?'active':'' ?>" href="<?= url('customer/profile.php') ?>"><i class="bi bi-person"></i> Profile</a>
    <a class="list-group-item list-group-item-action <?= $cur==='password.php'?'active':'' ?>" href="<?= url('customer/password.php') ?>"><i class="bi bi-key"></i> Password</a>
    <a class="list-group-item list-group-item-action text-danger" href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>
</div>
