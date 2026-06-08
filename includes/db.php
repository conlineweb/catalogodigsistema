<?php
define('BASE_PATH', dirname(__DIR__));
define('DATA_DIR',  BASE_PATH . '/data/');

/* ── Low-level JSON helpers ── */
function readJson(string $name): array {
    $file = DATA_DIR . $name . '.json';
    if (!file_exists($file)) return [];
    $raw = @file_get_contents($file);
    return ($raw !== false) ? (json_decode($raw, true) ?: []) : [];
}

function writeJson(string $name, array $data): bool {
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    $file = DATA_DIR . $name . '.json';
    return file_put_contents(
        $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

/* ── Initialise default data on first run ── */
function initDb(): void {
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    if (!file_exists(DATA_DIR . 'orders.json'))   writeJson('orders',   []);
    if (!file_exists(DATA_DIR . 'activity.json')) writeJson('activity', []);
    if (!file_exists(DATA_DIR . 'users.json')) {
        writeJson('users', [[
            'id'            => 'USR-ADMIN-001',
            'username'      => 'admin',
            'password_hash' => password_hash('Admin2024!', PASSWORD_DEFAULT),
            'role'          => 'admin',
            'name'          => 'Administrador',
            'email'         => 'admin@autorepuestospro.mx',
            'order_id'      => null,
            'created_at'    => date('c'),
        ]]);
    }
}

/* ── Order helpers ── */
function nextOrderNumber(): int {
    return count(readJson('orders')) + 1;
}

function generateOrderId(int $number): string {
    return 'ORD-' . date('Ymd') . '-' . str_pad((string)$number, 4, '0', STR_PAD_LEFT);
}

/* ── Activity log ── */
function logActivity(string $action, string $orderId, string $user, string $details = ''): void {
    $log   = readJson('activity');
    $log[] = [
        'timestamp' => date('c'),
        'action'    => $action,
        'order_id'  => $orderId,
        'user'      => $user,
        'details'   => $details,
    ];
    if (count($log) > 2000) $log = array_slice($log, -2000);
    writeJson('activity', $log);
}

/* ── Input sanitising ── */
function sanitize(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

initDb();
