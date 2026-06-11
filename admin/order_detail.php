<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$company = companyInfo();
$company = array_map(fn($value) => is_string($value) ? htmlspecialchars_decode($value, ENT_QUOTES) : $value, $company);
$companyName = $company['commercial_name'] ?: ($company['legal_name'] ?: 'AutoRepuestos Pro');
$companyAddress = trim(implode(', ', array_filter([
    $company['street'] ?? '',
    !empty($company['neighborhood']) ? 'Col. ' . $company['neighborhood'] : '',
    $company['city'] ?? '',
    $company['state'] ?? '',
    !empty($company['zip']) ? 'CP ' . $company['zip'] : '',
])));
$orderId = sanitize($_GET['id'] ?? '');
if (empty($orderId)) { header('Location: /catalogodigsistema/admin/orders.php'); exit; }

$orders = readJson('orders');
$order  = null;
foreach ($orders as $o) {
    if ($o['id'] === $orderId) { $order = $o; break; }
}
if (!$order) { header('Location: /catalogodigsistema/admin/orders.php'); exit; }

$paymentLabels  = ['pending'=>'Pendiente','transfer'=>'Transferencia','cash'=>'Efectivo','card'=>'Tarjeta'];
$paymentColors  = ['pending'=>'#D97706','transfer'=>'#2563EB','cash'=>'#059669','card'=>'#7C3AED'];
$paymentBg      = ['pending'=>'#FEF3C7','transfer'=>'#DBEAFE','cash'=>'#D1FAE5','card'=>'#EDE9FE'];
$deliveryLabels = ['pending'=>'Pendiente de entrega','delivered'=>'Entregado','cancelled'=>'Cancelado'];
$deliveryColors = ['pending'=>'#C2410C','delivered'=>'#047857','cancelled'=>'#DC2626'];
$deliveryBg     = ['pending'=>'#FFF7ED','delivered'=>'#A7F3D0','cancelled'=>'#FEE2E2'];
$methodLabels   = ['whatsapp'=>'WhatsApp','transfer'=>'Transferencia Bancaria','card'=>'Tarjeta de crédito/débito','cash'=>'Efectivo'];

$curPayStatus   = $order['payment_status']  ?? 'pending';
$curDelStatus   = $order['delivery_status'] ?? 'pending';
$curPayLabel    = $paymentLabels[$curPayStatus]  ?? $curPayStatus;
$curDelLabel    = $deliveryLabels[$curDelStatus] ?? $curDelStatus;
$curPayColor    = $paymentColors[$curPayStatus]  ?? '#aaa';
$curDelColor    = $deliveryColors[$curDelStatus] ?? '#aaa';
$curPayBg       = $paymentBg[$curPayStatus]      ?? '#f5f5f5';
$curDelBg       = $deliveryBg[$curDelStatus]     ?? '#f5f5f5';
$createdAt      = new DateTime($order['created_at']);
$paidAt         = isset($order['paid_at'])      ? new DateTime($order['paid_at'])      : null;
$deliveredAt    = isset($order['delivered_at']) ? new DateTime($order['delivered_at']) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedido <?= htmlspecialchars($order['id']) ?> · <?= htmlspecialchars($companyName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#f0f4f8;color:#1a2c3e;display:flex;min-height:100vh}
.sidebar{width:240px;background:#1a2c3e;position:fixed;top:0;left:0;height:100vh;display:flex;flex-direction:column;z-index:50}
.sidebar-brand{padding:1.6rem 1.4rem 1rem;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
.sidebar-brand .icon{background:linear-gradient(135deg,#C0392B,#E67E22);border-radius:12px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sidebar-brand .icon i{color:#fff;font-size:1.1rem}
.sidebar-brand span{font-weight:800;font-size:.95rem;color:#fff;line-height:1.2}
.sidebar-brand small{display:block;color:#7a9ead;font-size:.7rem;font-weight:400}
.sidebar-nav{flex:1;padding:1rem .8rem;overflow-y:auto}
.nav-item{display:flex;align-items:center;gap:12px;padding:.75rem 1rem;border-radius:14px;color:#7a9ead;text-decoration:none;font-weight:600;font-size:.88rem;transition:.2s;margin-bottom:.2rem}
.nav-item:hover,.nav-item.active{background:rgba(230,126,34,.15);color:#E67E22}
.nav-item i{width:18px;text-align:center;font-size:.95rem}
.sidebar-footer{padding:1rem .8rem;border-top:1px solid rgba(255,255,255,.08)}
.nav-logout{color:#e87f7f!important}
.nav-logout:hover{background:rgba(192,57,43,.15)!important;color:#e87f7f!important}
.main{margin-left:240px;flex:1;display:flex;flex-direction:column}
.topbar{background:#fff;padding:1rem 2rem;border-bottom:1px solid #e2ecea;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40;gap:1rem;flex-wrap:wrap}
.topbar-left{display:flex;align-items:center;gap:.8rem}
.topbar-left h2{font-size:1.1rem;font-weight:800}
.btn-back{background:#f0f4f8;border:none;padding:.45rem .9rem;border-radius:30px;font-size:.82rem;font-weight:600;cursor:pointer;color:#2c4b57;text-decoration:none;font-family:'Inter',sans-serif;display:flex;align-items:center;gap:6px}
.btn-back:hover{background:#E67E22;color:#fff}
.topbar-right{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
.btn-action{border:none;padding:.5rem 1.1rem;border-radius:30px;font-size:.82rem;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;display:inline-flex;align-items:center;gap:6px;transition:.2s}
.btn-print{background:#1a2c3e;color:#fff}
.btn-print:hover{background:#E67E22}
.btn-confirm{background:#D1FAE5;color:#047857}
.btn-confirm:hover{background:#059669;color:#fff}
.content{padding:1.8rem 2rem;flex:1;display:grid;grid-template-columns:1fr 320px;gap:1.4rem;align-items:start}
.col-main{display:flex;flex-direction:column;gap:1.4rem}
.col-side{display:flex;flex-direction:column;gap:1.4rem}
.card{background:#fff;border-radius:20px;padding:1.4rem;box-shadow:0 2px 12px rgba(0,0,0,.04)}
.card-title{font-size:.9rem;font-weight:700;color:#1a2c3e;margin-bottom:1rem;display:flex;align-items:center;gap:8px;padding-bottom:.7rem;border-bottom:1px solid #f0f4f8}
.card-title i{color:#E67E22}
.info-row{display:flex;gap:.4rem;margin-bottom:.5rem;font-size:.87rem;align-items:baseline}
.info-label{color:#6c8695;font-weight:500;min-width:100px;flex-shrink:0}
.info-val{font-weight:600;color:#1a2c3e}
.badge{display:inline-flex;align-items:center;padding:4px 12px;border-radius:30px;font-size:.78rem;font-weight:700}
table{width:100%;border-collapse:collapse}
th{text-align:left;padding:.65rem .8rem;border-bottom:2px solid #f0f4f8;font-size:.73rem;color:#6c8695;text-transform:uppercase}
td{padding:.8rem;border-bottom:1px solid #f7f9fb;font-size:.87rem;vertical-align:middle}
tr:last-child td{border-bottom:none}
.total-row td{font-weight:800;font-size:1rem;color:#C0392B;border-top:2px solid #f0f4f8}
.timeline{display:flex;flex-direction:column;gap:.8rem}
.tl-item{display:flex;gap:.8rem;align-items:flex-start;font-size:.83rem}
.tl-dot{width:10px;height:10px;border-radius:50%;background:#E67E22;flex-shrink:0;margin-top:4px}
.tl-time{color:#aaa;font-size:.75rem;white-space:nowrap}
.tl-note{color:#2c4b57;font-weight:500}
.access-box{background:#f8fcfb;border:1px solid #d1fae5;border-radius:16px;padding:1rem;font-size:.85rem}
.access-box p{margin:.3rem 0}
.access-box strong{color:#047857}
.status-form select{width:100%;border:2px solid #e2ecea;border-radius:12px;padding:.6rem .8rem;font-family:'Inter',sans-serif;font-size:.87rem;outline:none;transition:.2s;color:#1a2c3e}
.status-form select:focus{border-color:#E67E22}
.btn-update{width:100%;margin-top:.6rem;background:#E67E22;border:none;color:#fff;padding:.7rem;border-radius:30px;font-weight:700;font-size:.9rem;cursor:pointer;font-family:'Inter',sans-serif}
.btn-update:hover{background:#C0392B}

/* ── Print styles ── */
@media print {
  .sidebar,.topbar,.col-side,.btn-action,.btn-back,.status-form,.btn-update{display:none!important}
  body{background:#fff;display:block}
  .main{margin:0}
  .content{display:block;padding:.5rem}
  .col-main{gap:1rem}
  .card{box-shadow:none;border:1px solid #ddd;page-break-inside:avoid}
  .ticket-header{display:flex!important}
}
.ticket-header{display:none;justify-content:space-between;align-items:center;margin-bottom:1rem;padding-bottom:1rem;border-bottom:2px solid #e67e22}

@media(max-width:1024px){.content{grid-template-columns:1fr}}
@media(max-width:768px){.sidebar{width:60px}.sidebar-brand span,.sidebar-brand small,.nav-item span{display:none}.main{margin-left:60px}.content{padding:1rem}}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="icon"><i class="fas fa-car"></i></div>
    <div><span>AutoRepuestos<br>Pro</span><small>Panel Admin</small></div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="/catalogodigsistema/admin/index.php"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/products.php"><i class="fas fa-tags"></i><span>Catálogo</span></a>
    <a class="nav-item active" href="/catalogodigsistema/admin/orders.php"><i class="fas fa-box-open"></i><span>Pedidos</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/orders.php?status=pending"><i class="fas fa-clock"></i><span>Pendientes</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/orders.php?status=pending_transfer"><i class="fas fa-university"></i><span>Transferencias</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/orders.php?status=paid"><i class="fas fa-check-circle"></i><span>Pagados</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/company.php"><i class="fas fa-building"></i><span>Empresa</span></a>
    <a class="nav-item" href="/catalogodigsistema/index.php" target="_blank"><i class="fas fa-store"></i><span>Ver tienda</span></a>
  </nav>
  <div class="sidebar-footer">
    <a class="nav-item nav-logout" href="/catalogodigsistema/api/logout.php?role=admin"><i class="fas fa-sign-out-alt"></i><span>Cerrar sesión</span></a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <a class="btn-back" href="/catalogodigsistema/admin/orders.php"><i class="fas fa-arrow-left"></i> Volver</a>
      <h2>Pedido <strong><?= htmlspecialchars($order['id']) ?></strong></h2>
    </div>
    <div class="topbar-right">
      <?php if ($curPayStatus === 'pending' && !empty($order['payment_method']) && $order['payment_method'] === 'transfer'): ?>
      <button class="btn-action btn-confirm" onclick="openPayModal()"><i class="fas fa-check"></i> Confirmar pago</button>
      <?php endif; ?>
      <button class="btn-action btn-print" onclick="window.print()"><i class="fas fa-print"></i> Imprimir ticket</button>
    </div>
  </div>

  <div class="content">
    <div class="col-main">

      <!-- Ticket header (print only) -->
      <div class="ticket-header">
        <div>
          <div style="font-size:1.4rem;font-weight:800"><?= htmlspecialchars($companyName) ?></div>
          <div style="color:#E67E22;font-weight:600">Ticket de Pedido</div>
          <?php if (!empty($company['rfc']) || !empty($company['company_phone']) || !empty($company['company_email']) || $companyAddress): ?>
          <div style="font-size:.78rem;color:#6c8695;margin-top:.25rem">
            <?= $companyAddress ? htmlspecialchars($companyAddress) . '<br>' : '' ?>
            <?= !empty($company['rfc']) ? 'RFC: ' . htmlspecialchars($company['rfc']) . ' · ' : '' ?>
            <?= !empty($company['company_phone']) ? 'Tel: ' . htmlspecialchars($company['company_phone']) . ' · ' : '' ?>
            <?= !empty($company['company_email']) ? htmlspecialchars($company['company_email']) : '' ?>
          </div>
          <?php endif; ?>
        </div>
        <div style="text-align:right;font-size:.85rem">
          <div><strong><?= htmlspecialchars($order['id']) ?></strong></div>
          <div><?= $createdAt->format('d/m/Y H:i') ?></div>
        </div>
      </div>

      <!-- Order summary -->
      <div class="card">
        <div class="card-title"><i class="fas fa-receipt"></i> Resumen del pedido</div>
        <div class="info-row"><span class="info-label">Pedido</span><span class="info-val"><?= htmlspecialchars($order['id']) ?></span></div>
        <div class="info-row"><span class="info-label">Número</span><span class="info-val">#<?= $order['number'] ?></span></div>
        <div class="info-row"><span class="info-label">Fecha</span><span class="info-val"><?= $createdAt->format('d/m/Y H:i:s') ?></span></div>
        <?php if ($paidAt): ?>
        <div class="info-row"><span class="info-label">Fecha de pago</span><span class="info-val"><?= $paidAt->format('d/m/Y H:i:s') ?></span></div>
        <?php endif; ?>
        <?php if ($deliveredAt): ?>
        <div class="info-row"><span class="info-label">Fecha de entrega</span><span class="info-val"><?= $deliveredAt->format('d/m/Y H:i:s') ?></span></div>
        <?php endif; ?>
        <div class="info-row">
          <span class="info-label">Pago</span>
          <span class="badge" style="background:<?= $curPayBg ?>;color:<?= $curPayColor ?>"><?= $curPayLabel ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Entrega</span>
          <span class="badge" style="background:<?= $curDelBg ?>;color:<?= $curDelColor ?>"><?= $curDelLabel ?></span>
        </div>
        <div class="info-row"><span class="info-label">Canal de pedido</span><span class="info-val"><?= htmlspecialchars($methodLabels[$order['payment_method']] ?? $order['payment_method']) ?></span></div>
      </div>

      <!-- Customer info -->
      <div class="card">
        <div class="card-title"><i class="fas fa-user"></i> Datos del cliente</div>
        <?php $c = $order['customer']; ?>
        <div class="info-row"><span class="info-label">Nombre</span><span class="info-val"><?= htmlspecialchars($c['name'] ?: 'No proporcionado') ?></span></div>
        <div class="info-row"><span class="info-label">Correo</span><span class="info-val"><?= htmlspecialchars($c['email'] ?: 'No proporcionado') ?></span></div>
        <?php if ($c['street'] || $c['city']): ?>
        <div class="info-row"><span class="info-label">Dirección</span>
          <span class="info-val"><?= htmlspecialchars(trim(($c['street'] ? $c['street'] . ', ' : '') . ($c['city'] ?: '') . ($c['zip'] ? ' CP ' . $c['zip'] : ''))) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Products -->
      <div class="card">
        <div class="card-title"><i class="fas fa-shopping-cart"></i> Productos del pedido</div>
        <table>
          <thead>
            <tr>
              <th>Producto</th>
              <th style="text-align:center">Cant.</th>
              <th style="text-align:right">P. unitario</th>
              <th style="text-align:right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($order['items'] as $item): $sub = $item['price'] * $item['quantity']; ?>
            <tr>
              <td style="font-weight:600"><?= htmlspecialchars($item['name']) ?></td>
              <td style="text-align:center"><?= $item['quantity'] ?></td>
              <td style="text-align:right">$<?= number_format($item['price'], 2) ?></td>
              <td style="text-align:right;font-weight:700">$<?= number_format($sub, 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
              <td colspan="3" style="text-align:right;font-size:.85rem;color:#6c8695">TOTAL</td>
              <td style="text-align:right">$<?= number_format($order['total'], 2) ?> MXN</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Activity log -->
      <div class="card">
        <div class="card-title"><i class="fas fa-timeline"></i> Historial de actividad</div>
        <?php if (empty($order['activity'])): ?>
          <p style="color:#aaa;font-size:.85rem">Sin actividad registrada.</p>
        <?php else: ?>
        <div class="timeline">
          <?php foreach (array_reverse($order['activity']) as $ev): ?>
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div>
              <div class="tl-note"><?= htmlspecialchars($ev['note'] ?? $ev['action']) ?></div>
              <div class="tl-time"><?= htmlspecialchars((new DateTime($ev['timestamp']))->format('d/m/Y H:i')) ?> — <?= htmlspecialchars($ev['user']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </div><!-- /col-main -->

    <div class="col-side">

      <!-- Change payment status -->
      <div class="card">
        <div class="card-title"><i class="fas fa-money-bill-wave"></i> Estado de Pago</div>
        <div class="status-form">
          <select id="paySelect">
            <?php foreach (['pending'=>'Pendiente','transfer'=>'Transferencia','cash'=>'Efectivo','card'=>'Tarjeta'] as $sv => $sl): ?>
            <option value="<?= $sv ?>" <?= $curPayStatus === $sv ? 'selected' : '' ?>><?= $sl ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn-update" onclick="updatePayment()"><i class="fas fa-save"></i> Guardar pago</button>
        </div>
      </div>

      <!-- Change delivery status -->
      <div class="card">
        <div class="card-title"><i class="fas fa-truck"></i> Estado de Entrega</div>
        <div class="status-form">
          <select id="delSelect">
            <?php foreach (['pending'=>'Pendiente de entrega','delivered'=>'Entregado','cancelled'=>'Cancelado'] as $sv => $sl): ?>
            <option value="<?= $sv ?>" <?= $curDelStatus === $sv ? 'selected' : '' ?>><?= $sl ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn-update" onclick="updateDelivery()" style="background:#047857"><i class="fas fa-save"></i> Guardar entrega</button>
        </div>
      </div>

      <!-- Access credentials -->
      <?php if (!empty($order['access'])): ?>
      <div class="card">
        <div class="card-title"><i class="fas fa-key"></i> Acceso del cliente</div>
        <div class="access-box">
          <p>Usuario: <strong><?= htmlspecialchars($order['access']['username']) ?></strong></p>
          <p>Contraseña temporal: <strong><?= htmlspecialchars($order['access']['password']) ?></strong></p>
          <p style="font-size:.76rem;color:#6c8695;margin-top:.4rem">Portal: <a href="/catalogodigsistema/client/login.php" target="_blank" style="color:#059669">/client/login.php</a></p>
        </div>
      </div>
      <?php endif; ?>

      <!-- Notes -->
      <div class="card">
        <div class="card-title"><i class="fas fa-sticky-note"></i> Notas internas</div>
        <textarea id="orderNote" style="width:100%;border:2px solid #e2ecea;border-radius:12px;padding:.7rem;font-family:'Inter',sans-serif;font-size:.85rem;resize:vertical;min-height:80px;outline:none" placeholder="Agrega notas sobre este pedido…"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
        <button class="btn-update" onclick="saveNote()" style="background:#2c4b57"><i class="fas fa-save"></i> Guardar nota</button>
      </div>

    </div><!-- /col-side -->
  </div><!-- /content -->
</div><!-- /main -->

<script>
const ORDER_ID = <?= json_encode($order['id']) ?>;

async function postUpdate(body) {
  const res = await fetch('/catalogodigsistema/api/update_order.php', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)
  });
  return await res.json();
}

async function updatePayment() {
  const payment_status = document.getElementById('paySelect').value;
  const data = await postUpdate({order_id: ORDER_ID, payment_status});
  if (data.success) {
    Swal.fire({icon:'success',title:'Pago actualizado',timer:1400,showConfirmButton:false}).then(()=>location.reload());
  } else { Swal.fire('Error', data.error||'No se pudo actualizar','error'); }
}

async function updateDelivery() {
  const delivery_status = document.getElementById('delSelect').value;
  const data = await postUpdate({order_id: ORDER_ID, delivery_status});
  if (data.success) {
    Swal.fire({icon:'success',title:'Entrega actualizada',timer:1400,showConfirmButton:false}).then(()=>location.reload());
  } else { Swal.fire('Error', data.error||'No se pudo actualizar','error'); }
}

async function openPayModal() {
  document.getElementById('paySelect').focus();
  Swal.fire({icon:'info',title:'Cambia el estado de pago',text:'Usa el selector de la barra lateral.',timer:2000,showConfirmButton:false,toast:true,position:'top-end'});
}

async function saveNote() {
  const note = document.getElementById('orderNote').value.trim();
  const data = await postUpdate({order_id: ORDER_ID, note});
  Swal.fire({icon:data.success?'success':'error',title:data.success?'Nota guardada':'Error',timer:1200,showConfirmButton:false,toast:true,position:'top-end'});
}
</script>
</body>
</html>
