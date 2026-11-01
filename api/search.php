<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Product.php';
$q = trim((string)($_GET['q'] ?? ''));
$items = $q ? (new Product())->liveSearch($q, 6) : [];
foreach ($items as &$i) { $i['image_url'] = product_image_url($i['image']); $i['price'] = number_format((float)$i['price'], 2); if ($i['discount_price']) $i['discount_price'] = number_format((float)$i['discount_price'], 2); }
json_response(['items'=>$items]);
