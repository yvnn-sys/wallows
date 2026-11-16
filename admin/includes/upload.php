<?php
if (!defined('ADMIN_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

const UPLOAD_MAKS_UKURAN = 2097152;

function upload_gambar(array $file, string $subfolder = 'images/uploads'): array
{
    $kodeError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($kodeError === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if ($kodeError !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Upload gambar gagal. Kode kesalahan: ' . $kodeError . '.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['path' => null, 'error' => 'Berkas upload tidak valid.'];
    }

    $ukuran = (int) ($file['size'] ?? 0);
    if ($ukuran <= 0 || $ukuran > UPLOAD_MAKS_UKURAN) {
        return ['path' => null, 'error' => 'Ukuran gambar maksimal 2 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmp);
    $petaMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($petaMime[$mime])) {
        return ['path' => null, 'error' => 'Format gambar harus JPG, PNG, atau WEBP.'];
    }

    if (@getimagesize($tmp) === false) {
        return ['path' => null, 'error' => 'Berkas yang diunggah bukan gambar yang valid.'];
    }

    $folder = trim(str_replace('\\', '/', $subfolder), '/');
    $akar = dirname(__DIR__, 2);
    $direktori = $akar . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folder);

    if (!is_dir($direktori) && !mkdir($direktori, 0775, true) && !is_dir($direktori)) {
        return ['path' => null, 'error' => 'Folder penyimpanan gambar tidak dapat dibuat.'];
    }

    $namaBaru = bin2hex(random_bytes(16)) . '.' . $petaMime[$mime];
    $tujuan = $direktori . DIRECTORY_SEPARATOR . $namaBaru;

    if (!move_uploaded_file($tmp, $tujuan)) {
        return ['path' => null, 'error' => 'Gambar gagal disimpan.'];
    }

    return ['path' => $folder . '/' . $namaBaru, 'error' => null];
}

function hapus_file_upload(?string $path, string $subfolder = 'images/uploads'): void
{
    if ($path === null || trim($path) === '') {
        return;
    }

    $folder = trim(str_replace('\\', '/', $subfolder), '/');
    $pathNormal = ltrim(str_replace('\\', '/', $path), '/');

    if (strpos($pathNormal, $folder . '/') !== 0) {
        return;
    }

    $akar = dirname(__DIR__, 2);
    $file = $akar . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $pathNormal);

    if (is_file($file)) {
        @unlink($file);
    }
}

function tautan_media(?string $path): string
{
    $path = trim((string) $path);

    if ($path === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return BASE_PATH . '/' . ltrim(str_replace('\\', '/', $path), '/');
}
