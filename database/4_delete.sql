-- ============================================================
-- Tahap 2 - DELETE
-- Proyek : Wallows Official Web
-- Jalankan setelah: database/3_update.sql
-- Tabel anak dihapus lebih dulu agar kunci asing tidak dilanggar
-- ============================================================

START TRANSACTION;

-- Tabel anak
DELETE FROM `item_pesanan` WHERE `id` = 4;
DELETE FROM `lirik` WHERE `id` = 3;
DELETE FROM `video` WHERE `id` = 3;

-- tiket_konser adalah anak dari konser dan jenis_tiket
DELETE FROM `tiket_konser` WHERE `id` = 5;

-- konser adalah anak dari bagian_tur
DELETE FROM `konser` WHERE `id` = 3;

DELETE FROM `foto` WHERE `id` = 3;
DELETE FROM `berita` WHERE `id` = 2;
DELETE FROM `pesan_kontak` WHERE `id` = 2;

-- pesanan adalah anak dari pelanggan
DELETE FROM `pesanan` WHERE `id` = 2;

-- lagu adalah anak dari album
DELETE FROM `lagu` WHERE `id` = 10;

-- produk adalah anak dari kategori_produk
DELETE FROM `produk` WHERE `id` = 5;

-- Tabel induk, dihapus setelah tabel anak yang mengarah ke sini
DELETE FROM `pelanggan` WHERE `id` = 2;
DELETE FROM `bagian_tur` WHERE `id` = 1;
DELETE FROM `jenis_tiket` WHERE `id` = 3;
DELETE FROM `album` WHERE `id` = 6;
DELETE FROM `anggota` WHERE `id` = 3;
DELETE FROM `admin` WHERE `id` = 2;
DELETE FROM `kategori_foto` WHERE `id` = 3;
DELETE FROM `kategori_produk` WHERE `id` = 5;

COMMIT;
