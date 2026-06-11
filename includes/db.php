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

function defaultProducts(): array {
    return [
        ['id'=>1, 'name'=>'Bujía Iridium', 'desc'=>'Juego de 4 bujías, alto rendimiento', 'price'=>450, 'icon'=>'fas fa-plug', 'image'=>'', 'category'=>'motor'],
        ['id'=>2, 'name'=>'Filtro de Aceite', 'desc'=>'Filtro de alta capacidad', 'price'=>180, 'icon'=>'fas fa-filter', 'image'=>'', 'category'=>'motor'],
        ['id'=>3, 'name'=>'Correa de Distribución', 'desc'=>'Resistente a altas temperaturas', 'price'=>620, 'icon'=>'fas fa-cog', 'image'=>'', 'category'=>'motor'],
        ['id'=>4, 'name'=>'Bomba de Agua', 'desc'=>'Flujo óptimo, garantía 1 año', 'price'=>890, 'icon'=>'fas fa-tint', 'image'=>'', 'category'=>'motor'],
        ['id'=>5, 'name'=>'Amortiguador Delantero', 'desc'=>'Par de amortiguadores hidráulicos', 'price'=>1250, 'icon'=>'fas fa-car-side', 'image'=>'', 'category'=>'suspension'],
        ['id'=>6, 'name'=>'Espiral de Resorte', 'desc'=>'Resorte de acero reforzado', 'price'=>430, 'icon'=>'fas fa-wind', 'image'=>'', 'category'=>'suspension'],
        ['id'=>7, 'name'=>'Rótula de Dirección', 'desc'=>'Precisión en la conducción', 'price'=>320, 'icon'=>'fas fa-circle-nodes', 'image'=>'', 'category'=>'suspension'],
        ['id'=>8, 'name'=>'Barra Estabilizadora', 'desc'=>'Reduce balanceo, incluye bujes', 'price'=>580, 'icon'=>'fas fa-grip-lines', 'image'=>'', 'category'=>'suspension'],
        ['id'=>9, 'name'=>'Pastillas de Freno', 'desc'=>'Juego de 4 pastillas cerámicas', 'price'=>720, 'icon'=>'fas fa-shield-halved', 'image'=>'', 'category'=>'frenos'],
        ['id'=>10, 'name'=>'Disco de Freno', 'desc'=>'Disco ventilado de hierro', 'price'=>950, 'icon'=>'fas fa-compact-disc', 'image'=>'', 'category'=>'frenos'],
        ['id'=>11, 'name'=>'Líquido de Frenos DOT4', 'desc'=>'Botella 500ml, alto punto ebullición', 'price'=>110, 'icon'=>'fas fa-fill-drip', 'image'=>'', 'category'=>'frenos'],
        ['id'=>12, 'name'=>'Cilindro Maestro', 'desc'=>'Repuesto original para frenos', 'price'=>680, 'icon'=>'fas fa-cogs', 'image'=>'', 'category'=>'frenos'],
        ['id'=>13, 'name'=>'Batería 12V 60Ah', 'desc'=>'Libre mantenimiento', 'price'=>1890, 'icon'=>'fas fa-car-battery', 'image'=>'', 'category'=>'electrico'],
        ['id'=>14, 'name'=>'Alternador', 'desc'=>'140A, compatible múltiples marcas', 'price'=>2250, 'icon'=>'fas fa-bolt', 'image'=>'', 'category'=>'electrico'],
        ['id'=>15, 'name'=>'Sensor de Oxígeno', 'desc'=>'Mejora eficiencia combustible', 'price'=>860, 'icon'=>'fas fa-microchip', 'image'=>'', 'category'=>'electrico'],
        ['id'=>16, 'name'=>'Faros LED', 'desc'=>'Par de luces delanteras, 6000K', 'price'=>990, 'icon'=>'fas fa-lightbulb', 'image'=>'', 'category'=>'electrico'],
        ['id'=>17, 'name'=>'Espejo Lateral', 'desc'=>'Completo con ajuste eléctrico', 'price'=>550, 'icon'=>'fas fa-eye', 'image'=>'', 'category'=>'carroceria'],
        ['id'=>18, 'name'=>'Manija de Puerta', 'desc'=>'Cromada, izquierda o derecha', 'price'=>210, 'icon'=>'fas fa-hand', 'image'=>'', 'category'=>'carroceria'],
        ['id'=>19, 'name'=>'Limpiaparabrisas', 'desc'=>'Juego de 2, universal 22"', 'price'=>270, 'icon'=>'fas fa-rainbow', 'image'=>'', 'category'=>'carroceria'],
        ['id'=>20, 'name'=>'Defensa Delantera', 'desc'=>'Plástico reforzado, pintable', 'price'=>1670, 'icon'=>'fas fa-truck-pickup', 'image'=>'', 'category'=>'carroceria'],
    ];
}

function productsForCatalog(): array {
    $products = readJson('products');
    if (empty($products)) {
        $products = defaultProducts();
        writeJson('products', $products);
    }
    return $products;
}

/* ── Initialise default data on first run ── */
function initDb(): void {
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    if (!file_exists(DATA_DIR . 'orders.json'))   writeJson('orders',   []);
    if (!file_exists(DATA_DIR . 'activity.json')) writeJson('activity', []);
    if (!file_exists(DATA_DIR . 'products.json')) writeJson('products', defaultProducts());
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
