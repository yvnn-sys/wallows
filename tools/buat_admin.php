<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Skrip ini hanya boleh dijalankan dari terminal.\n");
}

function buat_sandi_acak(int $panjang = 16): string
{
    $alfabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    $hasil = '';

    for ($i = 0; $i < $panjang; $i++) {
        $hasil .= $alfabet[random_int(0, strlen($alfabet) - 1)];
    }

    return $hasil;
}

$configPath = dirname(__DIR__) . '/config/koneksi.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "File config/koneksi.php belum ada. Salin config/koneksi.example.php terlebih dahulu.\n");
    exit(1);
}

require_once $configPath;

$args = array_slice($argv, 1);

if (count($args) < 2) {
    fwrite(STDERR, "Penggunaan: php tools/buat_admin.php \"Nama\" email [sandi]\n");
    fwrite(STDERR, "Contoh: php tools/buat_admin.php \"Admin Wallows\" admin@wallows.test\n");
    exit(1);
}

$nama = trim($args[0]);
$email = trim($args[1]);
$sandi = $args[2] ?? '';

if ($nama === '') {
    fwrite(STDERR, "Nama tidak boleh kosong.\n");
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Format email tidak valid.\n");
    exit(1);
}

$sandiDibuatOtomatis = false;

if ($sandi === '') {
    $sandi = buat_sandi_acak();
    $sandiDibuatOtomatis = true;
} elseif (strlen($sandi) < 8) {
    fwrite(STDERR, "Sandi minimal 8 karakter.\n");
    exit(1);
}

$hash = password_hash($sandi, PASSWORD_DEFAULT);

try {
    $cek = $koneksi->prepare('SELECT id FROM admin WHERE email = ? LIMIT 1');
    $cek->bind_param('s', $email);
    $cek->execute();
    $akunLama = $cek->get_result()->fetch_assoc();
    $cek->close();

    if ($akunLama !== null) {
        $id = (int) $akunLama['id'];
        $update = $koneksi->prepare('UPDATE admin SET nama = ?, kata_sandi = ? WHERE id = ?');
        $update->bind_param('ssi', $nama, $hash, $id);
        $update->execute();
        $update->close();

        echo "Akun admin diperbarui: {$email}\n";
    } else {
        $insert = $koneksi->prepare('INSERT INTO admin (nama, email, kata_sandi) VALUES (?, ?, ?)');
        $insert->bind_param('sss', $nama, $email, $hash);
        $insert->execute();
        $insert->close();

        echo "Akun admin dibuat: {$email}\n";
    }
} catch (mysqli_sql_exception $error) {
    error_log('Gagal membuat admin: ' . $error->getMessage());
    fwrite(STDERR, "Gagal menyimpan akun admin. Periksa koneksi dan struktur tabel admin.\n");
    exit(1);
}

if ($sandiDibuatOtomatis) {
    echo "Sandi: {$sandi}\n";
    echo "Simpan sandi ini sekarang. Sandi tidak disimpan dalam bentuk asli.\n";
} else {
    echo "Sandi memakai nilai yang Anda berikan. Jika diketik di terminal, nilai itu bisa tersimpan di riwayat perintah.\n";
}
