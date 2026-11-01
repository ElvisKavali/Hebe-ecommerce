<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Catalog.php';

$categories = (new Category())->all();
$brands     = (new Brand())->all();
$productModel = new Product();

$filters = [
    'q'           => trim((string)($_GET['q'] ?? '')),
    'category_id' => null,
    'brand_id'    => $_GET['brand'] ?? null,
    'min_price'   => $_GET['min_price'] ?? '',
    'max_price'   => $_GET['max_price'] ?? '',
    'in_stock'    => !empty($_GET['in_stock']),
    'sort'        => $_GET['sort'] ?? 'newest',
    'page'        => (int)($_GET['page'] ?? 1),
    'per_page'    => 12,
];

if (!empty($_GET['category'])) {
    $cat = (new Category())->findBySlug($_GET['category']);
    if ($cat) $filters['category_id'] = (int)$cat['id'];
}

$result = $productModel->search($filters);
$pageTitle = 'Shop';
?>
<div class="container my-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
      <li class="breadcrumb-item active">Shop</li>
    </ol>
  </nav>
  <div class="row g-4">
    <aside class="col-lg-3">
      <form class="filter-card" method="get">
        <input type="hidden" name="q" value="<?= e($filters['q']) ?>">
        <h6>Filters</h6>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Category</label>
          <select name="category" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= e($c['slug']) ?>" <?= (!empty($_GET['category']) && $_GET['category']===$c['slug'])?'selected':'' ?>><?= e($c['name']) ?> (<?= $c['product_count'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Brand</label>
          <select name="brand" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach ($brands as $b): ?>
              <option value="<?= $b['id'] ?>" <?= ($filters['brand_id']==$b['id'])?'selected':'' ?>><?= e($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3 row g-2">
          <div class="col"><input type="number" min="0" step="0.01" name="min_price" value="<?= e($filters['min_price']) ?>" class="form-control form-control-sm" placeholder="Min $"></div>
          <div class="col"><input type="number" min="0" step="0.01" name="max_price" value="<?= e($filters['max_price']) ?>" class="form-control form-control-sm" placeholder="Max $"></div>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="inStock" <?= $filters['in_stock']?'checked':'' ?>>
          <label class="form-check-label small" for="inStock">In stock only</label>
        </div>
        <button class="btn btn-primary w-100 btn-sm">Apply filters</button>
        <a href="<?= url('views/shop.php') ?>" class="btn btn-link w-100 btn-sm">Reset</a>
      </form>
    </aside>
    <section class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="text-muted small">Showing <?= count($result['items']) ?> of <?= $result['pagination']['total'] ?> products</div>
        <form method="get" class="d-flex align-items-center gap-2">
          <?php foreach ($_GET as $k=>$v): if($k==='sort'||$k==='page') continue; ?>
            <input type="hidden" name="<?= e($k) ?>" value="<?= e(is_array($v)?'':$v) ?>">
          <?php endforeach; ?>
          <label class="small text-muted">Sort:</label>
          <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="newest"     <?= $filters['sort']==='newest'?'selected':'' ?>>Newest</option>
            <option value="price_asc"  <?= $filters['sort']==='price_asc'?'selected':'' ?>>Price: low to high</option>
            <option value="price_desc" <?= $filters['sort']==='price_desc'?'selected':'' ?>>Price: high to low</option>
            <option value="rating"     <?= $filters['sort']==='rating'?'selected':'' ?>>Highest rated</option>
            <option value="popular"    <?= $filters['sort']==='popular'?'selected':'' ?>>Most popular</option>
          </select>
        </form>
      </div>

      <?php if (empty($result['items'])): ?>
        <div class="empty"><i class="bi bi-search"></i><h5>No products found</h5><p>Try a different filter or search term.</p></div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($result['items'] as $p): ?>
            <div class="col-6 col-md-4"><?php include __DIR__ . '/../includes/product_card.php'; ?></div>
          <?php endforeach; ?>
        </div>
        <?php if ($result['pagination']['pages'] > 1): ?>
          <nav class="mt-4"><ul class="pagination justify-content-center">
            <?php for ($i=1;$i<=$result['pagination']['pages'];$i++):
              $q = $_GET; $q['page'] = $i; ?>
              <li class="page-item <?= $i==$result['pagination']['current']?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query($q) ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul></nav>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
