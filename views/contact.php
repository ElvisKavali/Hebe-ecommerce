<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Catalog.php';
$pageTitle = 'Contact';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_require();
    $d = [
        'name'=>trim($_POST['name'] ?? ''),
        'email'=>filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: '',
        'subject'=>trim($_POST['subject'] ?? ''),
        'message'=>trim($_POST['message'] ?? ''),
    ];
    if (!$d['name'] || !$d['email'] || !$d['subject'] || !$d['message']) {
        flash('error', 'All fields are required.');
    } else {
        (new Contact())->create($d);
        flash('success', 'Thanks — we will get back to you shortly.');
        redirect('views/contact.php');
    }
}
?>
<div class="container my-5">
  <div class="row g-5">
    <div class="col-lg-5">
      <h1>Get in touch</h1>
      <p class="text-muted">Questions about products or wholesale? Send us a message.</p>
      <ul class="list-unstyled">
        <li class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i> Buruburu phase 1, Nairobi, Kenya</li>
        <li class="mb-2"><a href="mailto:hebeappliances@gmail.com"><i class="bi bi-envelope text-primary me-2"></i></a> <span>hebeappliances@gmail.com</span></li>
        <li class="mb-2"><a href="tel:+254720487799"><i class="bi bi-telephone text-primary me-2"></i></a> <span> +254 720 48 77 99</span></li>
        <li class="mb-2"> <a href="http://wa.me/254720487799" target="_blank"><i class="bi bi-whatsapp text-success me-2"></i></a> <span> Chat on WhatsApp</span></li>
      </ul>
      <div class="ratio ratio-16x9 mt-3 rounded overflow-hidden">
        <iframe src="https://www.google.com/maps?q=Nairobi&output=embed" loading="lazy"></iframe>
      </div>
    </div>
    <div class="col-lg-7">
      <form method="post" class="checkout-card">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
          <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
          <div class="col-12"><label class="form-label">Subject</label><input class="form-control" name="subject" required></div>
          <div class="col-12"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="6" required></textarea></div>
          <div class="col-12"><button class="btn btn-primary btn-lg">Send message</button></div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
