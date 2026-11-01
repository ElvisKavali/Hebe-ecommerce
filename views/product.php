<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Catalog.php';

$slug = $_GET['slug'] ?? '';
$model = new Product();
$p = $model->findBySlug($slug);
if (!$p) { http_response_code(404); echo '<div class="container my-5"><div class="empty"><i class="bi bi-emoji-frown"></i><h4>Product not found</h4></div></div>'; require_once __DIR__ . '/../includes/footer.php'; exit; }

$gallery = $model->gallery((int)$p['id']);
$related = $model->related((int)$p['id'], $p['category_id'] ? (int)$p['category_id'] : null, 4);
$reviews = (new Review())->forProduct((int)$p['id']);
$price = $p['discount_price'] ?: $p['price'];
$hasDeal = !empty($p['discount_price']);
$pageTitle = $p['name'];
$pageMeta  = mb_substr(strip_tags($p['description']), 0, 155);

// Handle review submission
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['review_submit'])) {
    csrf_require();
    $author = is_logged_in() ? current_user()['name'] : trim($_POST['author_name'] ?? '');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $body   = trim($_POST['body'] ?? '');
    if ($author && $body) {
        (new Review())->create([
            'product_id'=>(int)$p['id'], 'user_id'=>current_user()['id'] ?? null,
            'author_name'=>$author, 'rating'=>$rating, 'title'=>trim($_POST['title'] ?? ''), 'body'=>$body,
        ]);
        flash('success', 'Thanks for your review!');
        redirect('views/product.php?slug=' . urlencode($p['slug']));
    }
}
?>
<div class="container my-4">
  <nav><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?= url('views/shop.php') ?>">Shop</a></li>
    <?php if (!empty($p['category_slug'])): ?><li class="breadcrumb-item"><a href="<?= url('views/shop.php?category=' . e($p['category_slug'])) ?>"><?= e($p['category_name']) ?></a></li><?php endif; ?>
    <li class="breadcrumb-item active"><?= e($p['name']) ?></li>
  </ol></nav>
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="product-gallery">
        <div class="main-img"><img src="<?= e(product_image_url($p['image'])) ?>" alt="<?= e($p['name']) ?>"></div>
        <?php if ($gallery): ?>
          <div class="thumbs">
            <img class="active" src="<?= e(product_image_url($p['image'])) ?>" alt="">
            <?php foreach ($gallery as $img): ?><img src="<?= e(product_image_url($img)) ?>" alt=""><?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="text-muted small text-uppercase mb-2"><?= e($p['brand_name'] ?? '') ?></div>
      <h1 class="h2"><?= e($p['name']) ?></h1>
      <div class="d-flex align-items-center gap-2 my-2">
        <span class="rating-stars"><?php for($i=1;$i<=5;$i++) echo '<i class="bi bi-star'.($i<=round($p['rating'])?'-fill':'').'"></i>'; ?></span>
        <span class="text-muted small">(<?= (int)$p['reviews_count'] ?> reviews) · SKU: <?= e($p['sku']) ?></span>
      </div>
      <div class="d-flex align-items-baseline gap-2 mb-2">
        <span class="h3 text-primary mb-0"><?= money((float)$price) ?></span>
        <?php if ($hasDeal): ?><del class="text-muted"><?= money((float)$p['price']) ?></del>
          <span class="badge text-bg-danger">Save <?= money((float)$p['price'] - (float)$price) ?></span>
        <?php endif; ?>
      </div>
      <?php $stock = (int)$p['stock']; ?>
      <p>
        <?php if ($stock <= 0): ?><span class="badge text-bg-danger">Out of stock</span>
        <?php elseif ($stock <= 5): ?><span class="badge text-bg-warning">Only <?= $stock ?> left</span>
        <?php else: ?><span class="badge text-bg-success">In stock</span><?php endif; ?>
      </p>
      <p class="text-muted"><?= nl2br(e($p['description'])) ?></p>
      <div class="d-flex align-items-center gap-3 my-3 flex-wrap">
        <div class="qty-selector">
          <button class="minus" type="button">−</button>
          <input id="qtyInput" type="number" min="1" value="1">
          <button class="plus" type="button">+</button>
        </div>
        <button class="btn btn-primary btn-lg" data-add-to-cart="<?= (int)$p['id'] ?>" <?= $stock<=0?'disabled':'' ?>><i class="bi bi-bag-plus"></i> Add to cart</button>
      </div>
      <div class="mb-3">
        <a class="btn btn-success btn-lg" target="_blank" rel="noopener" href="https://wa.me/254720487799?text=<?= rawurlencode('Hello HEBE, do you still have the ' . $p['name'] . ' (SKU: ' . $p['sku'] . ') in stock?') ?>"><i class="bi bi-whatsapp"></i> Ask on WhatsApp</a>
      </div>
      <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        <form action="<?= url('checkout/index.php') ?>" method="get" class="d-inline">
          <input type="hidden" name="buy_now" value="1">
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="qty" value="1" id="buyNowQty">
          <button class="btn btn-outline-primary btn-lg" type="submit" <?= $stock<=0?'disabled':'' ?>><i class="bi bi-lightning-charge"></i> Buy now</button>
        </form>
        <button class="btn btn-outline-primary btn-lg" data-toggle-wishlist="<?= (int)$p['id'] ?>"><i class="bi bi-heart"></i></button>
      </div>
      <ul class="list-unstyled small text-muted mt-3">
        <li><i class="bi bi-arrow-counterclockwise text-primary"></i> 30-day easy returns</li>
        <li><i class="bi bi-shield-check text-primary"></i>  M-Pesa checkout</li>
      </ul>
    </div>
  </div>

  <ul class="nav nav-tabs mt-5" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Description</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#spec">Specifications</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rev">Reviews (<?= count($reviews) ?>)</button></li>
  </ul>
  <div class="tab-content border border-top-0 p-4 rounded-bottom bg-white">
    <div class="tab-pane fade show active" id="desc"><?= nl2br(e($p['description'])) ?></div>
    <div class="tab-pane fade" id="spec">
      <?php if ($p['specifications']): ?>
        <ul class="list-unstyled">
        <?php foreach (explode(';', $p['specifications']) as $line): $line = trim($line); if(!$line) continue; ?>
          <li class="border-bottom py-2"><?= e($line) ?></li>
        <?php endforeach; ?>
        </ul>
      <?php else: ?><p class="text-muted">No specifications listed.</p><?php endif; ?>
    </div>
    <div class="tab-pane fade" id="rev">
      <div class="row g-4">
        <div class="col-md-7">
          <?php if (!$reviews): ?><p class="text-muted">No reviews yet — be the first!</p><?php endif; ?>
          <?php foreach ($reviews as $r): ?>
            <div class="border-bottom pb-3 mb-3">
              <div class="d-flex justify-content-between">
                <strong><?= e($r['author_name']) ?></strong>
                <span class="rating-stars"><?php for($i=1;$i<=5;$i++) echo '<i class="bi bi-star'.($i<=$r['rating']?'-fill':'').'"></i>'; ?></span>
              </div>
              <?php if ($r['title']): ?><div class="fw-semibold mt-1"><?= e($r['title']) ?></div><?php endif; ?>
              <p class="mb-1 mt-1 text-muted"><?= nl2br(e($r['body'])) ?></p>
              <small class="text-muted"><?= e(date('M d, Y', strtotime($r['created_at']))) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="col-md-5">
          <div class="card-body bg-light p-3 rounded">
            <h6>Write a review</h6>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="review_submit" value="1">
              <?php if (!is_logged_in()): ?>
                <input class="form-control mb-2" name="author_name" placeholder="Your name" required>
              <?php endif; ?>
              <select name="rating" class="form-select mb-2">
                <?php for ($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> star<?= $i>1?'s':'' ?></option><?php endfor; ?>
              </select>
              <input class="form-control mb-2" name="title" placeholder="Review title">
              <textarea class="form-control mb-2" name="body" rows="4" placeholder="Share your experience..." required></textarea>
              <button class="btn btn-primary w-100">Submit review</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($related): ?>
    <section class="mt-5">
      <h3 class="mb-3">Related products</h3>
      <div class="row g-4">
        <?php foreach ($related as $p): ?>
          <div class="col-6 col-md-3"><?php include __DIR__ . '/../includes/product_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
