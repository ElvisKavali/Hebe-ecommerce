<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Catalog.php';

$pm = new Product();
$id = (int)($_GET['id'] ?? 0);
$p  = $id ? $pm->find($id) : null;
$cats = (new Category())->all(false);
$brands = (new Brand())->all(false);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $d = [
        'name'=>trim($_POST['name'] ?? ''),
        'slug'=>slugify($_POST['slug'] ?: $_POST['name']),
        'sku'=>trim($_POST['sku'] ?? ''), 'barcode'=>trim($_POST['barcode'] ?? ''),
        'category_id'=>(int)($_POST['category_id'] ?? 0) ?: null,
        'brand_id'=>(int)($_POST['brand_id'] ?? 0) ?: null,
        'description'=>trim($_POST['description'] ?? ''),
        'specifications'=>trim($_POST['specifications'] ?? ''),
        'price'=>(float)($_POST['price'] ?? 0),
        'discount_price'=>$_POST['discount_price']!==''?(float)$_POST['discount_price']:null,
        'stock'=>(int)($_POST['stock'] ?? 0),
        'new_arrival'=>!empty($_POST['new_arrival']),
        'weight'=>$_POST['weight']!==''?(float)$_POST['weight']:null,
        'dimensions'=>trim($_POST['dimensions'] ?? ''),
        'tags'=>trim($_POST['tags'] ?? ''),
        'featured'=>!empty($_POST['featured']),
        'bestseller'=>!empty($_POST['bestseller']),
        'status'=>(int)($_POST['status'] ?? 1),
    ];
    if (!empty($_FILES['image']['name'])) {
        if ($path = upload_image($_FILES['image'], 'products')) $d['image'] = $path;
    }
    if (!$d['name'] || !$d['sku']) { flash('error','Name and SKU are required.'); }
    else {
      try {
        if ($id) { $pm->update($id, $d); flash('success','Product updated.'); }
        else     { $id = $pm->create($d); flash('success','Product created.'); }
        redirect('admin/product_edit.php?id=' . $id);
      } catch (PDOException $e) {
        error_log('[product_edit] ' . $e->getMessage());
        flash('error', str_contains($e->getMessage(), 'Duplicate entry') ? 'The slug or SKU is already in use.' : 'The product could not be saved.');
      }
    }
}
$pageTitle = $p ? 'Edit Product' : 'New Product';
?>
<div class="alert alert-light border mb-3">
  <strong>Edit product details</strong> — update the product name, price, stock, and image here.
</div>
<form method="post" enctype="multipart/form-data" class="admin-card p-3">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label">Product name</label>
      <input class="form-control" name="name" value="<?= e($p['name'] ?? '') ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Slug</label>
      <input class="form-control" name="slug" value="<?= e($p['slug'] ?? '') ?>" placeholder="auto from name">
    </div>
    <div class="col-md-4"><label class="form-label">SKU</label><input class="form-control" name="sku" value="<?= e($p['sku'] ?? '') ?>" required></div>
    <div class="col-md-4"><label class="form-label">Barcode</label><input class="form-control" name="barcode" value="<?= e($p['barcode'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Stock</label><input type="number" min="0" class="form-control" name="stock" value="<?= (int)($p['stock'] ?? 0) ?>"></div>
    <div class="col-md-4">
      <label class="form-label">Category</label>
      <select class="form-select" name="category_id"><option value="">-</option>
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($p['category_id'] ?? 0)==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Brand</label>
      <select class="form-select" name="brand_id"><option value="">-</option>
        <?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>" <?= ($p['brand_id'] ?? 0)==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="1" <?= ($p['status'] ?? 1)?'selected':'' ?>>Active</option><option value="0" <?= isset($p['status']) && !$p['status']?'selected':'' ?>>Hidden</option></select></div>
    <div class="col-md-4"><label class="form-label">Price ($)</label><input type="number" step="0.01" class="form-control" name="price" value="<?= e($p['price'] ?? '') ?>" required></div>
    <div class="col-md-4"><label class="form-label">Discount price ($)</label><input type="number" step="0.01" class="form-control" name="discount_price" value="<?= e($p['discount_price'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Weight (kg)</label><input type="number" step="0.01" class="form-control" name="weight" value="<?= e($p['weight'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Dimensions</label><input class="form-control" name="dimensions" value="<?= e($p['dimensions'] ?? '') ?>" placeholder="LxWxH cm"></div>
    <div class="col-md-6"><label class="form-label">Tags (comma)</label><input class="form-control" name="tags" value="<?= e($p['tags'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4"><?= e($p['description'] ?? '') ?></textarea></div>
    <div class="col-12"><label class="form-label">Specifications (separate with ; )</label><textarea class="form-control" name="specifications" rows="2"><?= e($p['specifications'] ?? '') ?></textarea></div>
    <div class="col-md-6"><label class="form-label">Main image</label><input type="file" class="form-control" name="image" accept="image/*"><?php if (!empty($p['image'])): ?><img src="<?= e(product_image_url($p['image'])) ?>" class="mt-2" style="height:80px"><?php endif; ?></div>
    <div class="col-md-6 d-flex align-items-end gap-3 flex-wrap">
      <div class="form-check"><input class="form-check-input" type="checkbox" id="f1" name="featured" <?= !empty($p['featured'])?'checked':'' ?>><label class="form-check-label" for="f1">Featured</label></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" id="b1" name="bestseller" <?= !empty($p['bestseller'])?'checked':'' ?>><label class="form-check-label" for="b1">Best seller</label></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" id="n1" name="new_arrival" <?= !empty($p['new_arrival'])?'checked':'' ?>><label class="form-check-label" for="n1">New arrival</label></div>
    </div>
  </div>
  <div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary">Save</button>
    <a class="btn btn-link" href="<?= url('admin/products.php') ?>">Cancel</a>
  </div>
</form>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
