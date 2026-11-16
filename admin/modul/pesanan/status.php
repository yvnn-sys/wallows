<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/fungsi.php';

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
$pilihanStatus = pilihan_status_pesanan();
$statusBaru = input_pilihan('status', array_keys($pilihanStatus), null);

$pesanan = $id > 0 ? db_satu($koneksi, 'SELECT id, status FROM pesanan WHERE id = ?', [$id]) : null;

if ($pesanan === null) {
    flash_set('gagal', 'Pesanan tidak ditemukan.');
    redirect($tujuan);
}

$detailUrl = ADMIN_URL . '/modul/pesanan/detail.php?id=' . $id;

if ($statusBaru === null) {
    flash_set('gagal', 'Status pesanan tidak valid.');
    redirect($detailUrl);
}

$statusLama = (string) $pesanan['status'];

if ($statusLama === $statusBaru) {
    flash_set('sukses', 'Status pesanan tidak berubah.');
    redirect($detailUrl);
}

if ($statusLama === 'cancelled' && $statusBaru !== 'cancelled') {
    flash_set('gagal', 'Pesanan yang sudah dibatalkan tidak dapat diaktifkan kembali.');
    redirect($detailUrl);
}

$koneksi->begin_transaction();

try {
    if ($statusBaru === 'cancelled' && $statusLama !== 'cancelled') {
        kembalikan_stok_pesanan($koneksi, $id);
    }

    db_jalankan($koneksi, 'UPDATE pesanan SET status = ? WHERE id = ?', [$statusBaru, $id]);
    $koneksi->commit();

    flash_set('sukses', 'Status pesanan diubah menjadi ' . $pilihanStatus[$statusBaru] . '.');
} catch (mysqli_sql_exception $e) {
    $koneksi->rollback();
    error_log('Ubah status pesanan gagal: ' . $e->getMessage());
    flash_set('gagal', 'Status pesanan gagal diubah.');
}

redirect($detailUrl);
