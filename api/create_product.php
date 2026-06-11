<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

startSession();
if (!isAdmin()) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'error' => 'No autorizado']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

$categoryIcons = [
    'motor'      => 'fas fa-oil-can',
    'suspension' => 'fas fa-car-side',
    'frenos'     => 'fas fa-shield-halved',
    'electrico'  => 'fas fa-car-battery',
    'carroceria' => 'fas fa-truck-pickup',
];

$name     = sanitize($_POST['name'] ?? '');
$desc     = sanitize($_POST['desc'] ?? '');
$category = sanitize($_POST['category'] ?? '');
$price    = round(floatval($_POST['price'] ?? 0), 2);
$icon     = sanitize($_POST['icon'] ?? '');

if ($name === '' || $desc === '' || $price <= 0 || !isset($categoryIcons[$category])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Nombre, descripción, categoría y precio válidos son requeridos']));
}

if ($icon === '') {
    $icon = $categoryIcons[$category];
}

$products = productsForCatalog();
$maxId = 0;
foreach ($products as $product) {
    $maxId = max($maxId, intval($product['id'] ?? 0));
}
$id = $maxId + 1;

$imagePath = '';
if (!empty($_FILES['image']['name'])) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'error' => 'No se pudo subir la imagen']));
    }

    $maxBytes = 5 * 1024 * 1024;
    if (($_FILES['image']['size'] ?? 0) > $maxBytes) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'error' => 'La imagen no debe superar 5 MB']));
    }

    $tmp = $_FILES['image']['tmp_name'];
    if (class_exists('finfo')) {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($tmp);
    } else {
        http_response_code(500);
        exit(json_encode(['success' => false, 'error' => 'El servidor no puede validar imágenes']));
    }
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($extensions[$mime])) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF']));
    }

    $uploadDir = dirname(__DIR__) . '/uploads/products';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $filename = 'product-' . $id . '-' . date('YmdHis') . '.' . $extensions[$mime];
    $target = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmp, $target)) {
        http_response_code(500);
        exit(json_encode(['success' => false, 'error' => 'No se pudo guardar la imagen']));
    }

    $imagePath = '/catalogodigsistema/uploads/products/' . $filename;
}

$product = [
    'id'         => $id,
    'name'       => $name,
    'desc'       => $desc,
    'price'      => $price,
    'icon'       => $icon,
    'image'      => $imagePath,
    'category'   => $category,
    'created_at' => date('c'),
    'updated_at' => date('c'),
];

$products[] = $product;
writeJson('products', $products);
logActivity('product_created', 'PRD-' . $id, $_SESSION['username'] ?? 'admin', $name . ' | $' . number_format($price, 2));

echo json_encode([
    'success' => true,
    'product' => $product,
]);
