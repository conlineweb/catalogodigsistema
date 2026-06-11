<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$allOrders = array_reverse(readJson('orders'));
$company = companyInfo();
$companyForTicket = array_map(fn($value) => is_string($value) ? htmlspecialchars_decode($value, ENT_QUOTES) : $value, $company);

$counts = [
    'all'       => count($allOrders),
    'pending'   => count(array_filter($allOrders, fn($o) => ($o['delivery_status'] ?? 'pending') === 'pending')),
    'delivered' => count(array_filter($allOrders, fn($o) => ($o['delivery_status'] ?? 'pending') === 'delivered')),
    'cancelled' => count(array_filter($allOrders, fn($o) => ($o['delivery_status'] ?? 'pending') === 'cancelled')),
];

$ordersJson = json_encode(array_values($allOrders), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$companyJson = json_encode($companyForTicket, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedidos · AutoRepuestos Pro</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<style>
:root{--orange:#E67E22;--red:#C0392B;--dark:#1a2c3e}
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
.topbar{background:#fff;padding:1rem 2rem;border-bottom:1px solid #e2ecea;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40;flex-wrap:wrap;gap:.6rem}
.topbar h2{font-size:1.2rem;font-weight:800}
.user-chip{background:#f0f4f8;padding:.4rem 1rem;border-radius:40px;font-size:.82rem;font-weight:600;color:#2c4b57;display:flex;align-items:center;gap:6px}
.content{padding:1.8rem 2rem;flex:1}
/* Tabs */
.tabs{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.4rem}
.tab{padding:.4rem 1rem;border-radius:30px;border:2px solid #e2ecea;background:#fff;font-size:.8rem;font-weight:600;color:#6c8695;cursor:pointer;transition:.2s;white-space:nowrap}
.tab:hover{border-color:var(--orange);color:var(--orange)}
.tab.active{background:var(--orange);border-color:var(--orange);color:#fff}
.tab .cnt{background:rgba(0,0,0,.12);border-radius:20px;padding:1px 6px;font-size:.7rem;margin-left:3px}
/* Table card */
.table-card{background:#fff;border-radius:20px;padding:1.6rem;box-shadow:0 2px 16px rgba(0,0,0,.05)}
/* DataTables overrides */
#ordersTable_filter input{border:2px solid #e2ecea!important;border-radius:30px!important;padding:.4rem .9rem!important;font-family:'Inter',sans-serif!important;font-size:.83rem!important;outline:none!important}
#ordersTable_filter input:focus{border-color:var(--orange)!important}
#ordersTable_length select{border:2px solid #e2ecea!important;border-radius:12px!important;padding:.3rem .6rem!important;font-family:'Inter',sans-serif!important}
.dataTables_info{font-size:.78rem;color:#aaa}
.dataTables_paginate .paginate_button{border-radius:8px!important;font-family:'Inter',sans-serif!important;font-size:.82rem!important;border:1px solid transparent!important;padding:.3rem .6rem!important}
.dataTables_paginate .paginate_button.current,.dataTables_paginate .paginate_button.current:hover{background:var(--orange)!important;border-color:var(--orange)!important;color:#fff!important}
.dataTables_paginate .paginate_button:hover:not(.current){background:#fee9e6!important;color:var(--orange)!important;border-color:var(--orange)!important}
#ordersTable thead th{background:#f8fafc;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#6c8695;border-bottom:2px solid #f0f4f8!important;padding:.8rem 1rem!important}
#ordersTable tbody td{font-size:.86rem;padding:.85rem 1rem!important;border-bottom:1px solid #f7f9fb!important;vertical-align:middle!important}
#ordersTable tbody tr:hover td{background:#fffaf7!important}
/* Badges - payment */
.b-pay-pending{background:#FEF3C7;color:#D97706}
.b-pay-transfer{background:#DBEAFE;color:#2563EB}
.b-pay-cash{background:#D1FAE5;color:#059669}
.b-pay-card{background:#EDE9FE;color:#7C3AED}
/* Badges - delivery */
.b-del-pending{background:#FFF7ED;color:#C2410C}
.b-del-delivered{background:#A7F3D0;color:#047857}
.b-del-cancelled{background:#FEE2E2;color:#DC2626}
/* Inline status cell */
.status-cell{display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
.btn-chg{border:none;background:none;cursor:pointer;color:#ccc;font-size:.7rem;padding:2px 4px;border-radius:6px;transition:.2s;line-height:1}
.btn-chg:hover{background:#f0f4f8;color:var(--orange)}
/* Buttons */
.btn-sm{border:none;padding:.35rem .75rem;border-radius:20px;font-size:.76rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:.2s;display:inline-flex;align-items:center;gap:4px;text-decoration:none}
.btn-detail{background:#f0f4f8;color:#2c4b57}
.btn-detail:hover{background:var(--orange);color:#fff}
.btn-status-chg{background:#FEF3C7;color:#92400E}
.btn-status-chg:hover{background:var(--orange);color:#fff}
.btn-pdf{background:#FEE2E2;color:#991B1B}
.btn-pdf:hover{background:#DC2626;color:#fff}
.actions-cell{display:flex;gap:.35rem;flex-wrap:wrap}
/* SweetAlert pay options */
.swal2-popup.swal-order{border-radius:24px!important;font-family:'Inter',sans-serif!important}
.pay-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:.8rem 0}
.pay-opt{border:2px solid #e2ecea;border-radius:14px;padding:.8rem .5rem;cursor:pointer;transition:.2s;text-align:center;background:#fafcfc}
.pay-opt:hover{border-color:var(--orange);background:#fffaf7}
.pay-opt.selected{border-color:var(--orange);background:#fff8f3;box-shadow:0 0 0 2px rgba(230,126,34,.2)}
.pay-opt i{font-size:1.5rem;margin-bottom:.3rem;display:block}
.pay-opt span{font-weight:700;font-size:.85rem;color:#1a2c3e;display:block}
.pay-opt small{font-size:.72rem;color:#aaa}
@media(max-width:768px){.sidebar{width:58px}.sb-title,.sb-sub,.nav-item span{display:none}.main{margin-left:58px}.content{padding:1rem}}
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
    <a class="nav-item" href="/catalogodigsistema/admin/products.php"><i class="fas fa-tags"></i><span>Catálogo</span></a>
    <a class="nav-item active" href="/catalogodigsistema/admin/orders.php"><i class="fas fa-box-open"></i><span>Pedidos</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/company.php"><i class="fas fa-building"></i><span>Empresa</span></a>
    <a class="nav-item" href="/catalogodigsistema/index.php" target="_blank"><i class="fas fa-store"></i><span>Ver tienda</span></a>
  </nav>
  <div class="sidebar-footer">
    <a class="nav-item nav-logout" href="/catalogodigsistema/api/logout.php?role=admin"><i class="fas fa-sign-out-alt"></i><span>Cerrar sesión</span></a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <h2><i class="fas fa-box-open" style="color:var(--orange);margin-right:.5rem"></i>Gestión de Pedidos</h2>
    <div class="user-chip"><i class="fas fa-user-shield"></i><?= htmlspecialchars($_SESSION['name']) ?></div>
  </div>

  <div class="content">
    <!-- Status filter tabs (by delivery) -->
    <div class="tabs" id="statusTabs">
      <button class="tab" data-filter="all">Todos <span class="cnt"><?= $counts['all'] ?></span></button>
      <button class="tab" data-filter="pending">Pend. Entrega <span class="cnt"><?= $counts['pending'] ?></span></button>
      <button class="tab" data-filter="delivered">Entregados <span class="cnt"><?= $counts['delivered'] ?></span></button>
      <button class="tab" data-filter="cancelled">Cancelados <span class="cnt"><?= $counts['cancelled'] ?></span></button>
    </div>

    <!-- DataTable -->
    <div class="table-card">
      <table id="ordersTable" style="width:100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Folio</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Pago</th>
            <th>Entrega</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="ordersBody"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- jQuery + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
/* ── DATA ── */
const ALL_ORDERS = <?= $ordersJson ?>;
const COMPANY = <?= $companyJson ?>;

const PAY_LABELS = {pending:'Pendiente',transfer:'Transferencia',cash:'Efectivo',card:'Tarjeta'};
const PAY_CSS    = {pending:'b-pay-pending',transfer:'b-pay-transfer',cash:'b-pay-cash',card:'b-pay-card'};
const DEL_LABELS = {pending:'Pend. Entrega',delivered:'Entregado',cancelled:'Cancelado'};
const DEL_CSS    = {pending:'b-del-pending',delivered:'b-del-delivered',cancelled:'b-del-cancelled'};

/* ── CUSTOM DELIVERY FILTER ── */
let curFilter = 'all';
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    if (settings.nTable.id !== 'ordersTable') return true;
    if (curFilter === 'all') return true;
    const row = settings.aoData[dataIndex].nTr;
    return row && row.dataset.delivery === curFilter;
});

/* ── BUILD ROWS ── */
function buildRows(orders) {
    const tbody = document.getElementById('ordersBody');
    tbody.innerHTML = '';
    orders.forEach(o => {
        const tr = document.createElement('tr');
        tr.dataset.delivery = o.delivery_status || 'pending';
        tr.innerHTML = buildRowHtml(o);
        tbody.appendChild(tr);
    });
}

function buildRowHtml(o) {
    const d     = new Date(o.created_at);
    const fecha = d.toLocaleDateString('es-MX',{day:'2-digit',month:'2-digit',year:'numeric'});
    const hora  = d.toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'});
    const name  = esc(o.customer?.name || 'Sin nombre');
    const email = o.customer?.email ? `<div style="font-size:.73rem;color:#aaa">${esc(o.customer.email)}</div>` : '';

    const ps = o.payment_status  || 'pending';
    const ds = o.delivery_status || 'pending';
    const isPaid = ps !== 'pending';

    const id  = JSON.stringify(o.id);
    const payCell = `<div class="status-cell">
        <span class="badge ${PAY_CSS[ps]||''}">${PAY_LABELS[ps]||ps}</span>
        <button class="btn-chg" title="Cambiar pago" onclick='openPayModal(${id})'><i class="fas fa-pencil-alt"></i></button>
    </div>`;
    const delCell = `<div class="status-cell">
        <span class="badge ${DEL_CSS[ds]||''}">${DEL_LABELS[ds]||ds}</span>
        <button class="btn-chg" title="Cambiar entrega" onclick='openDelModal(${id})'><i class="fas fa-pencil-alt"></i></button>
    </div>`;

    let actions = `<a class="btn-sm btn-detail" href="/catalogodigsistema/admin/order_detail.php?id=${encodeURIComponent(o.id)}"><i class="fas fa-eye"></i> Detalle</a>`;
    if (isPaid) {
        actions += ` <button class="btn-sm btn-pdf" onclick='generatePDF(${id})'><i class="fas fa-file-pdf"></i> PDF</button>`;
    }

    return `<td style="color:#aaa;font-size:.76rem">${o.number}</td>
        <td style="font-weight:700;font-size:.8rem">${esc(o.id)}</td>
        <td><div style="font-weight:600">${name}</div>${email}</td>
        <td style="font-weight:800;color:#C0392B">$${parseFloat(o.total).toLocaleString('es-MX',{minimumFractionDigits:2})}</td>
        <td>${payCell}</td>
        <td>${delCell}</td>
        <td style="font-size:.78rem;color:#6c8695;white-space:nowrap">${fecha}<br>${hora}</td>
        <td><div class="actions-cell">${actions}</div></td>`;
}

/* ── DATATABLES ── */
let dt;
function initDT() {
    if (dt) dt.destroy();
    buildRows(ALL_ORDERS);
    dt = $('#ordersTable').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[0,'desc']],
        columnDefs: [{ targets:[4,5,7], orderable:false, searchable:false }],
        pageLength: 15,
        lengthMenu: [[10,15,25,50,-1],[10,15,25,50,'Todos']],
    });
    applyFilter('all');
}

function applyFilter(filter) {
    curFilter = filter;
    document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.filter === filter));
    dt.draw();
}

document.getElementById('statusTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (tab) applyFilter(tab.dataset.filter);
});

initDT();

/* ── PAYMENT MODAL ── */
async function openPayModal(orderId) {
    const order = ALL_ORDERS.find(o => o.id === orderId);
    if (!order) return;
    const ps = order.payment_status || 'pending';

    const {value: newPs} = await Swal.fire({
        title: '<i class="fas fa-money-bill-wave" style="color:#E67E22"></i> Estado de Pago',
        html: `<p style="color:#6c8695;font-size:.85rem;margin-bottom:.8rem">
                   Pedido <strong>${esc(order.id)}</strong> — <em>${esc(order.customer?.name||'')}</em>
               </p>
               <div class="pay-grid">
                   <div class="pay-opt${ps==='pending'?' selected':''}" data-val="pending">
                       <i class="fas fa-clock" style="color:#D97706"></i>
                       <span>Pendiente</span><small>Sin pago aún</small>
                   </div>
                   <div class="pay-opt${ps==='transfer'?' selected':''}" data-val="transfer">
                       <i class="fas fa-university" style="color:#2563EB"></i>
                       <span>Transferencia</span><small>Pago bancario</small>
                   </div>
                   <div class="pay-opt${ps==='cash'?' selected':''}" data-val="cash">
                       <i class="fas fa-money-bill-wave" style="color:#059669"></i>
                       <span>Efectivo</span><small>Pago en mano</small>
                   </div>
                   <div class="pay-opt${ps==='card'?' selected':''}" data-val="card">
                       <i class="fas fa-credit-card" style="color:#7C3AED"></i>
                       <span>Tarjeta</span><small>Cargo a tarjeta</small>
                   </div>
               </div>`,
        customClass:{popup:'swal-order'}, width:520,
        showCancelButton:true,
        confirmButtonText:'<i class="fas fa-save"></i> Guardar',
        cancelButtonText:'Cancelar',
        confirmButtonColor:'#E67E22',
        didOpen: bindPayOpts,
        preConfirm() {
            const sel = document.querySelector('.pay-opt.selected');
            if (!sel) { Swal.showValidationMessage('Selecciona el estado de pago'); return false; }
            return sel.dataset.val;
        }
    });
    if (newPs && newPs !== ps) await doChange(order.id, 'payment_status', newPs);
}

/* ── DELIVERY MODAL ── */
async function openDelModal(orderId) {
    const order = ALL_ORDERS.find(o => o.id === orderId);
    if (!order) return;
    const ds = order.delivery_status || 'pending';

    const {value: newDs} = await Swal.fire({
        title: '<i class="fas fa-truck" style="color:#047857"></i> Estado de Entrega',
        html: `<p style="color:#6c8695;font-size:.85rem;margin-bottom:.8rem">
                   Pedido <strong>${esc(order.id)}</strong> — <em>${esc(order.customer?.name||'')}</em>
               </p>
               <div class="pay-grid" style="grid-template-columns:repeat(3,1fr)">
                   <div class="pay-opt${ds==='pending'?' selected':''}" data-val="pending">
                       <i class="fas fa-box" style="color:#D97706"></i>
                       <span>Pendiente</span><small>Por entregar</small>
                   </div>
                   <div class="pay-opt${ds==='delivered'?' selected':''}" data-val="delivered">
                       <i class="fas fa-check-circle" style="color:#059669"></i>
                       <span>Entregado</span><small>Completado</small>
                   </div>
                   <div class="pay-opt${ds==='cancelled'?' selected':''}" data-val="cancelled">
                       <i class="fas fa-times-circle" style="color:#DC2626"></i>
                       <span>Cancelado</span><small>Sin entregar</small>
                   </div>
               </div>`,
        customClass:{popup:'swal-order'}, width:480,
        showCancelButton:true,
        confirmButtonText:'<i class="fas fa-save"></i> Guardar',
        cancelButtonText:'Cancelar',
        confirmButtonColor:'#047857',
        didOpen: bindPayOpts,
        preConfirm() {
            const sel = document.querySelector('.pay-opt.selected');
            if (!sel) { Swal.showValidationMessage('Selecciona el estado de entrega'); return false; }
            return sel.dataset.val;
        }
    });
    if (newDs && newDs !== ds) await doChange(order.id, 'delivery_status', newDs);
}

function bindPayOpts() {
    document.querySelectorAll('.pay-opt').forEach(el => {
        el.addEventListener('click', () => {
            document.querySelectorAll('.pay-opt').forEach(x => x.classList.remove('selected'));
            el.classList.add('selected');
        });
    });
}

/* ── API CALL ── */
async function doChange(orderId, field, value) {
    const body = {order_id: orderId};
    body[field] = value;
    const res  = await fetch('/catalogodigsistema/api/update_order.php', {
        method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
    });
    const data = await res.json();
    if (data.success) {
        const idx = ALL_ORDERS.findIndex(o => o.id === orderId);
        if (idx !== -1) ALL_ORDERS[idx][field] = value;
        Swal.fire({icon:'success',title:'Actualizado',timer:900,showConfirmButton:false,toast:true,position:'top-end'})
            .then(() => location.reload());
    } else {
        Swal.fire('Error', data.error||'No se pudo actualizar','error');
    }
}

function pdfValue(value, fallback = '—') {
    const text = String(value || '').trim();
    return text || fallback;
}

function pdfDate(value) {
    if (!value) return '—';
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('es-MX') + ' ' + date.toLocaleTimeString('es-MX', {hour:'2-digit', minute:'2-digit'});
}

function companyDisplayName() {
    return pdfValue(COMPANY.commercial_name || COMPANY.legal_name, 'AutoRepuestos Pro');
}

function companyAddress() {
    return [
        COMPANY.street,
        COMPANY.neighborhood ? 'Col. ' + COMPANY.neighborhood : '',
        COMPANY.city,
        COMPANY.state,
        COMPANY.zip ? 'CP ' + COMPANY.zip : ''
    ].filter(Boolean).join(', ') || '—';
}

function companyTicketSections() {
    return [
        [
            'Razón Social: ' + pdfValue(COMPANY.legal_name) + '\n' +
            'Nombre Comercial: ' + companyDisplayName() + '\n' +
            'RFC: ' + pdfValue(COMPANY.rfc) + '\n' +
            'Giro / Industria: ' + pdfValue(COMPANY.industry) + '\n' +
            'Tamaño: ' + pdfValue(COMPANY.company_size) + '\n' +
            'Empleados: ' + pdfValue(COMPANY.employee_count),
            'Calle: ' + pdfValue(COMPANY.street) + '\n' +
            'Colonia: ' + pdfValue(COMPANY.neighborhood) + '\n' +
            'Ciudad: ' + pdfValue(COMPANY.city) + '\n' +
            'Estado: ' + pdfValue(COMPANY.state) + '\n' +
            'Código Postal: ' + pdfValue(COMPANY.zip),
            'Web: ' + pdfValue(COMPANY.website) + '\n' +
            'Correo empresa: ' + pdfValue(COMPANY.company_email) + '\n' +
            'Teléfono empresa: ' + pdfValue(COMPANY.company_phone) + '\n' +
            'Contacto: ' + pdfValue(COMPANY.contact_name) + '\n' +
            'Puesto: ' + pdfValue(COMPANY.contact_position) + '\n' +
            'Correo contacto: ' + pdfValue(COMPANY.contact_email) + '\n' +
            'Teléfono contacto: ' + pdfValue(COMPANY.contact_phone),
        ],
        [
            'Medio de Captación: ' + pdfValue(COMPANY.lead_source),
            'Ejecutivo Asignado: ' + pdfValue(COMPANY.assigned_executive),
            'Estatus: ' + pdfValue(COMPANY.status) + '\n' +
            'Observaciones: ' + pdfValue(COMPANY.observations),
        ],
        [
            'Fecha de Registro: ' + pdfDate(COMPANY.registered_at),
            'Usuario que Registró: ' + pdfValue(COMPANY.registered_by),
            'Fecha de Actualización: ' + pdfDate(COMPANY.updated_at),
        ],
    ];
}

/* ── PDF GENERATION ── */
function generatePDF(orderId) {
    const order = ALL_ORDERS.find(o => o.id === orderId);
    if (!order) return;
    const {jsPDF} = window.jspdf;
    const doc = new jsPDF({orientation:'portrait',unit:'mm',format:'a4'});
    const W = 210, M = 15;
    let y = M;

    doc.setFillColor(26,44,62);
    doc.rect(0,0,W,28,'F');
    doc.setTextColor(255,255,255);
    doc.setFont('helvetica','bold'); doc.setFontSize(15);
    doc.text(companyDisplayName(), M, 13);
    doc.setFont('helvetica','normal'); doc.setFontSize(8.5);
    doc.text('Orden de Pedido / Ticket Oficial', M, 20);
    if (COMPANY.rfc) doc.text('RFC: '+COMPANY.rfc, M, 25);
    doc.text('Folio: '+order.id, W-M, 13, {align:'right'});
    doc.text(new Date(order.created_at).toLocaleDateString('es-MX'), W-M, 20, {align:'right'});

    y = 36;
    doc.setTextColor(26,44,62);

    // Payment pill
    const psC = {pending:[217,119,6],transfer:[37,99,235],cash:[5,150,105],card:[124,58,237]};
    const [r,g,b] = psC[order.payment_status||'pending']||[100,100,100];
    doc.setFillColor(r,g,b);
    doc.roundedRect(M,y-5,44,8,2,2,'F');
    doc.setTextColor(255,255,255); doc.setFontSize(7.5); doc.setFont('helvetica','bold');
    doc.text('Pago: '+(PAY_LABELS[order.payment_status||'pending']||'?'), M+22, y, {align:'center'});

    // Delivery pill
    const dsC = {pending:[194,65,12],delivered:[4,120,87],cancelled:[220,38,38]};
    const [r2,g2,b2] = dsC[order.delivery_status||'pending']||[100,100,100];
    doc.setFillColor(r2,g2,b2);
    doc.roundedRect(M+48,y-5,42,8,2,2,'F');
    doc.text('Entrega: '+(DEL_LABELS[order.delivery_status||'pending']||'?'), M+48+21, y, {align:'center'});

    doc.setTextColor(26,44,62);
    doc.setFont('helvetica','normal'); doc.setFontSize(8.5);
    const chMap = {whatsapp:'WhatsApp',transfer:'Transferencia',card:'Tarjeta',cash:'Efectivo'};
    doc.text('Canal: '+(chMap[order.payment_method]||order.payment_method), W-M, y, {align:'right'});
    y += 12;

    doc.autoTable({
        startY:y,
        head:[['Datos de la empresa','Dirección','Contacto / Comercial']],
        body: companyTicketSections(),
        theme:'grid',
        margin:{left:M,right:M},
        headStyles:{fillColor:[26,44,62],fontStyle:'bold',fontSize:7.5},
        bodyStyles:{fontSize:7,cellPadding:2.2,valign:'top'},
        columnStyles:{0:{cellWidth:60},1:{cellWidth:48},2:{cellWidth:'auto'}},
    });
    y = doc.lastAutoTable.finalY + 8;

    // Customer + order boxes
    const half = (W-M*2)/2;
    doc.setFillColor(245,248,250);
    doc.roundedRect(M,y,half-3,28,3,3,'F');
    doc.setFont('helvetica','bold'); doc.setFontSize(8.5);
    doc.text('Datos del cliente',M+4,y+7);
    doc.setFont('helvetica','normal'); doc.setFontSize(8);
    const c=order.customer||{};
    doc.text('Nombre: '+(c.name||'—'),M+4,y+14);
    doc.text('Email:  '+(c.email||'—'),M+4,y+20);
    const addr=[c.street,c.city,c.zip?'CP '+c.zip:''].filter(Boolean).join(', ');
    doc.text('Dir:    '+(addr||'—'),M+4,y+26);

    const bx=M+half+3;
    doc.setFillColor(255,248,243);
    doc.roundedRect(bx,y,half-3,28,3,3,'F');
    doc.setFont('helvetica','bold'); doc.setFontSize(8.5);
    doc.text('Resumen del pedido',bx+4,y+7);
    doc.setFont('helvetica','normal'); doc.setFontSize(8);
    doc.text('Folio:   '+order.id,bx+4,y+14);
    doc.text('Número:  #'+order.number,bx+4,y+20);
    if(order.paid_at) doc.text('Pagado:  '+new Date(order.paid_at).toLocaleDateString('es-MX'),bx+4,y+26);
    y+=36;

    doc.setFont('helvetica','bold'); doc.setFontSize(10);
    doc.text('Productos del pedido',M,y); y+=4;
    doc.autoTable({
        startY:y,
        head:[['Producto','Cant.','Precio unit.','Subtotal']],
        body: order.items.map(i=>[i.name,i.quantity,'$'+parseFloat(i.price).toLocaleString('es-MX',{minimumFractionDigits:2}),'$'+(i.price*i.quantity).toLocaleString('es-MX',{minimumFractionDigits:2})]),
        foot:[['','','TOTAL','$'+parseFloat(order.total).toLocaleString('es-MX',{minimumFractionDigits:2})]],
        theme:'grid', margin:{left:M,right:M},
        headStyles:{fillColor:[26,44,62],fontStyle:'bold',fontSize:8.5},
        footStyles:{fillColor:[192,57,43],textColor:[255,255,255],fontStyle:'bold',fontSize:9},
        bodyStyles:{fontSize:8.5},
        columnStyles:{0:{cellWidth:'auto'},1:{halign:'center',cellWidth:16},2:{halign:'right',cellWidth:28},3:{halign:'right',cellWidth:28}},
        alternateRowStyles:{fillColor:[250,252,252]},
    });
    y=doc.lastAutoTable.finalY+10;
    doc.setFontSize(7); doc.setTextColor(150); doc.setFont('helvetica','normal');
    doc.text(companyDisplayName()+' · '+pdfValue(COMPANY.company_phone, 'Refacciones originales')+' · '+pdfValue(COMPANY.company_email, 'Calidad garantizada'), W/2, y, {align:'center'});
    doc.text('Generado el '+new Date().toLocaleDateString('es-MX'), W/2, y+4.5, {align:'center'});
    doc.save('Pedido-'+order.id+'.pdf');
}

function esc(s){return (s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}
</script>
</body>
</html>
