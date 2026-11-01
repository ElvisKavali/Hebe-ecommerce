<?php
/** @var array $p product row */
$price = $p['discount_price'] ?: $p['price'];
$hasDeal = !empty($p['discount_price']);
$stock = (int)$p['stock'];
$stockCls = $stock <= 0 ? 'out' : ($stock <= 5 ? 'low' : '');
$stockTxt = $stock <= 0 ? 'Out of stock' : ($stock <= 5 ? "Only $stock left" : 'In Stock');
?>
<div class="card-product fade-up">
  <div class="img-wrap">
    <a href="<?= url('views/product.php?slug=' . urlencode($p['slug'])) ?>">
      <img loading="lazy" src="<?= e(product_image_url($p['image'])) ?>" alt="<?= e($p['name']) ?>">
    </a>
    <?php if ($hasDeal): $pct = (int)round((($p['price'] - $price) / $p['price']) * 100); ?>
      <span class="badge-deal">-<?= $pct ?>%</span>
    <?php endif; ?>
    <?php if (!empty($p['new_arrival'])): ?>
      <span class="badge-deal" style="top: 48px; background: #0d6efd;">New</span>
    <?php endif; ?>
    <button class="wish" data-toggle-wishlist="<?= (int)$p['id'] ?>" aria-label="Wishlist"><i class="bi bi-heart"></i></button>
  </div>
  <div class="body">
    <div class="cat"><?= e($p['category_name'] ?? '') ?></div>
    <a class="title" href="<?= url('views/product.php?slug=' . urlencode($p['slug'])) ?>"><?= e($p['name']) ?></a>
    <div class="d-flex align-items-center justify-content-between">
      <div class="price"><?= money((float)$price) ?><?php if ($hasDeal): ?><del><?= money((float)$p['price']) ?></del><?php endif; ?></div>
      <div class="rating-stars small"><i class="bi bi-star-fill"></i> <?= number_format((float)$p['rating'], 1) ?></div>
    </div>
    <span class="stock-pill <?= $stockCls ?>"><?= $stockTxt ?></span>
    <button class="btn btn-outline-primary btn-sm add-btn" data-add-to-cart="<?= (int)$p['id'] ?>" <?= $stock <= 0 ? 'disabled' : '' ?>>
      <i class="bi bi-bag-plus"></i> Add to cart
    </button>
    <a class="btn btn-success btn-sm whatsapp-btn" target="_blank" rel="noopener" href="https://wa.me/254720487799?text=<?= rawurlencode('Hello HEBE, do you still have the ' . $p['name'] . ' (SKU: ' . ($p['sku'] ?? '') . ') in stock?') ?>">
      <i class="bi bi-whatsapp"></i> Ask on WhatsApp
    </a>
  </div>
</div>
