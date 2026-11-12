-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2026 at 06:17 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wallows`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kata_sandi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'disimpan dalam bentuk hash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Akun untuk login ke panel pengelola';

-- --------------------------------------------------------

--
-- Table structure for table `album`
--

CREATE TABLE `album` (
  `id` int NOT NULL,
  `judul` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('album','ep','single_collection') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_rilis` year NOT NULL,
  `tahun_rilis_akhir` year DEFAULT NULL COMMENT 'hanya untuk kumpulan single (2017 sampai 2023)',
  `jumlah_lagu` tinyint NOT NULL,
  `deskripsi` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gambar_sampul` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tautan_spotify` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Album, EP, dan kumpulan single';

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posisi` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `biodata` text COLLATE utf8mb4_unicode_ci,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'lokasi file foto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Data anggota band';

-- --------------------------------------------------------

--
-- Table structure for table `bagian_tur`
--

CREATE TABLE `bagian_tur` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamu_khusus` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Leg tur beserta tamu khusus';

-- --------------------------------------------------------

--
-- Table structure for table `berita`
--

CREATE TABLE `berita` (
  `id` int NOT NULL,
  `admin_id` int DEFAULT NULL COMMENT 'penulis',
  `judul` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_halaman` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'dipakai tautan news.html#model-out-now',
  `isi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tautan_gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teks_tombol` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tautan_tombol` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `tanggal_terbit` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Berita dan pengumuman';

-- --------------------------------------------------------

--
-- Table structure for table `foto`
--

CREATE TABLE `foto` (
  `id` int NOT NULL,
  `keterangan_foto` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'teks pengganti gambar (alt)',
  `lokasi_file_gambar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_foto_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Foto galeri';

-- --------------------------------------------------------

--
-- Table structure for table `item_pesanan`
--

CREATE TABLE `item_pesanan` (
  `id` int NOT NULL,
  `pesanan_id` int NOT NULL,
  `produk_id` int DEFAULT NULL COMMENT 'diisi jika item merchandise',
  `tiket_konser_id` int DEFAULT NULL COMMENT 'diisi jika item tiket',
  `nama_item` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'salinan nama saat pembelian',
  `harga_satuan` decimal(10,2) NOT NULL COMMENT 'salinan harga saat pembelian',
  `jumlah` smallint NOT NULL DEFAULT '1',
  `kode_tiket` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'kode masuk acara, hanya untuk item tiket'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Isi tiap pesanan';

-- --------------------------------------------------------

--
-- Table structure for table `jenis_tiket`
--

CREATE TABLE `jenis_tiket` (
  `id` int NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('standard','vip') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `fasilitas` text COLLATE utf8mb4_unicode_ci COMMENT 'satu fasilitas per baris'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Jenis tiket (Standard dan VIP)';

-- --------------------------------------------------------

--
-- Table structure for table `kategori_foto`
--

CREATE TABLE `kategori_foto` (
  `id` int NOT NULL,
  `nama` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kategori untuk filter galeri foto';

--
-- Dumping data for table `kategori_foto`
--

INSERT INTO `kategori_foto` (`id`, `nama`) VALUES
(2, 'Behind the Scenes'),
(1, 'Concert'),
(3, 'Studio');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_produk`
--

CREATE TABLE `kategori_produk` (
  `id` int NOT NULL,
  `nama` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kategori untuk filter toko';

--
-- Dumping data for table `kategori_produk`
--

INSERT INTO `kategori_produk` (`id`, `nama`) VALUES
(4, 'Accessories'),
(1, 'Apparel'),
(5, 'Bundle'),
(3, 'Poster'),
(2, 'Vinyl');

-- --------------------------------------------------------

--
-- Table structure for table `konser`
--

CREATE TABLE `konser` (
  `id` int NOT NULL,
  `bagian_tur_id` int NOT NULL,
  `nama_tempat` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wilayah` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_acara` date NOT NULL,
  `jam_pintu_buka` time DEFAULT NULL,
  `jam_tampil` time DEFAULT NULL,
  `batas_usia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_penjualan` enum('presale','on_sale','selling_fast','sold_out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'on_sale'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Jadwal tiap pertunjukan beserta tempatnya';

-- --------------------------------------------------------

--
-- Table structure for table `lagu`
--

CREATE TABLE `lagu` (
  `id` int NOT NULL,
  `album_id` int DEFAULT NULL,
  `judul` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `artis_tamu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `durasi_detik` smallint NOT NULL,
  `peringkat_popularitas` tinyint DEFAULT NULL,
  `tahun_rilis` year DEFAULT NULL COMMENT 'diisi untuk lagu single',
  `eksplisit` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lagu populer (Top Tracks)';

-- --------------------------------------------------------

--
-- Table structure for table `lirik`
--

CREATE TABLE `lirik` (
  `id` int NOT NULL,
  `lagu_id` int NOT NULL,
  `nama_bagian` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi_lengkap` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tautan_genius` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lirik lengkap, satu lagu satu baris lirik';

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kata_sandi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'disimpan dalam bentuk hash',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Akun pembeli (opsional)';

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int NOT NULL,
  `pelanggan_id` int DEFAULT NULL COMMENT 'kosong untuk pembeli tanpa akun',
  `nomor_pesanan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pelanggan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_pelanggan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_pengiriman` text COLLATE utf8mb4_unicode_ci COMMENT 'kosong untuk pesanan tiket',
  `total_harga` decimal(12,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Transaksi checkout (merchandise atau tiket)';

-- --------------------------------------------------------

--
-- Table structure for table `pesan_kontak`
--

CREATE TABLE `pesan_kontak` (
  `id` int NOT NULL,
  `dibalas_oleh` int DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `topik` enum('fan_message','feedback','merch','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','read','replied') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pesan dari form kontak';

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int NOT NULL,
  `nama` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_produk_id` int NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `tautan_gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label_produk` enum('new','limited') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Produk merchandise';

-- --------------------------------------------------------

--
-- Table structure for table `tiket_konser`
--

CREATE TABLE `tiket_konser` (
  `id` int NOT NULL,
  `konser_id` int NOT NULL,
  `jenis_tiket_id` int NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `kuota` int NOT NULL,
  `terjual` int NOT NULL DEFAULT '0' COMMENT 'sisa tiket = kuota - terjual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Harga dan kuota tiket untuk tiap konser';

-- --------------------------------------------------------

--
-- Table structure for table `video`
--

CREATE TABLE `video` (
  `id` int NOT NULL,
  `lagu_id` int DEFAULT NULL,
  `judul` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_youtube` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Video YouTube resmi';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_email` (`email`);

--
-- Indexes for table `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bagian_tur`
--
ALTER TABLE `bagian_tur`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_berita_alamat` (`alamat_halaman`),
  ADD KEY `idx_berita_admin` (`admin_id`);

--
-- Indexes for table `foto`
--
ALTER TABLE `foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_foto_kategori` (`kategori_foto_id`);

--
-- Indexes for table `item_pesanan`
--
ALTER TABLE `item_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_item_pesanan_kode_tiket` (`kode_tiket`),
  ADD KEY `idx_item_pesanan_pesanan` (`pesanan_id`),
  ADD KEY `idx_item_pesanan_produk` (`produk_id`),
  ADD KEY `idx_item_pesanan_tiket_konser` (`tiket_konser_id`);

--
-- Indexes for table `jenis_tiket`
--
ALTER TABLE `jenis_tiket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_foto`
--
ALTER TABLE `kategori_foto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kategori_foto_nama` (`nama`);

--
-- Indexes for table `kategori_produk`
--
ALTER TABLE `kategori_produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kategori_produk_nama` (`nama`);

--
-- Indexes for table `konser`
--
ALTER TABLE `konser`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_konser_bagian_tur` (`bagian_tur_id`);

--
-- Indexes for table `lagu`
--
ALTER TABLE `lagu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lagu_peringkat` (`peringkat_popularitas`),
  ADD KEY `idx_lagu_album` (`album_id`);

--
-- Indexes for table `lirik`
--
ALTER TABLE `lirik`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lirik_lagu` (`lagu_id`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pelanggan_email` (`email`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pesanan_nomor` (`nomor_pesanan`),
  ADD KEY `idx_pesanan_pelanggan` (`pelanggan_id`);

--
-- Indexes for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pesan_kontak_admin` (`dibalas_oleh`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produk_kategori` (`kategori_produk_id`);

--
-- Indexes for table `tiket_konser`
--
ALTER TABLE `tiket_konser`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tiket_konser` (`konser_id`,`jenis_tiket_id`),
  ADD KEY `idx_tiket_konser_jenis` (`jenis_tiket_id`);

--
-- Indexes for table `video`
--
ALTER TABLE `video`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_video_lagu` (`lagu_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `album`
--
ALTER TABLE `album`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bagian_tur`
--
ALTER TABLE `bagian_tur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `berita`
--
ALTER TABLE `berita`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `foto`
--
ALTER TABLE `foto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_pesanan`
--
ALTER TABLE `item_pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_tiket`
--
ALTER TABLE `jenis_tiket`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_foto`
--
ALTER TABLE `kategori_foto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori_produk`
--
ALTER TABLE `kategori_produk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `konser`
--
ALTER TABLE `konser`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lagu`
--
ALTER TABLE `lagu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lirik`
--
ALTER TABLE `lirik`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiket_konser`
--
ALTER TABLE `tiket_konser`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `video`
--
ALTER TABLE `video`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `berita`
--
ALTER TABLE `berita`
  ADD CONSTRAINT `fk_berita_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `foto`
--
ALTER TABLE `foto`
  ADD CONSTRAINT `fk_foto_kategori_foto` FOREIGN KEY (`kategori_foto_id`) REFERENCES `kategori_foto` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `item_pesanan`
--
ALTER TABLE `item_pesanan`
  ADD CONSTRAINT `fk_item_pesanan_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_pesanan_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_item_pesanan_tiket_konser` FOREIGN KEY (`tiket_konser_id`) REFERENCES `tiket_konser` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `konser`
--
ALTER TABLE `konser`
  ADD CONSTRAINT `fk_konser_bagian_tur` FOREIGN KEY (`bagian_tur_id`) REFERENCES `bagian_tur` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lagu`
--
ALTER TABLE `lagu`
  ADD CONSTRAINT `fk_lagu_album` FOREIGN KEY (`album_id`) REFERENCES `album` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lirik`
--
ALTER TABLE `lirik`
  ADD CONSTRAINT `fk_lirik_lagu` FOREIGN KEY (`lagu_id`) REFERENCES `lagu` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_pelanggan` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD CONSTRAINT `fk_pesan_kontak_admin` FOREIGN KEY (`dibalas_oleh`) REFERENCES `admin` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori_produk` FOREIGN KEY (`kategori_produk_id`) REFERENCES `kategori_produk` (`id`);

--
-- Constraints for table `tiket_konser`
--
ALTER TABLE `tiket_konser`
  ADD CONSTRAINT `fk_tiket_konser_jenis_tiket` FOREIGN KEY (`jenis_tiket_id`) REFERENCES `jenis_tiket` (`id`),
  ADD CONSTRAINT `fk_tiket_konser_konser` FOREIGN KEY (`konser_id`) REFERENCES `konser` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `video`
--
ALTER TABLE `video`
  ADD CONSTRAINT `fk_video_lagu` FOREIGN KEY (`lagu_id`) REFERENCES `lagu` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
