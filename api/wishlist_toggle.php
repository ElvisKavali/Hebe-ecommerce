<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cart.php';
if (!csrf_verify()) json_response(['ok'=>false,'message'=>'CSRF failed'], 419);
$user = current_user();
if (!$user) json_response(['ok'=>false,'message'=>'Please log in to use the wishlist.'], 401);
$body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$pid = (int)($body['product_id'] ?? 0);
$w = new Wishlist();
if ($w->has((int)$user['id'], $pid)) { $w->remove((int)$user['id'], $pid); $active = false; }
else { $w->add((int)$user['id'], $pid); $active = true; }
json_response(['ok'=>true,'active'=>$active,'count'=>$w->count((int)$user['id'])]);
