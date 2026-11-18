<?php
declare(strict_types=1);

/*
 * Berkas ini dipakai semua halaman depan untuk:
 * - menentukan alamat dasar proyek (BASE_URL),
 * - memuat koneksi database dari config/koneksi.php,
 * - menyediakan fungsi bantu yang dipakai berulang di halaman.
 */

if (!defined('BASE_URL')) {
    $akarDokumen = str_replace('\\', '/', (string) (realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ''));
    $akarProyek = str_replace('\\', '/', dirname(__DIR__));
    $basePath = '';

    // Contoh: C:/laragon/www + C:/laragon/www/wallows-s = /wallows-s
    if ($akarDokumen !== '' && stripos($akarProyek, $akarDokumen) === 0) {
        $basePath = substr($akarProyek, strlen($akarDokumen));
    }

    define('BASE_URL', rtrim($basePath, '/'));
}

require_once dirname(__DIR__) . '/config/koneksi.php';

function e(?string $nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

/**
 * Ubah jalur file dari database menjadi URL yang bisa dibuka browser.
 * Jalur absolut seperti https://... dibiarkan apa adanya.
 */
function url_gambar(?string $path, string $bawaan = ''): string
{
    $path = trim((string) $path);

    if ($path === '') {
        return $bawaan;
    }

    if (preg_match('#^(https?:)?//#i', $path) === 1) {
        return $path;
    }

    return BASE_URL . '/' . ltrim(str_replace('\\', '/', $path), '/');
}

function url_halaman(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Rapikan tautan yang tersimpan di database. Data lama masih memakai
 * akhiran .html, sedangkan halaman sekarang sudah memakai .php.
 */
function tautan_halaman(?string $tautan): string
{
    $tautan = trim((string) $tautan);

    if ($tautan === '') {
        return '#';
    }

    if (preg_match('#^(https?:)?//#i', $tautan) === 1) {
        return $tautan;
    }

    if (str_starts_with($tautan, '#')) {
        return $tautan;
    }

    $tautan = preg_replace('/\.html/i', '.php', $tautan) ?? $tautan;

    if (str_starts_with($tautan, '/')) {
        return $tautan;
    }

    return BASE_URL . '/' . ltrim($tautan, '/');
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function format_tanggal(?string $waktu, string $format = 'd M Y'): string
{
    if (empty($waktu)) {
        return '-';
    }

    $timestamp = strtotime($waktu);

    return $timestamp === false ? '-' : date($format, $timestamp);
}

function format_durasi(int $detik): string
{
    if ($detik <= 0) {
        return '-';
    }

    return sprintf('%d:%02d', intdiv($detik, 60), $detik % 60);
}

function panjang_teks(string $teks): int
{
    return function_exists('mb_strlen') ? mb_strlen($teks, 'UTF-8') : strlen($teks);
}

function ringkas_teks(?string $teks, int $panjang = 140): string
{
    $teks = trim((string) $teks);

    if ($teks === '') {
        return '';
    }

    if (panjang_teks($teks) <= $panjang) {
        return $teks;
    }

    $potong = function_exists('mb_substr')
        ? mb_substr($teks, 0, $panjang, 'UTF-8')
        : substr($teks, 0, $panjang);

    return rtrim($potong) . '...';
}

/**
 * Ubah nama kategori menjadi nilai untuk atribut data-category.
 * Contoh: "Behind the Scenes" menjadi "behind_the_scenes".
 */
function slug_kategori(string $teks): string
{
    $teks = strtolower(trim($teks));
    $teks = preg_replace('/[^a-z0-9]+/', '_', $teks) ?? $teks;

    return trim($teks, '_');
}

/**
 * Jalankan SELECT dan kembalikan semua baris sebagai array.
 * Query yang gagal dicatat ke log, halaman tetap tampil dengan data kosong.
 *
 * @return array<int, array<string, mixed>>
 */
function ambil_semua(mysqli $koneksi, string $sql): array
{
    try {
        $hasil = $koneksi->query($sql);
        $baris = $hasil->fetch_all(MYSQLI_ASSOC);
        $hasil->free();
    } catch (mysqli_sql_exception $error) {
        error_log('Query halaman depan gagal: ' . $error->getMessage());

        return [];
    }

    return $baris;
}
