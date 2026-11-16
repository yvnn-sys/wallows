<?php
require_once __DIR__ . '/../../includes/auth.php';

$tujuan = ADMIN_URL . '/modul/pesanan/index.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect($tujuan);
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || !csrf_valid($token)) {
    flash_set('gagal', 'Sesi form sudah kedaluwarsa. Silakan coba lagi.');
    redirect($tujuan);
}

$id = input_int('id');
$pesanan = $id > 0 ? db_satu($koneksi, 'SELECT id FROM pesanan WHERE id = ?', [$id]) : null;

if ($pesanan === null) {
    flash_set('gagal', 'Pesanan tidak ditemukan.');
    redirect($tujuan);
}

$koneksi->begin_transaction();

try {
    db_jalankan($koneksi, 'DELETE FROM item_pesanan WHERE pesanan_id = ?', [$id]);
    db_jalankan($koneksi, 'DELETE FROM pesanan WHERE id = ?', [$id]);
    $koneksi->commit();

    flash_set('sukses', 'Pesanan berhasil dihapus.');
} catch (mysqli_sql_exception $e) {
    $koneksi->rollback();
    error_log('Hapus pesanan gagal: ' . $e->getMessage());
    flash_set('gagal', 'Pesanan gagal dihapus.');
}

redirect($tujuan);
