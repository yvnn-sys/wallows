<?php
require_once __DIR__ . '/includes/init.php';

$token = isset($_GET['token']) ? (string) $_GET['token'] : null;

if (!csrf_valid($token)) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

redirect(ADMIN_URL . '/index.php');
