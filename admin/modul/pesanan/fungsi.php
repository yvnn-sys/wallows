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
 * Sesuaikan stok produk atau kuota tiket saat jumlah item berubah.
 * Nilai delta positif menambah pemakaian, negatif mengembalikan pemakaian.
 * Harus dipanggil di dalam transaksi.
 */
function sesuaikan_pemakaian_item(mysqli $koneksi, ?int $produkId, ?int $tiketId, int $delta): void
{
    if ($delta === 0) {
        return;
    }

    if ($produkId !== null) {
        if ($delta > 0) {
            $info = db_jalankan(
                $koneksi,
                'UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?',
                [$delta, $produkId, $delta]
            );
        } else {
            $info = db_jalankan(
                $koneksi,
                'UPDATE produk SET stok = stok + ? WHERE id = ?',
                [-$delta, $produkId]
            );
        }

        if ($info['terpengaruh'] !== 1) {
            throw new RuntimeException('Stok produk tidak mencukupi.');
        }

        return;
    }

    if ($tiketId === null) {
        return;
    }

    if ($delta > 0) {
        $info = db_jalankan(
            $koneksi,
            'UPDATE tiket_konser SET terjual = terjual + ? WHERE id = ? AND kuota - terjual >= ?',
            [$delta, $tiketId, $delta]
        );

        if ($info['terpengaruh'] !== 1) {
            throw new RuntimeException('Kuota tiket tidak mencukupi.');
        }
    } else {
        db_jalankan(
            $koneksi,
            'UPDATE tiket_konser SET terjual = GREATEST(0, terjual - ?) WHERE id = ?',
            [-$delta, $tiketId]
        );
    }
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
