<?php
declare(strict_types=1);

function pilihan_status_pesanan(): array
{
    return [
        'pending' => 'Menunggu',
        'paid' => 'Dibayar',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
}

function sesuaikan_total_pesanan(mysqli $koneksi, int $pesananId): void
{
    db_jalankan(
        $koneksi,
        'UPDATE pesanan
         SET total_harga = (
             SELECT COALESCE(SUM(harga_satuan * jumlah), 0)
             FROM item_pesanan
             WHERE pesanan_id = ?
         )
         WHERE id = ?',
        [$pesananId, $pesananId]
    );
}

/**
 * Kembalikan stok produk dan kuota tiket untuk seluruh item pesanan. Harus
 * dipanggil di dalam transaksi yang sama dengan perubahan status pesanan.
 */
function kembalikan_stok_pesanan(mysqli $koneksi, int $pesananId): void
{
    $baris = db_semua(
        $koneksi,
        'SELECT produk_id, SUM(jumlah) AS jumlah
         FROM item_pesanan
         WHERE pesanan_id = ? AND produk_id IS NOT NULL
         GROUP BY produk_id',
        [$pesananId]
    );

    foreach ($baris as $item) {
        db_jalankan(
            $koneksi,
            'UPDATE produk SET stok = stok + ? WHERE id = ?',
            [(int) $item['jumlah'], (int) $item['produk_id']]
        );
    }

    $tiket = db_semua(
        $koneksi,
        'SELECT tiket_konser_id, SUM(jumlah) AS jumlah
         FROM item_pesanan
         WHERE pesanan_id = ? AND tiket_konser_id IS NOT NULL
         GROUP BY tiket_konser_id',
        [$pesananId]
    );

    foreach ($tiket as $item) {
        db_jalankan(
            $koneksi,
            'UPDATE tiket_konser SET terjual = GREATEST(0, terjual - ?) WHERE id = ?',
            [(int) $item['jumlah'], (int) $item['tiket_konser_id']]
        );
    }
}
