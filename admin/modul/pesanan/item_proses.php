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

$aksi = input_pilihan('aksi', ['tambah', 'ubah', 'hapus'], null);
$pesananId = input_int('pesanan_id');
$itemId = input_int('item_id');
$jumlah = input_int('jumlah');

$pesanan = $pesananId > 0 ? db_satu($koneksi, 'SELECT id, status FROM pesanan WHERE id = ?', [$pesananId]) : null;

if ($pesanan === null) {
    flash_set('gagal', 'Pesanan tidak ditemukan.');
    redirect($tujuan);
}

$detailUrl = ADMIN_URL . '/modul/pesanan/detail.php?id=' . $pesananId;

if ($aksi === null) {
    flash_set('gagal', 'Aksi item tidak valid.');
    redirect($detailUrl);
}

if ($pesanan['status'] === 'cancelled') {
    flash_set('gagal', 'Pesanan yang sudah dibatalkan tidak bisa diubah itemnya.');
    redirect($detailUrl);
}

if ($jumlah < 1) {
    $jumlah = 1;
}

$koneksi->begin_transaction();

try {
    if ($aksi === 'tambah') {
        $produkId = input_int('produk_id');
        $produk = $produkId > 0 ? db_satu($koneksi, 'SELECT id, nama, harga FROM produk WHERE id = ?', [$produkId]) : null;

        if ($produk === null) {
            throw new RuntimeException('Produk tidak ditemukan.');
        }

        db_jalankan(
            $koneksi,
            'INSERT INTO item_pesanan (pesanan_id, produk_id, tiket_konser_id, nama_item, harga_satuan, jumlah, kode_tiket)
             VALUES (?, ?, NULL, ?, ?, ?, NULL)',
            [$pesananId, $produkId, (string) $produk['nama'], (float) $produk['harga'], $jumlah]
        );

        sesuaikan_total_pesanan($koneksi, $pesananId);
        $koneksi->commit();

        flash_set('sukses', 'Item berhasil ditambahkan.');
    } elseif ($aksi === 'ubah') {
        $item = $itemId > 0
            ? db_satu($koneksi, 'SELECT id FROM item_pesanan WHERE id = ? AND pesanan_id = ?', [$itemId, $pesananId])
            : null;

        if ($item === null) {
            throw new RuntimeException('Item pesanan tidak ditemukan.');
        }

        db_jalankan($koneksi, 'UPDATE item_pesanan SET jumlah = ? WHERE id = ? AND pesanan_id = ?', [$jumlah, $itemId, $pesananId]);
        sesuaikan_total_pesanan($koneksi, $pesananId);
        $koneksi->commit();

        flash_set('sukses', 'Jumlah item berhasil diperbarui.');
    } else {
        db_jalankan($koneksi, 'DELETE FROM item_pesanan WHERE id = ? AND pesanan_id = ?', [$itemId, $pesananId]);
        sesuaikan_total_pesanan($koneksi, $pesananId);
        $koneksi->commit();

        flash_set('sukses', 'Item berhasil dihapus.');
    }
} catch (mysqli_sql_exception $e) {
    $koneksi->rollback();
    error_log('Ubah item pesanan gagal: ' . $e->getMessage());
    flash_set('gagal', 'Item pesanan gagal diproses.');
} catch (RuntimeException $e) {
    $koneksi->rollback();
    flash_set('gagal', $e->getMessage());
}

redirect($detailUrl);
