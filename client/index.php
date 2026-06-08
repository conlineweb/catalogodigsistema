<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireClientLogin();

$user = currentUser();
if (!$user) { doLogout(); header('Location: /catalogodigsistema/client/login.php'); exit; }

// Load client's order(s) — clients have one order_id, admins see all
$orders = readJson('orders');
if ($user['role'] === 'admin') {
    $myOrders = array_reverse($orders);
} else {
    $myOrders = array_filter($orders, fn($o) => $o['id'] === ($user['order_id'] ?? ''));
    $myOrders = array_values(array_reverse($myOrders));
}

$paymentLabels = ['pending'=>'Pendiente de pago','transfer'=>'Pagado (Transferencia)','cash'=>'Pagado (Efectivo)','card'=>'Pagado (Tarjeta)'];
$paymentColors = ['pending'=>'#D97706','transfer'=>'#2563EB','cash'=>'#059669','card'=>'#7C3AED'];
$paymentBg     = ['pending'=>'#FEF3C7','transfer'=>'#DBEAFE','cash'=>'#D1FAE5','card'=>'#EDE9FE'];
$deliveryLabels= ['pending'=>'En preparación','delivered'=>'Entregado','cancelled'=>'Cancelado'];
$deliveryColors= ['pending'=>'#C2410C','delivered'=>'#047857','cancelled'=>'#DC2626'];
$deliveryBg    = ['pending'=>'#FFF7ED','delivered'=>'#A7F3D0','cancelled'=>'#FEE2E2'];
$methodLabels  = ['whatsapp'=>'WhatsApp','transfer'=>'Transferencia Bancaria','card'=>'Tarjeta','cash'=>'Efectivo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Pedidos · AutoRepuestos Pro</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#f0f4f8;color:#1a2c3e;min-height:100vh}
.topbar{background:#1a2c3e;padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem}
.brand{display:flex;align-items:center;gap:8px;font-weight:800;color:#fff;font-size:1.1rem}
.brand i{color:#E67E22}
.user-info{display:flex;align-items:center;gap:.8rem}
.user-chip{background:rgba(255,255,255,.1);padding:.4rem 1rem;border-radius:40px;font-size:.82rem;font-weight:600;color:#fff;display:flex;align-items:center;gap:6px}
.btn-logout{background:rgba(192,57,43,.3);border:none;color:#fff;padding:.4rem .8rem;border-radius:30px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;text-decoration:none;display:flex;align-items:center;gap:6px}
.btn-logout:hover{background:#C0392B}
.container{max-width:860px;margin:0 auto;padding:2rem 1.5rem}
.page-title{font-size:1.6rem;font-weight:800;margin-bottom:1.6rem;display:flex;align-items:center;gap:10px}
.page-title i{color:#E67E22}
.empty-state{text-align:center;padding:4rem;background:#fff;border-radius:24px;box-shadow:0 2px 12px rgba(0,0,0,.04)}
.empty-state i{font-size:3rem;color:#e2ecea;margin-bottom:1rem}
.empty-state p{color:#aab;font-size:.95rem}
.empty-state a{color:#E67E22;text-decoration:none;font-weight:600}
.order-card{background:#fff;border-radius:24px;box-shadow:0 2px 12px rgba(0,0,0,.04);margin-bottom:1.4rem;overflow:hidden}
.order-header{padding:1.2rem 1.4rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;border-bottom:1px solid #f0f4f8}
.order-id{font-weight:800;font-size:.95rem;color:#1a2c3e}
.order-date{font-size:.78rem;color:#aaa;margin-top:.1rem}
.badge{display:inline-flex;align-items:center;padding:5px 14px;border-radius:30px;font-size:.78rem;font-weight:700}
.order-body{padding:1.4rem}
.section-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#aaa;margin-bottom:.6rem}
.items-list{display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.2rem}
.item-row{display:flex;justify-content:space-between;align-items:center;font-size:.88rem;padding:.4rem 0;border-bottom:1px solid #f7f9fb}
.item-row:last-child{border-bottom:none}
.item-name{font-weight:600}
.item-qty{color:#aaa;font-size:.78rem}
.item-price{font-weight:700;color:#C0392B}
.order-total{display:flex;justify-content:space-between;align-items:center;padding:.8rem 0;border-top:2px solid #f0f4f8;font-weight:800;font-size:1.1rem;color:#1a2c3e}
.order-total span:last-child{color:#C0392B}
.order-meta{display:flex;gap:1rem;flex-wrap:wrap;margin-top:.8rem}
.meta-item{font-size:.82rem;color:#6c8695;display:flex;align-items:center;gap:5px}
.meta-item i{color:#E67E22}
.transfer-notice{background:#EFF6FF;border:1px solid #BFDBFE;border-radius:16px;padding:1rem;font-size:.85rem;color:#1D4ED8;margin-top:1rem;line-height:1.6}
.transfer-notice strong{display:block;margin-bottom:.3rem}
.steps{display:flex;gap:1rem;margin-top:1.4rem;flex-wrap:wrap}
.step{flex:1;min-width:140px;text-align:center;padding:1rem;background:#f8fcfb;border-radius:16px;border:1px solid #e2ecea}
.step-num{width:36px;height:36px;background:#E67E22;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;margin:0 auto .5rem}
.step.done .step-num{background:#059669}
.step.done{border-color:#6EE7B7;background:#f0fdf4}
.step-label{font-size:.78rem;font-weight:600;color:#2c4b57}
.back-link{display:inline-flex;align-items:center;gap:6px;color:#E67E22;text-decoration:none;font-weight:600;font-size:.87rem;margin-bottom:1rem}
.back-link:hover{color:#C0392B}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand"><i class="fas fa-car"></i> AutoRepuestos Pro</div>
  <div class="user-info">
    <div class="user-chip"><i class="fas fa-user"></i> <?= htmlspecialchars($user['name']) ?></div>
    <a class="btn-logout" href="/catalogodigsistema/api/logout.php?role=client"><i class="fas fa-sign-out-alt"></i> Salir</a>
  </div>
</div>

<div class="container">
  <a class="back-link" href="/catalogodigsistema/index.php"><i class="fas fa-arrow-left"></i> Volver a la tienda</a>
  <h1 class="page-title"><i class="fas fa-box-open"></i> Mis Pedidos</h1>

  <?php if (empty($myOrders)): ?>
  <div class="empty-state">
    <i class="fas fa-box-open"></i>
    <p>No tienes pedidos registrados aún.<br>
    <a href="/catalogodigsistema/index.php">Visita nuestra tienda</a> para hacer tu primer pedido.</p>
  </div>
  <?php else: ?>
  <?php foreach ($myOrders as $o):
    $ps  = $o['payment_status']  ?? 'pending';
    $ds  = $o['delivery_status'] ?? 'pending';
    $payBg = $paymentBg[$ps]  ?? '#f5f5f5';
    $payCl = $paymentColors[$ps] ?? '#aaa';
    $payLb = $paymentLabels[$ps] ?? $ps;
    $delBg = $deliveryBg[$ds]  ?? '#f5f5f5';
    $delCl = $deliveryColors[$ds] ?? '#aaa';
    $delLb = $deliveryLabels[$ds] ?? $ds;
    $cd    = new DateTime($o['created_at']);
    $steps = [
      ['label'=>'Pedido recibido', 'done'=>true],
      ['label'=>'Pago confirmado', 'done'=>$ps !== 'pending'],
      ['label'=>'En preparación',  'done'=>$ds !== 'pending'],
      ['label'=>'Entregado',        'done'=>$ds === 'delivered'],
    ];
  ?>
  <div class="order-card">
    <div class="order-header">
      <div>
        <div class="order-id"><i class="fas fa-receipt" style="color:#E67E22;margin-right:5px"></i><?= htmlspecialchars($o['id']) ?></div>
        <div class="order-date"><?= $cd->format('d/m/Y \a \l\a\s H:i') ?></div>
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <span class="badge" style="background:<?= $payBg ?>;color:<?= $payCl ?>"><?= htmlspecialchars($payLb) ?></span>
        <span class="badge" style="background:<?= $delBg ?>;color:<?= $delCl ?>"><?= htmlspecialchars($delLb) ?></span>
      </div>
    </div>
    <div class="order-body">

      <!-- Progress steps -->
      <div class="steps">
        <?php foreach ($steps as $step): ?>
        <div class="step <?= $step['done'] ? 'done' : '' ?>">
          <div class="step-num"><?= $step['done'] ? '<i class="fas fa-check"></i>' : '·' ?></div>
          <div class="step-label"><?= $step['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Items -->
      <div style="margin-top:1.4rem">
        <div class="section-label">Productos</div>
        <div class="items-list">
          <?php foreach ($o['items'] as $item): ?>
          <div class="item-row">
            <div>
              <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="item-qty">Cantidad: <?= $item['quantity'] ?></div>
            </div>
            <div class="item-price">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="order-total">
          <span>Total del pedido</span>
          <span>$<?= number_format($o['total'], 2) ?> MXN</span>
        </div>
      </div>

      <!-- Meta info -->
      <div class="order-meta">
        <div class="meta-item">
          <i class="fas fa-credit-card"></i>
          <?= htmlspecialchars($methodLabels[$o['payment_method']] ?? $o['payment_method']) ?>
        </div>
        <?php if (!empty($o['customer']['city'])): ?>
        <div class="meta-item">
          <i class="fas fa-location-dot"></i>
          <?= htmlspecialchars($o['customer']['city']) ?>
          <?php if (!empty($o['customer']['zip'])): ?> · CP <?= htmlspecialchars($o['customer']['zip']) ?><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Transfer notice -->
      <?php if ($ps === 'pending' && ($o['payment_method'] ?? '') === 'transfer'): ?>
      <div class="transfer-notice">
        <strong><i class="fas fa-university"></i> Instrucciones de pago por transferencia</strong>
        <strong>Banco:</strong> BBVA México · <strong>CLABE:</strong> 012 3456 789012345678<br>
        <strong>Cuenta:</strong> 1234567890 · <strong>Beneficiario:</strong> AutoRepuestos Pro S.A. de C.V.<br>
        <strong>Monto exacto:</strong> $<?= number_format($o['total'], 2) ?> MXN<br>
        Envía tu comprobante de pago por WhatsApp al <strong>4771181285</strong>.
        Tu pedido se procesará al confirmar el pago.
      </div>
      <?php endif; ?>

    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

</div>
</body>
</html>
