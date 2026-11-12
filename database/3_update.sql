-- ============================================================
-- Tahap 2 - UPDATE
-- Proyek : Wallows Official Web
-- Jalankan setelah: database/2_select.sql
-- Satu baris per tabel, selalu memakai WHERE
-- ============================================================

START TRANSACTION;

UPDATE `admin` SET `nama` = 'Admin Utama Wallows' WHERE `id` = 1;

UPDATE `album` SET `deskripsi` = 'Album studio pertama Wallows.' WHERE `id` = 2;

UPDATE `anggota` SET `posisi` = 'Vokalis, gitar ritme' WHERE `id` = 1;

UPDATE `bagian_tur` SET `tamu_khusus` = 'Hembree dan Benee' WHERE `id` = 2;

-- Berita nomor 2 diubah dari draft menjadi published
UPDATE `berita` SET `status` = 'published' WHERE `id` = 2;

UPDATE `foto` SET `keterangan_foto` = 'Suasana latihan di belakang panggung' WHERE `id` = 2;

UPDATE `item_pesanan` SET `nama_item` = 'Kaos Nothing Happens (Hitam)' WHERE `id` = 1;

UPDATE `jenis_tiket` SET `deskripsi` = 'Akses area umum tanpa merchandise' WHERE `id` = 1;

UPDATE `kategori_foto` SET `nama` = 'Studio Session' WHERE `id` = 3;

UPDATE `kategori_produk` SET `nama` = 'Bundle Hemat' WHERE `id` = 5;

UPDATE `konser` SET `status_penjualan` = 'selling_fast' WHERE `id` = 1;

UPDATE `lagu` SET `durasi_detik` = 180 WHERE `id` = 1;

UPDATE `lirik` SET `isi_lengkap` = 'Contoh isi lirik yang sudah diperbarui.' WHERE `id` = 1;

UPDATE `pelanggan` SET `nama` = 'Rani Putri Lestari' WHERE `id` = 1;

UPDATE `pesanan` SET `status` = 'shipped' WHERE `id` = 1;

UPDATE `pesan_kontak` SET `status` = 'read' WHERE `id` = 2;

UPDATE `produk` SET `stok` = 35 WHERE `id` = 1;

UPDATE `tiket_konser` SET `terjual` = 140 WHERE `id` = 1;

UPDATE `video` SET `judul` = 'Are You Bored Yet? (Official Music Video)' WHERE `id` = 1;

COMMIT;
