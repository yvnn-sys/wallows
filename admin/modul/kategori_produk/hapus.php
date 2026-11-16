<?php
require_once __DIR__ . '/../../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect(ADMIN_URL . '/modul/kategori_produk/index.php');
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || !csrf_valid($token)) {
    flash_set('gagal', 'Sesi form sudah kedaluwarsa. Silakan coba lagi.');
    redirect(ADMIN_URL . '/modul/kategori_produk/index.php');
}

$id = input_int('id');
$baris = $id > 0 ? db_satu($koneksi, 'SELECT id FROM kategori_produk WHERE id = ?', [$id]) : null;

if ($baris === null) {
    flash_set('gagal', 'Kategori tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/kategori_produk/index.php');
}

try {
    db_jalankan($koneksi, 'DELETE FROM kategori_produk WHERE id = ?', [$id]);
    flash_set('sukses', 'Kategori berhasil dihapus.');
} catch (mysqli_sql_exception $e) {
    error_log('Hapus kategori produk gagal: ' . $e->getMessage());
    flash_set('gagal', 'Kategori tidak bisa dihapus karena masih dipakai oleh produk. Pindahkan produknya ke kategori lain terlebih dahulu.');
}

redirect(ADMIN_URL . '/modul/kategori_produk/index.php');
