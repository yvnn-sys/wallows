-- ============================================================
-- Tahap 1 - SELECT
-- Proyek : Wallows Official Web
-- Jalankan setelah: database/1_insert.sql
-- Berisi SELECT semua tabel dan query JOIN untuk tiap halaman
-- ============================================================

-- ============================================================
-- BAGIAN A - SELECT semua tabel (19 tabel)
-- ============================================================

SELECT * FROM `admin`;
SELECT * FROM `album`;
SELECT * FROM `anggota`;
SELECT * FROM `bagian_tur`;
SELECT * FROM `berita`;
SELECT * FROM `foto`;
SELECT * FROM `item_pesanan`;
SELECT * FROM `jenis_tiket`;
SELECT * FROM `kategori_foto`;
SELECT * FROM `kategori_produk`;
SELECT * FROM `konser`;
SELECT * FROM `lagu`;
SELECT * FROM `lirik`;
SELECT * FROM `pelanggan`;
SELECT * FROM `pesanan`;
SELECT * FROM `pesan_kontak`;
SELECT * FROM `produk`;
SELECT * FROM `tiket_konser`;
SELECT * FROM `video`;

-- ============================================================
-- BAGIAN B - Query JOIN untuk tiap halaman website
-- ============================================================

-- Halaman berita (news.html): berita beserta nama penulis
SELECT b.`id`, b.`judul`, b.`alamat_halaman`, b.`status`, b.`tanggal_terbit`, a.`nama` AS `penulis`
FROM `berita` b
LEFT JOIN `admin` a ON a.`id` = b.`admin_id`
ORDER BY b.`tanggal_terbit` DESC;

-- Halaman album (music/albums.html): album beserta jumlah lagu yang tercatat
SELECT al.`id`, al.`judul`, al.`jenis`, al.`tahun_rilis`, COUNT(l.`id`) AS `jumlah_lagu_tercatat`
FROM `album` al
LEFT JOIN `lagu` l ON l.`album_id` = al.`id`
GROUP BY al.`id`, al.`judul`, al.`jenis`, al.`tahun_rilis`
ORDER BY al.`tahun_rilis` DESC;

-- Halaman lagu populer (music/songs.html): lagu beserta albumnya
SELECT l.`id`, l.`judul`, al.`judul` AS `album`, l.`artis_tamu`, l.`durasi_detik`, l.`peringkat_popularitas`
FROM `lagu` l
LEFT JOIN `album` al ON al.`id` = l.`album_id`
ORDER BY l.`peringkat_popularitas` ASC;

-- Halaman lirik (music/lyrics.html): lirik beserta judul lagu
SELECT lr.`id`, l.`judul` AS `lagu`, lr.`nama_bagian`, lr.`tautan_genius`
FROM `lirik` lr
JOIN `lagu` l ON l.`id` = lr.`lagu_id`
ORDER BY l.`judul` ASC;

-- Halaman galeri foto (media/photos.html): foto beserta kategorinya
SELECT f.`id`, f.`lokasi_file_gambar`, f.`keterangan_foto`, kf.`nama` AS `kategori`
FROM `foto` f
LEFT JOIN `kategori_foto` kf ON kf.`id` = f.`kategori_foto_id`
ORDER BY f.`id` ASC;

-- Halaman video (media/videos.html): video beserta lagu terkait
SELECT v.`id`, v.`judul`, v.`kode_youtube`, l.`judul` AS `lagu`
FROM `video` v
LEFT JOIN `lagu` l ON l.`id` = v.`lagu_id`
ORDER BY v.`id` ASC;

-- Halaman jadwal tur (tour/schedule.html): konser beserta bagian tur
SELECT k.`id`, bt.`nama` AS `bagian_tur`, k.`nama_tempat`, k.`kota`, k.`wilayah`,
       k.`tanggal_acara`, k.`jam_tampil`, k.`status_penjualan`
FROM `konser` k
JOIN `bagian_tur` bt ON bt.`id` = k.`bagian_tur_id`
ORDER BY k.`tanggal_acara` ASC;

-- Halaman tiket (tour/tickets.html): tiket beserta konser dan jenis tiket
SELECT tk.`id`, k.`nama_tempat`, k.`kota`, k.`tanggal_acara`,
       jt.`nama` AS `jenis_tiket`, jt.`kategori`, tk.`harga`,
       (tk.`kuota` - tk.`terjual`) AS `sisa_kuota`
FROM `tiket_konser` tk
JOIN `konser` k ON k.`id` = tk.`konser_id`
JOIN `jenis_tiket` jt ON jt.`id` = tk.`jenis_tiket_id`
ORDER BY k.`tanggal_acara` ASC, jt.`nama` ASC;

-- Halaman toko (store/merchandise.html): produk beserta kategorinya
SELECT p.`id`, p.`nama`, kp.`nama` AS `kategori`, p.`harga`, p.`stok`,
       p.`label_produk`, p.`status_aktif`
FROM `produk` p
JOIN `kategori_produk` kp ON kp.`id` = p.`kategori_produk_id`
ORDER BY kp.`nama` ASC, p.`nama` ASC;

-- Halaman anggota (about/members.html)
SELECT `id`, `nama`, `posisi`
FROM `anggota`
ORDER BY `id` ASC;

-- Halaman kontak (about/contact.html): pesan masuk beserta admin yang membalas
SELECT pk.`id`, pk.`nama`, pk.`email`, pk.`topik`, pk.`status`, pk.`dibuat_pada`,
       a.`nama` AS `dibalas_oleh`
FROM `pesan_kontak` pk
LEFT JOIN `admin` a ON a.`id` = pk.`dibalas_oleh`
ORDER BY pk.`dibuat_pada` DESC;

-- Halaman pesanan admin: pesanan beserta akun pelanggan
SELECT ps.`id`, ps.`nomor_pesanan`, ps.`nama_pelanggan`, ps.`status`,
       ps.`total_harga`, pl.`nama` AS `akun_pelanggan`
FROM `pesanan` ps
LEFT JOIN `pelanggan` pl ON pl.`id` = ps.`pelanggan_id`
ORDER BY ps.`dibuat_pada` DESC;

-- Rincian pesanan admin: item beserta produk atau tiket yang dibeli
SELECT ip.`id`, ps.`nomor_pesanan`, ip.`nama_item`, ip.`harga_satuan`,
       ip.`jumlah`, ip.`kode_tiket`, pr.`nama` AS `produk`,
       tk.`id` AS `tiket_konser_id`
FROM `item_pesanan` ip
JOIN `pesanan` ps ON ps.`id` = ip.`pesanan_id`
LEFT JOIN `produk` pr ON pr.`id` = ip.`produk_id`
LEFT JOIN `tiket_konser` tk ON tk.`id` = ip.`tiket_konser_id`
ORDER BY ps.`nomor_pesanan` ASC, ip.`id` ASC;

-- Ringkasan penjualan tiket per konser
SELECT k.`nama_tempat`, k.`kota`,
       SUM(tk.`terjual`) AS `total_terjual`,
       SUM(tk.`kuota`) AS `total_kuota`
FROM `konser` k
JOIN `tiket_konser` tk ON tk.`konser_id` = k.`id`
GROUP BY k.`id`, k.`nama_tempat`, k.`kota`
ORDER BY `total_terjual` DESC;
