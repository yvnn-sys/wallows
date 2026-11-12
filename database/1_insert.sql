-- ============================================================
-- Tahap 1 - INSERT data contoh
-- Proyek : Wallows Official Web
-- Jalankan setelah: database/wallows.sql
-- Catatan: tabel induk diisi lebih dulu agar kunci asing aman
-- ============================================================

START TRANSACTION;

-- 1. admin
-- Tabel induk untuk berita.admin_id dan pesan_kontak.dibalas_oleh
INSERT INTO `admin` (`id`, `nama`, `email`, `kata_sandi`) VALUES
(1, 'Admin Utama', 'admin@wallows.local', 'GANTI_DENGAN_HASH'),
(2, 'Admin Konten', 'konten@wallows.local', 'GANTI_DENGAN_HASH');

-- 2. kategori_foto
-- Data ini sudah ada di wallows.sql, INSERT IGNORE mencegah duplikat
INSERT IGNORE INTO `kategori_foto` (`id`, `nama`) VALUES
(1, 'Concert'),
(2, 'Behind the Scenes'),
(3, 'Studio');

-- 3. kategori_produk
-- Data ini sudah ada di wallows.sql, INSERT IGNORE mencegah duplikat
INSERT IGNORE INTO `kategori_produk` (`id`, `nama`) VALUES
(1, 'Apparel'),
(2, 'Vinyl'),
(3, 'Poster'),
(4, 'Accessories'),
(5, 'Bundle');

-- 4. album
INSERT INTO `album` (`id`, `judul`, `jenis`, `tahun_rilis`, `tahun_rilis_akhir`, `jumlah_lagu`, `deskripsi`, `gambar_sampul`, `tautan_spotify`) VALUES
(1, 'Spring', 'ep', 2018, NULL, 6, 'EP debut Wallows', 'images/nothing_ep.png', NULL),
(2, 'Nothing Happens', 'album', 2019, NULL, 10, 'Album studio pertama', 'images/nothing_happens.png', 'https://open.spotify.com/album/contoh'),
(3, 'Remote', 'ep', 2020, NULL, 6, 'EP yang dikerjakan dari jarak jauh', NULL, NULL),
(4, 'Tell Me That It''s Over', 'album', 2022, NULL, 10, 'Album studio kedua', NULL, NULL),
(5, 'Model', 'album', 2024, NULL, 12, 'Album studio ketiga', NULL, NULL),
(6, 'The Wallows Singles Collection', 'single_collection', 2017, 2023, 15, 'Kumpulan single 2017 sampai 2023', NULL, NULL);

-- 5. anggota
INSERT INTO `anggota` (`id`, `nama`, `posisi`, `biodata`, `foto`) VALUES
(1, 'Dylan Minnette', 'Vokalis dan gitar ritme', 'Anggota pendiri Wallows.', 'images/dylan.jpg'),
(2, 'Braeden Lemasters', 'Gitaris utama dan vokalis', 'Anggota pendiri Wallows.', 'images/braeden.jpg'),
(3, 'Cole Preston', 'Drummer', 'Anggota pendiri Wallows.', 'images/cole.jpg');

-- 6. bagian_tur
-- Tabel induk untuk konser.bagian_tur_id
INSERT INTO `bagian_tur` (`id`, `nama`, `tamu_khusus`) VALUES
(1, 'Nothing Happens Tour', NULL),
(2, 'Tell Me That It''s Over Tour', 'Hembree'),
(3, 'Model Tour', 'Benee');

-- 7. jenis_tiket
-- Tabel induk untuk tiket_konser.jenis_tiket_id
INSERT INTO `jenis_tiket` (`id`, `nama`, `kategori`, `deskripsi`, `fasilitas`) VALUES
(1, 'Standard', 'standard', 'Akses area umum', 'Akses area umum\nTanpa merchandise'),
(2, 'VIP', 'vip', 'Akses area depan panggung', 'Akses area depan panggung\nMerchandise eksklusif\nMasuk lebih awal'),
(3, 'Standard Plus', 'standard', 'Area duduk reguler', 'Kursi bernomor\nTanpa merchandise');

-- 8. pelanggan
-- Tabel induk untuk pesanan.pelanggan_id
INSERT INTO `pelanggan` (`id`, `nama`, `email`, `kata_sandi`, `dibuat_pada`) VALUES
(1, 'Rani Putri', 'rani@example.com', 'GANTI_DENGAN_HASH', '2026-11-01 10:00:00'),
(2, 'Budi Santoso', 'budi@example.com', 'GANTI_DENGAN_HASH', '2026-11-02 11:30:00');

-- 9. lagu
-- album_id mengacu ke tabel album
INSERT INTO `lagu` (`id`, `album_id`, `judul`, `artis_tamu`, `durasi_detik`, `peringkat_popularitas`, `tahun_rilis`, `eksplisit`) VALUES
(1, 2, 'Are You Bored Yet?', 'Clairo', 178, 1, NULL, 0),
(2, 2, 'Pleaser', NULL, 214, 2, NULL, 0),
(3, 2, 'Scrawny', NULL, 190, 3, NULL, 0),
(4, 3, 'Remember When', NULL, 219, 4, NULL, 0),
(5, 5, 'Wish Me Luck', NULL, 205, 5, NULL, 0),
(6, 5, 'Your Apartment', NULL, 195, 6, NULL, 0),
(7, 5, 'Calling After Me', NULL, 190, 7, NULL, 0),
(8, 4, 'These Days', NULL, 203, 8, NULL, 0),
(9, 3, 'Quarterback', NULL, 200, 9, NULL, 0),
(10, 4, 'OK', NULL, 226, 10, NULL, 0);

-- 10. lirik
-- Satu lagu hanya boleh punya satu baris lirik (UNIQUE lagu_id)
INSERT INTO `lirik` (`id`, `lagu_id`, `nama_bagian`, `isi_lengkap`, `tautan_genius`) VALUES
(1, 1, 'Verse 1', 'Contoh isi lirik untuk latihan basis data.', 'https://genius.com/contoh'),
(2, 2, 'Verse 1', 'Contoh isi lirik untuk latihan basis data.', NULL),
(3, 3, 'Verse 1', 'Contoh isi lirik untuk latihan basis data.', NULL);

-- 11. foto
-- kategori_foto_id mengacu ke tabel kategori_foto
INSERT INTO `foto` (`id`, `keterangan_foto`, `lokasi_file_gambar`, `kategori_foto_id`) VALUES
(1, 'Panggung utama saat konser', 'images/photos1.jpg', 1),
(2, 'Suasana di belakang panggung', 'images/photos2.jpg', 2),
(3, 'Sesi rekaman di studio', 'images/photos3.jpg', 3);

-- 12. berita
-- admin_id mengacu ke tabel admin
INSERT INTO `berita` (`id`, `admin_id`, `judul`, `alamat_halaman`, `isi`, `tautan_gambar`, `teks_tombol`, `tautan_tombol`, `status`, `tanggal_terbit`) VALUES
(1, 1, 'Model Out Now', 'model-out-now', 'Album Model sudah tersedia di semua platform digital.', 'images/home2.jpg', 'Dengarkan', 'https://open.spotify.com/album/contoh', 'published', '2026-11-05 09:00:00'),
(2, 2, 'Tur 2026 Diumumkan', 'tur-2026-diumumkan', 'Jadwal tur Model Tour 2026 sudah diumumkan.', 'images/homeimg1.jpg', 'Lihat Jadwal', 'tour/schedule.html', 'draft', '2026-11-06 09:00:00');

-- 13. konser
-- bagian_tur_id mengacu ke tabel bagian_tur
INSERT INTO `konser` (`id`, `bagian_tur_id`, `nama_tempat`, `kota`, `wilayah`, `tanggal_acara`, `jam_pintu_buka`, `jam_tampil`, `batas_usia`, `catatan`, `status_penjualan`) VALUES
(1, 3, 'Madison Square Garden', 'New York', 'New York', '2026-09-12', '18:30:00', '20:00:00', 'Semua usia', 'Pintu dibuka 90 menit sebelum acara', 'on_sale'),
(2, 3, 'The Forum', 'Los Angeles', 'California', '2026-09-20', '18:00:00', '19:30:00', 'Semua usia', NULL, 'selling_fast'),
(3, 2, 'O2 Academy Brixton', 'London', 'England', '2026-10-05', '19:00:00', '20:00:00', '18+', 'Wajib membawa identitas', 'presale');

-- 14. tiket_konser
-- konser_id mengacu ke konser, jenis_tiket_id mengacu ke jenis_tiket
INSERT INTO `tiket_konser` (`id`, `konser_id`, `jenis_tiket_id`, `harga`, `kuota`, `terjual`) VALUES
(1, 1, 1, 750000.00, 500, 130),
(2, 1, 2, 1500000.00, 100, 40),
(3, 2, 1, 800000.00, 400, 55),
(4, 3, 1, 600000.00, 300, 20),
(5, 3, 2, 1200000.00, 80, 10);

-- 15. produk
-- kategori_produk_id mengacu ke tabel kategori_produk
INSERT INTO `produk` (`id`, `nama`, `kategori_produk_id`, `harga`, `stok`, `tautan_gambar`, `label_produk`, `status_aktif`) VALUES
(1, 'Kaos Nothing Happens', 1, 250000.00, 40, 'images/nothing_happens.png', 'new', 1),
(2, 'Vinyl Nothing Happens', 2, 450000.00, 25, 'images/turntable_vinyl.png', NULL, 1),
(3, 'Poster Model Tour', 3, 120000.00, 60, NULL, 'limited', 1),
(4, 'Tote Bag Wallows', 4, 150000.00, 80, NULL, NULL, 1),
(5, 'Bundle Vinyl dan Kaos', 5, 650000.00, 15, NULL, NULL, 0);

-- 16. pesanan
-- pelanggan_id boleh kosong untuk pembeli tanpa akun
INSERT INTO `pesanan` (`id`, `pelanggan_id`, `nomor_pesanan`, `nama_pelanggan`, `email_pelanggan`, `alamat_pengiriman`, `total_harga`, `status`, `dibuat_pada`) VALUES
(1, 1, 'ORD-2026-0001', 'Rani Putri', 'rani@example.com', 'Jalan Merdeka 1, Bandung', 700000.00, 'paid', '2026-11-03 13:00:00'),
(2, NULL, 'ORD-2026-0002', 'Budi Santoso', 'budi@example.com', NULL, 2250000.00, 'pending', '2026-11-04 14:15:00');

-- 17. item_pesanan
-- Mengacu ke pesanan, produk, dan tiket_konser
INSERT INTO `item_pesanan` (`id`, `pesanan_id`, `produk_id`, `tiket_konser_id`, `nama_item`, `harga_satuan`, `jumlah`, `kode_tiket`) VALUES
(1, 1, 1, NULL, 'Kaos Nothing Happens', 250000.00, 1, NULL),
(2, 1, 2, NULL, 'Vinyl Nothing Happens', 450000.00, 1, NULL),
(3, 2, NULL, 1, 'Tiket Standard - Madison Square Garden', 750000.00, 1, 'TIX-0001'),
(4, 2, NULL, 2, 'Tiket VIP - Madison Square Garden', 1500000.00, 1, 'TIX-0002');

-- 18. video
-- lagu_id boleh kosong jika video tidak terikat lagu tertentu
INSERT INTO `video` (`id`, `lagu_id`, `judul`, `kode_youtube`) VALUES
(1, 1, 'Are You Bored Yet? (Official Video)', 'contohvideo'),
(2, 5, 'Wish Me Luck (Official Video)', 'contohklip1'),
(3, 7, 'Calling After Me (Official Video)', 'contohklip2');

-- 19. pesan_kontak
-- dibalas_oleh mengacu ke tabel admin dan boleh kosong
INSERT INTO `pesan_kontak` (`id`, `dibalas_oleh`, `nama`, `email`, `topik`, `pesan`, `status`, `dibuat_pada`) VALUES
(1, 1, 'Sari Dewi', 'sari@example.com', 'fan_message', 'Halo, kapan tur ke Indonesia?', 'replied', '2026-11-05 08:00:00'),
(2, NULL, 'Andi Pratama', 'andi@example.com', 'merch', 'Apakah kaos ukuran L masih tersedia?', 'new', '2026-11-06 10:30:00');

COMMIT;
