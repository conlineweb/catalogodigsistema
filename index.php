<?php
require_once __DIR__ . '/includes/db.php';

$products = productsForCatalog();
$categories = [
    'motor' => ['label' => 'Motor', 'icon' => 'fas fa-oil-can'],
    'suspension' => ['label' => 'Suspensión', 'icon' => 'fas fa-car-side'],
    'frenos' => ['label' => 'Frenos', 'icon' => 'fas fa-shield-halved'],
    'electrico' => ['label' => 'Eléctrico', 'icon' => 'fas fa-car-battery'],
    'carroceria' => ['label' => 'Carrocería', 'icon' => 'fas fa-truck-pickup'],
];
$productsJson = json_encode(array_values($products), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>AutoRepuestos Pro · Tienda digital</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root{--dark:#111f2e;--ink:#17283a;--muted:#6d8393;--line:#e6edf2;--orange:#E67E22;--red:#C0392B;--green:#25D366;--soft:#f6f8fb}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--soft);color:var(--ink);min-height:100vh}
.navbar{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.94);backdrop-filter:blur(16px);border-bottom:1px solid rgba(230,237,242,.9);padding:.85rem clamp(1rem,3vw,2rem);display:flex;align-items:center;justify-content:space-between;gap:1rem}
.brand{display:flex;align-items:center;gap:.7rem;text-decoration:none;color:var(--ink);font-weight:900;letter-spacing:-.03em}
.brand-icon{width:42px;height:42px;border-radius:16px;background:linear-gradient(135deg,var(--red),var(--orange));display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 10px 22px rgba(230,126,34,.24)}
.brand small{display:block;color:var(--muted);font-size:.67rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase}
.nav-links{display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
.nav-link{color:#385367;text-decoration:none;font-weight:700;font-size:.86rem;padding:.45rem .2rem;border-bottom:2px solid transparent;transition:.2s}
.nav-link:hover{color:var(--orange);border-bottom-color:var(--orange)}
.btn-account{background:#111f2e;color:#fff;text-decoration:none;border-radius:999px;padding:.55rem 1rem;font-weight:800;font-size:.82rem;display:inline-flex;align-items:center;gap:.45rem}
.cart-button,.menu-button{border:none;cursor:pointer;font-family:'Inter',sans-serif}
.cart-button{background:linear-gradient(135deg,var(--red),var(--orange));color:#fff;border-radius:999px;padding:.58rem 1rem;display:flex;align-items:center;gap:.55rem;font-weight:900;box-shadow:0 10px 24px rgba(230,126,34,.24)}
.cart-count{background:#fff;color:var(--red);border-radius:999px;padding:2px 8px;font-size:.72rem}
.menu-button{display:none;background:#fff;border:1px solid var(--line);border-radius:14px;width:42px;height:42px;color:var(--ink);font-size:1.1rem}
.mobile-panel{position:fixed;inset:0 auto 0 -290px;width:290px;background:#fff;z-index:200;padding:1.2rem;transition:.28s ease;box-shadow:12px 0 28px rgba(17,31,46,.12);display:flex;flex-direction:column;gap:.7rem}
.mobile-panel.open{left:0}
.mobile-panel .nav-link{display:block;padding:.85rem;border-bottom:1px solid var(--line)}
.overlay{position:fixed;inset:0;background:rgba(17,31,46,.42);backdrop-filter:blur(4px);opacity:0;visibility:hidden;transition:.2s;z-index:190}
.overlay.active{opacity:1;visibility:visible}
.hero{background:radial-gradient(circle at top left,rgba(230,126,34,.22),transparent 34%),linear-gradient(135deg,#111f2e,#263f55);color:#fff;padding:clamp(3rem,7vw,5.5rem) clamp(1rem,4vw,2rem)}
.hero-inner{max-width:1180px;margin:0 auto;display:grid;grid-template-columns:1.1fr .9fr;gap:2rem;align-items:center}
.eyebrow{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);border-radius:999px;padding:.42rem .8rem;color:#f6d8bd;font-weight:800;font-size:.78rem;margin-bottom:1rem}
.hero h1{font-size:clamp(2.25rem,6vw,4.7rem);line-height:.98;letter-spacing:-.075em;margin-bottom:1rem}
.hero p{color:#c8d6df;font-size:1rem;line-height:1.75;max-width:640px}
.hero-actions{display:flex;gap:.8rem;flex-wrap:wrap;margin-top:1.6rem}
.btn-primary,.btn-secondary{border:none;border-radius:999px;padding:.9rem 1.25rem;font-family:'Inter',sans-serif;font-weight:900;text-decoration:none;display:inline-flex;align-items:center;gap:.55rem;cursor:pointer}
.btn-primary{background:var(--green);color:#fff;box-shadow:0 15px 30px rgba(37,211,102,.24)}
.btn-secondary{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.18)}
.hero-card{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.16);border-radius:32px;padding:1.3rem;box-shadow:0 24px 55px rgba(0,0,0,.18)}
.hero-card-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.mini-card{background:rgba(255,255,255,.1);border-radius:24px;padding:1rem}
.mini-card i{color:#f2a052;font-size:1.4rem;margin-bottom:.8rem}
.mini-card strong{display:block;font-size:1.45rem}
.mini-card span{color:#c8d6df;font-size:.78rem}
.container{max-width:1180px;margin:0 auto;padding:2rem clamp(1rem,3vw,1.5rem)}
.section-toolbar{display:flex;align-items:end;justify-content:space-between;gap:1rem;margin:1rem 0 1.6rem;flex-wrap:wrap}
.section-toolbar h2{font-size:clamp(1.55rem,3vw,2.2rem);letter-spacing:-.04em}
.section-toolbar p{color:var(--muted);font-size:.92rem;margin-top:.3rem}
.search-box{background:#fff;border:1px solid var(--line);border-radius:20px;padding:.55rem .85rem;display:flex;align-items:center;gap:.55rem;min-width:min(100%,320px)}
.search-box i{color:var(--orange)}
.search-box input{border:none;outline:none;font-family:'Inter',sans-serif;width:100%;font-weight:600;color:var(--ink)}
.category-section{scroll-margin-top:100px;margin-bottom:2.4rem}
.category-title{display:flex;align-items:center;gap:.8rem;margin-bottom:1rem}
.category-title .cat-icon{width:44px;height:44px;border-radius:16px;background:#fff4ec;color:var(--orange);display:flex;align-items:center;justify-content:center}
.category-title h3{font-size:1.22rem;letter-spacing:-.03em}
.products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(245px,1fr));gap:1.15rem}
.product-card{background:#fff;border:1px solid var(--line);border-radius:28px;overflow:hidden;box-shadow:0 14px 35px rgba(17,31,46,.05);transition:.22s ease;display:flex;flex-direction:column;min-height:100%}
.product-card:hover{transform:translateY(-4px);box-shadow:0 20px 45px rgba(17,31,46,.1);border-color:#f0c6a2}
.product-media{height:180px;background:linear-gradient(145deg,#fff8f3,#f4f8fb);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
.product-media img{width:100%;height:100%;object-fit:cover}
.product-media i{font-size:3.4rem;color:var(--orange)}
.product-chip{position:absolute;top:14px;left:14px;background:rgba(255,255,255,.9);color:#385367;border:1px solid rgba(230,237,242,.9);border-radius:999px;padding:.28rem .65rem;font-size:.7rem;font-weight:900}
.product-body{padding:1.1rem;display:flex;flex-direction:column;gap:.65rem;flex:1}
.product-title{font-weight:900;font-size:1rem;letter-spacing:-.02em}
.product-desc{color:var(--muted);font-size:.8rem;line-height:1.5;min-height:38px}
.price-row{margin-top:auto;display:flex;align-items:center;justify-content:space-between;gap:.8rem}
.price{font-weight:900;color:var(--red);font-size:1.35rem;letter-spacing:-.04em}
.btn-add{border:none;background:#111f2e;color:#fff;border-radius:999px;padding:.62rem .9rem;font-family:'Inter',sans-serif;font-weight:900;font-size:.78rem;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem}
.btn-add:hover{background:var(--orange)}
.trust-strip{background:#fff;border:1px solid var(--line);border-radius:28px;padding:1rem;display:grid;grid-template-columns:repeat(3,1fr);gap:.7rem;margin:2.5rem 0}
.trust-item{display:flex;align-items:center;gap:.75rem;padding:.8rem;border-radius:20px;background:#f9fbfc}
.trust-item i{color:var(--orange);font-size:1.2rem}
.trust-item strong{display:block;font-size:.86rem}
.trust-item span{color:var(--muted);font-size:.75rem}
.cart-overlay{position:fixed;inset:0;background:rgba(17,31,46,.48);backdrop-filter:blur(5px);z-index:300;opacity:0;visibility:hidden;transition:.2s}
.cart-overlay.active{opacity:1;visibility:visible}
.cart-drawer{position:fixed;inset:0 -440px 0 auto;width:min(100%,440px);background:#fff;z-index:310;transition:.28s ease;display:flex;flex-direction:column;box-shadow:-18px 0 40px rgba(17,31,46,.18)}
.cart-drawer.open{right:0}
.cart-header{padding:1.25rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}
.cart-header h3{font-size:1.05rem}
.close-cart{border:none;background:#f3f6f8;border-radius:14px;width:38px;height:38px;cursor:pointer;font-size:1.3rem}
.cart-items{flex:1;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.75rem}
.empty-cart{text-align:center;color:var(--muted);padding:3rem 1rem}
.empty-cart i{font-size:2.2rem;color:#c9d5dc;margin-bottom:.8rem}
.cart-item{border:1px solid var(--line);border-radius:22px;padding:.9rem;display:grid;grid-template-columns:1fr auto;gap:.8rem;align-items:center}
.cart-item h4{font-size:.9rem;margin-bottom:.2rem}
.cart-item p{color:var(--red);font-weight:800;font-size:.8rem}
.qty-controls{display:flex;align-items:center;gap:.45rem}
.qty-controls button{border:none;background:#f2f5f7;border-radius:999px;width:28px;height:28px;cursor:pointer;font-weight:900;color:var(--ink)}
.remove-item{color:#d55;background:transparent!important}
.cart-footer{border-top:1px solid var(--line);padding:1rem}
.cart-total{display:flex;align-items:center;justify-content:space-between;font-size:1.25rem;font-weight:900;margin-bottom:.9rem}
.checkout-btn,.clear-btn{width:100%;border:none;border-radius:999px;padding:.9rem 1rem;font-family:'Inter',sans-serif;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.55rem;margin-top:.55rem}
.checkout-btn{background:var(--green);color:#fff}
.clear-btn{background:#eef3f6;color:#385367}
.wa-float{position:fixed;right:22px;bottom:22px;width:60px;height:60px;border:none;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.8rem;box-shadow:0 16px 30px rgba(37,211,102,.3);z-index:90;cursor:pointer}
footer{text-align:center;color:var(--muted);font-size:.78rem;padding:2.5rem 1rem 3rem}
@media(max-width:900px){.hero-inner{grid-template-columns:1fr}.hero-card{display:none}.trust-strip{grid-template-columns:1fr}.nav-links{display:none}.menu-button{display:inline-flex;align-items:center;justify-content:center}.navbar{gap:.55rem}.brand small{display:none}}
@media(max-width:560px){.cart-button span.label{display:none}.products-grid{grid-template-columns:1fr}.hero-actions{flex-direction:column}.btn-primary,.btn-secondary{justify-content:center}.section-toolbar{align-items:stretch}.search-box{width:100%}}
</style>
</head>
<body>
<nav class="navbar">
  <button class="menu-button" id="menuBtn"><i class="fas fa-bars"></i></button>
  <a class="brand" href="/catalogodigsistema/index.php">
    <span class="brand-icon"><i class="fas fa-car"></i></span>
    <span>AutoRepuestos Pro<small>Catálogo digital</small></span>
  </a>
  <div class="nav-links">
    <?php foreach ($categories as $key => $cat): ?>
    <a class="nav-link" href="#<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($cat['label']) ?></a>
    <?php endforeach; ?>
    <a class="btn-account" href="/catalogodigsistema/admin/login.php"><i class="fas fa-user-shield"></i> Administrador</a>
  </div>
  <button class="cart-button" id="cartBtn"><i class="fas fa-bag-shopping"></i><span class="label">Pedido</span><span class="cart-count" id="cartCounter">0</span></button>
</nav>

<div class="overlay" id="menuOverlay"></div>
<aside class="mobile-panel" id="mobilePanel">
  <button class="close-cart" id="closeMenuBtn" style="align-self:flex-end">&times;</button>
  <?php foreach ($categories as $key => $cat): ?>
  <a class="nav-link mobile-link" href="#<?= htmlspecialchars($key) ?>"><i class="<?= htmlspecialchars($cat['icon']) ?>"></i> <?= htmlspecialchars($cat['label']) ?></a>
  <?php endforeach; ?>
  <a class="btn-account" href="/catalogodigsistema/admin/login.php" style="justify-content:center;margin-top:.7rem"><i class="fas fa-user-shield"></i> Administrador</a>
</aside>

<header class="hero">
  <div class="hero-inner">
    <div>
      <span class="eyebrow"><i class="fab fa-whatsapp"></i> Pedidos directos por WhatsApp</span>
      <h1>Refacciones para tu auto con atención profesional.</h1>
      <p>Explora el catálogo actualizado, agrega tus productos al pedido y envía el detalle directamente por WhatsApp para recibir atención personalizada.</p>
      <div class="hero-actions">
        <a class="btn-primary" href="#catalogo"><i class="fas fa-cart-plus"></i> Ver catálogo</a>
        <button class="btn-secondary" id="shareCatalogBtn"><i class="fab fa-whatsapp"></i> Solicitar asesoría</button>
      </div>
    </div>
    <div class="hero-card">
      <div class="hero-card-grid">
        <div class="mini-card"><i class="fas fa-tags"></i><strong><?= count($products) ?></strong><span>productos disponibles</span></div>
        <div class="mini-card"><i class="fas fa-mobile-screen"></i><strong>100%</strong><span>responsive</span></div>
        <div class="mini-card"><i class="fab fa-whatsapp"></i><strong>WA</strong><span>pedido inmediato</span></div>
        <div class="mini-card"><i class="fas fa-shield-halved"></i><strong>Pro</strong><span>catálogo administrable</span></div>
      </div>
    </div>
  </div>
</header>

<main class="container" id="catalogo">
  <div class="section-toolbar">
    <div>
      <h2>Catálogo de productos</h2>
      <p>Tarjetas modernas, precios actualizados y selección simple para enviar por WhatsApp.</p>
    </div>
    <label class="search-box">
      <i class="fas fa-search"></i>
      <input type="search" id="searchInput" placeholder="Buscar producto...">
    </label>
  </div>

  <?php foreach ($categories as $key => $cat): ?>
  <section id="<?= htmlspecialchars($key) ?>" class="category-section" data-category="<?= htmlspecialchars($key) ?>">
    <div class="category-title">
      <span class="cat-icon"><i class="<?= htmlspecialchars($cat['icon']) ?>"></i></span>
      <div>
        <h3><?= htmlspecialchars($cat['label']) ?></h3>
      </div>
    </div>
    <div class="products-grid" id="<?= htmlspecialchars($key) ?>-grid"></div>
  </section>
  <?php endforeach; ?>

  <section class="trust-strip">
    <div class="trust-item"><i class="fas fa-clipboard-check"></i><div><strong>Catálogo actualizado</strong><span>Cambios desde administrador</span></div></div>
    <div class="trust-item"><i class="fab fa-whatsapp"></i><div><strong>Compra asistida</strong><span>Pedido detallado por WhatsApp</span></div></div>
    <div class="trust-item"><i class="fas fa-truck-fast"></i><div><strong>Atención formal</strong><span>Orientado a comercio digital</span></div></div>
  </section>
</main>

<button class="wa-float" id="waFloatBtn" aria-label="Contactar por WhatsApp"><i class="fab fa-whatsapp"></i></button>

<div class="cart-overlay" id="cartOverlay"></div>
<aside class="cart-drawer" id="cartDrawer">
  <div class="cart-header">
    <h3><i class="fas fa-bag-shopping" style="color:var(--orange)"></i> Mi pedido</h3>
    <button class="close-cart" id="closeCartBtn">&times;</button>
  </div>
  <div class="cart-items" id="cartItems"></div>
  <div class="cart-footer">
    <div class="cart-total"><span>Total</span><span id="cartTotal">$0.00</span></div>
    <button class="checkout-btn" id="checkoutBtn"><i class="fab fa-whatsapp"></i> Enviar pedido por WhatsApp</button>
    <button class="clear-btn" id="clearCartBtn"><i class="fas fa-trash-alt"></i> Vaciar pedido</button>
  </div>
</aside>

<footer>AutoRepuestos Pro · Catálogo administrable · Pedidos directos por WhatsApp</footer>

<script>
const PRODUCTS = <?= $productsJson ?>;
const CATEGORIES = <?= json_encode($categories, JSON_UNESCAPED_UNICODE) ?>;
const WHATSAPP_NUMBER = '524771181285';
let cart = [];

function money(value) {
  return '$' + Number(value || 0).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function esc(str) {
  return String(str || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function productMedia(product) {
  if (product.image) return `<img src="${esc(product.image)}" alt="${esc(product.name)}">`;
  return `<i class="${esc(product.icon || 'fas fa-box')}"></i>`;
}

function loadCart() {
  try { cart = JSON.parse(localStorage.getItem('autorepuestos_cart') || '[]'); }
  catch (e) { cart = []; }
  updateCartUI();
}

function saveCart() {
  localStorage.setItem('autorepuestos_cart', JSON.stringify(cart));
}

function cartTotal() {
  return cart.reduce((sum, item) => sum + Number(item.price) * Number(item.quantity), 0);
}

function renderProducts(filter = '') {
  Object.keys(CATEGORIES).forEach(category => {
    const grid = document.getElementById(`${category}-grid`);
    if (grid) grid.innerHTML = '';
  });

  const term = filter.trim().toLowerCase();
  const visibleByCategory = {};
  PRODUCTS.filter(product => {
    const haystack = `${product.name || ''} ${product.desc || ''} ${CATEGORIES[product.category]?.label || ''}`.toLowerCase();
    return !term || haystack.includes(term);
  }).forEach(product => {
    const category = product.category || 'motor';
    const grid = document.getElementById(`${category}-grid`);
    if (!grid) return;
    visibleByCategory[category] = true;
    const card = document.createElement('article');
    card.className = 'product-card';
    card.innerHTML = `
      <div class="product-media">
        <span class="product-chip">${esc(CATEGORIES[category]?.label || category)}</span>
        ${productMedia(product)}
      </div>
      <div class="product-body">
        <div class="product-title">${esc(product.name)}</div>
        <div class="product-desc">${esc(product.desc || 'Refacción seleccionada para catálogo digital.')}</div>
        <div class="price-row">
          <span class="price">${money(product.price)}</span>
          <button class="btn-add" data-id="${product.id}"><i class="fas fa-cart-plus"></i> Agregar</button>
        </div>
      </div>`;
    grid.appendChild(card);
  });

  document.querySelectorAll('.category-section').forEach(section => {
    const category = section.dataset.category;
    section.style.display = visibleByCategory[category] || !term ? '' : 'none';
  });
  document.querySelectorAll('.btn-add').forEach(btn => btn.addEventListener('click', () => addToCart(parseInt(btn.dataset.id, 10))));
}

function addToCart(id) {
  const product = PRODUCTS.find(item => parseInt(item.id, 10) === id);
  if (!product) return;
  const existing = cart.find(item => parseInt(item.id, 10) === id);
  if (existing) existing.quantity += 1;
  else cart.push({ id: product.id, name: product.name, price: Number(product.price), quantity: 1 });
  saveCart();
  updateCartUI();
  Swal.fire({ icon:'success', title:'Producto agregado', text: product.name, timer:1100, showConfirmButton:false, toast:true, position:'top-end' });
}

function changeQuantity(id, delta) {
  const idx = cart.findIndex(item => parseInt(item.id, 10) === id);
  if (idx === -1) return;
  cart[idx].quantity += delta;
  if (cart[idx].quantity <= 0) cart.splice(idx, 1);
  saveCart();
  updateCartUI();
}

function removeFromCart(id) {
  cart = cart.filter(item => parseInt(item.id, 10) !== id);
  saveCart();
  updateCartUI();
}

function updateCartUI() {
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  document.getElementById('cartCounter').textContent = totalItems;
  document.getElementById('cartTotal').textContent = money(cartTotal());
  renderCartItems();
}

function renderCartItems() {
  const container = document.getElementById('cartItems');
  if (!cart.length) {
    container.innerHTML = '<div class="empty-cart"><i class="fas fa-bag-shopping"></i><p>Tu pedido está vacío.<br>Agrega productos para enviarlos por WhatsApp.</p></div>';
    return;
  }
  container.innerHTML = cart.map(item => `
    <div class="cart-item">
      <div>
        <h4>${esc(item.name)}</h4>
        <p>${money(item.price)} c/u · Subtotal ${money(item.price * item.quantity)}</p>
      </div>
      <div class="qty-controls">
        <button data-action="dec" data-id="${item.id}">-</button>
        <strong>${item.quantity}</strong>
        <button data-action="inc" data-id="${item.id}">+</button>
        <button class="remove-item" data-action="remove" data-id="${item.id}"><i class="fas fa-trash"></i></button>
      </div>
    </div>`).join('');
  container.querySelectorAll('button[data-action]').forEach(button => {
    button.addEventListener('click', () => {
      const id = parseInt(button.dataset.id, 10);
      if (button.dataset.action === 'inc') changeQuantity(id, 1);
      if (button.dataset.action === 'dec') changeQuantity(id, -1);
      if (button.dataset.action === 'remove') removeFromCart(id);
    });
  });
}

function openCart() {
  document.getElementById('cartOverlay').classList.add('active');
  document.getElementById('cartDrawer').classList.add('open');
}

function closeCart() {
  document.getElementById('cartOverlay').classList.remove('active');
  document.getElementById('cartDrawer').classList.remove('open');
}

function sendWhatsAppMessage(message) {
  window.open(`https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`, '_blank');
}

function buildCatalogMessage() {
  let msg = '*AUTOREPUESTOS PRO*\\nCatálogo digital actualizado\\n\\n';
  Object.keys(CATEGORIES).forEach(category => {
    const items = PRODUCTS.filter(product => product.category === category);
    if (!items.length) return;
    msg += `*${CATEGORIES[category].label.toUpperCase()}*\\n`;
    items.forEach(product => { msg += `- ${product.name}: ${money(product.price)}\\n`; });
    msg += '\\n';
  });
  msg += 'Me interesa recibir asesoría sobre estas refacciones.';
  return msg;
}

async function collectCustomerData() {
  const { value } = await Swal.fire({
    title: 'Datos para enviar el pedido',
    html: `<div style="text-align:left;display:grid;gap:.8rem">
      <label style="font-weight:700;font-size:.85rem">Nombre completo<input id="swal-name" class="swal2-input" style="width:100%;margin:.35rem 0 0" placeholder="Ej. Juan Pérez"></label>
      <label style="font-weight:700;font-size:.85rem">Correo electrónico<input id="swal-email" class="swal2-input" style="width:100%;margin:.35rem 0 0" placeholder="cliente@correo.com"></label>
      <label style="font-weight:700;font-size:.85rem">Calle y número<input id="swal-street" class="swal2-input" style="width:100%;margin:.35rem 0 0" placeholder="Av. Reforma 123"></label>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem">
        <label style="font-weight:700;font-size:.85rem">Ciudad<input id="swal-city" style="width:100%;margin:.35rem 0 0;padding:.75rem;border:1px solid #d7dee5;border-radius:.7rem" placeholder="León"></label>
        <label style="font-weight:700;font-size:.85rem">CP<input id="swal-zip" style="width:100%;margin:.35rem 0 0;padding:.75rem;border:1px solid #d7dee5;border-radius:.7rem" placeholder="37420"></label>
      </div>
    </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fab fa-whatsapp"></i> Enviar por WhatsApp',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#25D366',
    preConfirm: () => {
      const data = {
        name: document.getElementById('swal-name').value.trim(),
        email: document.getElementById('swal-email').value.trim(),
        street: document.getElementById('swal-street').value.trim(),
        city: document.getElementById('swal-city').value.trim(),
        zip: document.getElementById('swal-zip').value.trim(),
      };
      const missing = [];
      if (!data.name) missing.push('nombre');
      if (!data.email) missing.push('correo');
      if (!data.street) missing.push('dirección');
      if (!data.city) missing.push('ciudad');
      if (!data.zip) missing.push('código postal');
      if (missing.length) {
        Swal.showValidationMessage('Completa: ' + missing.join(', '));
        return false;
      }
      if (!/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(data.email)) {
        Swal.showValidationMessage('Ingresa un correo electrónico válido');
        return false;
      }
      return data;
    }
  });
  return value || null;
}

async function createOrder(items, customer) {
  try {
    const res = await fetch('/catalogodigsistema/api/create_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items, customer, payment_method: 'whatsapp' })
    });
    return await res.json();
  } catch (e) {
    return { success: false };
  }
}

function buildOrderMessage(order, customer, items) {
  let msg = '*PEDIDO AUTOREPUESTOS PRO*\\n';
  if (order.success && order.order_id) msg += `Folio: ${order.order_id}\\n`;
  msg += '\\n*Productos*\\n';
  items.forEach(item => { msg += `- ${item.name} x${item.quantity}: ${money(item.price * item.quantity)}\\n`; });
  msg += `\\n*Total:* ${money(items.reduce((sum, item) => sum + item.price * item.quantity, 0))} MXN\\n`;
  msg += '\\n*Datos de contacto*\\n';
  msg += `Nombre: ${customer.name}\\nCorreo: ${customer.email}\\nDirección: ${customer.street}, ${customer.city}, CP ${customer.zip}\\n`;
  msg += '\\nPedido enviado desde el catálogo web.';
  return msg;
}

async function checkoutWhatsApp() {
  if (!cart.length) {
    Swal.fire('Pedido vacío', 'Agrega productos antes de enviar tu pedido.', 'warning');
    return;
  }
  const customer = await collectCustomerData();
  if (!customer) return;
  const items = cart.map(item => ({...item}));
  const order = await createOrder(items, customer);
  sendWhatsAppMessage(buildOrderMessage(order, customer, items));
  cart = [];
  saveCart();
  updateCartUI();
  closeCart();
  if (order.success) {
    Swal.fire({
      icon:'success',
      title:'Pedido registrado',
      html:`Folio: <strong>${esc(order.order_id)}</strong><br>Se abrió WhatsApp con el detalle del pedido.`,
      confirmButtonColor:'#25D366'
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  renderProducts();
  loadCart();
  document.getElementById('searchInput').addEventListener('input', e => renderProducts(e.target.value));
  document.getElementById('cartBtn').addEventListener('click', openCart);
  document.getElementById('closeCartBtn').addEventListener('click', closeCart);
  document.getElementById('cartOverlay').addEventListener('click', closeCart);
  document.getElementById('checkoutBtn').addEventListener('click', checkoutWhatsApp);
  document.getElementById('clearCartBtn').addEventListener('click', async () => {
    if (!cart.length) return;
    const result = await Swal.fire({title:'Vaciar pedido',text:'Se eliminarán los productos seleccionados.',icon:'question',showCancelButton:true,confirmButtonText:'Vaciar',cancelButtonText:'Cancelar'});
    if (result.isConfirmed) { cart = []; saveCart(); updateCartUI(); }
  });
  document.getElementById('shareCatalogBtn').addEventListener('click', () => sendWhatsAppMessage(buildCatalogMessage()));
  document.getElementById('waFloatBtn').addEventListener('click', () => sendWhatsAppMessage(buildCatalogMessage()));

  const menuPanel = document.getElementById('mobilePanel');
  const menuOverlay = document.getElementById('menuOverlay');
  const closeMenu = () => { menuPanel.classList.remove('open'); menuOverlay.classList.remove('active'); };
  document.getElementById('menuBtn').addEventListener('click', () => { menuPanel.classList.add('open'); menuOverlay.classList.add('active'); });
  document.getElementById('closeMenuBtn').addEventListener('click', closeMenu);
  menuOverlay.addEventListener('click', closeMenu);
  document.querySelectorAll('.mobile-link').forEach(link => link.addEventListener('click', closeMenu));
});
</script>
</body>
</html>
