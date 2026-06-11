<?php
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];

/* ── Validate items ── */
$rawItems = $body['items'] ?? [];
if (empty($rawItems) || !is_array($rawItems)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Sin productos']));
}

$items = [];
$total = 0.0;
$catalog = productsForCatalog();
$catalogById = [];
foreach ($catalog as $product) {
    $catalogById[intval($product['id'] ?? 0)] = $product;
}
foreach ($rawItems as $item) {
    $productId = intval($item['id'] ?? 0);
    $catalogProduct = $catalogById[$productId] ?? null;
    if (!$catalogProduct) continue;
    $price = round(floatval($catalogProduct['price'] ?? 0), 2);
    $qty   = max(1, min(99, intval($item['quantity'] ?? 1)));
    if ($price <= 0) continue;
    $items[] = [
        'id'       => $productId,
        'name'     => sanitize($catalogProduct['name'] ?? ''),
        'price'    => $price,
        'quantity' => $qty,
    ];
    $total += $price * $qty;
}

if (empty($items)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Productos inválidos']));
}

/* ── Customer data ── */
$rawCustomer = $body['customer'] ?? [];
$customer = [
    'name'   => sanitize($rawCustomer['name']   ?? ''),
    'email'  => sanitize(filter_var($rawCustomer['email'] ?? '', FILTER_SANITIZE_EMAIL)),
    'street' => sanitize($rawCustomer['street'] ?? ''),
    'city'   => sanitize($rawCustomer['city']   ?? ''),
    'zip'    => sanitize($rawCustomer['zip']    ?? ''),
];

/* ── Order channel: public checkout is WhatsApp only ── */
$paymentMethod = 'whatsapp';

/* ── Initial statuses ── */
$paymentStatus  = 'pending';
$deliveryStatus = 'pending';

/* ── Create order ── */
$number  = nextOrderNumber();
$orderId = generateOrderId($number);
$access  = createClientUser($orderId, $customer['name'] ?: 'Cliente', $customer['email']);

$order = [
    'id'             => $orderId,
    'number'         => $number,
    'created_at'     => date('c'),
    'customer'       => $customer,
    'items'          => $items,
    'total'           => round($total, 2),
    'payment_method'  => $paymentMethod,
    'payment_status'  => $paymentStatus,
    'delivery_status' => $deliveryStatus,
    'access'          => $access,
    'notes'          => '',
    'activity'       => [[
        'timestamp' => date('c'),
        'action'    => 'created',
        'user'      => 'system',
        'note'      => 'Pedido creado desde tienda web',
    ]],
];

if ($paymentStatus !== 'pending') {
    $order['paid_at'] = date('c');
}

$orders   = readJson('orders');
$orders[] = $order;
writeJson('orders', $orders);
logActivity('order_created', $orderId, 'system', "Método: $paymentMethod | Total: $" . number_format($total, 2));

echo json_encode([
    'success'      => true,
    'order_id'     => $orderId,
    'order_number' => $number,
    'total'        => round($total, 2),
    'access'       => $access,
]);
