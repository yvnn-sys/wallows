<?php
require_once __DIR__ . '/init.php';

if (empty($_SESSION['admin_id'])) {
    flash_set('error', 'Silakan masuk terlebih dahulu.');
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}
