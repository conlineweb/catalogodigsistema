<?php
require_once dirname(__DIR__) . '/includes/auth.php';
doLogout();
$dest = (isset($_GET['role']) && $_GET['role'] === 'admin')
    ? '/catalogodigsistema/admin/login.php'
    : '/catalogodigsistema/client/login.php';
header('Location: ' . $dest);
exit;
