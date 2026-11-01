<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cart.php';
if (!csrf_verify()) json_response(['ok'=>false,'message'=>'CSRF failed'], 419);
$body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$pid = (int)($body['product_id'] ?? 0);
$qty = (int)($body['quantity'] ?? 0);
if (!$pid) json_response(['ok'=>false,'message'=>'Invalid product'], 400);
$cart = new Cart();
$cart->update($pid, $qty);
json_response(['ok'=>true,'count'=>$cart->count()]);
