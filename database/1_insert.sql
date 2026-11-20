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
(1, 'Spring', 'ep', 2018, NULL, 6, 'Major-label debut EP', 'https://is1-ssl.mzstatic.com/image/thumb/Music221/v4/91/18/01/91180107-9829-50a7-e5c5-002e6180c44f/075679643575.jpg/1200x1200bf-60.jpg', 'https://open.spotify.com/album/3GbofUquULdgvfmK83AXVz'),
(2, 'Nothing Happens', 'album', 2019, NULL, 11, 'Debut studio album', 'images/dg2.jpg', 'https://open.spotify.com/album/7eed9MBclFPjjjvotfR2e9'),
(3, 'Remote', 'ep', 2020, NULL, 6, 'Third EP release', 'https://i.pinimg.com/originals/17/6c/71/176c71b4a59eb236d15967c669ec9e48.jpg', 'https://open.spotify.com/album/0P2kTQ7mJ3z7CFjII7GWkw'),
(4, 'Tell Me That It''s Over', 'album', 2022, NULL, 10, 'Second studio album', 'images/dg1.jpg', 'https://open.spotify.com/album/5L6fAzbz2x5oF8l0qluSKm'),
(5, 'Model', 'album', 2024, NULL, 12, 'Third studio album', 'https://images.unsplash.com/photo-1614613535308-eb5fbd3d2c17?q=80&w=1000&auto=format&fit=crop', 'https://open.spotify.com/album/64SX9Efb2JGW3rJFE1ZddJ'),
(6, 'The Wallows Singles Collection', 'single_collection', 2017, 2023, 8, 'Popular non-album singles', 'https://i.discogs.com/CiAE1sJWKjzHq20ShOTj23lRz1rV2MFlZU9ZySvMyzg/rs:fit/g:sm/q:90/h:600/w:599/czM6Ly9kaXNjb2dz/LWRhdGFiYXNlLWlt/YWdlcy9SLTIyOTc2/NTczLTE3MTgwNDY3/ODYtMTYzNi5qcGVn.jpeg', 'https://open.spotify.com/album/5Jay8TPhuSdRhlogbzBVrj');

-- 5. anggota
INSERT INTO `anggota` (`id`, `nama`, `posisi`, `biodata`, `foto`) VALUES
(1, 'Dylan Minnette', 'VOCALS · RHYTHM GUITAR · KEYBOARDS', 'Dylan is the lead vocalist and rhythm guitarist of Wallows. Beyond music, he is also known as an actor, having appeared in numerous TV shows and films. Born in Evansville, Indiana, he moved to LA where he met his future bandmates.', 'images/dylan.jpg'),
(2, 'Braeden Lemasters', 'VOCALS · LEAD GUITAR · BASS', 'Braeden handles lead guitar duties and shares vocal responsibilities with Dylan. Also an actor by trade, he brings a creative and cinematic perspective to Wallows'' songwriting and visual aesthetic.', 'images/braeden.jpg'),
(3, 'Cole Preston', 'DRUMS · GUITAR · KEYBOARDS · BACKING VOCALS', 'Cole is the powerhouse behind the kit, providing the rhythmic backbone to Wallows'' sound. A multi-instrumentalist, he also contributes guitar and keyboard parts, making him one of the most versatile members of the group.', 'images/cole.jpg');

-- 6. bagian_tur
-- Tabel induk untuk konser.bagian_tur_id
INSERT INTO `bagian_tur` (`id`, `nama`, `tamu_khusus`) VALUES
(1, 'Nothing Happens Tour', NULL),
(2, 'Tell Me That It''s Over Tour', 'Hembree'),
(3, 'Model Tour', 'Benee');

-- 7. jenis_tiket
-- Tabel induk untuk tiket_konser.jenis_tiket_id
INSERT INTO `jenis_tiket` (`id`, `nama`, `kategori`, `deskripsi`, `fasilitas`) VALUES
(1, 'General Admission', 'standard', 'Standard entry to the floor or seating areas depending on the venue configuration.', 'Standard entry to the floor / seating\nAccess to official tour merch stand\nAccess to public concessions & bars'),
(2, 'The "Especially You" VIP Package', 'vip', 'Early venue entry with exclusive pre-show perks and a merch bundle.', '1 General Admission ticket\nEarly venue entry\nExclusive signed poster\nExclusive Wallows tote bag\nCommemorative VIP laminate\nFirst access to tour merch'),
(3, 'Reserved Seating', 'standard', 'Numbered seating in the regular section of the venue.', 'Numbered seat\nAccess to official tour merch stand\nAccess to public concessions & bars');

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
(1, 1, 'Verse 1', 'Full lyrics for this track are not included in the sample database. Use the Genius link below to read the official lyrics.', 'https://genius.com/Wallows-are-you-bored-yet-lyrics'),
(2, 2, 'Verse 1', 'Full lyrics for this track are not included in the sample database. Use the Genius link below to read the official lyrics.', 'https://genius.com/Wallows-pleaser-lyrics'),
(3, 3, 'Verse 1', 'Full lyrics for this track are not included in the sample database. Use the Genius link below to read the official lyrics.', 'https://genius.com/Wallows-scrawny-lyrics');

-- 11. foto
-- kategori_foto_id mengacu ke tabel kategori_foto
INSERT INTO `foto` (`id`, `keterangan_foto`, `lokasi_file_gambar`, `kategori_foto_id`) VALUES
(1, 'Live in concert', 'images/photos1.jpg', 1),
(2, 'Crowd at a show', 'images/photos2.jpg', 1),
(3, 'Band practice', 'images/photos3.jpg', 2),
(4, 'Studio session', 'images/photos4.jpg', 3),
(5, 'Live crowd', 'images/photos5.jpg', 1),
(6, 'Stage lights', 'images/photos6.jpg', 1),
(7, 'Backstage', 'images/photos7.jpg', 2),
(8, 'Recording', 'images/photos8.jpg', 3);

-- 12. berita
-- admin_id mengacu ke tabel admin
INSERT INTO `berita` (`id`, `admin_id`, `judul`, `alamat_halaman`, `isi`, `tautan_gambar`, `teks_tombol`, `tautan_tombol`, `status`, `tanggal_terbit`) VALUES
(1, 1, 'Model Out Now', 'model-out-now', 'We''re so incredibly excited to share this new body of work with you. Model is available now on all streaming platforms, with physical editions on the way.', 'images/home2.jpg', 'Listen Now', 'music/albums.php', 'published', '2024-05-24 09:00:00'),
(2, 2, 'New World Tour Dates Added', 'new-world-tour-dates-added', 'Due to overwhelming demand, we''ve added second nights in London, NYC, and Los Angeles — plus new dates in Tokyo. Tickets are on sale now.', 'images/homeimg1.jpg', 'Tour Dates', 'tour/schedule.php', 'published', '2026-03-22 09:00:00'),
(3, 1, 'Valentine''s Day Merch Drop — Limited Edition', 'valentines-day-merch-drop', 'A special limited-edition merch drop just in time for Valentine''s Day, featuring exclusive designs available for a short time only.', 'images/home3.jpg', 'Shop Now', 'store/merchandise.php', 'published', '2026-02-14 09:00:00');

-- 13. konser
-- bagian_tur_id mengacu ke tabel bagian_tur
INSERT INTO `konser` (`id`, `bagian_tur_id`, `nama_tempat`, `kota`, `wilayah`, `tanggal_acara`, `jam_pintu_buka`, `jam_tampil`, `batas_usia`, `catatan`, `status_penjualan`) VALUES
(1, 3, 'Madison Square Garden', 'New York', 'New York', '2026-09-12', '18:30:00', '20:00:00', 'All ages', 'Doors open 90 minutes before showtime', 'on_sale'),
(2, 3, 'The Forum', 'Los Angeles', 'California', '2026-09-20', '18:00:00', '19:30:00', 'All ages', 'Doors open 90 minutes before showtime', 'selling_fast'),
(3, 2, 'O2 Academy Brixton', 'London', 'England', '2026-10-05', '19:00:00', '20:00:00', '18+', 'Photo ID required for entry', 'presale');

-- 14. tiket_konser
-- konser_id mengacu ke konser, jenis_tiket_id mengacu ke jenis_tiket
INSERT INTO `tiket_konser` (`id`, `konser_id`, `jenis_tiket_id`, `harga`, `kuota`, `terjual`) VALUES
(1, 1, 1, 46.88, 500, 130),
(2, 1, 2, 93.75, 100, 40),
(3, 2, 1, 50.00, 400, 55),
(4, 3, 1, 37.50, 300, 20),
(5, 3, 2, 75.00, 80, 10);

-- 15. produk
-- kategori_produk_id mengacu ke tabel kategori_produk
INSERT INTO `produk` (`id`, `nama`, `kategori_produk_id`, `harga`, `stok`, `tautan_gambar`, `label_produk`, `status_aktif`) VALUES
(1, 'Tell Me That It''s Over Tour Tee', 1, 15.63, 40, 'https://media-photos.depop.com/b1/9904709/2642915748_2b41bfec7af54c8788a3a814e65c1d30/P0.jpg', 'new', 1),
(2, 'Model Exclusive Vinyl', 2, 28.13, 25, 'images/turntable_vinyl.png', 'limited', 1),
(3, 'Model Tour Poster (Signed)', 3, 7.50, 60, 'images/home4.jpg', NULL, 1),
(4, 'Wallows Embroidered Cap', 4, 9.38, 80, 'https://images.unsplash.com/photo-1521369909029-2afed882baee?q=80&w=1000&auto=format&fit=crop', NULL, 1),
(5, 'Fan Bundle — Vinyl + Tee + Poster', 5, 40.63, 15, 'https://tse4.mm.bing.net/th/id/OIP.Xr8lfexVWYVk3aXpeX3M6wHaJ4?r=0&w=1080&h=1440&rs=1&pid=ImgDetMain&o=7&rm=3', NULL, 1);

-- 16. pesanan
-- pelanggan_id boleh kosong untuk pembeli tanpa akun
INSERT INTO `pesanan` (`id`, `pelanggan_id`, `nomor_pesanan`, `nama_pelanggan`, `email_pelanggan`, `alamat_pengiriman`, `total_harga`, `status`, `dibuat_pada`) VALUES
(1, 1, 'ORD-2026-0001', 'Rani Putri', 'rani@example.com', 'Jalan Merdeka 1, Bandung', 43.75, 'paid', '2026-11-03 13:00:00'),
(2, NULL, 'ORD-2026-0002', 'Budi Santoso', 'budi@example.com', NULL, 140.63, 'pending', '2026-11-04 14:15:00');

-- 17. item_pesanan
-- Mengacu ke pesanan, produk, dan tiket_konser
INSERT INTO `item_pesanan` (`id`, `pesanan_id`, `produk_id`, `tiket_konser_id`, `nama_item`, `harga_satuan`, `jumlah`, `kode_tiket`) VALUES
(1, 1, 1, NULL, 'Kaos Nothing Happens', 15.63, 1, NULL),
(2, 1, 2, NULL, 'Vinyl Nothing Happens', 28.13, 1, NULL),
(3, 2, NULL, 1, 'Tiket Standard - Madison Square Garden', 46.88, 1, 'TIX-0001'),
(4, 2, NULL, 2, 'Tiket VIP - Madison Square Garden', 93.75, 1, 'TIX-0002');

-- 18. video
-- lagu_id boleh kosong jika video tidak terikat lagu tertentu
INSERT INTO `video` (`id`, `lagu_id`, `judul`, `kode_youtube`) VALUES
(1, 1, 'Are You Bored Yet? (Official Video)', 'wIgmyE5Juzw'),
(2, 2, 'Pleaser (Official Video)', 'fUU0xHiW2_0'),
(3, 3, 'Scrawny (Official Video)', 'zgOR698RMLY');

-- 19. pesan_kontak
-- dibalas_oleh mengacu ke tabel admin dan boleh kosong
INSERT INTO `pesan_kontak` (`id`, `dibalas_oleh`, `nama`, `email`, `topik`, `pesan`, `status`, `dibuat_pada`) VALUES
(1, 1, 'Sari Dewi', 'sari@example.com', 'fan_message', 'Halo, kapan tur ke Indonesia?', 'replied', '2026-11-05 08:00:00'),
(2, NULL, 'Andi Pratama', 'andi@example.com', 'merch', 'Apakah kaos ukuran L masih tersedia?', 'new', '2026-11-06 10:30:00');

COMMIT;
