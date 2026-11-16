<?php
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Permintaan hapus tidak valid.');
    redirect(ADMIN_URL . '/modul/pesan_kontak/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$filter = (string) ($_POST['status'] ?? '');
$tujuan = ADMIN_URL . '/modul/pesan_kontak/index.php';
if (in_array($filter, ['new', 'read', 'replied'], true)) {
    $tujuan .= '?status=' . urlencode($filter);
}

try {
    $stmt = $koneksi->prepare('DELETE FROM pesan_kontak WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $berhasil = $stmt->affected_rows > 0;
    $stmt->close();
    flash_set(
        $berhasil ? 'sukses' : 'error',
        $berhasil ? 'Pesan kontak berhasil dihapus.' : 'Pesan kontak tidak ditemukan.'
    );
} catch (mysqli_sql_exception $exception) {
    error_log('Hapus pesan kontak gagal: ' . $exception->getMessage());
    flash_set('error', 'Pesan kontak gagal dihapus.');
}

redirect($tujuan);
