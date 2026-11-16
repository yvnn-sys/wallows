<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Permintaan hapus tidak valid.');
    redirect(ADMIN_URL . '/modul/foto/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = $koneksi->prepare('SELECT id, lokasi_file_gambar FROM foto WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$foto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($foto === null) {
    flash_set('error', 'Foto tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/foto/index.php');
}

try {
    $hapus = $koneksi->prepare('DELETE FROM foto WHERE id = ?');
    $hapus->bind_param('i', $id);
    $hapus->execute();
    $hapus->close();

    hapus_file_upload($foto['lokasi_file_gambar']);
    flash_set('sukses', 'Foto berhasil dihapus.');
} catch (mysqli_sql_exception $exception) {
    error_log('Hapus foto gagal: ' . $exception->getMessage());
    flash_set('error', 'Foto gagal dihapus.');
}

redirect(ADMIN_URL . '/modul/foto/index.php');
