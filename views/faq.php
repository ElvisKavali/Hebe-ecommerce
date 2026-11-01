<?php require_once __DIR__ . '/../includes/header.php'; $pageTitle = 'FAQ';
$faqs = [
  ['How long does delivery take?','Within Nairobi: 1–2 business days. Across Kenya: 3–5 days.'],
  ['What payment methods do you accept?',' M-Pesa STK Push, PayPal, and Cash on Delivery within Nairobi.'],
  ['What is your return policy?','30 days for unused items in original packaging. Contact support to start a return.'],
  ['Are your products genuine?','Yes — we source directly from brands or authorized distributors. Every order includes a HEBE warranty card.'],
  ['Do you offer wholesale pricing?','Yes — for orders over 4 items please contact us at hebeappliances@gmail.com.'],
]; ?>
<div class="container my-5">
  <h1 class="mb-4">Frequently asked questions</h1>
  <div class="accordion" id="faq">
    <?php foreach ($faqs as $i=>$f): ?>
      <div class="accordion-item">
        <h2 class="accordion-header"><button class="accordion-button <?= $i?'collapsed':'' ?>" data-bs-toggle="collapse" data-bs-target="#f<?= $i ?>"><?= e($f[0]) ?></button></h2>
        <div id="f<?= $i ?>" class="accordion-collapse collapse <?= $i?'':'show' ?>" data-bs-parent="#faq"><div class="accordion-body text-muted"><?= e($f[1]) ?></div></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
