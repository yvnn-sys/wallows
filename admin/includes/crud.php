<?php
declare(strict_types=1);

if (!defined('ADMIN_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

function db_bind(mysqli_stmt $stmt, array &$params): void
{
    if ($params === []) {
        return;
    }

    $tipe = '';
    foreach ($params as $nilai) {
        if (is_int($nilai) || is_bool($nilai)) {
            $tipe .= 'i';
        } elseif (is_float($nilai)) {
            $tipe .= 'd';
        } else {
            $tipe .= 's';
        }
    }

    $referensi = [$tipe];
    foreach ($params as $indeks => $_) {
        $referensi[] = &$params[$indeks];
    }

    call_user_func_array([$stmt, 'bind_param'], $referensi);
}

function db_semua(mysqli $koneksi, string $sql, array $params = []): array
{
    $stmt = $koneksi->prepare($sql);
    db_bind($stmt, $params);
    $stmt->execute();

    $hasil = $stmt->get_result();
    $baris = $hasil->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $baris;
}

function db_satu(mysqli $koneksi, string $sql, array $params = []): ?array
{
    $stmt = $koneksi->prepare($sql);
    db_bind($stmt, $params);
    $stmt->execute();

    $baris = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $baris === null ? null : $baris;
}

function db_nilai(mysqli $koneksi, string $sql, array $params = [], mixed $bawaan = null): mixed
{
    $stmt = $koneksi->prepare($sql);
    db_bind($stmt, $params);
    $stmt->execute();

    $baris = $stmt->get_result()->fetch_row();

    $stmt->close();

    if ($baris === null || !array_key_exists(0, $baris)) {
        return $bawaan;
    }

    return $baris[0] ?? $bawaan;
}

function db_jalankan(mysqli $koneksi, string $sql, array $params = []): array
{
    $stmt = $koneksi->prepare($sql);
    db_bind($stmt, $params);
    $stmt->execute();

    $info = [
        'terpengaruh' => $stmt->affected_rows,
        'id_terakhir' => $stmt->insert_id,
    ];

    $stmt->close();

    return $info;
}

function input_teks(string $kunci, string $bawaan = ''): string
{
    $nilai = $_POST[$kunci] ?? null;

    return is_scalar($nilai) ? (string) $nilai : $bawaan;
}

function input_int(string $kunci, int $bawaan = 0): int
{
    $nilai = $_POST[$kunci] ?? null;

    return is_scalar($nilai) ? (int) $nilai : $bawaan;
}

function input_angka(string $kunci, float $bawaan = 0.0): float
{
    $nilai = $_POST[$kunci] ?? null;
    if (!is_scalar($nilai)) {
        return $bawaan;
    }

    $teks = trim((string) $nilai);
    if ($teks === '' || !is_numeric($teks)) {
        return $bawaan;
    }

    return (float) $teks;
}

function input_pilihan(string $kunci, array $pilihan, ?string $bawaan = null): ?string
{
    $nilai = $_POST[$kunci] ?? null;
    if (!is_scalar($nilai)) {
        return $bawaan;
    }

    $nilai = (string) $nilai;

    return in_array($nilai, $pilihan, true) ? $nilai : $bawaan;
}

/**
 * Validasi banyak field sekaligus. Kunci yang didukung: wajib, min, maks,
 * email, angka. Mengembalikan pesan kesalahan per kunci field.
 *
 * @param array<string, array{nilai?: mixed, label?: string, wajib?: bool, min?: int, maks?: int, email?: bool, angka?: bool}> $aturan
 * @return array<string, string>
 */
function validasi_field(array $aturan): array
{
    $errors = [];

    foreach ($aturan as $kunci => $opsi) {
        $nilai = trim((string) ($opsi['nilai'] ?? ''));
        $label = (string) ($opsi['label'] ?? $kunci);

        if (!empty($opsi['wajib']) && $nilai === '') {
            $errors[$kunci] = $label . ' wajib diisi.';
            continue;
        }

        if ($nilai === '') {
            continue;
        }

        if (isset($opsi['min']) && panjang_teks($nilai) < (int) $opsi['min']) {
            $errors[$kunci] = $label . ' minimal ' . (int) $opsi['min'] . ' karakter.';
            continue;
        }

        if (isset($opsi['maks']) && panjang_teks($nilai) > (int) $opsi['maks']) {
            $errors[$kunci] = $label . ' maksimal ' . (int) $opsi['maks'] . ' karakter.';
            continue;
        }

        if (!empty($opsi['email']) && !filter_var($nilai, FILTER_VALIDATE_EMAIL)) {
            $errors[$kunci] = $label . ' harus berupa alamat email yang valid.';
            continue;
        }

        if (!empty($opsi['angka']) && !is_numeric($nilai)) {
            $errors[$kunci] = $label . ' harus berupa angka.';
        }
    }

    return $errors;
}

function kelas_error(array $errors, string $kunci): string
{
    return isset($errors[$kunci]) ? ' is-invalid' : '';
}

function pesan_error(array $errors, string $kunci): string
{
    if (!isset($errors[$kunci])) {
        return '';
    }

    return '<div class="invalid-feedback">' . e($errors[$kunci]) . '</div>';
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function hitung_paginasi(int $total, int $perHalaman = 10, string $parameter = 'hal'): array
{
    $perHalaman = max(1, $perHalaman);
    $totalHalaman = max(1, (int) ceil($total / $perHalaman));

    $mentah = $_GET[$parameter] ?? 1;
    $halaman = is_scalar($mentah) ? (int) $mentah : 1;
    $halaman = max(1, min($totalHalaman, $halaman));

    $offset = ($halaman - 1) * $perHalaman;

    return [
        'total' => $total,
        'per_halaman' => $perHalaman,
        'halaman' => $halaman,
        'total_halaman' => $totalHalaman,
        'offset' => $offset,
        'dari' => $total === 0 ? 0 : $offset + 1,
        'sampai' => min($total, $offset + $perHalaman),
        'parameter' => $parameter,
    ];
}

function render_paginasi(array $info, array $query = []): string
{
    $totalHalaman = (int) ($info['total_halaman'] ?? 1);
    if ($totalHalaman <= 1) {
        return '';
    }

    $halaman = (int) ($info['halaman'] ?? 1);
    $parameter = (string) ($info['parameter'] ?? 'hal');

    $tautan = static function (int $nomor) use ($query, $parameter): string {
        $query[$parameter] = $nomor;

        return '?' . http_build_query($query);
    };

    $html = '<nav aria-label="Navigasi halaman"><ul class="pagination pagination-sm mb-0">';

    $html .= $halaman > 1
        ? '<li class="page-item"><a class="page-link" href="' . e($tautan($halaman - 1)) . '" aria-label="Sebelumnya">&laquo;</a></li>'
        : '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';

    $awal = max(1, $halaman - 2);
    $akhir = min($totalHalaman, $halaman + 2);

    if ($awal > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($tautan(1)) . '">1</a></li>';
        if ($awal > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
    }

    for ($nomor = $awal; $nomor <= $akhir; $nomor++) {
        $html .= $nomor === $halaman
            ? '<li class="page-item active" aria-current="page"><span class="page-link">' . $nomor . '</span></li>'
            : '<li class="page-item"><a class="page-link" href="' . e($tautan($nomor)) . '">' . $nomor . '</a></li>';
    }

    if ($akhir < $totalHalaman) {
        if ($akhir < $totalHalaman - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . e($tautan($totalHalaman)) . '">' . $totalHalaman . '</a></li>';
    }

    $html .= $halaman < $totalHalaman
        ? '<li class="page-item"><a class="page-link" href="' . e($tautan($halaman + 1)) . '" aria-label="Berikutnya">&raquo;</a></li>'
        : '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';

    return $html . '</ul></nav>';
}

function panjang_teks(string $teks): int
{
    return function_exists('mb_strlen') ? mb_strlen($teks, 'UTF-8') : strlen($teks);
}

function ringkas_teks(?string $teks, int $panjang = 80): string
{
    $teks = trim((string) $teks);

    if ($teks === '') {
        return '-';
    }

    if (panjang_teks($teks) <= $panjang) {
        return $teks;
    }

    $potong = function_exists('mb_substr')
        ? mb_substr($teks, 0, $panjang, 'UTF-8')
        : substr($teks, 0, $panjang);

    return rtrim($potong) . '…';
}

function url_gambar(?string $path, string $bawaan = ''): string
{
    $path = trim((string) $path);

    if ($path === '') {
        return $bawaan;
    }

    if (preg_match('#^(https?:)?//#i', $path) === 1) {
        return $path;
    }

    return BASE_PATH . '/' . ltrim(str_replace('\\', '/', $path), '/');
}

/**
 * Simpan gambar dari $_FILES ke images/unggahan. Mengembalikan path relatif
 * untuk kolom database, atau null jika pengguna tidak memilih berkas.
 *
 * @param array<string, mixed> $file Satu entri dari $_FILES.
 * @throws RuntimeException Jika berkas tidak valid, terlalu besar, atau gagal disimpan.
 */
function unggah_gambar(array $file, int $maksByte = 2097152): ?string
{
    $kodeError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($kodeError === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($kodeError !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Berkas gagal diunggah. Silakan pilih ulang gambarnya.');
    }

    $ukuran = (int) ($file['size'] ?? 0);
    if ($ukuran > $maksByte) {
        $batas = number_format($maksByte / 1048576, 1, ',', '.');
        throw new RuntimeException('Ukuran gambar maksimal ' . $batas . ' MB.');
    }

    $sementara = (string) ($file['tmp_name'] ?? '');
    if ($sementara === '' || !is_uploaded_file($sementara)) {
        throw new RuntimeException('Berkas tidak valid. Silakan pilih ulang gambarnya.');
    }

    $info = @getimagesize($sementara);
    if ($info === false) {
        throw new RuntimeException('Berkas yang dipilih bukan gambar yang valid.');
    }

    $petaMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $mime = (string) ($info['mime'] ?? '');
    if (!isset($petaMime[$mime])) {
        throw new RuntimeException('Format gambar harus JPG, PNG, WEBP, atau GIF.');
    }

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0775, true) && !is_dir(UPLOAD_DIR)) {
        throw new RuntimeException('Folder unggahan tidak dapat dibuat.');
    }

    $namaBerkas = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $petaMime[$mime];
    $tujuan = UPLOAD_DIR . '/' . $namaBerkas;

    if (!move_uploaded_file($sementara, $tujuan)) {
        throw new RuntimeException('Gambar gagal disimpan. Coba lagi.');
    }

    return UPLOAD_RELATIF . '/' . $namaBerkas;
}

/**
 * Hanya menghapus berkas di dalam images/unggahan agar aset bawaan situs
 * seperti images/dylan.jpg tidak ikut terhapus.
 */
function hapus_gambar(?string $path): bool
{
    $path = trim((string) $path);

    if ($path === '' || strpos($path, UPLOAD_RELATIF . '/') !== 0) {
        return false;
    }

    $berkas = UPLOAD_DIR . '/' . basename($path);

    if (is_file($berkas)) {
        return unlink($berkas);
    }

    return false;
}

/**
 * Escape karakter %, _, dan \ agar input pengguna tidak menjadi wildcard LIKE.
 */
function pola_like(string $teks): string
{
    $teks = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $teks);

    return '%' . $teks . '%';
}
