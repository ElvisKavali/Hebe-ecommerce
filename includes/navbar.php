<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm site-nav">
  <div class="container">
    <a class="navbar-brand" href="<?= url('/') ?>">HEBE<span class="brand-dot">.</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hebeNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="hebeNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= url('/') ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('views/shop.php') ?>">Shop</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Categories</a>
          <ul class="dropdown-menu">
            <?php foreach ($__categories as $c): ?>
              <li><a class="dropdown-item" href="<?= url('views/shop.php?category=' . e($c['slug'])) ?>"><?= e($c['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?= url('views/about.php') ?>">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('views/contact.php') ?>">Contact</a></li>
      </ul>
      <form class="d-flex me-3 position-relative search-form" action="<?= url('views/search.php') ?>" method="get">
        <input id="liveSearch" class="form-control" type="search" name="q" placeholder="Search products..." autocomplete="off">
        <button class="btn btn-primary ms-2" type="submit"><i class="bi bi-search"></i></button>
        <div id="searchResults" class="search-results"></div>
      </form>
      <ul class="navbar-nav align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link icon-link" href="<?= url('customer/wishlist.php') ?>"><i class="bi bi-heart"></i><span class="badge bg-primary" id="wishlistCount"><?= (int)$__wishlistCount ?></span></a></li>
        <li class="nav-item"><a class="nav-link icon-link" href="<?= url('cart/index.php') ?>"><i class="bi bi-bag"></i><span class="badge bg-primary" id="cartCount"><?= (int)$__cartCount ?></span></a></li>
        <?php if ($__user): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#"><i class="bi bi-person-circle"></i> <?= e(explode(' ', $__user['name'])[0]) ?></a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if ($__user['role'] === 'admin'): ?>
                <li><a class="dropdown-item" href="<?= url('admin/index.php') ?>"><i class="bi bi-speedometer2"></i> Admin Dashboard</a></li>
                <li><hr class="dropdown-divider"></li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="<?= url('customer/index.php') ?>"><i class="bi bi-grid"></i> My Account</a></li>
              <li><a class="dropdown-item" href="<?= url('customer/orders.php') ?>"><i class="bi bi-receipt"></i> Orders</a></li>
              <li><a class="dropdown-item" href="<?= url('customer/addresses.php') ?>"><i class="bi bi-geo-alt"></i> Addresses</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('auth/login.php') ?>">Login</a></li>
          <li class="nav-item"><a class="btn btn-primary ms-lg-2" href="<?= url('auth/register.php') ?>">Sign up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
