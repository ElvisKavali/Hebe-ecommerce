<?php
header('Content-Type: application/xml');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Catalog.php';
$urls = [url('/'), url('views/shop.php'), url('views/categories.php'), url('views/about.php'), url('views/contact.php'), url('views/faq.php')];
foreach ((new Category())->all() as $c) $urls[] = url('views/shop.php?category=' . $c['slug']);
foreach (db()->query('SELECT slug FROM products WHERE status=1') as $r) $urls[] = url('views/product.php?slug=' . $r['slug']);
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $u) echo "  <url><loc>" . e($u) . "</loc></url>\n";
echo "</urlset>";
