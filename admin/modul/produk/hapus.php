<?php
require_once __DIR__ . '/../../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect(ADMIN_URL . '/modul/produk/index.php');
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || !csrf_valid($token)) {
    flash_set('gagal', 'Sesi form sudah kedaluwarsa. Silakan coba lagi.');
    redirect(ADMIN_URL . '/modul/produk/index.php');
}

$id = input_int('id');
$baris = $id > 0 ? db_satu($koneksi, 'SELECT id, tautan_gambar FROM produk WHERE id = ?', [$id]) : null;

if ($baris === null) {
    flash_set('gagal', 'Produk tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/produk/index.php');
}

try {
    db_jalankan($koneksi, 'DELETE FROM produk WHERE id = ?', [$id]);
    hapus_gambar($baris['tautan_gambar']);
    flash_set('sukses', 'Produk berhasil dihapus.');
} catch (mysqli_sql_exception $e) {
    error_log('Hapus produk gagal: ' . $e->getMessage());
    flash_set('gagal', 'Produk gagal dihapus.');
}

redirect(ADMIN_URL . '/modul/produk/index.php');
