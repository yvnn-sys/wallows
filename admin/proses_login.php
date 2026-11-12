<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . '/index.php');
}

if (!csrf_valid(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    flash_set('error', 'Sesi form sudah kedaluwarsa. Silakan coba lagi.');
    redirect(ADMIN_URL . '/index.php');
}

$email = trim((string) ($_POST['email'] ?? ''));
$sandi = (string) ($_POST['password'] ?? '');

if ($email === '' || $sandi === '') {
    flash_set('error', 'Email dan sandi wajib diisi.');
    redirect(ADMIN_URL . '/index.php');
}

try {
    $stmt = $koneksi->prepare('SELECT id, nama, email, kata_sandi FROM admin WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $error) {
    error_log('Login admin gagal: ' . $error->getMessage());
    flash_set('error', 'Terjadi kesalahan pada basis data. Coba lagi nanti.');
    redirect(ADMIN_URL . '/index.php');
}

if ($admin === null || !password_verify($sandi, $admin['kata_sandi'])) {
    flash_set('error', 'Email atau sandi salah.');
    redirect(ADMIN_URL . '/index.php');
}

$adminId = (int) $admin['id'];

if (password_needs_rehash($admin['kata_sandi'], PASSWORD_DEFAULT)) {
    try {
        $hashBaru = password_hash($sandi, PASSWORD_DEFAULT);
        $update = $koneksi->prepare('UPDATE admin SET kata_sandi = ? WHERE id = ?');
        $update->bind_param('si', $hashBaru, $adminId);
        $update->execute();
        $update->close();
    } catch (mysqli_sql_exception $error) {
        error_log('Rehash sandi admin gagal: ' . $error->getMessage());
    }
}

session_regenerate_id(true);
$_SESSION['admin_id'] = $adminId;
$_SESSION['admin_nama'] = (string) $admin['nama'];
unset($_SESSION['csrf_token']);

flash_set('sukses', 'Selamat datang, ' . $admin['nama'] . '.');
redirect(ADMIN_URL . '/dashboard.php');
