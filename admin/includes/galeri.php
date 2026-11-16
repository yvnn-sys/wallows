<?php
if (!defined('ADMIN_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

function galeri_url(string $halaman): string
{
    return ADMIN_URL . '/modul/' . $halaman . '/index.php';
}

function galeri_nav(string $aktif): void
{
    $tab = [
        'foto' => ['Foto', 'bi-image'],
        'kategori_foto' => ['Kategori Foto', 'bi-tags'],
        'video' => ['Video', 'bi-camera-video'],
    ];

    echo '<ul class="nav nav-tabs mb-4">';
    foreach ($tab as $kunci => $data) {
        $kelas = $kunci === $aktif ? 'nav-link active' : 'nav-link';
        echo '<li class="nav-item">';
        echo '<a class="' . $kelas . '" href="' . e(galeri_url($kunci)) . '">';
        echo '<i class="bi ' . $data[1] . ' me-1"></i>' . e($data[0]);
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
}

function kode_youtube_valid(string $input): ?string
{
    $input = trim($input);

    if ($input === '') {
        return null;
    }

    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
        return $input;
    }

    $url = filter_var($input, FILTER_VALIDATE_URL);
    if ($url === false) {
        return null;
    }

    $bagian = parse_url($url);
    if ($bagian === false) {
        return null;
    }

    $host = strtolower((string) ($bagian['host'] ?? ''));
    $host = preg_replace('/^www\./', '', $host) ?? $host;
    $path = (string) ($bagian['path'] ?? '');

    if ($host === 'youtu.be') {
        $kode = trim($path, '/');
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $kode) ? $kode : null;
    }

    if (!in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com'], true)) {
        return null;
    }

    if ($path === '/watch') {
        parse_str((string) ($bagian['query'] ?? ''), $query);
        $kode = (string) ($query['v'] ?? '');
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $kode) ? $kode : null;
    }

    if (preg_match('#^/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})#', $path, $cocok)) {
        return $cocok[1];
    }

    return null;
}

function url_youtube(string $kode): string
{
    return 'https://www.youtube.com/watch?v=' . rawurlencode($kode);
}

function thumbnail_youtube(string $kode): string
{
    return 'https://img.youtube.com/vi/' . rawurlencode($kode) . '/mqdefault.jpg';
}
