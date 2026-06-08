<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>AutoRepuestos Pro · Catálogo + Pagos Integrados</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fb;
            color: #1a2c3e;
            scroll-behavior: smooth;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(220, 60, 50, 0.2);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 1.3rem;
            background: linear-gradient(135deg, #C0392B, #E67E22);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .logo-area i {
            color: #E67E22;
            font-size: 1.6rem;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: #C0392B;
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1.2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            font-weight: 600;
            color: #2c4b57;
            transition: 0.2s;
            font-size: 0.9rem;
            border-bottom: 2px solid transparent;
        }

        .nav-link:hover {
            color: #E67E22;
            border-bottom-color: #E67E22;
        }

        .cart-icon {
            cursor: pointer;
            background: #eef3f2;
            padding: 8px 12px;
            border-radius: 60px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-icon i {
            font-size: 1.4rem;
            color: #C0392B;
        }

        .cart-count {
            background: #E67E22;
            color: white;
            border-radius: 40px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Menú móvil */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100%;
            background: white;
            z-index: 1001;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            transition: left 0.3s ease;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .mobile-menu.open { left: 0; }
        .mobile-nav-link {
            font-weight: 600;
            font-size: 1.2rem;
            color: #1f3e48;
            text-decoration: none;
            padding: 0.6rem 0;
            border-bottom: 1px solid #e0edec;
        }
        .close-menu {
            align-self: flex-end;
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
        }
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(3px);
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transition: 0.2s;
        }
        .menu-overlay.active { visibility: visible; opacity: 1; }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        .category-section {
            margin-bottom: 3rem;
            scroll-margin-top: 90px;
        }

        .category-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.5rem;
            border-left: 5px solid #E67E22;
            padding-left: 1rem;
        }
        .category-title i {
            font-size: 1.8rem;
            color: #E67E22;
            background: #fee9e6;
            padding: 8px;
            border-radius: 18px;
        }
        .category-title h2 {
            font-weight: 700;
            font-size: 1.7rem;
            color: #1f3e48;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.8rem;
        }

        .product-card {
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 12px 24px -12px rgba(0,0,0,0.08);
            transition: all 0.25s ease;
            border: 1px solid rgba(230,126,34,0.15);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 32px -14px rgba(0,0,0,0.12);
        }
        .product-img {
            background: linear-gradient(145deg, #fefaf8, #fff5f0);
            padding: 1.5rem 0.5rem;
            text-align: center;
            border-bottom: 1px solid #fee9e6;
        }
        .product-img i { font-size: 3.4rem; color: #E67E22; }
        .product-info { padding: 1.2rem; display: flex; flex-direction: column; }
        .product-title { font-weight: 800; font-size: 1.1rem; margin-bottom: 0.3rem; }
        .product-desc { font-size: 0.75rem; color: #6c8695; margin: 0.2rem 0 0.8rem; }
        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            gap: 8px;
        }
        .product-price { font-weight: 800; font-size: 1.3rem; color: #C0392B; }
        .btn-add {
            background: #E67E22;
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-add:hover { background: #C0392B; transform: scale(0.97); }

        /* Carrito drawer */
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(4px);
            z-index: 1002;
            visibility: hidden;
            opacity: 0;
            transition: 0.25s;
        }
        .cart-overlay.active { visibility: visible; opacity: 1; }
        .cart-drawer {
            position: fixed;
            top: 0;
            right: -420px;
            width: 100%;
            max-width: 420px;
            height: 100%;
            background: white;
            box-shadow: -8px 0 32px rgba(0,0,0,0.1);
            z-index: 1003;
            transition: right 0.3s cubic-bezier(0.2,0.9,0.4,1.1);
            display: flex;
            flex-direction: column;
        }
        .cart-drawer.open { right: 0; }
        .cart-header { padding: 1.5rem; border-bottom: 1px solid #eef2f0; display: flex; justify-content: space-between; }
        .close-cart { background: none; border: none; font-size: 1.6rem; cursor: pointer; }
        .cart-items { flex: 1; overflow-y: auto; padding: 1rem; }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafdfc;
            padding: 0.8rem;
            border-radius: 20px;
            margin-bottom: 0.8rem;
            border: 1px solid #e2ecea;
        }
        .cart-item-info h4 { font-size: 0.9rem; font-weight: 700; }
        .cart-item-info p { font-size: 0.8rem; color: #E67E22; font-weight: 600; }
        .cart-item-qty { display: flex; align-items: center; gap: 10px; }
        .cart-item-qty button { background: #eef3f2; border: none; width: 28px; border-radius: 30px; cursor: pointer; }
        .cart-item-remove { color: #e07c7c; background: none; border: none; cursor: pointer; }
        .cart-footer { padding: 1.2rem; border-top: 2px dashed #dce9e6; }
        .cart-total { display: flex; justify-content: space-between; font-weight: 800; font-size: 1.2rem; margin-bottom: 1rem; }
        .btn-cart-action {
            width: 100%;
            border: none;
            padding: 0.9rem;
            border-radius: 60px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .btn-clear-cart { background: #eef2f5; color: #4b6a78; }
        .whatsapp-float {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #25D366;
            color: white;
            width: 58px;
            height: 58px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(37,211,102,0.3);
            cursor: pointer;
            z-index: 99;
            font-size: 1.8rem;
        }
        .share-bar { display: flex; justify-content: center; margin: 1rem 0; }
        .btn-wa-share {
            background: #25D366;
            border: none;
            padding: 0.8rem 1.8rem;
            border-radius: 60px;
            font-weight: 600;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 1rem;
        }
        footer { text-align: center; font-size: 0.7rem; margin-top: 3rem; padding: 1rem; color: #7f9aa8; }
        .btn-mi-cuenta {
            background: linear-gradient(135deg, #C0392B, #E67E22);
            color: white;
            border: none;
            padding: 0.45rem 1.1rem;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
            white-space: nowrap;
        }
        .btn-mi-cuenta:hover { opacity: 0.88; transform: scale(0.97); }
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hamburger { display: block; }
            .container { padding: 1.2rem; }
            .category-title h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <button class="hamburger" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
    <div class="logo-area"><i class="fas fa-car"></i><span>AutoRepuestos Pro</span></div>
    <div class="nav-links">
        <a href="#motor" class="nav-link">🔧 Motor</a>
        <a href="#suspension" class="nav-link">⚙️ Suspensión</a>
        <a href="#frenos" class="nav-link">🛑 Frenos</a>
        <a href="#electrico" class="nav-link">🔌 Eléctrico</a>
        <a href="#carroceria" class="nav-link">🚗 Carrocería</a>
        <a href="/catalogodigsistema/admin/login.php" class="btn-mi-cuenta"><i class="fas fa-user-shield"></i> Mi cuenta</a>
    </div>
    <div class="cart-icon" id="cartIconBtn">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" id="cartCounter">0</span>
    </div>
</nav>

<div class="menu-overlay" id="menuOverlay"></div>
<div class="mobile-menu" id="mobileMenu">
    <button class="close-menu" id="closeMenuBtn"><i class="fas fa-times"></i></button>
    <a href="#motor" class="mobile-nav-link">🔧 Motor</a>
    <a href="#suspension" class="mobile-nav-link">⚙️ Suspensión</a>
    <a href="#frenos" class="mobile-nav-link">🛑 Frenos</a>
    <a href="#electrico" class="mobile-nav-link">🔌 Eléctrico</a>
    <a href="#carroceria" class="mobile-nav-link">🚗 Carrocería</a>
    <a href="/catalogodigsistema/admin/login.php" class="btn-mi-cuenta" style="margin-top:.5rem;justify-content:center;"><i class="fas fa-user-shield"></i> Mi cuenta</a>
</div>

<div class="container">
    <div style="text-align:center; margin-bottom:1rem;">
        <p style="font-weight:500;">🔩 Refacciones originales · Calidad garantizada 🔧</p>
        <div style="background:#eef3f2; display:inline-block; padding:0.2rem 1.2rem; border-radius:40px; font-size:0.8rem;"><i class="fas fa-cart-plus"></i> Agrega al carrito y elige método de pago</div>
    </div>

    <div id="motor" class="category-section"><div class="category-title"><i class="fas fa-oil-can"></i><h2>Motor</h2></div><div class="products-grid" id="motor-grid"></div></div>
    <div id="suspension" class="category-section"><div class="category-title"><i class="fas fa-car-side"></i><h2>Suspensión</h2></div><div class="products-grid" id="suspension-grid"></div></div>
    <div id="frenos" class="category-section"><div class="category-title"><i class="fas fa-stop-circle"></i><h2>Frenos</h2></div><div class="products-grid" id="frenos-grid"></div></div>
    <div id="electrico" class="category-section"><div class="category-title"><i class="fas fa-car-battery"></i><h2>Eléctrico</h2></div><div class="products-grid" id="electrico-grid"></div></div>
    <div id="carroceria" class="category-section"><div class="category-title"><i class="fas fa-truck-pickup"></i><h2>Carrocería</h2></div><div class="products-grid" id="carroceria-grid"></div></div>

    <div class="share-bar">
        <button class="btn-wa-share" id="shareCatalogBtn"><i class="fab fa-whatsapp"></i> Estoy interesado en sus refacciones</button>
    </div>
    <footer>AutoRepuestos Pro · Carrito inteligente · Envíos a todo México</footer>
</div>

<div class="whatsapp-float" id="whatsappFloatBtn"><i class="fab fa-whatsapp"></i></div>

<!-- Carrito drawer -->
<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-header"><h3><i class="fas fa-bag-shopping"></i> Mi Pedido</h3><button class="close-cart" id="closeCartBtn">&times;</button></div>
    <div class="cart-items" id="cartItemsList"><div style="text-align:center; padding:2rem; color:#aaa;">Carrito vacío</div></div>
    <div class="cart-footer">
        <div class="cart-total"><span>Total:</span><span id="cartTotalPrice">$0</span></div>
        <button class="btn-cart-action" id="checkoutWhatsAppBtn" style="background:#25D366;"><i class="fab fa-whatsapp"></i> Enviar pedido por WhatsApp</button>
        <button class="btn-cart-action" id="checkoutTransferBtn" style="background:#2c3e50;"><i class="fas fa-university"></i> Transferencia bancaria</button>
        <button class="btn-cart-action" id="checkoutCardBtn" style="background:#1e3799;"><i class="fas fa-credit-card"></i> Pagar con tarjeta</button>
        <button class="btn-cart-action btn-clear-cart" id="clearCartBtn"><i class="fas fa-trash-alt"></i> Vaciar carrito</button>
    </div>
</div>

<script>
    // PRODUCTOS: 20 refacciones de auto
    const products = [
        { id: 1, name: "Bujía Iridium", desc: "Juego de 4 bujías, alto rendimiento", price: 450, icon: "fas fa-plug", category: "motor" },
        { id: 2, name: "Filtro de Aceite", desc: "Filtro de alta capacidad", price: 180, icon: "fas fa-filter", category: "motor" },
        { id: 3, name: "Correa de Distribución", desc: "Resistente a altas temperaturas", price: 620, icon: "fas fa-cog", category: "motor" },
        { id: 4, name: "Bomba de Agua", desc: "Flujo óptimo, garantía 1 año", price: 890, icon: "fas fa-tint", category: "motor" },
        { id: 5, name: "Amortiguador Delantero", desc: "Par de amortiguadores hidráulicos", price: 1250, icon: "fas fa-shock-absorber", category: "suspension" },
        { id: 6, name: "Espiral de Resorte", desc: "Resorte de acero reforzado", price: 430, icon: "fas fa-spring", category: "suspension" },
        { id: 7, name: "Rótula de Dirección", desc: "Precisión en la conducción", price: 320, icon: "fas fa-steering-wheel", category: "suspension" },
        { id: 8, name: "Barra Estabilizadora", desc: "Reduce balanceo, incluye bujes", price: 580, icon: "fas fa-charging-station", category: "suspension" },
        { id: 9, name: "Pastillas de Freno", desc: "Juego de 4 pastillas cerámicas", price: 720, icon: "fas fa-brake-warning", category: "frenos" },
        { id: 10, name: "Disco de Freno", desc: "Disco ventilado de hierro", price: 950, icon: "fas fa-circle", category: "frenos" },
        { id: 11, name: "Líquido de Frenos DOT4", desc: "Botella 500ml, alto punto ebullición", price: 110, icon: "fas fa-fill-drip", category: "frenos" },
        { id: 12, name: "Cilindro Maestro", desc: "Repuesto original para frenos", price: 680, icon: "fas fa-cogs", category: "frenos" },
        { id: 13, name: "Batería 12V 60Ah", desc: "Libre mantenimiento", price: 1890, icon: "fas fa-car-battery", category: "electrico" },
        { id: 14, name: "Alternador", desc: "140A, compatible múltiples marcas", price: 2250, icon: "fas fa-bolt", category: "electrico" },
        { id: 15, name: "Sensor de Oxígeno", desc: "Mejora eficiencia combustible", price: 860, icon: "fas fa-microchip", category: "electrico" },
        { id: 16, name: "Faros LED", desc: "Par de luces delanteras, 6000K", price: 990, icon: "fas fa-lightbulb", category: "electrico" },
        { id: 17, name: "Espejo Lateral", desc: "Completo con ajuste eléctrico", price: 550, icon: "fas fa-eye", category: "carroceria" },
        { id: 18, name: "Manija de Puerta", desc: "Cromada, izquierda o derecha", price: 210, icon: "fas fa-hand-paper", category: "carroceria" },
        { id: 19, name: "Limpiaparabrisas", desc: "Juego de 2, universal 22\"", price: 270, icon: "fas fa-tachometer-alt", category: "carroceria" },
        { id: 20, name: "Defensa Delantera", desc: "Plástico reforzado, pintable", price: 1670, icon: "fas fa-truck", category: "carroceria" }
    ];

    let cart = [];
    function saveCart() { localStorage.setItem('autorepuestos_cart', JSON.stringify(cart)); }
    function loadCart() { const stored = localStorage.getItem('autorepuestos_cart'); if (stored) cart = JSON.parse(stored); else cart = []; updateCartUI(); }
    function updateCartUI() {
        const totalItems = cart.reduce((sum, i) => sum + i.quantity, 0);
        document.getElementById('cartCounter').innerText = totalItems;
        renderCartDrawer();
        updateCartTotal();
    }
    function getCartTotal() { return cart.reduce((sum, i) => sum + (i.price * i.quantity), 0); }
    function updateCartTotal() { document.getElementById('cartTotalPrice').innerText = `$${getCartTotal().toFixed(2)}`; }
    function renderCartDrawer() {
        const container = document.getElementById('cartItemsList');
        if (!container) return;
        if (cart.length === 0) { container.innerHTML = '<div style="text-align:center; padding:2rem; color:#aaa;"><i class="fas fa-cart-shopping"></i> Tu carrito está vacío</div>'; return; }
        container.innerHTML = '';
        cart.forEach(item => {
            const div = document.createElement('div'); div.className = 'cart-item';
            div.innerHTML = `
                <div class="cart-item-info"><h4>${item.name}</h4><p>$${item.price} c/u</p></div>
                <div class="cart-item-qty">
                    <button class="cart-qty-dec" data-id="${item.id}">−</button>
                    <span>${item.quantity}</span>
                    <button class="cart-qty-inc" data-id="${item.id}">+</button>
                    <button class="cart-item-remove" data-id="${item.id}"><i class="fas fa-trash-can"></i></button>
                </div>
            `;
            container.appendChild(div);
        });
        document.querySelectorAll('.cart-qty-dec').forEach(btn => btn.addEventListener('click', (e) => { changeQuantity(parseInt(btn.dataset.id), -1); }));
        document.querySelectorAll('.cart-qty-inc').forEach(btn => btn.addEventListener('click', (e) => { changeQuantity(parseInt(btn.dataset.id), 1); }));
        document.querySelectorAll('.cart-item-remove').forEach(btn => btn.addEventListener('click', async (e) => {
            const id = parseInt(btn.dataset.id);
            const result = await Swal.fire({ title: '¿Eliminar producto?', text: 'Se eliminará del carrito', icon: 'question', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar' });
            if (result.isConfirmed) { cart = cart.filter(i => i.id !== id); saveCart(); updateCartUI(); Swal.fire('Eliminado', '', 'success'); }
        }));
    }
    function changeQuantity(id, delta) {
        const index = cart.findIndex(i => i.id === id);
        if (index !== -1) {
            const newQty = cart[index].quantity + delta;
            if (newQty <= 0) cart.splice(index, 1);
            else cart[index].quantity = newQty;
            saveCart(); updateCartUI();
        }
    }
    function addToCart(product) {
        const existing = cart.find(i => i.id === product.id);
        if (existing) { existing.quantity += 1; Swal.fire({ icon: 'success', title: 'Actualizado', text: `${product.name} ahora tiene ${existing.quantity} unidades`, timer: 1500, showConfirmButton: false }); }
        else { cart.push({ id: product.id, name: product.name, price: product.price, quantity: 1 }); Swal.fire({ icon: 'success', title: '¡Agregado!', text: `${product.name} añadido al carrito`, timer: 1400, showConfirmButton: false, toast: true, position: 'top-end' }); }
        saveCart(); updateCartUI();
    }
    function renderAllProducts() {
        const grids = { motor: document.getElementById('motor-grid'), suspension: document.getElementById('suspension-grid'), frenos: document.getElementById('frenos-grid'), electrico: document.getElementById('electrico-grid'), carroceria: document.getElementById('carroceria-grid') };
        for (let g in grids) if (grids[g]) grids[g].innerHTML = '';
        products.forEach(prod => {
            const card = document.createElement('div'); card.className = 'product-card';
            card.innerHTML = `<div class="product-img"><i class="${prod.icon}"></i></div><div class="product-info"><div class="product-title">${prod.name}</div><div class="product-desc">${prod.desc}</div><div class="price-row"><span class="product-price">$${prod.price}</span><button class="btn-add" data-id="${prod.id}"><i class="fas fa-cart-plus"></i> Añadir</button></div></div>`;
            grids[prod.category].appendChild(card);
        });
        document.querySelectorAll('.btn-add').forEach(btn => btn.addEventListener('click', (e) => { const id = parseInt(btn.dataset.id); const product = products.find(p => p.id === id); if (product) addToCart(product); }));
    }

    // Mensaje catálogo completo
    function buildFullCatalogMessage() {
        let msg = "✅ *AUTOREPUESTOS PRO* ✅\n🔩 *20 refacciones de auto* categorizadas\n🛒 Calidad y precio garantizado\n\n";
        const cats = { motor: "🔧 MOTOR", suspension: "⚙️ SUSPENSIÓN", frenos: "🛑 FRENOS", electrico: "🔌 ELÉCTRICO", carroceria: "🚗 CARROCERÍA" };
        for (let cat of Object.keys(cats)) {
            msg += `📌 *${cats[cat]}*\n`;
            products.filter(p => p.category === cat).forEach(p => { msg += `   ✅ *${p.name}* — $${p.price}\n`; });
            msg += "\n";
        }
        msg += "💳 Aceptamos transferencia y tarjeta.\n📲 Para pedidos, usa el carrito o responde a este mensaje.\n✨ ¡Gracias por confiar en nosotros!";
        return msg;
    }

    // ================= MODAL PROFESIONAL CON BOTÓN DINÁMICO SEGÚN MÉTODO DE PAGO =================
    async function showProfessionalOrderModal(paymentMethod) {
        if (cart.length === 0) {
            Swal.fire('Carrito vacío', 'Agrega productos antes de continuar', 'warning');
            return null;
        }
        let tempCart = cart.map(item => ({ ...item }));

        function renderEditableCart(containerId, tempCart, updateTotalCallback) {
            const container = document.getElementById(containerId);
            if (!container) return;
            if (tempCart.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:1.5rem; color:#aaa;">No hay productos en el pedido</div>';
                if (updateTotalCallback) updateTotalCallback(0);
                return;
            }
            let html = `<div style="overflow-x: auto; border-radius: 20px;">
                        <table style="width:100%; border-collapse: collapse; font-size: 0.85rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e2ecea;">
                                    <th style="text-align:left; padding:10px 0;">Producto</th>
                                    <th style="text-align:center; padding:10px 8px;">Cantidad</th>
                                    <th style="text-align:right; padding:10px 8px;">Precio</th>
                                    <th style="text-align:right; padding:10px 0;">Subtotal</th>
                                    <th style="width:40px;"></th>
                                 </tr>
                            </thead>
                            <tbody>`;
            tempCart.forEach((item, idx) => {
                const subtotal = item.price * item.quantity;
                html += `<tr style="border-bottom:1px solid #f0f3f5;">
                            <td style="padding:12px 0;"><strong>${escapeHtml(item.name)}</strong></td>
                            <td style="text-align:center; padding:8px;">
                                <div style="display:flex; justify-content:center; gap:8px;">
                                    <button type="button" class="qty-dec" data-idx="${idx}" style="background:#eef3f2; border:none; width:28px; height:28px; border-radius:30px; cursor:pointer; font-weight:bold;">−</button>
                                    <span style="min-width:32px; text-align:center; font-weight:600;">${item.quantity}</span>
                                    <button type="button" class="qty-inc" data-idx="${idx}" style="background:#eef3f2; border:none; width:28px; height:28px; border-radius:30px; cursor:pointer; font-weight:bold;">+</button>
                                </div>
                             </td>
                            <td style="text-align:right; padding:8px;">$${item.price.toFixed(2)}</td>
                            <td style="text-align:right; padding:8px 0;">$${subtotal.toFixed(2)}</td>
                            <td style="text-align:center;"><button type="button" class="remove-item" data-idx="${idx}" style="background:none; border:none; color:#e07c7c; cursor:pointer; font-size:1rem;"><i class="fas fa-trash-alt"></i></button></td>
                         </tr>`;
            });
            html += `</tbody>
                     </table>
                </div>`;
            container.innerHTML = html;

            container.querySelectorAll('.qty-dec').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.idx);
                    if (tempCart[idx].quantity > 1) tempCart[idx].quantity--;
                    else tempCart.splice(idx, 1);
                    renderEditableCart(containerId, tempCart, updateTotalCallback);
                    if (updateTotalCallback) updateTotalCallback(calculateTempTotal(tempCart));
                });
            });
            container.querySelectorAll('.qty-inc').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.idx);
                    tempCart[idx].quantity++;
                    renderEditableCart(containerId, tempCart, updateTotalCallback);
                    if (updateTotalCallback) updateTotalCallback(calculateTempTotal(tempCart));
                });
            });
            container.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.idx);
                    tempCart.splice(idx, 1);
                    renderEditableCart(containerId, tempCart, updateTotalCallback);
                    if (updateTotalCallback) updateTotalCallback(calculateTempTotal(tempCart));
                });
            });
        }

        function calculateTempTotal(tc) { return tc.reduce((s,i) => s + i.price * i.quantity, 0); }

        // Configurar título y texto del botón según el método
        let titleText = "📋 Resumen del pedido";
        let confirmButtonText = "";
        switch(paymentMethod) {
            case 'whatsapp':
                confirmButtonText = "✅ Realizar pedido por WhatsApp";
                break;
            case 'transfer':
                confirmButtonText = "💰 Realizar pedido por Transferencia";
                break;
            case 'card':
                confirmButtonText = "💳 Pagar con tarjeta";
                break;
            default:
                confirmButtonText = "Continuar";
        }

        const modalHtml = `
            <div style="max-height: 65vh; overflow-y: auto; padding-right: 8px;">
                <!-- Sección del carrito editable -->
                <div style="background: #ffffff; border-radius: 24px; padding: 1rem; margin-bottom: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.04); border: 1px solid #edf2f4;">
                    <h3 style="font-size: 1.2rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; color: #C0392B;">
                        <i class="fas fa-edit"></i> Ajusta tu pedido
                    </h3>
                    <div id="editableCartContainer"></div>
                    <div style="text-align: right; margin-top: 1rem; padding-top: 0.8rem; border-top: 1px solid #eef2f5; font-weight: 800; font-size: 1.2rem;">
                        <span>Total: </span><span id="modalTotalAmount" style="color:#C0392B;">$0</span>
                    </div>
                </div>

                <!-- Datos de contacto con labels alineados a la izquierda -->
                <div style="background: #ffffff; border-radius: 24px; padding: 1rem; border: 1px solid #edf2f4;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; color: #2c3e50;">
                        <i class="fas fa-address-card"></i> Información de contacto <span style="font-size:0.7rem; font-weight:normal; color:#C0392B;">* Todos los campos son obligatorios</span>
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.85rem; color: #2c3e50; text-align: left;"><i class="fas fa-user"></i> Nombre completo <span style="color:#C0392B">*</span></label>
                            <input type="text" id="swal-name" class="swal2-input" placeholder="Ej: Juan Pérez" style="width:100%; padding: 12px; border-radius: 16px; border: 1.5px solid #dcdfe3; font-size:0.9rem;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.85rem; color: #2c3e50; text-align: left;"><i class="fas fa-envelope"></i> Correo electrónico <span style="color:#C0392B">*</span></label>
                            <input type="email" id="swal-email" class="swal2-input" placeholder="cliente@ejemplo.com" style="width:100%; padding: 12px; border-radius: 16px; border: 1.5px solid #dcdfe3;">
                        </div>
                        <hr style="margin: 0.2rem 0;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.85rem; color: #2c3e50; text-align: left;"><i class="fas fa-location-dot"></i> Calle y número <span style="color:#C0392B">*</span></label>
                            <input type="text" id="swal-street" class="swal2-input" placeholder="Av. Reforma 123" style="width:100%; padding: 12px; border-radius: 16px; border: 1.5px solid #dcdfe3;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.85rem; color: #2c3e50; text-align: left;"><i class="fas fa-city"></i> Ciudad <span style="color:#C0392B">*</span></label>
                                <input type="text" id="swal-city" placeholder="Ciudad de México" style="width:100%; padding: 12px; border-radius: 16px; border: 1.5px solid #dcdfe3;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.85rem; color: #2c3e50; text-align: left;"><i class="fas fa-mail-bulk"></i> Código postal <span style="color:#C0392B">*</span></label>
                                <input type="text" id="swal-zip" placeholder="12345" style="width:100%; padding: 12px; border-radius: 16px; border: 1.5px solid #dcdfe3;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const result = await Swal.fire({
            title: `<span style="font-size:1.5rem;">${titleText}</span>`,
            html: modalHtml,
            width: '900px',
            showCancelButton: true,
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'btn-confirm-pro',
                cancelButton: 'btn-cancel-pro'
            },
            didOpen: () => {
                const style = document.createElement('style');
                style.textContent = `
                    .btn-confirm-pro { background-color: #E67E22 !important; color: white !important; border-radius: 60px !important; padding: 0.7rem 1.5rem !important; font-weight: 600 !important; border: none !important; }
                    .btn-cancel-pro { background-color: #eef2f5 !important; color: #2c3e50 !important; border-radius: 60px !important; padding: 0.7rem 1.5rem !important; }
                    .swal2-input { margin: 0 !important; font-family: 'Inter', sans-serif; }
                `;
                document.head.appendChild(style);
                renderEditableCart('editableCartContainer', tempCart, (total) => {
                    document.getElementById('modalTotalAmount').innerText = `$${total.toFixed(2)}`;
                });
                document.getElementById('modalTotalAmount').innerText = `$${calculateTempTotal(tempCart).toFixed(2)}`;
            },
            preConfirm: () => {
                const name   = document.getElementById('swal-name')?.value.trim() || '';
                const email  = document.getElementById('swal-email')?.value.trim() || '';
                const street = document.getElementById('swal-street')?.value.trim() || '';
                const city   = document.getElementById('swal-city')?.value.trim() || '';
                const zip    = document.getElementById('swal-zip')?.value.trim() || '';

                const missing = [];
                if (!name)   missing.push('Nombre completo');
                if (!email)  missing.push('Correo electrónico');
                if (!street) missing.push('Calle y número');
                if (!city)   missing.push('Ciudad');
                if (!zip)    missing.push('Código postal');

                if (missing.length) {
                    Swal.showValidationMessage(
                        `<i class="fas fa-exclamation-circle" style="color:#C0392B"></i> Faltan llenar: <strong>${missing.join(', ')}</strong>`
                    );
                    return false;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-circle" style="color:#C0392B"></i> Ingresa un correo electrónico válido');
                    return false;
                }
                if (tempCart.length === 0) {
                    Swal.showValidationMessage('El pedido está vacío, agrega al menos un producto');
                    return false;
                }
                return { name, email, street, city, zip, finalCart: tempCart };
            }
        });

        if (!result.isConfirmed) return null;
        return result.value;
    }

    // 1. WhatsApp
    async function sendCartOrderWhatsApp() {
        const data = await showProfessionalOrderModal('whatsapp');
        if (!data) return;
        const { name, email, street, city, zip, finalCart } = data;
        if (finalCart.length === 0) { Swal.fire('Pedido vacío', 'No hay productos', 'warning'); return; }

        // Register order in backend
        const orderResult = await createOrderInSystem(
            finalCart,
            { name, email, street, city, zip },
            'whatsapp'
        );

        let orderMsg = "🛒 *PEDIDO AUTOREPUESTOS PRO* 🛒\n";
        if (orderResult.success) orderMsg += `📋 *Folio: ${orderResult.order_id}*\n`;
        orderMsg += "✅ *Productos:*\n";
        finalCart.forEach(item => { orderMsg += `   ✅ ${item.name} x${item.quantity} → $${(item.price * item.quantity).toFixed(2)}\n`; });
        const total = finalCart.reduce((s,i) => s + i.price * i.quantity, 0);
        orderMsg += `\n📝 *TOTAL: $${total.toFixed(2)} MXN*\n`;
        let contact = "";
        if (name) contact += `👤 Nombre: ${name}\n`;
        if (email) contact += `📧 Email: ${email}\n`;
        if (street || city || zip) {
            let addr = street || '';
            if (city) addr += (addr ? ", " : "") + city;
            if (zip) addr += (addr ? " - CP: " : "CP: ") + zip;
            if (addr) contact += `📍 Dirección: ${addr}\n`;
        }
        if (contact) orderMsg += `\n📋 *Datos de contacto:*\n${contact}`;
        orderMsg += `\n📲 Enviado desde catálogo web. ¡Gracias!`;
        sendWhatsAppMessage(orderMsg);
        cart = []; saveCart(); updateCartUI();
        showAccessInfo(orderResult);
    }

    // 2. Transferencia
    async function sendTransferOrder() {
        const data = await showProfessionalOrderModal('transfer');
        if (!data) return;
        const { name, email, street, city, zip, finalCart } = data;
        if (finalCart.length === 0) return;
        const total = finalCart.reduce((s,i) => s + i.price * i.quantity, 0);

        // Register order in backend
        const orderResult = await createOrderInSystem(
            finalCart,
            { name, email, street, city, zip },
            'transfer'
        );

        await Swal.fire({
            title: '💰 Transferencia bancaria',
            html: `<div style="text-align:left;">
                        ${orderResult.success ? `<div style="background:#f0fdf4;border:1px solid #6EE7B7;padding:.6rem 1rem;border-radius:12px;margin-bottom:.8rem;font-size:.85rem"><strong>Folio:</strong> ${escapeHtml(orderResult.order_id)}</div>` : ''}
                        <div style="background:#f8f9fa; padding:1rem; border-radius:20px;">
                            <p><strong><i class="fas fa-university"></i> Banco:</strong> BBVA México</p>
                            <p><strong>CLABE:</strong> 012 3456 789012345678</p>
                            <p><strong>Cuenta:</strong> 1234567890</p>
                            <p><strong>Beneficiario:</strong> AutoRepuestos Pro S.A. de C.V.</p>
                        </div>
                        <hr>
                        <p><strong>Total a pagar:</strong> <span style="font-size:1.2rem; color:#C0392B;">$${total.toFixed(2)} MXN</span></p>
                        <p>✅ Realiza la transferencia y envía el comprobante por WhatsApp al 4771181285</p>
                        <p>📌 Tu pedido se procesará al confirmar el pago.</p>
                    </div>`,
            icon: 'info',
            confirmButtonText: 'Entendido, voy a transferir',
            confirmButtonColor: '#2c3e50'
        });
        cart = []; saveCart(); updateCartUI();
        showAccessInfo(orderResult);
    }

    // 3. Pago con tarjeta (simulado)
    async function payWithCard() {
        const data = await showProfessionalOrderModal('card');
        if (!data) return;
        const { name, email, street, city, zip, finalCart } = data;
        if (finalCart.length === 0) return;
        const total = finalCart.reduce((s,i) => s + i.price * i.quantity, 0);
        const { value: cardData } = await Swal.fire({
            title: '💳 Pago con tarjeta',
            html: `
                <div style="text-align:left;">
                    <div style="background:#f8f9fa; padding:1rem; border-radius:20px; margin-bottom:1rem;">
                        <p><strong>Total a pagar:</strong> <span style="font-size:1.2rem; color:#C0392B;">$${total.toFixed(2)} MXN</span></p>
                    </div>
                    <div>
                        <label style="display:block; text-align:left; font-weight:600; margin-bottom:5px;">Número de tarjeta</label>
                        <input id="cardNumber" class="swal2-input" placeholder="4242 4242 4242 4242" maxlength="19" style="width:100%;">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px;">
                        <div>
                            <label style="display:block; text-align:left; font-weight:600; margin-bottom:5px;">Fecha expiración</label>
                            <input id="cardExpiry" class="swal2-input" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div>
                            <label style="display:block; text-align:left; font-weight:600; margin-bottom:5px;">CVV</label>
                            <input id="cardCvv" class="swal2-input" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Pagar ahora',
            preConfirm: () => {
                const number = document.getElementById('cardNumber').value.trim();
                const expiry = document.getElementById('cardExpiry').value.trim();
                const cvv = document.getElementById('cardCvv').value.trim();
                if (!number || !expiry || !cvv) {
                    Swal.showValidationMessage('Completa todos los campos (simulación)');
                    return false;
                }
                return { number, expiry, cvv };
            }
        });
        if (cardData) {
            // Register order in backend (auto approved as "paid")
            const orderResult = await createOrderInSystem(
                finalCart,
                { name, email, street, city, zip },
                'card'
            );
            await Swal.fire({ title: 'Procesando pago...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }, timer: 1500 });
            cart = []; saveCart(); updateCartUI();
            showAccessInfo(orderResult);
        }
    }

    function sendWhatsAppMessage(text, number = "524771181285") {
        const encoded = encodeURIComponent(text);
        window.open(`https://wa.me/${number}?text=${encoded}`, '_blank');
    }
    function escapeHtml(str) { return str.replace(/[&<>]/g, m => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;' }[m])); }

    /* ── Backend: registrar pedido ── */
    async function createOrderInSystem(items, customer, paymentMethod) {
        try {
            const res  = await fetch('/catalogodigsistema/api/create_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ items, customer, payment_method: paymentMethod })
            });
            return await res.json();
        } catch (e) {
            console.warn('No se pudo registrar el pedido en el sistema:', e);
            return { success: false };
        }
    }

    function showAccessInfo(orderResult) {
        if (!orderResult || !orderResult.success) return;
        Swal.fire({
            title: '🔐 Acceso a tu pedido',
            html: `<div style="text-align:left;background:#f0fdf4;border:1px solid #6EE7B7;padding:1rem;border-radius:16px;font-size:.9rem;line-height:1.8">
                    <div style="font-weight:700;color:#047857;margin-bottom:.4rem"><i class="fas fa-receipt"></i> Pedido registrado</div>
                    <div>🔖 <strong>Folio:</strong> ${escapeHtml(orderResult.order_id)}</div>
                    <div>👤 <strong>Usuario:</strong> ${escapeHtml(orderResult.access?.username || '')}</div>
                    <div>🔑 <strong>Contraseña:</strong> ${escapeHtml(orderResult.access?.password || '')}</div>
                    <div style="margin-top:.6rem;font-size:.78rem;color:#6c8695">
                        Con estos datos puedes consultar el estatus de tu pedido en:<br>
                        <a href="/catalogodigsistema/client/login.php" target="_blank" style="color:#059669;font-weight:600">autorepuestospro.mx/client/</a>
                    </div>
                  </div>`,
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#E67E22',
        });
    }

    // Controles UI
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const menuOverlay = document.getElementById('menuOverlay');
    const closeMenuBtn = document.getElementById('closeMenuBtn');
    function openMobileMenu() { mobileMenu.classList.add('open'); menuOverlay.classList.add('active'); }
    function closeMobileMenu() { mobileMenu.classList.remove('open'); menuOverlay.classList.remove('active'); }
    hamburgerBtn.addEventListener('click', openMobileMenu);
    closeMenuBtn.addEventListener('click', closeMobileMenu);
    menuOverlay.addEventListener('click', closeMobileMenu);
    document.querySelectorAll('.mobile-nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            closeMobileMenu();
            const targetId = link.getAttribute('href').substring(1);
            const element = document.getElementById(targetId);
            if (element) element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    function openCart() { document.getElementById('cartOverlay').classList.add('active'); document.getElementById('cartDrawer').classList.add('open'); renderCartDrawer(); }
    function closeCart() { document.getElementById('cartOverlay').classList.remove('active'); document.getElementById('cartDrawer').classList.remove('open'); }

    document.addEventListener('DOMContentLoaded', () => {
        renderAllProducts(); loadCart();
        document.getElementById('cartIconBtn').addEventListener('click', openCart);
        document.getElementById('closeCartBtn').addEventListener('click', closeCart);
        document.getElementById('cartOverlay').addEventListener('click', closeCart);
        document.getElementById('checkoutWhatsAppBtn').addEventListener('click', () => { closeCart(); sendCartOrderWhatsApp(); });
        document.getElementById('checkoutTransferBtn').addEventListener('click', () => { closeCart(); sendTransferOrder(); });
        document.getElementById('checkoutCardBtn').addEventListener('click', () => { closeCart(); payWithCard(); });
        document.getElementById('clearCartBtn').addEventListener('click', async () => {
            const result = await Swal.fire({ title: 'Vaciar carrito', text: '¿Estás seguro?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, vaciar' });
            if (result.isConfirmed) { cart = []; saveCart(); updateCartUI(); Swal.fire('Carrito vacío', '', 'info'); if (document.getElementById('cartDrawer').classList.contains('open')) renderCartDrawer(); }
        });
        document.getElementById('shareCatalogBtn').addEventListener('click', () => { sendWhatsAppMessage(buildFullCatalogMessage()); });
        document.getElementById('whatsappFloatBtn').addEventListener('click', () => { sendWhatsAppMessage(buildFullCatalogMessage()); });
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => { e.preventDefault(); const targetId = link.getAttribute('href').substring(1); const element = document.getElementById(targetId); if (element) element.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
        });
    });
</script>
</body>
</html>