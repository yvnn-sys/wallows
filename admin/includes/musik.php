<?php
if (!defined('ADMIN_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

/**
 * Pilihan nilai enum album.jenis.
 */
function opsi_jenis_album(): array
{
    return [
        'album' => 'Album',
        'ep' => 'EP',
        'single_collection' => 'Kumpulan single',
    ];
}

function nama_jenis_album(string $jenis): string
{
    return opsi_jenis_album()[$jenis] ?? $jenis;
}

function badge_jenis_album(string $jenis): string
{
    $peta = [
        'album' => ['Album', 'primary'],
        'ep' => ['EP', 'info'],
        'single_collection' => ['Kumpulan single', 'warning'],
    ];

    [$label, $warna] = $peta[$jenis] ?? [$jenis, 'secondary'];

    return '<span class="badge text-bg-' . $warna . '">' . e($label) . '</span>';
}

/**
 * Ubah durasi detik menjadi format menit:detik.
 */
function format_durasi(int $detik): string
{
    if ($detik <= 0) {
        return '-';
    }

    return sprintf('%d:%02d', intdiv($detik, 60), $detik % 60);
}

/**
 * Terima input durasi dalam detik atau format menit:detik.
 */
function parse_durasi(string $nilai): ?int
{
    $nilai = trim($nilai);
    if ($nilai === '') {
        return null;
    }

    if (preg_match('/^(\d{1,3}):([0-5]?\d)$/', $nilai, $cocok)) {
        return ((int) $cocok[1]) * 60 + (int) $cocok[2];
    }

    if (ctype_digit($nilai)) {
        return (int) $nilai;
    }

    return null;
}

function url_musik(string $tab): string
{
    $peta = [
        'album' => '/modul/album/index.php',
        'lagu' => '/modul/lagu/index.php',
        'lirik' => '/modul/lirik/index.php',
    ];

    return ADMIN_URL . ($peta[$tab] ?? $peta['album']);
}

/**
 * Sub-navigasi Musik yang tampil di dalam halaman.
 */
function render_musik_nav(string $aktif): void
{
    $tab = [
        'album' => ['Album', 'bi-disc'],
        'lagu' => ['Lagu', 'bi-music-note-beamed'],
        'lirik' => ['Lirik', 'bi-file-text'],
    ];
    ?>
    <div class="card card-outline card-primary mb-4">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($tab as $kunci => [$label, $ikon]): ?>
                    <li class="nav-item">
                        <a
                            href="<?= e(url_musik($kunci)) ?>"
                            class="nav-link <?= $aktif === $kunci ? 'active' : '' ?>"
                            role="tab"
                        >
                            <i class="bi <?= e($ikon) ?> me-1"></i><?= e($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Ambil satu baris berdasarkan id dengan prepared statement.
 */
function musik_ambil_satu(mysqli $koneksi, string $sql, int $id): ?array
{
    try {
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $baris = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (mysqli_sql_exception $error) {
        error_log('Query detail musik gagal: ' . $error->getMessage());

        return null;
    }

    return $baris ?: null;
}

function hitung_baris(mysqli $koneksi, string $sql, int $id): int
{
    try {
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $jumlah = (int) ($stmt->get_result()->fetch_assoc()['jumlah'] ?? 0);
        $stmt->close();
    } catch (mysqli_sql_exception $error) {
        error_log('Query hitung musik gagal: ' . $error->getMessage());

        return 0;
    }

    return $jumlah;
}
