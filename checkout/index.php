<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Catalog.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../includes/mpesa.php';

$cart = new Cart();
$buyNowProductId = !empty($_GET['buy_now']) ? (int)($_GET['product_id'] ?? 0) : 0;
$buyNowQty = max(1, (int)($_GET['qty'] ?? 1));
$checkoutMode = 'cart';
$items = $cart->items();

if ($buyNowProductId > 0) {
    $product = (new Product())->find($buyNowProductId);
    if (!$product || (int)$product['status'] !== 1) {
        flash('error', 'That product is no longer available.');
        redirect('views/shop.php');
    }
    $items = [[
        'product_id' => (int)$product['id'],
        'name' => $product['name'],
        'sku' => $product['sku'],
        'price' => $product['price'],
        'discount_price' => $product['discount_price'],
        'quantity' => $buyNowQty,
    ]];
    $checkoutMode = 'buy_now';
} elseif (!$items) {
    flash('error', 'Your cart is empty.');
    redirect('views/shop.php');
}

$couponResult = null;
$totals = ['subtotal' => 0.0, 'shipping' => 0.0, 'discount' => 0.0, 'tax' => 0.0, 'total' => 0.0];
if ($checkoutMode === 'cart') {
    $couponResult = !empty($_SESSION['coupon_code']) ? (new Coupon())->apply($_SESSION['coupon_code'], $cart->subtotal()) : null;
    $totals = $cart->totals($couponResult);
} else {
    $subtotal = 0.0;
    foreach ($items as $it) {
        $price = (float)($it['discount_price'] ?: $it['price']);
        $subtotal += $price * (int)$it['quantity'];
    }
    $shipping = $subtotal >= FREE_SHIPPING_THRESHOLD || $subtotal === 0.0 ? 0.0 : SHIPPING_FLAT_FEE;
    $tax = round($subtotal * TAX_RATE, 2);
    $totals = ['subtotal' => round($subtotal, 2), 'shipping' => $shipping, 'discount' => 0.0, 'tax' => $tax, 'total' => round($subtotal + $shipping + $tax, 2)];
}
$user = current_user();
$defaultAddr = $user ? (new Address())->defaultFor((int)$user['id']) : null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $payment = $_POST['payment_method'] ?? 'cod';
    $payment = in_array($payment, ['cod','stripe','mpesa','paypal','bank']) ? $payment : 'cod';
    $paymentPhone = trim($_POST['payment_phone'] ?? '');
    $orderData = [
        'user_id'       => $user['id'] ?? null,
        'guest_email'   => $user ? null : trim($_POST['email'] ?? ''),
        'full_name'     => trim($_POST['full_name'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'address_line'  => trim($_POST['address_line'] ?? ''),
        'city'          => trim($_POST['city'] ?? ''),
        'country'       => trim($_POST['country'] ?? 'Kenya'),
        'postal_code'   => trim($_POST['postal_code'] ?? ''),
        'notes'         => trim($_POST['notes'] ?? ''),
        'coupon_code'   => $couponResult && $couponResult['ok'] ? $couponResult['coupon']['code'] : null,
    ];
    foreach (['full_name','phone','address_line','city'] as $req) {
        if (empty($orderData[$req])) { flash('error', 'Please complete shipping details.'); redirect('checkout/index.php'); }
    }
    if (!$user && (!$orderData['guest_email'] || !filter_var($orderData['guest_email'], FILTER_VALIDATE_EMAIL))) {
        flash('error', 'Please enter a valid email.'); redirect('checkout/index.php');
    }
    if ($payment === 'mpesa' && !$paymentPhone) {
        flash('error', 'Please enter your M-Pesa phone number.'); redirect('checkout/index.php');
    }

    $orderId = (new Order())->create($orderData, $items, $totals, $payment);
    if ($couponResult && $couponResult['ok']) {
        (new Coupon())->incrementUse((int)$couponResult['coupon']['id']);
    }

    if ($payment === 'stripe' && STRIPE_SECRET_KEY !== 'sk_test_REPLACE_ME') {
        $_SESSION['pending_order_id'] = $orderId;
        redirect('checkout/stripe.php');
    }
    if ($payment === 'mpesa') {
        (new Order())->setPayment($orderId, 'pending', $paymentPhone, 'mpesa');
        $_SESSION['pending_order_id'] = $orderId;
        redirect('checkout/mpesa.php?id=' . $orderId);
    }
    // COD / others: confirm immediately
    if ($payment !== 'stripe') {
        (new Order())->setStatus($orderId, 'confirmed');
    }
    if ($checkoutMode === 'cart') {
        $cart->clear();
    }
    unset($_SESSION['coupon_code']);
    redirect('checkout/success.php?id=' . $orderId);
}
$pageTitle = 'Checkout';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
  <h1 class="mb-4">Checkout</h1>
  <form method="post" class="row g-4">
    <?= csrf_field() ?>
    <div class="col-lg-8">
      <div class="checkout-card mb-4">
        <h5>Shipping address</h5>
        <div class="row g-3">
          <?php if (!$user): ?>
            <div class="col-12"><label class="form-label">Email (for order updates)</label><input type="email" class="form-control" name="email" required></div>
          <?php endif; ?>
          <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="full_name" value="<?= e($defaultAddr['full_name'] ?? ($user['name'] ?? '')) ?>" required></div>
          <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($defaultAddr['phone'] ?? '') ?>" required></div>
          <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address_line" value="<?= e($defaultAddr['line1'] ?? '') ?>" required></div>
          <div class="col-md-5"><label class="form-label">City</label><input class="form-control" name="city" value="<?= e($defaultAddr['city'] ?? '') ?>" required></div>
          <div class="col-md-4"><label class="form-label">Country</label><input class="form-control" name="country" value="<?= e($defaultAddr['country'] ?? 'Kenya') ?>" required></div>
          <div class="col-md-3"><label class="form-label">Postal code</label><input class="form-control" name="postal_code" value="<?= e($defaultAddr['postal_code'] ?? '') ?>"></div>
          <div class="col-12"><label class="form-label">Order notes (optional)</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
      </div>

      <div class="checkout-card">
        <h5>Payment method</h5>
        <div class="form-check"><input class="form-check-input" type="radio" name="payment_method" value="stripe" id="pm_stripe" <?= STRIPE_SECRET_KEY !== 'sk_test_REPLACE_ME' ? 'checked' : 'disabled' ?>><label class="form-check-label" for="pm_stripe"><i class="bi bi-credit-card text-primary"></i> Card (Stripe) <?= STRIPE_SECRET_KEY === 'sk_test_REPLACE_ME' ? '<small class="text-muted">— add your Stripe keys in config to enable</small>' : '' ?></label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="payment_method" value="mpesa" id="pm_mpesa" checked><label class="form-check-label" for="pm_mpesa"><i class="bi bi-phone text-success"></i> M-Pesa (sandbox/demo)</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="payment_method" value="paypal" id="pm_pp"><label class="form-check-label" for="pm_pp"><i class="bi bi-paypal text-primary"></i> PayPal</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="payment_method" value="bank" id="pm_bank"><label class="form-check-label" for="pm_bank"><i class="bi bi-bank"></i> Bank transfer</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="payment_method" value="cod" id="pm_cod" <?= STRIPE_SECRET_KEY === 'sk_test_REPLACE_ME' ? 'checked' : '' ?>><label class="form-check-label" for="pm_cod"><i class="bi bi-cash"></i> Cash on Delivery</label></div>
        <div class="mt-3" id="mpesaPhoneWrap">
          <label class="form-label">M-Pesa phone number</label>
          <input class="form-control" name="payment_phone" id="paymentPhone" value="<?= e($user['phone'] ?? '') ?>" placeholder="254712345678" required>
          <small class="text-muted">We will send an STK push request to this number when you place the order.</small>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="checkout-card">
        <h5>Order summary</h5>
        <?php foreach ($items as $it): $price = (float)($it['discount_price'] ?: $it['price']); ?>
          <div class="d-flex justify-content-between small py-1">
            <span><?= e($it['name']) ?> × <?= (int)$it['quantity'] ?></span>
            <span><?= money($price * (int)$it['quantity']) ?></span>
          </div>
        <?php endforeach; ?>
        <hr>
        <div class="summary-row"><span>Subtotal</span><span><?= money($totals['subtotal']) ?></span></div>
        <?php if ($totals['discount']>0): ?><div class="summary-row text-success"><span>Discount</span><span>-<?= money($totals['discount']) ?></span></div><?php endif; ?>
        <div class="summary-row"><span>Shipping</span><span><?= $totals['shipping']>0 ? money($totals['shipping']) : 'Free' ?></span></div>
        <div class="summary-row"><span>Tax</span><span><?= money($totals['tax']) ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?= money($totals['total']) ?></span></div>
        <button class="btn btn-primary w-100 mt-3 btn-lg" type="submit"><i class="bi bi-lock-fill"></i> Place order</button>
        <small class="text-muted d-block mt-2 text-center">Your payment information is encrypted.</small>
      </div>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
