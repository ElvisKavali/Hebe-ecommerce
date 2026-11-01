<?php
/**
 * Stripe Checkout — creates a Checkout Session via REST API (no Composer dependency).
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';

$orderId = (int)($_SESSION['pending_order_id'] ?? 0);
if (!$orderId || STRIPE_SECRET_KEY === 'sk_test_REPLACE_ME') {
    flash('error', 'Stripe is not configured.');
    redirect('cart/index.php');
}
$order = (new Order())->find($orderId);
$items = (new Order())->items($orderId);
if (!$order || !$items) { flash('error', 'Order not found.'); redirect('cart/index.php'); }

// Build line items for Stripe
$params = [
    'mode' => 'payment',
    'success_url' => url('checkout/success.php?id=' . $orderId . '&session_id={CHECKOUT_SESSION_ID}'),
    'cancel_url'  => url('checkout/index.php'),
    'metadata[order_id]' => (string)$orderId,
];
$i = 0;
foreach ($items as $it) {
    $params["line_items[$i][quantity]"] = (int)$it['quantity'];
    $params["line_items[$i][price_data][currency]"] = STRIPE_CURRENCY;
    $params["line_items[$i][price_data][unit_amount]"] = (int)round($it['price'] * 100);
    $params["line_items[$i][price_data][product_data][name]"] = $it['name'];
    $i++;
}
if ((float)$order['shipping'] > 0) {
    $params["line_items[$i][quantity]"] = 1;
    $params["line_items[$i][price_data][currency]"] = STRIPE_CURRENCY;
    $params["line_items[$i][price_data][unit_amount]"] = (int)round($order['shipping'] * 100);
    $params["line_items[$i][price_data][product_data][name]"] = 'Shipping';
    $i++;
}
if ((float)$order['tax'] > 0) {
    $params["line_items[$i][quantity]"] = 1;
    $params["line_items[$i][price_data][currency]"] = STRIPE_CURRENCY;
    $params["line_items[$i][price_data][unit_amount]"] = (int)round($order['tax'] * 100);
    $params["line_items[$i][price_data][product_data][name]"] = 'Tax';
    $i++;
}

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($params),
    CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($resp, true);
if ($code === 200 && !empty($data['url'])) {
    header('Location: ' . $data['url']);
    exit;
}
flash('error', 'Stripe error: ' . ($data['error']['message'] ?? 'Unknown'));
redirect('cart/index.php');
