</main>

<footer class="site-footer mt-5">
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-lg-4">
        <a class="footer-brand" href="<?= url('/') ?>">HEBE<span>.</span></a>
        <p class="text-muted mt-2">Premium kitchenware and small kitchen electronics. Designed for everyday cooks who care about quality.</p>
        <div class="d-flex gap-2 mt-3">
          <a href="" class="social-icon"><i class="bi bi-facebook"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6>Shop</h6>
        <ul class="footer-links">
          <li><a href="<?= url('views/shop.php') ?>">All Products</a></li>
          <li><a href="<?= url('views/categories.php') ?>">Categories</a></li>
          <?php foreach (array_slice($__categories, 0, 4) as $c): ?>
            <li><a href="<?= url('views/shop.php?category=' . e($c['slug'])) ?>"><?= e($c['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>Help</h6>
        <ul class="footer-links">
          <li><a href="<?= url('views/contact.php') ?>">Contact</a></li>
          <li><a href="<?= url('views/faq.php') ?>">FAQ</a></li>
          <li><a href="<?= url('views/track.php') ?>">Track Order</a></li>
          <li><a href="<?= url('views/about.php') ?>">About HEBE</a></li>
        </ul>
      </div>
      <div class="col-12 col-lg-4">
        <h6>Newsletter</h6>
        <p class="text-muted small">Get 10% off your first order. New product drops every Friday.</p>
        <form class="d-flex gap-2" onsubmit="event.preventDefault(); toast('Subscribed!');">
          <input type="email" class="form-control" placeholder="Your email" required>
          <button class="btn btn-primary">Subscribe</button>
        </form>
      </div>
    </div>
    <hr class="my-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 small text-muted">
      <span>© <?= date('Y') ?> HEBE Kitchen Store. All rights reserved.</span>
      <div class="d-flex gap-3">
        <a href="<?= url('views/privacy.php') ?>">Privacy</a>
        <a href="<?= url('views/terms.php') ?>">Terms</a>
      </div>
    </div>
  </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index:1080"></div>

<button class="assistant-toggle" id="assistantToggle" type="button" aria-label="Open shopping assistant"><i class="bi bi-stars"></i></button>
<section class="assistant-panel" id="assistantPanel" aria-label="Shopping assistant">
  <div class="assistant-header"><strong>Hebe Helper</strong><span>Product guide</span></div>
  <div class="assistant-messages" id="assistantMessages"><div class="assistant-message bot">Hi, I can help identify products, compare prices, and suggest items. What are you looking for?</div></div>
  <form class="assistant-form" id="assistantForm"><input id="assistantInput" class="form-control" placeholder="Try: compare pans" autocomplete="off"><button class="btn btn-primary" aria-label="Send"><i class="bi bi-send"></i></button></form>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') . '?v=' . filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
</body>
</html>
