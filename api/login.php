<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false]));
}

$body     = json_decode(file_get_contents('php://input'), true) ?: [];
$username = trim($body['username'] ?? '');
$password = $body['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Usuario y contraseña requeridos']));
}

$user = doLogin($username, $password);
if ($user) {
    echo json_encode(['success' => true, 'role' => $user['role'], 'name' => $user['name']]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas']);
}
