<?php
if (!defined('ADMIN_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

if (!defined('UPLOAD_RELATIVE_DIR')) {
    define('UPLOAD_RELATIVE_DIR', 'images/uploads');
}

if (!defined('UPLOAD_MAX_BYTES')) {
    define('UPLOAD_MAX_BYTES', 2 * 1024 * 1024);
}

/**
 * Pesan kesalahan bawaan PHP untuk proses unggah berkas.
 */
function pesan_error_upload(int $kode): string
{
    $peta = [
        UPLOAD_ERR_INI_SIZE => 'Ukuran gambar melebihi batas unggah server.',
        UPLOAD_ERR_FORM_SIZE => 'Ukuran gambar melebihi batas formulir.',
        UPLOAD_ERR_PARTIAL => 'Gambar hanya terunggah sebagian. Coba lagi.',
        UPLOAD_ERR_NO_FILE => 'Tidak ada gambar yang dipilih.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara unggahan tidak tersedia.',
        UPLOAD_ERR_CANT_WRITE => 'Gambar gagal ditulis ke disk.',
        UPLOAD_ERR_EXTENSION => 'Unggahan dihentikan oleh ekstensi PHP.',
    ];

    return $peta[$kode] ?? 'Gambar gagal diunggah.';
}

/**
 * Memproses unggahan gambar dengan aturan:
 * 1. Hanya menerima berkas dari formulir POST.
 * 2. Memeriksa kode kesalahan unggahan.
 * 3. Membatasi ukuran maksimal 2 MB.
 * 4. Memvalidasi isi berkas sebagai gambar.
 * 5. Hanya mengizinkan JPEG, PNG, dan WebP.
 * 6. Membuat nama berkas acak yang aman (tidak memakai nama asli pengguna).
 * 7. Menyimpan ke images/uploads dan mengembalikan path relatif.
 *
 * @return array{path: ?string, error: ?string}
 */
function proses_upload_gambar(array $file, string $awalan): array
{
    $hasil = ['path' => null, 'error' => null];

    if (!isset($file['error']) || is_array($file['error'])) {
        $hasil['error'] = 'Data gambar tidak valid.';

        return $hasil;
    }

    $kode = (int) $file['error'];
    if ($kode === UPLOAD_ERR_NO_FILE) {
        return $hasil;
    }

    if ($kode !== UPLOAD_ERR_OK) {
        $hasil['error'] = pesan_error_upload($kode);

        return $hasil;
    }

    $ukuran = (int) ($file['size'] ?? 0);
    if ($ukuran <= 0) {
        $hasil['error'] = 'Berkas gambar kosong.';

        return $hasil;
    }

    if ($ukuran > UPLOAD_MAX_BYTES) {
        $hasil['error'] = 'Ukuran gambar maksimal 2 MB.';

        return $hasil;
    }

    $sementara = (string) ($file['tmp_name'] ?? '');
    if ($sementara === '' || !is_uploaded_file($sementara)) {
        $hasil['error'] = 'Berkas gambar tidak berasal dari unggahan formulir.';

        return $hasil;
    }

    $informasi = @getimagesize($sementara);
    if ($informasi === false || empty($informasi[0]) || empty($informasi[1])) {
        $hasil['error'] = 'Berkas yang dipilih bukan gambar yang valid.';

        return $hasil;
    }

    $petaMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = (string) ($informasi['mime'] ?? '');
    if (!isset($petaMime[$mime])) {
        $hasil['error'] = 'Format gambar harus JPG, PNG, atau WebP.';

        return $hasil;
    }

    $folder = APP_ROOT . '/' . UPLOAD_RELATIVE_DIR;
    if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) {
        $hasil['error'] = 'Folder unggahan tidak dapat dibuat.';

        return $hasil;
    }

    $namaBerkas = $awalan . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $petaMime[$mime];
    $tujuan = $folder . '/' . $namaBerkas;

    if (!move_uploaded_file($sementara, $tujuan)) {
        $hasil['error'] = 'Gambar gagal disimpan. Periksa izin folder images/uploads.';

        return $hasil;
    }

    $hasil['path'] = UPLOAD_RELATIVE_DIR . '/' . $namaBerkas;

    return $hasil;
}

/**
 * URL publik untuk gambar yang tersimpan di basis data.
 */
function url_gambar(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }

    if (preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }

    return BASE_PATH . '/' . ltrim(str_replace('\\', '/', $path), '/');
}

/**
 * Hapus hanya berkas yang berada di folder unggahan aplikasi.
 */
function hapus_gambar_upload(?string $path): void
{
    $relatif = str_replace('\\', '/', trim((string) $path));
    if ($relatif === '' || strpos($relatif, UPLOAD_RELATIVE_DIR . '/') !== 0) {
        return;
    }

    $folder = realpath(APP_ROOT . '/' . UPLOAD_RELATIVE_DIR);
    $berkas = realpath(APP_ROOT . '/' . $relatif);

    if ($folder === false || $berkas === false) {
        return;
    }

    $folder = str_replace('\\', '/', $folder);
    $berkas = str_replace('\\', '/', $berkas);

    if (strpos($berkas, $folder . '/') === 0 && is_file($berkas)) {
        @unlink($berkas);
    }
}
