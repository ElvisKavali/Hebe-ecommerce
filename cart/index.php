<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Catalog.php';

$cart = new Cart();
$items = $cart->items();

$couponResult = null;
if (!empty($_SESSION['coupon_code'])) {
    $couponResult = (new Coupon())->apply($_SESSION['coupon_code'], $cart->subtotal());
    if (!$couponResult['ok']) unset($_SESSION['coupon_code']);
}
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['coupon_code'])) {
    csrf_require();
    $code = trim($_POST['coupon_code']);
    if ($code) {
        $r = (new Coupon())->apply($code, $cart->subtotal());
        if ($r['ok']) { $_SESSION['coupon_code'] = $code; flash('success', 'Coupon applied.'); }
        else { unset($_SESSION['coupon_code']); flash('error', $r['message']); }
    } else { unset($_SESSION['coupon_code']); }
    redirect('cart/index.php');
}
$couponResult = !empty($_SESSION['coupon_code']) ? (new Coupon())->apply($_SESSION['coupon_code'], $cart->subtotal()) : null;
$totals = $cart->totals($couponResult);
$pageTitle = 'Shopping Cart';
  require_once __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
  <h1 class="mb-4">Your Cart</h1>
  <?php if (!$items): ?>
    <div class="empty"><i class="bi bi-bag"></i><h5>Your cart is empty</h5><p>Browse our shop to add products.</p><a href="<?= url('views/shop.php') ?>" class="btn btn-primary">Continue shopping</a></div>
  <?php else: ?>
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="checkout-card p-0">
          <table class="table mb-0 align-middle">
            <thead><tr><th class="ps-3">Product</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($items as $it): $price = (float)($it['discount_price'] ?: $it['price']); ?>
              <tr>
                <td class="ps-3">
                  <div class="d-flex align-items-center gap-3">
                    <img src="<?= e(product_image_url($it['image'])) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:.4rem" alt="">
                    <a href="<?= url('views/product.php?slug=' . urlencode($it['slug'])) ?>" class="fw-semibold text-dark"><?= e($it['name']) ?></a>
                  </div>
                </td>
                <td><?= money($price) ?></td>
                <td><input type="number" min="1" max="<?= (int)$it['stock'] ?>" value="<?= (int)$it['quantity'] ?>" class="form-control form-control-sm" style="width:80px" data-cart-qty="<?= (int)$it['product_id'] ?>"></td>
                <td class="fw-semibold"><?= money($price * (int)$it['quantity']) ?></td>
                <td><button class="btn btn-link text-danger" onclick="if(confirm('Remove?'))fetch('<?= url('api/cart_update.php') ?>',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':HEBE.CSRF},body:JSON.stringify({product_id:<?= (int)$it['product_id'] ?>,quantity:0})}).then(()=>location.reload())"><i class="bi bi-trash"></i></button></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <form method="post" class="d-flex gap-2 mt-3">
          <?= csrf_field() ?>
          <input class="form-control" name="coupon_code" placeholder="Have a coupon? e.g. WELCOME10" value="<?= e($_SESSION['coupon_code'] ?? '') ?>">
          <button class="btn btn-outline-primary">Apply</button>
        </form>
      </div>
      <div class="col-lg-4">
        <div class="checkout-card">
          <h5>Order summary</h5>
          <div class="summary-row"><span>Subtotal</span><span><?= money($totals['subtotal']) ?></span></div>
          <?php if ($totals['discount'] > 0): ?><div class="summary-row text-success"><span>Discount</span><span>-<?= money($totals['discount']) ?></span></div><?php endif; ?>
          <div class="summary-row"><span>Shipping</span><span><?= $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' ?></span></div>
          <div class="summary-row"><span>Tax (<?= (int)(TAX_RATE*100) ?>%)</span><span><?= money($totals['tax']) ?></span></div>
          <div class="summary-row total"><span>Total</span><span><?= money($totals['total']) ?></span></div>
          <div class="small text-muted mt-2"><i class="bi bi-credit-card me-1"></i> Payment options: M-Pesa, card, PayPal, bank transfer, or cash on delivery.</div>
          <a href="<?= url('checkout/index.php') ?>" class="btn btn-primary w-100 mt-2">Checkout <i class="bi bi-arrow-right"></i></a>
          <?php $cartMessage = 'Hello HEBE, do you still have these items in stock? ' . implode('; ', array_map(fn($item) => $item['name'] . ' x' . (int)$item['quantity'], $items)); ?>
          <a href="https://wa.me/254720487799?text=<?= rawurlencode($cartMessage) ?>" target="_blank" rel="noopener" class="btn btn-success w-100 mt-2"><i class="bi bi-whatsapp"></i> Ask about stock on WhatsApp</a>
          <a href="<?= url('views/shop.php') ?>" class="btn btn-link w-100">Continue shopping</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
