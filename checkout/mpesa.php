<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../includes/mpesa.php';

$orderId = (int)($_GET['id'] ?? $_SESSION['pending_order_id'] ?? 0);
$order = (new Order())->find($orderId);
if (!$order) {
    flash('error', 'Order not found.');
    redirect('views/shop.php');
}

$phone = trim($_POST['phone'] ?? $order['phone'] ?? '');
$amount = (float)$order['total'];
$reference = $order['order_number'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    if (!$phone) {
        flash('error', 'Please provide a phone number.');
        redirect('checkout/mpesa.php?id=' . $orderId);
    }
    try {
        $result = mpesa_stk_push($phone, $amount, $reference);
        (new Order())->setPayment($orderId, $result['success'] ? 'pending' : 'failed', $result['CheckoutRequestID'] ?? null, 'mpesa');
        if ($result['success']) {
            flash('success', 'M-Pesa prompt sent. Complete the payment on your phone.');
            redirect('checkout/success.php?id=' . $orderId);
        }
        flash('error', $result['error'] ?? 'M-Pesa request failed.');
    } catch (Throwable $e) {
        flash('error', 'M-Pesa setup error: ' . $e->getMessage());
    }
}

$pageTitle = 'Pay with M-Pesa';
?>
<div class="container my-5" style="max-width:560px">
  <div class="checkout-card">
    <h1 class="h3 mb-3">Pay with M-Pesa</h1>
    <p class="text-muted">Order <strong><?= e($order['order_number']) ?></strong> for <strong><?= money((float)$order['total']) ?></strong>.</p>
    <form method="post">
      <?= csrf_field() ?>
      <label class="form-label">Phone number</label>
      <input class="form-control mb-3" name="phone" value="<?= e($phone) ?>" placeholder="254712345678" required>
      <button class="btn btn-success w-100">Send M-Pesa prompt</button>
    </form>
    <p class="small text-muted mt-3">This uses the Daraja STK Push flow. In sandbox, use a valid test number if you have a configured account.</p>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
