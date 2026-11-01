<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
if (!csrf_verify()) json_response(['ok'=>false,'message'=>'CSRF failed'], 419);
$body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$pid = (int)($body['product_id'] ?? 0);
$qty = max(1, (int)($body['quantity'] ?? 1));
if (!$pid) json_response(['ok'=>false,'message'=>'Invalid product'], 400);
$product = (new Product())->find($pid);
if (!$product || (int)$product['status'] !== 1) json_response(['ok'=>false,'message'=>'This product is no longer available.'], 404);
if ((int)$product['stock'] < $qty) json_response(['ok'=>false,'message'=>'Only ' . (int)$product['stock'] . ' item(s) are currently in stock.'], 409);
$cart = new Cart();
try {
	$cart->add($pid, $qty);
} catch (Throwable $e) {
	error_log('[cart_add] ' . $e->getMessage());
	json_response(['ok'=>false,'message'=>'Could not add this item to the cart.'], 500);
}
json_response(['ok'=>true,'count'=>$cart->count()]);
