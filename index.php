<?php
require_once __DIR__ . '/includes/situs.php';

$judulHalaman = 'Wallows | Official Site';
$deskripsiHalaman = 'The official website of indie rock band Wallows — music, tour dates, merch, and more.';

$daftarBerita = ambil_semua(
    $koneksi,
    "SELECT id, judul, alamat_halaman, isi, tautan_gambar, tanggal_terbit
     FROM berita
     WHERE status = 'published'
     ORDER BY tanggal_terbit DESC, id DESC
     LIMIT 3"
);

require __DIR__ . '/includes/header.php';
?>

    <section class="hero-new">
        <div class="hero-bg-container">
            <img src="<?= e(url_gambar('images/homepg2.png')) ?>" alt="Wallows band members" class="hero-bg-image">
            <div class="hero-bg-overlay"></div>
        </div>
        <div class="hero-content-new">
            <h1>WELCOME TO<br>WALLOWS</h1>
            <p>Explore the music, the memories, and everything in between. A cinematic journey through the alternative soundscape of the modern era.</p>
            <div class="hero-btns">
                <a href="<?= e(url_halaman('music/albums.php')) ?>" class="btn btn-primary">Listen Now</a>
                <a href="<?= e(url_halaman('tour/schedule.php')) ?>" class="btn btn-outline">Tour Dates</a>
            </div>
        </div>
    </section>

    <section class="section fade-in-element">
        <div class="news-header-block">
            <h2 class="news-title-accent">LATEST NEWS</h2>
        </div>
        <div class="news-grid-home">
            <?php if ($daftarBerita === []): ?>
                <p style="color: var(--text-muted);">No news has been published yet.</p>
            <?php else: ?>
                <?php foreach ($daftarBerita as $berita): ?>
                    <div class="news-card-home" data-news-id="<?= (int) $berita['id'] ?>" data-published="<?= e(format_tanggal($berita['tanggal_terbit'], 'Y-m-d')) ?>">
                        <?php if (!empty($berita['tautan_gambar'])): ?>
                            <div class="news-card-image-wrap">
                                <img src="<?= e(url_gambar($berita['tautan_gambar'])) ?>" alt="<?= e($berita['judul']) ?>" onerror="this.style.display='none'">
                            </div>
                        <?php endif; ?>
                        <div class="news-card-content-home">
                            <span class="news-card-date-home"><?= e(strtoupper(format_tanggal($berita['tanggal_terbit'], 'F j, Y'))) ?></span>
                            <h3 class="news-card-title-home"><?= e($berita['judul']) ?></h3>
                            <p class="news-card-excerpt-home"><?= e(ringkas_teks($berita['isi'], 140)) ?></p>
                            <a href="<?= e(url_halaman('news.php')) ?>#<?= e($berita['alamat_halaman']) ?>" class="news-card-link-home">Read More <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="news-footer-actions">
            <a href="<?= e(url_halaman('news.php')) ?>" class="btn btn-outline-dark">View All News</a>
        </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
