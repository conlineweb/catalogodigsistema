<?php
require_once __DIR__ . '/db.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        session_start();
    }
}

function isAdmin(): bool {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /catalogodigsistema/admin/login.php');
        exit;
    }
}

function requireClientLogin(): void {
    startSession();
    if (!isLoggedIn()) {
        header('Location: /catalogodigsistema/client/login.php');
        exit;
    }
}

function doLogin(string $username, string $password): array|false {
    $users    = readJson('users');
    $username = strtolower(trim($username));
    foreach ($users as $user) {
        if (strtolower($user['username']) === $username && password_verify($password, $user['password_hash'])) {
            startSession();
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['name']     = $user['name'];
            if (!empty($user['order_id'])) $_SESSION['order_id'] = $user['order_id'];
            return $user;
        }
    }
    return false;
}

function doLogout(): void {
    startSession();
    $_SESSION = [];
    session_destroy();
}

/* ── Create a client access account when an order is placed ── */
function createClientUser(string $orderId, string $name, string $email): array {
    $users = readJson('users');

    // Build a unique username from first name
    $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', strtok(trim($name) ?: 'cliente', ' ')));
    if (strlen($base) < 3) $base = 'cliente';
    $clientCount = count(array_filter($users, fn($u) => $u['role'] === 'client')) + 1;
    $username    = $base . str_pad((string)$clientCount, 3, '0', STR_PAD_LEFT);

    $tempPass = generatePassword();
    $user = [
        'id'            => 'USR-' . strtoupper(uniqid()),
        'username'      => $username,
        'password_hash' => password_hash($tempPass, PASSWORD_DEFAULT),
        'temp_password' => $tempPass,        // visible to admin in order detail
        'role'          => 'client',
        'name'          => sanitize($name),
        'email'         => sanitize($email),
        'order_id'      => $orderId,
        'created_at'    => date('c'),
    ];
    $users[] = $user;
    writeJson('users', $users);

    return ['username' => $username, 'password' => $tempPass];
}

function generatePassword(int $len = 8): string {
    $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $pwd  = '';
    for ($i = 0; $i < $len; $i++) $pwd .= $pool[random_int(0, strlen($pool) - 1)];
    return $pwd;
}

function currentUser(): array|null {
    startSession();
    if (empty($_SESSION['user_id'])) return null;
    foreach (readJson('users') as $u) {
        if ($u['id'] === $_SESSION['user_id']) return $u;
    }
    return null;
}
