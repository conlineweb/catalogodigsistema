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

$id    = intval($_POST['id'] ?? 0);
$name  = sanitize($_POST['name'] ?? '');
$price = round(floatval($_POST['price'] ?? 0), 2);

if ($id <= 0 || $name === '' || $price <= 0) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Producto, nombre y precio válidos son requeridos']));
}

$products = productsForCatalog();
$foundIdx = null;
foreach ($products as $idx => $product) {
    if (intval($product['id'] ?? 0) === $id) {
        $foundIdx = $idx;
        break;
    }
}

if ($foundIdx === null) {
    http_response_code(404);
    exit(json_encode(['success' => false, 'error' => 'Producto no encontrado']));
}

$imagePath = $products[$foundIdx]['image'] ?? '';
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

$products[$foundIdx]['name'] = $name;
$products[$foundIdx]['price'] = $price;
$products[$foundIdx]['image'] = $imagePath;
$products[$foundIdx]['updated_at'] = date('c');

writeJson('products', $products);
logActivity('product_updated', 'PRD-' . $id, $_SESSION['username'] ?? 'admin', $name . ' | $' . number_format($price, 2));

echo json_encode([
    'success' => true,
    'product' => $products[$foundIdx],
]);
