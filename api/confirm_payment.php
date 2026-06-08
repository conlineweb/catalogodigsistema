<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

startSession();
if (!isAdmin()) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'error' => 'No autorizado']));
}

$body    = json_decode(file_get_contents('php://input'), true) ?: [];
$orderId = sanitize($body['order_id'] ?? '');
if (empty($orderId)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'order_id requerido']));
}

$orders = readJson('orders');
$found  = false;
foreach ($orders as &$order) {
    if ($order['id'] === $orderId) {
        $order['payment_status'] = 'transfer';
        $order['paid_at']        = date('c');
        $order['activity'][] = [
            'timestamp' => date('c'),
            'action'    => 'payment_confirmed',
            'user'      => $_SESSION['username'],
            'note'      => 'Pago por transferencia confirmado por administrador',
        ];
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
logActivity('payment_confirmed', $orderId, $_SESSION['username'], 'Transferencia bancaria confirmada');
echo json_encode(['success' => true]);
