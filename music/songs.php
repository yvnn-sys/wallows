<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Popular Songs | Wallows';
$deskripsiHalaman = 'The most popular Wallows songs across all releases.';

$daftarLagu = ambil_semua(
    $koneksi,
    'SELECT l.id, l.judul, l.artis_tamu, l.durasi_detik, l.peringkat_popularitas, l.tahun_rilis,
            a.judul AS album, a.tahun_rilis AS tahun_album
     FROM lagu l
     LEFT JOIN album a ON a.id = l.album_id
     ORDER BY l.peringkat_popularitas ASC, l.id ASC
     LIMIT 10'
);

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>POPULAR SONGS</h1>
        <p>Fan favorites across all releases.</p>
    </header>

    <section class="section fade-in-element" style="max-width:800px;">
        <div class="section-header" style="text-align:left; margin-bottom:10px;">
            <h2>Top Tracks</h2>
        </div>

        <?php if ($daftarLagu === []): ?>
            <p style="color: var(--text-muted);">No song data yet.</p>
        <?php else: ?>
            <?php foreach ($daftarLagu as $nomor => $lagu): ?>
                <?php
                $judulAlbum = $lagu['album'] ?? 'Single';
                $tahunLagu = $lagu['tahun_album'] ?: $lagu['tahun_rilis'];

                $keteranganLagu = $judulAlbum;
                if (!empty($tahunLagu)) {
                    $keteranganLagu .= ' · ' . $tahunLagu;
                }
                ?>
                <div class="track-row" data-song-id="<?= (int) $lagu['id'] ?>" data-popularity-rank="<?= (int) $lagu['peringkat_popularitas'] ?>" data-duration-seconds="<?= (int) $lagu['durasi_detik'] ?>">
                    <span class="track-num"><?= $nomor + 1 ?></span>
                    <span class="track-main">
                        <span class="track-name">
                            <?= e($lagu['judul']) ?>
                            <?php if (!empty($lagu['artis_tamu'])): ?>
                                <span class="track-feat">feat. <?= e($lagu['artis_tamu']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="track-album"><?= e($keteranganLagu) ?></span>
                    </span>
                    <span class="track-duration"><?= e(format_durasi((int) $lagu['durasi_detik'])) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="text-align:center; margin-top:40px;">
            <a href="https://open.spotify.com/artist/0NIPkIjTV8mB795yEIiPYL?si=m8xNJom0SKamhtZFzigRgQ" target="_blank" rel="noopener" class="btn btn-primary">Listen on Spotify</a>
        </div>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
