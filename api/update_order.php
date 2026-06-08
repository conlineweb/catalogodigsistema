<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

startSession();
if (!isAdmin()) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'error' => 'No autorizado']));
}

$body           = json_decode(file_get_contents('php://input'), true) ?: [];
$orderId        = sanitize($body['order_id']      ?? '');
$paymentStatus  = $body['payment_status']          ?? null;
$deliveryStatus = $body['delivery_status']         ?? null;
$note           = sanitize($body['note']           ?? '');

$validPayment  = ['pending', 'transfer', 'cash', 'card'];
$validDelivery = ['pending', 'delivered', 'cancelled'];

if (empty($orderId)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'order_id requerido']));
}
if ($paymentStatus !== null && !in_array($paymentStatus, $validPayment, true)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Estado de pago inválido']));
}
if ($deliveryStatus !== null && !in_array($deliveryStatus, $validDelivery, true)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Estado de entrega inválido']));
}

$payLabels = ['pending'=>'Pendiente','transfer'=>'Transferencia','cash'=>'Efectivo','card'=>'Tarjeta'];
$delLabels = ['pending'=>'Pendiente','delivered'=>'Entregado','cancelled'=>'Cancelado'];

$orders = readJson('orders');
$found  = false;
foreach ($orders as &$order) {
    if ($order['id'] === $orderId) {

        if ($paymentStatus !== null) {
            $prev = $order['payment_status'] ?? 'pending';
            $order['payment_status'] = $paymentStatus;
            if ($paymentStatus !== 'pending' && empty($order['paid_at'])) {
                $order['paid_at'] = date('c');
            }
            $order['activity'][] = [
                'timestamp' => date('c'),
                'action'    => 'payment_change',
                'user'      => $_SESSION['username'],
                'note'      => 'Pago: '.($payLabels[$prev]??$prev).' → '.($payLabels[$paymentStatus]??$paymentStatus).($note ? " — $note" : ''),
            ];
        }

        if ($deliveryStatus !== null) {
            $prev = $order['delivery_status'] ?? 'pending';
            $order['delivery_status'] = $deliveryStatus;
            if ($deliveryStatus === 'delivered' && empty($order['delivered_at'])) {
                $order['delivered_at'] = date('c');
            }
            $order['activity'][] = [
                'timestamp' => date('c'),
                'action'    => 'delivery_change',
                'user'      => $_SESSION['username'],
                'note'      => 'Entrega: '.($delLabels[$prev]??$prev).' → '.($delLabels[$deliveryStatus]??$deliveryStatus).($note ? " — $note" : ''),
            ];
        }

        if ($note && $paymentStatus === null && $deliveryStatus === null) {
            $order['notes'] = $note;
            $order['activity'][] = [
                'timestamp' => date('c'),
                'action'    => 'note',
                'user'      => $_SESSION['username'],
                'note'      => "Nota: $note",
            ];
        }

        $found = true;
        break;
    }
}
unset($order);

if (!$found) {
    http_response_code(404);
    exit(json_encode(['success' => false, 'error' => 'Pedido no encontrado']));
}

writeJson('orders', $orders);
logActivity('order_updated', $orderId, $_SESSION['username'], 'Actualización de pedido');
echo json_encode(['success' => true]);
