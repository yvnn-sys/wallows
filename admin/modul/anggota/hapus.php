<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Permintaan hapus tidak valid.');
    redirect(ADMIN_URL . '/modul/anggota/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = $koneksi->prepare('SELECT id, foto FROM anggota WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$anggota = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($anggota === null) {
    flash_set('error', 'Data anggota tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/anggota/index.php');
}

try {
    $hapus = $koneksi->prepare('DELETE FROM anggota WHERE id = ?');
    $hapus->bind_param('i', $id);
    $hapus->execute();
    $hapus->close();

    hapus_file_upload($anggota['foto']);
    flash_set('sukses', 'Data anggota berhasil dihapus.');
} catch (mysqli_sql_exception $exception) {
    error_log('Hapus anggota gagal: ' . $exception->getMessage());
    flash_set('error', 'Data anggota gagal dihapus.');
}

redirect(ADMIN_URL . '/modul/anggota/index.php');
