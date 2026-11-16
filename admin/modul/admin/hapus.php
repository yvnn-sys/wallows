<?php
require_once __DIR__ . '/../../includes/auth.php';

$tujuan = ADMIN_URL . '/modul/admin/index.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect($tujuan);
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || !csrf_valid($token)) {
    flash_set('gagal', 'Sesi form sudah kedaluwarsa. Silakan coba lagi.');
    redirect($tujuan);
}

$id = input_int('id');

if ($id <= 0) {
    flash_set('gagal', 'Akun admin tidak ditemukan.');
    redirect($tujuan);
}

if ($id === (int) ($_SESSION['admin_id'] ?? 0)) {
    flash_set('gagal', 'Anda tidak bisa menghapus akun yang sedang dipakai untuk masuk.');
    redirect($tujuan);
}

$baris = db_satu($koneksi, 'SELECT id FROM admin WHERE id = ?', [$id]);

if ($baris === null) {
    flash_set('gagal', 'Akun admin tidak ditemukan.');
    redirect($tujuan);
}

try {
    db_jalankan($koneksi, 'DELETE FROM admin WHERE id = ?', [$id]);
    flash_set('sukses', 'Akun admin berhasil dihapus.');
} catch (mysqli_sql_exception $e) {
    error_log('Hapus akun admin gagal: ' . $e->getMessage());
    flash_set('gagal', 'Akun admin gagal dihapus.');
}

redirect($tujuan);
