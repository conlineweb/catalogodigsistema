<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard · AutoRepuestos Prooooo</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#f0f4f8;color:#1a2c3e;display:flex;min-height:100vh}

/* ── Sidebar ── */
.sidebar{width:240px;background:#1a2c3e;position:fixed;top:0;left:0;height:100vh;display:flex;flex-direction:column;z-index:50;transition:.3s}
.sidebar-brand{padding:1.6rem 1.4rem 1rem;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
.sidebar-brand .icon{background:linear-gradient(135deg,#C0392B,#E67E22);border-radius:12px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sidebar-brand .icon i{color:#fff;font-size:1.1rem}
.sidebar-brand span{font-weight:800;font-size:.95rem;color:#fff;line-height:1.2}
.sidebar-brand small{display:block;color:#7a9ead;font-size:.7rem;font-weight:400}
.sidebar-nav{flex:1;padding:1rem .8rem;overflow-y:auto}
.nav-item{display:flex;align-items:center;gap:12px;padding:.75rem 1rem;border-radius:14px;color:#7a9ead;text-decoration:none;font-weight:600;font-size:.88rem;transition:.2s;margin-bottom:.2rem;cursor:pointer}
.nav-item:hover,.nav-item.active{background:rgba(230,126,34,.15);color:#E67E22}
.nav-item i{width:18px;text-align:center;font-size:.95rem}
.sidebar-footer{padding:1rem .8rem;border-top:1px solid rgba(255,255,255,.08)}
.nav-logout{color:#e87f7f !important}
.nav-logout:hover{background:rgba(192,57,43,.15) !important;color:#e87f7f !important}

/* ── Main ── */
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{background:#fff;padding:1rem 2rem;border-bottom:1px solid #e2ecea;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40;gap:1rem}
.topbar h2{font-size:1.25rem;font-weight:800;color:#1a2c3e}
.topbar-right{display:flex;align-items:center;gap:.8rem}
.user-chip{background:#f0f4f8;padding:.4rem 1rem;border-radius:40px;font-size:.82rem;font-weight:600;color:#2c4b57;display:flex;align-items:center;gap:6px}
.btn-refresh{background:#E67E22;border:none;color:#fff;padding:.45rem .9rem;border-radius:30px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;display:flex;align-items:center;gap:6px}
.btn-refresh:hover{background:#C0392B}
.content{padding:1.8rem 2rem;flex:1}

/* ── Stat cards ── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(195px,1fr));gap:1.2rem;margin-bottom:2rem}
.stat-card{background:#fff;border-radius:20px;padding:1.4rem;display:flex;align-items:center;gap:1rem;box-shadow:0 2px 12px rgba(0,0,0,.04);transition:.2s}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.08)}
.stat-icon{width:52px;height:52px;border-radius:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.3rem}
.stat-num{font-size:1.9rem;font-weight:800;line-height:1}
.stat-label{font-size:.76rem;color:#6c8695;font-weight:500;margin-top:.2rem}

/* ── Charts ── */
.charts-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:1.2rem;margin-bottom:2rem}
.chart-card{background:#fff;border-radius:20px;padding:1.4rem;box-shadow:0 2px 12px rgba(0,0,0,.04)}
.chart-card h3{font-size:.9rem;font-weight:700;color:#1a2c3e;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
.chart-card h3 i{color:#E67E22}
.chart-wrap{position:relative;height:200px}

/* ── Orders table ── */
.table-card{background:#fff;border-radius:20px;padding:1.4rem;box-shadow:0 2px 12px rgba(0,0,0,.04)}
.table-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;flex-wrap:wrap;gap:.6rem}
.table-card-header h3{font-size:.95rem;font-weight:700;color:#1a2c3e;display:flex;align-items:center;gap:8px}
.table-card-header h3 i{color:#E67E22}
.btn-view-all{background:#f0f4f8;border:none;color:#2c4b57;padding:.45rem 1rem;border-radius:30px;font-size:.8rem;font-weight:600;cursor:pointer;text-decoration:none;font-family:'Inter',sans-serif}
.btn-view-all:hover{background:#E67E22;color:#fff}
table{width:100%;border-collapse:collapse}
th{text-align:left;padding:.7rem 1rem;border-bottom:2px solid #f0f4f8;font-size:.75rem;color:#6c8695;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
td{padding:.85rem 1rem;border-bottom:1px solid #f7f9fb;font-size:.87rem;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafcfc}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:30px;font-size:.73rem;font-weight:700;white-space:nowrap}
.b-pay-pending{background:#FEF3C7;color:#D97706}
.b-pay-transfer{background:#DBEAFE;color:#2563EB}
.b-pay-cash{background:#D1FAE5;color:#059669}
.b-pay-card{background:#EDE9FE;color:#7C3AED}
.b-del-pending{background:#FFF7ED;color:#C2410C}
.b-del-delivered{background:#A7F3D0;color:#047857}
.b-del-cancelled{background:#FEE2E2;color:#DC2626}
.method-icon{display:inline-flex;align-items:center;gap:5px;font-size:.82rem;font-weight:600}
.btn-detail{background:#f0f4f8;border:none;padding:.35rem .8rem;border-radius:20px;font-size:.77rem;font-weight:600;cursor:pointer;color:#2c4b57;text-decoration:none;font-family:'Inter',sans-serif}
.btn-detail:hover{background:#E67E22;color:#fff}

.empty-state{text-align:center;padding:3rem;color:#aab;font-size:.9rem}

/* ── Clickable stat cards ── */
.stat-card.clickable{cursor:pointer}
.stat-card.clickable:hover{outline:2px solid rgba(230,126,34,.45);box-shadow:0 8px 24px rgba(0,0,0,.1)}
.stat-rev{font-size:.7rem;font-weight:700;color:#059669;margin-top:.15rem}
/* ── Revenue breakdown bar ── */
.rev-bar{background:#fff;border-radius:20px;padding:1.1rem 1.6rem;margin-bottom:2rem;box-shadow:0 2px 12px rgba(0,0,0,.04);display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap}
.rev-item{display:flex;align-items:center;gap:.75rem;flex:1;min-width:130px}
.rev-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem}
.rev-amount{font-size:1.05rem;font-weight:800;color:#1a2c3e;line-height:1}
.rev-lbl{font-size:.7rem;color:#6c8695;font-weight:500;margin-top:.12rem}
.rev-divider{width:1px;height:36px;background:#e2ecea;flex-shrink:0}
.rev-total-item .rev-amount{color:#E67E22}
/* ── Orders modal ── */
.swal-orders-list{max-height:360px;overflow-y:auto}
.swal-orders-list table{width:100%;border-collapse:collapse;font-size:.81rem}
.swal-orders-list th{padding:.45rem .6rem;border-bottom:2px solid #f0f4f8;color:#6c8695;font-size:.7rem;text-transform:uppercase;letter-spacing:.03em;text-align:left;white-space:nowrap;background:#fafcfc}
.swal-orders-list td{padding:.6rem;border-bottom:1px solid #f7f9fb;vertical-align:middle}
.swal-orders-list tr:last-child td{border-bottom:none}
.swal2-popup.swal-orders-popup{border-radius:24px!important;font-family:'Inter',sans-serif!important}
@media(max-width:1024px){.charts-grid{grid-template-columns:1fr 1fr}}
@media(max-width:768px){
  .sidebar{width:60px}
  .sidebar-brand span,.sidebar-brand small,.nav-item span{display:none}
  .main{margin-left:60px}
  .charts-grid{grid-template-columns:1fr}
  .content{padding:1rem}
}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="icon"><i class="fas fa-car"></i></div>
    <div>
      <span>AutoRepuestos<br>Pro</span>
      <small>Panel Admin</small>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item active" href="/catalogodigsistema/admin/index.php">
      <i class="fas fa-chart-pie"></i><span>Dashboard</span>
    </a>
    <a class="nav-item" href="/catalogodigsistema/admin/orders.php">
      <i class="fas fa-box-open"></i><span>Pedidos</span>
    </a>
    <a class="nav-item" href="/catalogodigsistema/admin/orders.php">
      <i class="fas fa-clock"></i><span>Pendientes</span>
    </a>
    <a class="nav-item" href="/catalogodigsistema/index.php" target="_blank">
      <i class="fas fa-store"></i><span>Ver tienda</span>
    </a>
  </nav>
  <div class="sidebar-footer">
    <a class="nav-item nav-logout" href="/catalogodigsistema/api/logout.php?role=admin">
      <i class="fas fa-sign-out-alt"></i><span>Cerrar sesión</span>
    </a>
  </div>
</aside>

<!-- Main -->
<div class="main">
  <div class="topbar">
    <h2><i class="fas fa-chart-pie" style="color:#E67E22;margin-right:.5rem"></i>Dashboard</h2>
    <div class="topbar-right">
      <div class="user-chip"><i class="fas fa-user-shield"></i><?= htmlspecialchars($_SESSION['name']) ?></div>
      <button class="btn-refresh" onclick="loadStats()"><i class="fas fa-sync-alt"></i> Actualizar</button>
    </div>
  </div>

  <div class="content">

    <!-- Stat cards -->
    <div class="stats-grid">
      <div class="stat-card clickable" onclick="showCardOrders('payment_status','pending','Pendientes de pago')">
        <div class="stat-icon" style="background:#FEF3C7"><i class="fas fa-clock" style="color:#D97706"></i></div>
        <div><div class="stat-num" id="s-pay-pending">—</div><div class="stat-label">Pend. pago</div></div>
      </div>
      <div class="stat-card clickable" onclick="showCardOrders('payment_status','transfer','Pagados · Transferencia')">
        <div class="stat-icon" style="background:#DBEAFE"><i class="fas fa-university" style="color:#2563EB"></i></div>
        <div>
          <div class="stat-num" id="s-pay-transfer">—</div>
          <div class="stat-label">Transferencias</div>
          <div class="stat-rev" id="r-pay-transfer"></div>
        </div>
      </div>
      <div class="stat-card clickable" onclick="showCardOrders('payment_status','cash','Pagados · Efectivo')">
        <div class="stat-icon" style="background:#D1FAE5"><i class="fas fa-money-bill-wave" style="color:#059669"></i></div>
        <div>
          <div class="stat-num" id="s-pay-cash">—</div>
          <div class="stat-label">Efectivo</div>
          <div class="stat-rev" id="r-pay-cash"></div>
        </div>
      </div>
      <div class="stat-card clickable" onclick="showCardOrders('payment_status','card','Pagados · Tarjeta')">
        <div class="stat-icon" style="background:#EDE9FE"><i class="fas fa-credit-card" style="color:#7C3AED"></i></div>
        <div>
          <div class="stat-num" id="s-pay-card">—</div>
          <div class="stat-label">Tarjeta</div>
          <div class="stat-rev" id="r-pay-card"></div>
        </div>
      </div>
      <div class="stat-card clickable" onclick="showCardOrders('delivery_status','delivered','Pedidos entregados')">
        <div class="stat-icon" style="background:#A7F3D0"><i class="fas fa-truck" style="color:#047857"></i></div>
        <div><div class="stat-num" id="s-del-delivered">—</div><div class="stat-label">Entregados</div></div>
      </div>
      <div class="stat-card clickable" onclick="showCardOrders('delivery_status','cancelled','Pedidos cancelados')">
        <div class="stat-icon" style="background:#FEE2E2"><i class="fas fa-times-circle" style="color:#DC2626"></i></div>
        <div><div class="stat-num" id="s-del-cancelled">—</div><div class="stat-label">Cancelados</div></div>
      </div>
      <div class="stat-card clickable" onclick="showCardOrders('_paid_','','Ingresos confirmados')">
        <div class="stat-icon" style="background:#EDE9FE"><i class="fas fa-dollar-sign" style="color:#7C3AED"></i></div>
        <div><div class="stat-num" id="s-revenue">—</div><div class="stat-label">Ingresos confirmados</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#fee9e6"><i class="fas fa-percent" style="color:#E67E22"></i></div>
        <div><div class="stat-num" id="s-conv">—</div><div class="stat-label">Conversión</div></div>
      </div>
    </div>

    <!-- Revenue breakdown by payment method -->
    <div class="rev-bar">
      <div style="font-size:.75rem;font-weight:700;color:#6c8695;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Desglose de ingresos</div>
      <div class="rev-divider"></div>
      <div class="rev-item">
        <div class="rev-icon" style="background:#DBEAFE"><i class="fas fa-university" style="color:#2563EB"></i></div>
        <div><div class="rev-amount" id="rb-transfer">—</div><div class="rev-lbl">Transferencia</div></div>
      </div>
      <div class="rev-item">
        <div class="rev-icon" style="background:#D1FAE5"><i class="fas fa-money-bill-wave" style="color:#059669"></i></div>
        <div><div class="rev-amount" id="rb-cash">—</div><div class="rev-lbl">Efectivo</div></div>
      </div>
      <div class="rev-item">
        <div class="rev-icon" style="background:#EDE9FE"><i class="fas fa-credit-card" style="color:#7C3AED"></i></div>
        <div><div class="rev-amount" id="rb-card">—</div><div class="rev-lbl">Tarjeta</div></div>
      </div>
      <div class="rev-divider"></div>
      <div class="rev-item rev-total-item">
        <div class="rev-icon" style="background:#fee9e6"><i class="fas fa-dollar-sign" style="color:#E67E22"></i></div>
        <div><div class="rev-amount" id="rb-total">—</div><div class="rev-lbl">Total confirmado</div></div>
      </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
      <div class="chart-card">
        <h3><i class="fas fa-chart-line"></i> Pedidos — últimos 7 días</h3>
        <div class="chart-wrap"><canvas id="chartLine"></canvas></div>
      </div>
      <div class="chart-card">
        <h3><i class="fas fa-circle-half-stroke"></i> Estado de pedidos</h3>
        <div class="chart-wrap"><canvas id="chartDoughnut"></canvas></div>
      </div>
      <div class="chart-card">
        <h3><i class="fas fa-chart-bar"></i> Métodos de pago</h3>
        <div class="chart-wrap"><canvas id="chartBar"></canvas></div>
      </div>
    </div>

    <!-- Recent orders table -->
    <div class="table-card">
      <div class="table-card-header">
        <h3><i class="fas fa-list-check"></i> Pedidos recientes</h3>
        <a href="/catalogodigsistema/admin/orders.php" class="btn-view-all">Ver todos <i class="fas fa-arrow-right"></i></a>
      </div>
      <div id="recentOrdersTable">
        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i> Cargando…</div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<script>
const PAY_LABELS  = {pending:'Pendiente',transfer:'Transferencia',cash:'Efectivo',card:'Tarjeta'};
const PAY_CSS     = {pending:'b-pay-pending',transfer:'b-pay-transfer',cash:'b-pay-cash',card:'b-pay-card'};
const DEL_LABELS  = {pending:'Pend. Entrega',delivered:'Entregado',cancelled:'Cancelado'};
const DEL_CSS     = {pending:'b-del-pending',delivered:'b-del-delivered',cancelled:'b-del-cancelled'};
const METHOD_LABELS = {whatsapp:'WhatsApp',transfer:'Transferencia',card:'Tarjeta',cash:'Efectivo'};
const METHOD_ICONS  = {whatsapp:'<i class="fab fa-whatsapp" style="color:#25D366"></i>',transfer:'<i class="fas fa-university" style="color:#2563EB"></i>',card:'<i class="fas fa-credit-card" style="color:#7C3AED"></i>',cash:'<i class="fas fa-money-bill-wave" style="color:#059669"></i>'};

let chartLine, chartDoughnut, chartBar;
let ALL_ORDERS_STAT = [];

async function loadStats() {
  try {
    const res  = await fetch('/catalogodigsistema/api/get_stats.php');
    const data = await res.json();
    if (!res.ok) return;

    ALL_ORDERS_STAT = data.all_orders || [];

    document.getElementById('s-pay-pending').textContent   = data.pay_pending;
    document.getElementById('s-pay-transfer').textContent  = data.pay_transfer;
    document.getElementById('s-pay-cash').textContent      = data.pay_cash;
    document.getElementById('s-pay-card').textContent      = data.pay_card;
    document.getElementById('s-del-delivered').textContent = data.del_delivered;
    document.getElementById('s-del-cancelled').textContent = data.del_cancelled;
    document.getElementById('s-revenue').textContent  = '$' + data.paid_revenue.toLocaleString('es-MX');
    document.getElementById('s-conv').textContent     = data.conversion + '%';

    // Revenue by payment status
    const rbp  = data.revenue_by_pay_status || {};
    const fmt  = v => '$' + (v||0).toLocaleString('es-MX', {minimumFractionDigits:2});
    const setR = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = fmt(val); };
    setR('r-pay-transfer', rbp.transfer);
    setR('r-pay-cash',     rbp.cash);
    setR('r-pay-card',     rbp.card);
    setR('rb-transfer',    rbp.transfer);
    setR('rb-cash',        rbp.cash);
    setR('rb-card',        rbp.card);
    setR('rb-total',       data.paid_revenue);

    buildCharts(data);
    buildRecentTable(data.recent_orders);
  } catch(e) { console.error(e); }
}

function showCardOrders(field, value, title) {
  let filtered;
  if (field === '_paid_') {
    filtered = ALL_ORDERS_STAT.filter(o => (o.payment_status || 'pending') !== 'pending');
  } else {
    filtered = ALL_ORDERS_STAT.filter(o => (o[field] || 'pending') === value);
  }

  if (!filtered.length) {
    Swal.fire({ title, text: 'No hay pedidos en esta categoría.', icon: 'info', confirmButtonColor: '#E67E22' });
    return;
  }

  const total = filtered.reduce((s, o) => s + parseFloat(o.total||0), 0);
  const rows  = filtered.map(o => {
    const d   = new Date(o.created_at);
    const fec = d.toLocaleDateString('es-MX',{day:'2-digit',month:'2-digit',year:'numeric'});
    const ps  = o.payment_status  || 'pending';
    const ds  = o.delivery_status || 'pending';
    return `<tr>
      <td style="color:#aaa;font-size:.73rem">#${o.number}</td>
      <td style="font-weight:700;font-size:.76rem;white-space:nowrap">${escHtml(o.id)}</td>
      <td>${escHtml(o.customer_name)}</td>
      <td style="font-weight:700;color:#C0392B;white-space:nowrap">$${parseFloat(o.total).toLocaleString('es-MX',{minimumFractionDigits:2})}</td>
      <td><span class="badge b-pay-${ps}">${PAY_LABELS[ps]||ps}</span></td>
      <td><span class="badge b-del-${ds}">${DEL_LABELS[ds]||ds}</span></td>
      <td style="font-size:.73rem;color:#6c8695;white-space:nowrap">${fec}</td>
      <td><a href="/catalogodigsistema/admin/order_detail.php?id=${encodeURIComponent(o.id)}" target="_blank" style="background:#f0f4f8;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;color:#2c4b57;text-decoration:none">Ver</a></td>
    </tr>`;
  }).join('');

  Swal.fire({
    title: `<span style="font-size:.95rem;font-weight:800">${escHtml(title)}</span>`,
    html: `<div style="font-size:.82rem;color:#6c8695;text-align:left;margin-bottom:.5rem">
        ${filtered.length} pedido${filtered.length!==1?'s':''} &nbsp;&bull;&nbsp;
        Total: <strong style="color:#C0392B">$${total.toLocaleString('es-MX',{minimumFractionDigits:2})}</strong>
      </div>
      <div class="swal-orders-list">
        <table>
          <thead><tr><th>#</th><th>Folio</th><th>Cliente</th><th>Importe</th><th>Pago</th><th>Entrega</th><th>Fecha</th><th></th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
      <div style="text-align:right;margin-top:.7rem">
        <a href="/catalogodigsistema/admin/orders.php" style="font-size:.79rem;color:#E67E22;font-weight:600;text-decoration:none">Ver todos los pedidos <i class="fas fa-arrow-right"></i></a>
      </div>`,
    width: 820,
    showConfirmButton: false,
    showCloseButton: true,
    customClass: { popup: 'swal-orders-popup' },
  });
}

function buildCharts(data) {
  const labels7 = data.by_day.map(d => d.date);
  const counts7 = data.by_day.map(d => d.count);

  // Line chart
  if (chartLine) chartLine.destroy();
  chartLine = new Chart(document.getElementById('chartLine'), {
    type: 'line',
    data: {
      labels: labels7,
      datasets: [{ label: 'Pedidos', data: counts7, borderColor: '#E67E22', backgroundColor: 'rgba(230,126,34,.1)', tension: .4, fill: true, pointBackgroundColor: '#E67E22' }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
  });

  // Doughnut — delivery status
  if (chartDoughnut) chartDoughnut.destroy();
  chartDoughnut = new Chart(document.getElementById('chartDoughnut'), {
    type: 'doughnut',
    data: {
      labels: ['Pend. pago','Transferencia','Efectivo','Tarjeta'],
      datasets: [{ data: [data.pay_pending, data.pay_transfer, data.pay_cash, data.pay_card],
        backgroundColor: ['#FDE68A','#93C5FD','#6EE7B7','#C4B5FD'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }, cutout: '60%' }
  });

  // Bar – methods
  if (chartBar) chartBar.destroy();
  chartBar = new Chart(document.getElementById('chartBar'), {
    type: 'bar',
    data: {
      labels: ['WhatsApp','Transferencia','Tarjeta','Efectivo'],
      datasets: [{ label: 'Pedidos', data: [data.methods.whatsapp, data.methods.transfer, data.methods.card, data.methods.cash||0],
        backgroundColor: ['#6EE7B7','#93C5FD','#C4B5FD','#FDE68A'], borderRadius: 10, borderSkipped: false }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
  });
}

function buildRecentTable(orders) {
  const container = document.getElementById('recentOrdersTable');
  if (!orders || !orders.length) {
    container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><br>No hay pedidos aún</div>';
    return;
  }
  let html = `<table>
    <thead><tr>
      <th>#</th><th>Pedido</th><th>Cliente</th><th>Total</th><th>Método</th><th>Estado</th><th>Fecha</th><th></th>
    </tr></thead><tbody>`;
  orders.forEach(o => {
    const d = new Date(o.created_at);
    const fecha = d.toLocaleDateString('es-MX', { day:'2-digit', month:'2-digit', year:'numeric' });
    const hora  = d.toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' });
    html += `<tr>
      <td style="color:#aaa;font-size:.78rem">#${o.number}</td>
      <td style="font-weight:700;font-size:.82rem">${escHtml(o.id)}</td>
      <td>${escHtml(o.customer_name)}</td>
      <td style="font-weight:700;color:#C0392B">$${parseFloat(o.total).toLocaleString('es-MX',{minimumFractionDigits:2})}</td>
      <td><span class="method-icon">${METHOD_ICONS[o.payment_method]||''} ${METHOD_LABELS[o.payment_method]||o.payment_method}</span></td>
      <td><span class="badge b-pay-${o.payment_status||'pending'}">${PAY_LABELS[o.payment_status||'pending']||''}</span></td>
      <td style="font-size:.78rem;color:#6c8695">${fecha}<br>${hora}</td>
      <td><a class="btn-detail" href="/catalogodigsistema/admin/order_detail.php?id=${encodeURIComponent(o.id)}">Ver <i class="fas fa-arrow-right"></i></a></td>
    </tr>`;
  });
  html += '</tbody></table>';
  container.innerHTML = html;
}

function escHtml(str) { return (str||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

loadStats();
</script>
</body>
</html>
