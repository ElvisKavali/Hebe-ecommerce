<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Product.php';

$q = trim((string)($_GET['q'] ?? ''));
if ($q === '') json_response(['message' => 'Tell me what you need, such as a pan, kettle, or blender.', 'items' => []]);

$terms = preg_split('/\s+/', strtolower($q), -1, PREG_SPLIT_NO_EMPTY);
$stopWords = ['a', 'an', 'and', 'best', 'buy', 'can', 'compare', 'do', 'find', 'for', 'help', 'i', 'item', 'me', 'of', 'or', 'price', 'show', 'the', 'want', 'with'];
$terms = array_values(array_filter($terms, fn($term) => !in_array($term, $stopWords, true) && strlen($term) > 1));
$keyword = implode(' ', $terms);
$model = new Product();
$result = $model->search(['q' => $keyword ?: $q, 'page' => 1, 'per_page' => 4, 'sort' => 'rating']);
$items = array_map(function (array $item): array {
    $price = (float)($item['discount_price'] ?: $item['price']);
    return ['name' => $item['name'], 'slug' => $item['slug'], 'price' => number_format($price, 2), 'rating' => (float)$item['rating']];
}, $result['items']);

$isCompare = str_contains(strtolower($q), 'compare') || str_contains(strtolower($q), 'versus') || str_contains(strtolower($q), ' vs ');
if ($items && $isCompare) {
    $message = 'Here are the closest matches, ranked by rating so you can compare quality and price.';
} elseif ($items) {
    $message = 'These products look like a good match. Open one to see details, stock, and checkout options.';
} else {
    $message = 'I could not find that exact item. Try a category or feature, such as cookware, kettle, knife, or blender.';
}
json_response(['message' => $message, 'items' => $items]);
