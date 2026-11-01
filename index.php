<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Catalog.php';

$productModel = new Product();
$categories = (new Category())->all();
$featured = $productModel->featured(4);
// Fetch extra new arrivals then remove any that are already featured so the sections don't overlap
$newArrivals = $productModel->newArrivals(12);
if ($featured) {
  $featIds = array_column($featured, 'id');
  $newArrivals = array_values(array_filter($newArrivals, function($x) use ($featIds) { return !in_array($x['id'], $featIds); }));
}
$newArrivals = array_slice($newArrivals, 0, 6);
$heroProduct = $newArrivals[0] ?? ($featured[0] ?? null);
$pageTitle = 'Home';
?>
<div class="container my-4 home-page">
  <section class="hero home-hero rounded-4 overflow-hidden shadow-sm">
    <video class="home-hero-video" autoplay muted loop playsinline preload="metadata"><source src="<?= asset('videos/kitchen-hero.mp4') ?>" type="video/mp4"></video>
    <div class="home-hero-video-overlay"></div>
    <div class="row g-0 align-items-center position-relative">
      <div class="col-lg-7 p-4 p-md-5 hero-copy">
        <div class="eyebrow text-uppercase small fw-semibold mb-3"><span></span> New arrivals are here</div>
        <div class="hero-ad-label"><i class="bi bi-stars"></i> Fresh from the kitchen</div>
        <h1 class="display-5 fw-bold mb-3">Meet the new pieces made for better cooking.</h1>
        <p class="lead text-muted mb-4">Discover the latest kitchen appliances and everyday essentials, chosen to make every meal feel special.</p>
        <div class="d-flex flex-wrap gap-2 mb-4">
          <a href="<?= url('views/shop.php?sort=newest') ?>" class="btn btn-primary"><i class="bi bi-bag me-1"></i> Shop new arrivals</a>
          <a href="<?= url('views/categories.php') ?>" class="btn btn-outline-primary">Explore categories</a>
        </div>
        <div class="d-flex flex-wrap gap-3 small text-muted">
          <span><i class="bi bi-truck text-primary me-1"></i> Fast delivery</span>
          <span><i class="bi bi-shield-check text-primary me-1"></i> Secure checkout</span>
          <span><i class="bi bi-arrow-counterclockwise text-primary me-1"></i> Easy returns</span>
        </div>
      </div>
      <div class="col-lg-5 p-3 p-md-4">
        <div class="hero-stage h-100 position-relative">
          <svg class="kitchen-table-scene" viewBox="0 0 500 350" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <style>
                .item { animation-duration: 5s; animation-iteration-count: infinite; animation-timing-function: ease-in-out; }
                .plate1 { animation-name: wobble1; }
                .plate2 { animation-name: wobble2; }
                .knife1 { animation-name: sway1; }
                .knife2 { animation-name: sway2; }
                .cup1 { animation-name: bounce1; }
                .bowl1 { animation-name: spin1; }
                @keyframes wobble1 { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(-3deg) translateY(-2px); } }
                @keyframes wobble2 { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(3deg) translateY(-2px); } }
                @keyframes sway1 { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(3px); } }
                @keyframes sway2 { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(-3px); } }
                @keyframes bounce1 { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
                @keyframes spin1 { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(8deg); } }
              </style>
            </defs>
            <!-- Table -->
            <ellipse cx="250" cy="260" rx="220" ry="55" fill="#c9a876"/>
            <ellipse cx="250" cy="250" rx="210" ry="50" fill="#9d7d52" opacity="0.5"/>
            <ellipse cx="250" cy="275" rx="200" ry="25" fill="#000" opacity="0.08"/>
            <!-- Plate 1 -->
            <circle class="item plate1" cx="120" cy="240" r="50" fill="#fef8f3" stroke="#d4a574" stroke-width="3"/>
            <circle class="item plate1" cx="120" cy="240" r="38" fill="none" stroke="#e8dcc0" stroke-width="1" opacity="0.6"/>
            <!-- Plate 2 -->
            <circle class="item plate2" cx="380" cy="240" r="50" fill="#fef8f3" stroke="#d4a574" stroke-width="3"/>
            <circle class="item plate2" cx="380" cy="240" r="38" fill="none" stroke="#e8dcc0" stroke-width="1" opacity="0.6"/>
            <!-- Knife 1 -->
            <g class="item knife1" transform="translate(100, 180)">
              <rect x="0" y="0" width="8" height="100" fill="#bebebe" rx="4"/>
              <polygon points="4,0 0,-10 8,-10" fill="#8b7355"/>
            </g>
            <!-- Knife 2 -->
            <g class="item knife2" transform="translate(400, 200)">
              <rect x="0" y="0" width="8" height="100" fill="#bebebe" rx="4"/>
              <polygon points="4,0 0,-10 8,-10" fill="#8b7355"/>
            </g>
            <!-- Cup (green Stanley) -->
            <g class="item cup1" transform="translate(250, 160)">
              <rect x="-20" y="0" width="40" height="60" fill="#1b5e20" rx="2" stroke="#0d3b1a" stroke-width="1"/>
              <rect x="-22" y="3" width="44" height="12" fill="#2e7d32" rx="1"/>
              <circle cx="16" cy="30" r="4" fill="#fff" opacity="0.4"/>
            </g>
            <!-- Bowl (red) -->
            <g class="item bowl1" transform="translate(200, 200)">
              <ellipse cx="0" cy="0" rx="45" ry="35" fill="#d32f2f" opacity="0.85"/>
              <ellipse cx="0" cy="-8" rx="40" ry="30" fill="#b71c1c" opacity="0.6"/>
            </g>
            <!-- Spoon 1 -->
            <g class="item" transform="translate(280, 240)">
              <rect x="0" y="0" width="6" height="80" fill="#c0c0c0" rx="3"/>
              <ellipse cx="3" cy="-20" rx="18" ry="24" fill="#e0e0e0" stroke="#808080" stroke-width="1"/>
            </g>
          </svg>
          <div class="hero-orbit orbit-one"></div>
          <div class="hero-orbit orbit-two"></div>
          <?php if ($heroProduct): ?>
            <div class="hero-product-art">
              <img src="<?= e(product_image_url($heroProduct['image'])) ?>" alt="<?= e($heroProduct['name']) ?>">
              <span><i class="bi bi-heart-fill"></i> New in</span>
            </div>
          <?php else: ?>
            <div class="hero-bowl"><i class="bi bi-cup-hot-fill"></i></div>
          <?php endif; ?>
          <span class="floating-spice spice-one">✦</span>
          <span class="floating-spice spice-two">✧</span>
          <?php if ($heroProduct): ?>
            <div class="hero-note hero-product-note"><span class="hero-note-kicker">JUST DROPPED</span><strong><?= e($heroProduct['name']) ?></strong><span><?= money((float)($heroProduct['discount_price'] ?: $heroProduct['price'])) ?></span></div>
          <?php else: ?>
            <div class="hero-note"><i class="bi bi-stars"></i><span>Made for your<br><strong>next delicious idea</strong></span></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="mt-5 reveal">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h4 mb-0">Popular categories</h2>
      <a href="<?= url('views/categories.php') ?>" class="btn btn-link p-0">View all</a>
    </div>
    <div class="row g-3">
      <?php foreach (array_slice($categories, 0, 4) as $c): ?>
        <div class="col-sm-6 col-lg-3">
          <a class="text-decoration-none" href="<?= url('views/shop.php?category=' . urlencode($c['slug'])) ?>">
            <div class="cat-tile h-100">
              <i class="bi bi-grid-3x3-gap"></i>
              <div class="fw-bold mb-1"><?= e($c['name']) ?></div>
              <div class="small text-muted">Browse curated picks</div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($featured): ?>
    <section class="mt-5 reveal">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Featured products</h2>
        <a href="<?= url('views/shop.php') ?>" class="btn btn-link p-0">See all</a>
      </div>
      <div class="row g-4">
        <?php foreach ($featured as $p): ?>
          <div class="col-6 col-md-4 col-lg-3"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($newArrivals): ?>
    <section class="mt-5 reveal">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">New arrivals</h2>
        <a href="<?= url('views/shop.php?sort=newest') ?>" class="btn btn-link p-0">View more</a>
      </div>
      <div class="row g-4">
        <?php foreach ($newArrivals as $p):
          // Use first gallery image for new arrivals when available so they can show a different image
          $gallery = (new Product())->gallery((int)$p['id']);
          if (!empty($gallery) && $gallery[0] !== ($p['image'] ?? '')) {
            $p['image'] = $gallery[0];
          }
        ?>
          <div class="col-6 col-md-4 col-lg-3"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
