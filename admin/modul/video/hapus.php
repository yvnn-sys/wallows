<?php
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Permintaan hapus tidak valid.');
    redirect(ADMIN_URL . '/modul/video/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

try {
    $stmt = $koneksi->prepare('DELETE FROM video WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $berhasil = $stmt->affected_rows > 0;
    $stmt->close();
    flash_set(
        $berhasil ? 'sukses' : 'error',
        $berhasil ? 'Video berhasil dihapus.' : 'Video tidak ditemukan.'
    );
} catch (mysqli_sql_exception $exception) {
    error_log('Hapus video gagal: ' . $exception->getMessage());
    flash_set('error', 'Video gagal dihapus.');
}

redirect(ADMIN_URL . '/modul/video/index.php');
