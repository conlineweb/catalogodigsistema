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

$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];
$id = intval($body['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Producto válido requerido']));
}

$products = productsForCatalog();
$deletedProduct = null;
$remaining = [];
foreach ($products as $product) {
    if (intval($product['id'] ?? 0) === $id) {
        $deletedProduct = $product;
        continue;
    }
    $remaining[] = $product;
}

if ($deletedProduct === null) {
    http_response_code(404);
    exit(json_encode(['success' => false, 'error' => 'Producto no encontrado']));
}

writeJson('products', $remaining);
logActivity('product_deleted', 'PRD-' . $id, $_SESSION['username'] ?? 'admin', $deletedProduct['name'] ?? 'Producto eliminado');

echo json_encode([
    'success' => true,
    'deleted_id' => $id,
]);
