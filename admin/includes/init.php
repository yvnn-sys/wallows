<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$posisiAdmin = strpos($scriptName, '/admin/');
$basePath = $posisiAdmin === false ? '' : substr($scriptName, 0, $posisiAdmin);

define('BASE_PATH', $basePath);
define('ADMIN_URL', $basePath . '/admin');
define('APP_NAME', 'Wallows Admin');

$configPath = dirname(__DIR__, 2) . '/config/koneksi.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('File config/koneksi.php belum ada. Salin config/koneksi.example.php menjadi config/koneksi.php.');
}

require_once $configPath;

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $tujuan): void
{
    header('Location: ' . $tujuan);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_valid(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function safe_query(mysqli $koneksi, string $sql): ?mysqli_result
{
    try {
        return $koneksi->query($sql);
    } catch (mysqli_sql_exception $error) {
        error_log('Query admin gagal: ' . $error->getMessage());

        return null;
    }
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function tanggal_singkat(?string $waktu): string
{
    if (empty($waktu)) {
        return '-';
    }

    $timestamp = strtotime($waktu);

    return $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);
}

function badge_status(string $status): string
{
    $peta = [
        'pending' => ['Menunggu', 'warning'],
        'paid' => ['Dibayar', 'info'],
        'shipped' => ['Dikirim', 'primary'],
        'completed' => ['Selesai', 'success'],
        'cancelled' => ['Dibatalkan', 'danger'],
        'new' => ['Baru', 'danger'],
        'read' => ['Dibaca', 'secondary'],
        'replied' => ['Dibalas', 'success'],
    ];

    [$label, $warna] = $peta[$status] ?? [$status, 'secondary'];

    return '<span class="badge text-bg-' . $warna . '">' . e($label) . '</span>';
}

function label_topik(string $topik): string
{
    $peta = [
        'fan_message' => 'Pesan penggemar',
        'feedback' => 'Masukan',
        'merch' => 'Merchandise',
        'other' => 'Lainnya',
    ];

    return $peta[$topik] ?? $topik;
}
