// HEBE Storefront JS
const API = HEBE.BASE_URL + '/api';

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function buildOrbitalDecor() {
  const stage = document.querySelector('.hero-stage');
  if (!stage || prefersReducedMotion) return;

  const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
  const items = [
    { type: 'stanley', asset: 'orbit-stanley.svg', size: 36, radius: 136, angle: 0.2, rotate: 18 },
    { type: 'plate', asset: 'orbit-plate.svg', size: 40, radius: 98, angle: 1.6, rotate: -24 },
    { type: 'spoon', asset: 'orbit-spoon.svg', size: 30, radius: 150, angle: 2.8, rotate: 12 },
    { type: 'blender', asset: 'orbit-blender.svg', size: 46, radius: 118, angle: 4.1, rotate: -20 },
    { type: 'pan', asset: 'orbit-pan.svg', size: 42, radius: 146, angle: 5.3, rotate: 16 },
    { type: 'bowl', asset: 'orbit-bowl.svg', size: 32, radius: 170, angle: 6.2, rotate: -10 },
    { type: 'knife', asset: 'orbit-knife.svg', size: 28, radius: 112, angle: 3.7, rotate: -14 },
    { type: 'cup', asset: 'orbit-cup.svg', size: 34, radius: 163, angle: 0.9, rotate: 26 },
    { type: 'utensil', asset: 'orbit-utensil.svg', size: 28, radius: 182, angle: 5.8, rotate: 22 }
  ];
  const visibleItems = isMobile ? items.slice(0, 4) : items;
  const scene = document.createElement('div');
  scene.className = 'hero-orbital-scene';
  if (isMobile) scene.classList.add('mobile-compact');
  stage.appendChild(scene);

  const orbitNodes = visibleItems.map((item, index) => {
    const img = document.createElement('img');
    img.src = `${HEBE.BASE_URL}/assets/images/${item.asset}`;
    img.alt = item.type;
    img.className = 'orbital-item';
    img.style.width = `${isMobile ? item.size * 0.7 : item.size}px`;
    img.style.height = `${isMobile ? item.size * 0.7 : item.size}px`;
    img.style.zIndex = `${10 + index}`;
    scene.appendChild(img);
    return { ...item, radius: isMobile ? item.radius * 0.72 : item.radius, node: img, startedAt: performance.now() + (index * 180) };
  });

  const center = { x: stage.clientWidth / 2, y: stage.clientHeight / 2 };
  let phase = 0;

  const tick = (now) => {
    phase += isMobile ? 0.012 : 0.018;
    orbitNodes.forEach(({ node, radius, angle, rotate, startedAt }) => {
      const eased = Math.max(0, Math.min(1, (now - startedAt) / 2200));
      const travel = (Math.sin(phase * 1.2 + angle + 1) + 1) / 2;
      const x = Math.cos(phase + angle) * radius * (0.7 + travel * 0.42);
      const y = Math.sin(phase + angle) * (radius * 0.7) * (0.72 + travel * 0.42);
      const lift = (1 - eased) * -120;
      const finalX = center.x + x + Math.cos(phase * 1.5 + angle) * 6;
      const finalY = center.y + y + lift + Math.sin(phase * 1.2 + angle) * 8;
      const rot = (phase * 40) + rotate;
      node.style.transform = `translate(${finalX - node.offsetWidth / 2}px, ${finalY - node.offsetHeight / 2}px) rotate(${rot}deg)`;
    });
    requestAnimationFrame(tick);
  };

  requestAnimationFrame(tick);
}

function initRevealObserver() {
  if (prefersReducedMotion) {
    document.querySelectorAll('.reveal, .card-product, .cat-tile, .section, .hero-copy').forEach(el => el.classList.add('is-visible'));
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

  document.querySelectorAll('.reveal, .card-product, .cat-tile, .section, .hero-copy').forEach((el) => observer.observe(el));
}

const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
buildOrbitalDecor();
initRevealObserver();

function toast(msg, type='success'){
  const c = document.getElementById('toastContainer'); if(!c) return alert(msg);
  const el = document.createElement('div');
  el.className = `toast align-items-center text-bg-${type==='error'?'danger':'primary'} border-0 show`;
  el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
  c.appendChild(el);
  setTimeout(()=>el.remove(), 3500);
}

async function api(path, opts={}){
  const res = await fetch(API + path, {
    method: opts.method || 'GET',
    headers: { 'Content-Type':'application/json', 'X-CSRF-Token': HEBE.CSRF, ...(opts.headers||{}) },
    body: opts.body ? JSON.stringify(opts.body) : undefined,
    credentials:'same-origin'
  });
  const text = await res.text();
  let data;
  try { data = JSON.parse(text); } catch (error) { data = {}; }
  if(!res.ok) throw new Error(data.message || `Request failed (${res.status})`);
  return data;
}

function escapeHtml(value){
  return String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
}

// --- Cart ---
document.addEventListener('click', async e => {
  const btn = e.target.closest('[data-add-to-cart]');
  if(btn){
    e.preventDefault();
    const id = parseInt(btn.dataset.addToCart,10);
    const qty = parseInt(btn.dataset.qty || document.querySelector('#qtyInput')?.value || 1, 10);
    btn.disabled = true; const orig = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    try {
      const r = await api('/cart_add.php', { method:'POST', body:{ product_id:id, quantity:qty } });
      const cartCount = document.getElementById('cartCount');
      if(r.ok){
        if(cartCount) cartCount.textContent = r.count;
        toast(`Added to cart. <a class="text-white fw-semibold" href="${HEBE.BASE_URL}/cart/index.php">View cart and payment options</a>`);
      } else toast(r.message || 'Could not add', 'error');
    } catch(error) {
      toast(error.message || 'Could not add this item to the cart.', 'error');
    } finally {
      btn.disabled = false; btn.innerHTML = orig;
    }
  }
  const w = e.target.closest('[data-toggle-wishlist]');
  if(w){
    e.preventDefault();
    const id = parseInt(w.dataset.toggleWishlist,10);
    const r = await api('/wishlist_toggle.php', { method:'POST', body:{ product_id:id } });
    if(r.ok){
      w.classList.toggle('active', r.active);
      document.getElementById('wishlistCount').textContent = r.count;
      toast(r.active ? 'Added to wishlist' : 'Removed from wishlist');
    } else toast(r.message || 'Login required', 'error');
  }
});

// Cart qty update on cart page
document.addEventListener('change', async e => {
  const inp = e.target.closest('[data-cart-qty]');
  if(!inp) return;
  const id = parseInt(inp.dataset.cartQty,10);
  const r = await api('/cart_update.php', { method:'POST', body:{ product_id:id, quantity: parseInt(inp.value,10) } });
  if(r.ok){ location.reload(); } else toast(r.message,'error');
});

// Live search
const liveSearch = document.getElementById('liveSearch');
const searchResults = document.getElementById('searchResults');
if(liveSearch){
  let t;
  liveSearch.addEventListener('input', () => {
    clearTimeout(t);
    const q = liveSearch.value.trim();
    if(!q){ searchResults.classList.remove('show'); searchResults.innerHTML=''; return; }
    t = setTimeout(async () => {
      const r = await api('/search.php?q=' + encodeURIComponent(q));
      if(!r.items || !r.items.length){
        searchResults.innerHTML = '<div class="p-3 text-muted small">No results</div>';
      } else {
        searchResults.innerHTML = r.items.map(it => `
          <a class="item" href="${HEBE.BASE_URL}/views/product.php?slug=${encodeURIComponent(it.slug)}">
            <img src="${escapeHtml(it.image_url)}" alt="">
            <div><div class="fw-semibold small">${escapeHtml(it.name)}</div><div class="text-primary small">KSh ${(it.discount_price||it.price)}</div></div>
          </a>`).join('');
      }
      searchResults.classList.add('show');
    }, 220);
  });
  document.addEventListener('click', e => {
    if(!e.target.closest('.search-form')) searchResults?.classList.remove('show');
  });
}

// Local shopping assistant: product advice works without an external AI key.
const assistantToggle = document.getElementById('assistantToggle');
const assistantPanel = document.getElementById('assistantPanel');
const assistantForm = document.getElementById('assistantForm');
const assistantInput = document.getElementById('assistantInput');
const assistantMessages = document.getElementById('assistantMessages');
const assistantAdd = (html, type='bot') => {
  if(!assistantMessages) return;
  const message = document.createElement('div');
  message.className = `assistant-message ${type}`;
  message.innerHTML = html;
  assistantMessages.appendChild(message);
  assistantMessages.scrollTop = assistantMessages.scrollHeight;
};
assistantToggle?.addEventListener('click', () => {
  assistantPanel?.classList.toggle('show');
  assistantInput?.focus();
});
assistantForm?.addEventListener('submit', async e => {
  e.preventDefault();
  const q = assistantInput.value.trim();
  if(!q) return;
  assistantAdd(escapeHtml(q), 'user');
  assistantInput.value = '';
  assistantAdd('Let me check the catalogue...', 'bot');
  try {
    const r = await api('/assistant.php?q=' + encodeURIComponent(q));
    assistantMessages.lastElementChild.remove();
    assistantAdd(escapeHtml(r.message || 'I could not find a close match.'), 'bot');
    (r.items || []).forEach(item => assistantAdd(`<a href="${HEBE.BASE_URL}/views/product.php?slug=${encodeURIComponent(item.slug)}"><strong>${escapeHtml(item.name)}</strong><br><span>KSh ${escapeHtml(item.price)}</span></a>`, 'bot'));
  } catch (error) {
    assistantMessages.lastElementChild.remove();
    assistantAdd('I am temporarily unavailable. Try the product search above.', 'bot');
  }
});
if(!sessionStorage.getItem('hebeVisitThanked')){
  sessionStorage.setItem('hebeVisitThanked', '1');
  setTimeout(() => toast('Thanks for visiting HEBE Kitchen Store. We hope you find something useful.'), 12000);
}

// Back to top
const backToTop = document.getElementById('backToTop');
window.addEventListener('scroll', () => backToTop?.classList.toggle('show', window.scrollY > 400));
backToTop?.addEventListener('click', () => window.scrollTo({top:0,behavior:'smooth'}));

// Product image gallery + zoom
document.querySelectorAll('.product-gallery .thumbs img').forEach(t => {
  t.addEventListener('click', () => {
    document.querySelectorAll('.product-gallery .thumbs img').forEach(i=>i.classList.remove('active'));
    t.classList.add('active');
    document.querySelector('.product-gallery .main-img img').src = t.src;
  });
});
const mainImg = document.querySelector('.product-gallery .main-img');
mainImg?.addEventListener('mousemove', e => {
  const r = mainImg.getBoundingClientRect();
  const x = ((e.clientX - r.left)/r.width)*100;
  const y = ((e.clientY - r.top)/r.height)*100;
  const img = mainImg.querySelector('img');
  img.style.transformOrigin = `${x}% ${y}%`;
  img.style.transform = 'scale(1.6)';
});
mainImg?.addEventListener('mouseleave', () => {
  const img = mainImg.querySelector('img'); img.style.transform = ''; img.style.transformOrigin = '';
});

// Qty controls
document.querySelectorAll('.qty-selector').forEach(qs => {
  const inp = qs.querySelector('input');
  const syncQty = () => {
    const value = Math.max(1, parseInt(inp.value,10) || 1);
    inp.value = value;
    const buyNowQty = document.getElementById('buyNowQty');
    if (buyNowQty) buyNowQty.value = value;
  };
  qs.querySelector('.minus')?.addEventListener('click', () => { inp.value = Math.max(1, parseInt(inp.value,10)-1); syncQty(); });
  qs.querySelector('.plus')?.addEventListener('click', () => { inp.value = parseInt(inp.value,10)+1; syncQty(); });
  inp?.addEventListener('input', syncQty);
});

const mpesaPhoneWrap = document.getElementById('mpesaPhoneWrap');
const paymentPhone = document.getElementById('paymentPhone');
const mpesaRadio = document.getElementById('pm_mpesa');
const codRadio = document.getElementById('pm_cod');
const toggleMpesaPhone = () => {
  if (mpesaPhoneWrap && paymentPhone) {
    const visible = mpesaRadio?.checked;
    mpesaPhoneWrap.style.display = visible ? 'block' : 'none';
    paymentPhone.required = visible;
  }
};
mpesaRadio?.addEventListener('change', toggleMpesaPhone);
codRadio?.addEventListener('change', toggleMpesaPhone);
window.addEventListener('load', toggleMpesaPhone);
