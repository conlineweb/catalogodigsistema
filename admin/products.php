<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$products = productsForCatalog();
usort($products, function($a, $b) {
    $categoryCompare = strcmp($a['category'] ?? '', $b['category'] ?? '');
    return $categoryCompare !== 0 ? $categoryCompare : intval($a['id'] ?? 0) <=> intval($b['id'] ?? 0);
});
$productsJson = json_encode(array_values($products), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$categoryLabels = [
    'motor' => 'Motor',
    'suspension' => 'Suspensión',
    'frenos' => 'Frenos',
    'electrico' => 'Eléctrico',
    'carroceria' => 'Carrocería',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catálogo · AutoRepuestos Pro</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root{--orange:#E67E22;--red:#C0392B;--dark:#1a2c3e;--muted:#6c8695}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#f0f4f8;color:#1a2c3e;display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--dark);position:fixed;top:0;left:0;height:100vh;display:flex;flex-direction:column;z-index:50}
.sidebar-brand{padding:1.4rem;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
.sb-icon{background:linear-gradient(135deg,#C0392B,#E67E22);border-radius:12px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sb-icon i{color:#fff;font-size:1.1rem}
.sb-title{font-weight:800;font-size:.9rem;color:#fff;line-height:1.2}
.sb-sub{color:#7a9ead;font-size:.68rem}
.sidebar-nav{flex:1;padding:1rem .8rem;overflow-y:auto}
.nav-item{display:flex;align-items:center;gap:12px;padding:.7rem 1rem;border-radius:14px;color:#7a9ead;text-decoration:none;font-weight:600;font-size:.86rem;transition:.2s;margin-bottom:.15rem}
.nav-item:hover,.nav-item.active{background:rgba(230,126,34,.15);color:#E67E22}
.nav-item i{width:18px;text-align:center}
.sidebar-footer{padding:.8rem;border-top:1px solid rgba(255,255,255,.08)}
.nav-logout{color:#e87f7f!important}
.nav-logout:hover{background:rgba(192,57,43,.15)!important;color:#e87f7f!important}
.main{margin-left:240px;flex:1;display:flex;flex-direction:column}
.topbar{background:#fff;padding:1rem 2rem;border-bottom:1px solid #e2ecea;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40;flex-wrap:wrap;gap:.8rem}
.topbar h2{font-size:1.2rem;font-weight:800}
.user-chip{background:#f0f4f8;padding:.4rem 1rem;border-radius:40px;font-size:.82rem;font-weight:600;color:#2c4b57;display:flex;align-items:center;gap:6px}
.content{padding:1.8rem 2rem;flex:1}
.hero{background:linear-gradient(135deg,#1a2c3e,#29495a);color:#fff;border-radius:26px;padding:1.7rem;margin-bottom:1.4rem;display:flex;justify-content:space-between;gap:1rem;align-items:center;box-shadow:0 14px 32px rgba(26,44,62,.15)}
.hero h1{font-size:1.45rem;margin-bottom:.35rem}
.hero p{color:#c8d7df;font-size:.9rem;max-width:720px;line-height:1.5}
.hero-stat{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);border-radius:20px;padding:1rem 1.2rem;min-width:140px;text-align:center}
.hero-stat strong{font-size:2rem;display:block}
.table-card{background:#fff;border-radius:22px;padding:1.4rem;box-shadow:0 2px 16px rgba(0,0,0,.05)}
.table-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}
.table-head h3{font-size:.95rem;display:flex;align-items:center;gap:8px}
.table-head h3 i{color:var(--orange)}
.hint{font-size:.78rem;color:var(--muted)}
#productsTable_filter input{border:2px solid #e2ecea!important;border-radius:30px!important;padding:.42rem .9rem!important;font-family:'Inter',sans-serif!important;font-size:.83rem!important;outline:none!important}
#productsTable_filter input:focus{border-color:var(--orange)!important}
#productsTable_length select{border:2px solid #e2ecea!important;border-radius:12px!important;padding:.3rem .6rem!important;font-family:'Inter',sans-serif!important}
#productsTable thead th{background:#f8fafc;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#6c8695;border-bottom:2px solid #f0f4f8!important;padding:.8rem 1rem!important}
#productsTable tbody td{font-size:.86rem;padding:.85rem 1rem!important;border-bottom:1px solid #f7f9fb!important;vertical-align:middle!important}
#productsTable tbody tr:hover td{background:#fffaf7!important}
.img-preview{width:64px;height:54px;border-radius:16px;background:linear-gradient(145deg,#fff7ed,#fee9e6);border:1px solid #f4dfd6;display:flex;align-items:center;justify-content:center;overflow:hidden;color:var(--orange);font-size:1.4rem}
.img-preview img{width:100%;height:100%;object-fit:cover}
.category-badge{display:inline-flex;padding:4px 10px;border-radius:999px;background:#eef3f2;color:#2c4b57;font-size:.74rem;font-weight:700}
.edit-input{width:100%;min-width:180px;border:2px solid #e2ecea;border-radius:14px;padding:.55rem .7rem;font-family:'Inter',sans-serif;font-weight:600;color:#1a2c3e;outline:none}
.edit-input:focus{border-color:var(--orange);box-shadow:0 0 0 3px rgba(230,126,34,.12)}
.price-input{min-width:110px}
.file-input{font-size:.76rem;max-width:190px}
.btn-save{border:none;background:linear-gradient(135deg,#C0392B,#E67E22);color:#fff;border-radius:30px;padding:.55rem 1rem;font-weight:800;font-family:'Inter',sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 8px 18px rgba(230,126,34,.22)}
.btn-save:hover{filter:brightness(.96);transform:translateY(-1px)}
.btn-save:disabled{opacity:.6;cursor:not-allowed;transform:none}
@media(max-width:768px){
  .sidebar{width:58px}.sb-title,.sb-sub,.nav-item span{display:none}.main{margin-left:58px}.content{padding:1rem}.hero{align-items:flex-start;flex-direction:column}.topbar{padding:1rem}
}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sb-icon"><i class="fas fa-car"></i></div>
    <div><div class="sb-title">AutoRepuestos<br>Pro</div><div class="sb-sub">Panel Admin</div></div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="/catalogodigsistema/admin/index.php"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
    <a class="nav-item active" href="/catalogodigsistema/admin/products.php"><i class="fas fa-tags"></i><span>Catálogo</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/orders.php"><i class="fas fa-box-open"></i><span>Pedidos</span></a>
    <a class="nav-item" href="/catalogodigsistema/index.php" target="_blank"><i class="fas fa-store"></i><span>Ver tienda</span></a>
  </nav>
  <div class="sidebar-footer">
    <a class="nav-item nav-logout" href="/catalogodigsistema/api/logout.php?role=admin"><i class="fas fa-sign-out-alt"></i><span>Cerrar sesión</span></a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <h2><i class="fas fa-tags" style="color:var(--orange);margin-right:.5rem"></i>Gestión del Catálogo</h2>
    <div class="user-chip"><i class="fas fa-user-shield"></i><?= htmlspecialchars($_SESSION['name']) ?></div>
  </div>

  <div class="content">
    <section class="hero">
      <div>
        <h1>Administración visual de productos</h1>
        <p>Actualiza nombre, precio e imagen desde una tabla ordenable. Los cambios se reflejan automáticamente en la tienda pública.</p>
      </div>
      <div class="hero-stat"><strong><?= count($products) ?></strong><span>productos</span></div>
    </section>

    <section class="table-card">
      <div class="table-head">
        <h3><i class="fas fa-table-list"></i> Productos del catálogo</h3>
        <div class="hint"><i class="fas fa-circle-info"></i> Sube imágenes JPG, PNG, WEBP o GIF de hasta 5 MB.</div>
      </div>
      <table id="productsTable" style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>Categoría</th>
            <th>Producto</th>
            <th>Precio</th>
            <th>Nueva imagen</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="productsBody"></tbody>
      </table>
    </section>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
const PRODUCTS = <?= $productsJson ?>;
const CATEGORY_LABELS = <?= json_encode($categoryLabels, JSON_UNESCAPED_UNICODE) ?>;

function esc(str) {
  return String(str || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function productImage(product) {
  if (product.image) return `<img src="${esc(product.image)}" alt="${esc(product.name)}">`;
  return `<i class="${esc(product.icon || 'fas fa-box')}"></i>`;
}

function buildRows() {
  const body = document.getElementById('productsBody');
  body.innerHTML = PRODUCTS.map(product => {
    const id = parseInt(product.id, 10);
    return `<tr data-id="${id}">
      <td style="font-weight:800;color:#aaa">#${id}</td>
      <td><div class="img-preview" id="preview-${id}">${productImage(product)}</div></td>
      <td><span class="category-badge">${esc(CATEGORY_LABELS[product.category] || product.category)}</span></td>
      <td><input class="edit-input name-input" value="${esc(product.name)}" aria-label="Nombre de ${esc(product.name)}"></td>
      <td><input class="edit-input price-input" type="number" min="0.01" step="0.01" value="${Number(product.price).toFixed(2)}" aria-label="Precio de ${esc(product.name)}"></td>
      <td><input class="file-input image-input" type="file" accept="image/jpeg,image/png,image/webp,image/gif"></td>
      <td><button class="btn-save" onclick="saveProduct(${id}, this)"><i class="fas fa-save"></i> Guardar</button></td>
    </tr>`;
  }).join('');
}

async function saveProduct(id, button) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) return;
  const name = row.querySelector('.name-input').value.trim();
  const price = parseFloat(row.querySelector('.price-input').value);
  const file = row.querySelector('.image-input').files[0];
  if (!name || !price || price <= 0) {
    Swal.fire('Datos incompletos', 'Ingresa un nombre y precio válidos.', 'warning');
    return;
  }

  const form = new FormData();
  form.append('id', id);
  form.append('name', name);
  form.append('price', price.toFixed(2));
  if (file) form.append('image', file);

  button.disabled = true;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando';
  try {
    const res = await fetch('/catalogodigsistema/api/update_product.php', { method: 'POST', body: form });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.error || 'No se pudo actualizar');
    const product = data.product;
    const current = PRODUCTS.find(p => parseInt(p.id, 10) === id);
    if (current) Object.assign(current, product);
    document.getElementById(`preview-${id}`).innerHTML = productImage(product);
    row.querySelector('.image-input').value = '';
    Swal.fire({icon:'success',title:'Producto actualizado',timer:1100,showConfirmButton:false,toast:true,position:'top-end'});
  } catch (error) {
    Swal.fire('Error', error.message, 'error');
  } finally {
    button.disabled = false;
    button.innerHTML = '<i class="fas fa-save"></i> Guardar';
  }
}

buildRows();
$('#productsTable').DataTable({
  language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
  order: [[2, 'asc'], [0, 'asc']],
  pageLength: 20,
  lengthMenu: [[10,20,50,-1],[10,20,50,'Todos']],
  columnDefs: [{ targets:[1,5,6], orderable:false, searchable:false }],
});
</script>
</body>
</html>
