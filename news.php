<?php
require_once __DIR__ . '/includes/situs.php';

$judulHalaman = 'News | Wallows';
$deskripsiHalaman = 'Releases, tour announcements, and news from Wallows.';

$daftarBerita = ambil_semua(
    $koneksi,
    "SELECT id, judul, alamat_halaman, isi, tautan_gambar, teks_tombol, tautan_tombol, tanggal_terbit
     FROM berita
     WHERE status = 'published'
     ORDER BY tanggal_terbit DESC, id DESC"
);

require __DIR__ . '/includes/header.php';
?>

    <header class="page-header">
        <h1>LATEST NEWS</h1>
        <p>Stay up to date with releases, tours, and announcements.</p>
    </header>

    <section class="section fade-in-element" style="max-width: 900px;">
        <?php if ($daftarBerita === []): ?>
            <p style="color: var(--text-muted);">No news has been published yet.</p>
        <?php else: ?>
            <?php foreach ($daftarBerita as $berita): ?>
                <article class="news-item" id="<?= e($berita['alamat_halaman']) ?>" data-news-id="<?= (int) $berita['id'] ?>" data-published="<?= e(format_tanggal($berita['tanggal_terbit'], 'Y-m-d')) ?>">
                    <?php if (!empty($berita['tautan_gambar'])): ?>
                        <img src="<?= e(url_gambar($berita['tautan_gambar'])) ?>" class="news-img" alt="<?= e($berita['judul']) ?>" onerror="this.style.display='none'">
                    <?php endif; ?>
                    <div class="news-content">
                        <span class="news-date"><?= e(strtoupper(format_tanggal($berita['tanggal_terbit'], 'F j, Y'))) ?></span>
                        <h2 class="news-title"><?= e($berita['judul']) ?></h2>
                        <p style="color: var(--text-muted); margin-bottom: 20px; line-height: 1.8;"><?= nl2br(e($berita['isi'])) ?></p>
                        <?php if (!empty($berita['teks_tombol'])): ?>
                            <a href="<?= e(tautan_halaman($berita['tautan_tombol'])) ?>" class="btn btn-outline-dark"><?= e($berita['teks_tombol']) ?></a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
