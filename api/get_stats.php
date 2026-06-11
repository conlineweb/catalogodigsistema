<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

startSession();
if (!isAdmin()) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'error' => 'No autorizado']));
}

$allOrders = readJson('orders');
$startParam = trim($_GET['start'] ?? '');
$endParam   = trim($_GET['end'] ?? '');
$period     = $_GET['period'] ?? 'day';
$validPeriods = ['day', 'week', 'month'];
if (!in_array($period, $validPeriods, true)) $period = 'day';

$startTs = $startParam ? strtotime($startParam . ' 00:00:00') : null;
$endTs   = $endParam ? strtotime($endParam . ' 23:59:59') : null;
if ($startTs === false) {
    $startTs = null;
    $startParam = '';
}
if ($endTs === false) {
    $endTs = null;
    $endParam = '';
}

$orders = array_values(array_filter($allOrders, function($order) use ($startTs, $endTs) {
    $created = strtotime($order['created_at'] ?? '');
    if (!$created) return false;
    if ($startTs !== null && $created < $startTs) return false;
    if ($endTs !== null && $created > $endTs) return false;
    return true;
}));

$stats = [
    'total_orders'   => count($orders),
    'total_catalog_orders' => count($allOrders),
    'pay_pending'    => 0,
    'pay_transfer'   => 0,
    'pay_cash'       => 0,
    'pay_card'       => 0,
    'del_pending'    => 0,
    'del_delivered'  => 0,
    'del_cancelled'  => 0,
    'total_revenue'  => 0.0,
    'paid_revenue'            => 0.0,
    'revenue_by_pay_status'   => ['pending'=>0.0,'transfer'=>0.0,'cash'=>0.0,'card'=>0.0],
    'methods'                 => ['whatsapp' => 0, 'transfer' => 0, 'card' => 0, 'cash' => 0],
    'by_day'         => [],
    'by_period'      => [],
    'recent_orders'  => [],
    'range'          => ['start' => $startParam, 'end' => $endParam, 'period' => $period],
    'average_order'  => 0.0,
    'whatsapp_orders'=> 0,
];

$dayMap = [];
$periodMap = [];
foreach ($orders as $o) {
    $ps = $o['payment_status']  ?? 'pending';
    $ds = $o['delivery_status'] ?? 'pending';
    $payKey = 'pay_' . $ps;
    if (isset($stats[$payKey])) $stats[$payKey]++;
    $delKey = 'del_' . $ds;
    if (isset($stats[$delKey])) $stats[$delKey]++;
    $stats['total_revenue'] += (float)$o['total'];
    if ($ps !== 'pending') {
        $stats['paid_revenue'] += (float)$o['total'];
    }
    $stats['revenue_by_pay_status'][$ps] = ($stats['revenue_by_pay_status'][$ps] ?? 0) + (float)$o['total'];
    $m = $o['payment_method'] ?? 'whatsapp';
    if (isset($stats['methods'][$m])) $stats['methods'][$m]++;
    if ($m === 'whatsapp') $stats['whatsapp_orders']++;
    $day = date('Y-m-d', strtotime($o['created_at']));
    $dayMap[$day] = ($dayMap[$day] ?? 0) + 1;

    $createdTs = strtotime($o['created_at']);
    if ($period === 'week') {
        $periodKey = date('o-\WW', $createdTs);
        $periodLabel = 'Sem. ' . date('W/Y', $createdTs);
    } elseif ($period === 'month') {
        $periodKey = date('Y-m', $createdTs);
        $periodLabel = date('m/Y', $createdTs);
    } else {
        $periodKey = date('Y-m-d', $createdTs);
        $periodLabel = date('d/m', $createdTs);
    }
    if (!isset($periodMap[$periodKey])) {
        $periodMap[$periodKey] = ['date' => $periodLabel, 'count' => 0, 'revenue' => 0.0];
    }
    $periodMap[$periodKey]['count']++;
    $periodMap[$periodKey]['revenue'] += (float)$o['total'];
}

ksort($periodMap);
foreach ($periodMap as $row) {
    $row['revenue'] = round($row['revenue'], 2);
    $stats['by_period'][] = $row;
}

// Backwards compatible chart data. Defaults to last 7 days when no range is selected.
if ($startTs === null && $endTs === null && $period === 'day') {
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-{$i} days"));
        $stats['by_day'][] = ['date' => date('d/m', strtotime($day)), 'count' => $dayMap[$day] ?? 0];
    }
} else {
    $stats['by_day'] = array_map(fn($row) => ['date' => $row['date'], 'count' => $row['count']], $stats['by_period']);
}

$stats['conversion'] = $stats['total_orders'] > 0
    ? round(($stats['total_orders'] - $stats['pay_pending']) / $stats['total_orders'] * 100, 1)
    : 0;

$stats['total_revenue'] = round($stats['total_revenue'], 2);
$stats['paid_revenue']  = round($stats['paid_revenue'], 2);
$stats['average_order'] = $stats['total_orders'] > 0 ? round($stats['total_revenue'] / $stats['total_orders'], 2) : 0.0;
foreach ($stats['revenue_by_pay_status'] as &$v) { $v = round($v, 2); }
unset($v);

// All orders compact (for dashboard card drill-down)
$stats['all_orders'] = array_map(fn($o) => [
    'id'              => $o['id'],
    'number'          => $o['number'],
    'customer_name'   => $o['customer']['name'] ?: 'Sin nombre',
    'total'           => $o['total'],
    'payment_status'  => $o['payment_status']  ?? 'pending',
    'delivery_status' => $o['delivery_status'] ?? 'pending',
    'payment_method'  => $o['payment_method']  ?? 'whatsapp',
    'created_at'      => $o['created_at'],
], array_reverse($orders));

// Recent 10
$recent = array_slice(array_reverse($orders), 0, 10);
$stats['recent_orders'] = array_map(fn($o) => [
    'id'              => $o['id'],
    'number'          => $o['number'],
    'customer_name'   => $o['customer']['name'] ?: 'Sin nombre',
    'total'           => $o['total'],
    'payment_status'  => $o['payment_status']  ?? 'pending',
    'delivery_status' => $o['delivery_status'] ?? 'pending',
    'payment_method'  => $o['payment_method']  ?? 'whatsapp',
    'created_at'      => $o['created_at'],
], $recent);

echo json_encode($stats);
