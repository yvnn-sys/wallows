<?php
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Permintaan hapus tidak valid.');
    redirect(ADMIN_URL . '/modul/kategori_foto/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = $koneksi->prepare('SELECT id, nama FROM kategori_foto WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$kategori = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($kategori === null) {
    flash_set('error', 'Kategori foto tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/kategori_foto/index.php');
}

$hitung = $koneksi->prepare('SELECT COUNT(*) AS jumlah FROM foto WHERE kategori_foto_id = ?');
$hitung->bind_param('i', $id);
$hitung->execute();
$jumlahFoto = (int) ($hitung->get_result()->fetch_assoc()['jumlah'] ?? 0);
$hitung->close();

if ($jumlahFoto > 0) {
    flash_set(
        'error',
        'Kategori "' . $kategori['nama'] . '" masih memiliki ' . $jumlahFoto . ' foto. '
        . 'Sesuai aturan ON DELETE SET NULL, foto akan menjadi tanpa kategori bila kategori dihapus. '
        . 'Pindahkan foto ke kategori lain atau hapus fotonya terlebih dahulu.'
    );
    redirect(ADMIN_URL . '/modul/kategori_foto/index.php');
}

try {
    $stmt = $koneksi->prepare('DELETE FROM kategori_foto WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash_set('sukses', 'Kategori foto berhasil dihapus.');
} catch (mysqli_sql_exception $exception) {
    error_log('Hapus kategori foto gagal: ' . $exception->getMessage());
    flash_set('error', 'Kategori foto gagal dihapus.');
}

redirect(ADMIN_URL . '/modul/kategori_foto/index.php');
