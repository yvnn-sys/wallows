<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Music | Wallows';
$deskripsiHalaman = 'Albums, EPs, and singles from Wallows.';

$daftarAlbum = ambil_semua(
    $koneksi,
    'SELECT id, judul, jenis, tahun_rilis, tahun_rilis_akhir, jumlah_lagu, deskripsi, gambar_sampul, tautan_spotify
     FROM album
     ORDER BY tahun_rilis DESC, id ASC'
);

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>DISCOGRAPHY</h1>
        <p>Explore all official album releases.</p>
    </header>

    <section class="section fade-in-element" style="position: relative; overflow: visible;">
        <?php if ($daftarAlbum === []): ?>
            <p style="color: var(--text-muted);">No album data yet.</p>
        <?php else: ?>
            <div class="carousel-wrapper">
                <button class="carousel-btn prev-btn" aria-label="Previous album"><i class="fa-solid fa-chevron-left"></i></button>
                <div class="carousel-track">
                    <?php foreach ($daftarAlbum as $album): ?>
                        <?php
                        $rentangTahun = (string) $album['tahun_rilis'];
                        if (!empty($album['tahun_rilis_akhir'])) {
                            $rentangTahun .= '–' . $album['tahun_rilis_akhir'];
                        }
                        ?>
                        <div class="carousel-card" data-album-id="<?= (int) $album['id'] ?>" data-type="<?= e($album['jenis']) ?>" data-release-year="<?= e((string) $album['tahun_rilis']) ?>" style="text-align: center;">
                            <?php if (!empty($album['gambar_sampul'])): ?>
                                <img src="<?= e(url_gambar($album['gambar_sampul'])) ?>" alt="<?= e($album['judul']) ?> Album Cover" onerror="this.style.display='none'">
                            <?php endif; ?>
                            <h2><?= e($album['judul']) ?></h2>
                            <p style="color: var(--text-muted); margin: 8px 0 5px;"><?= e($rentangTahun) ?> · <?= (int) $album['jumlah_lagu'] ?> tracks</p>
                            <p style="color: var(--text-muted); font-size:0.85rem; margin-bottom:20px;"><?= e($album['deskripsi'] ?? '') ?></p>
                            <?php if (!empty($album['tautan_spotify'])): ?>
                                <a href="<?= e($album['tautan_spotify']) ?>" target="_blank" rel="noopener" class="btn btn-primary" style="width:100%; display:block;">Listen on Spotify</a>
                            <?php else: ?>
                                <span style="display:block; color: var(--text-muted); font-size:0.8rem; padding:12px 0;">Streaming link coming soon</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-btn next-btn" aria-label="Next album"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
            <div class="carousel-dots">
                <?php foreach ($daftarAlbum as $nomor => $album): ?>
                    <span class="carousel-dot <?= $nomor === 0 ? 'active' : '' ?>" data-slide="<?= (int) $nomor ?>"></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="section fade-in-element" style="max-width:900px;">
        <div class="section-header">
            <h2>SPOTIFY PLAYER</h2>
            <p>Listen directly — no app needed</p>
        </div>
        <iframe style="border-radius:12px; width:100%; border:none;"
            src="https://open.spotify.com/embed/artist/0NIPkIjTV8mB795yEIiPYL?utm_source=generator&theme=0" height="352"
            allowfullscreen allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
            loading="lazy"></iframe>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
