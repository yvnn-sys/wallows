<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Videos | Wallows';
$deskripsiHalaman = 'Official music videos from Wallows.';

$daftarVideo = ambil_semua(
    $koneksi,
    'SELECT v.id, v.judul, v.kode_youtube, v.lagu_id, l.judul AS lagu
     FROM video v
     LEFT JOIN lagu l ON l.id = v.lagu_id
     ORDER BY v.id ASC'
);

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>VIDEOS</h1>
        <p>Official music videos, live sessions, and more.</p>
    </header>

    <section class="section fade-in-element">
        <div class="video-grid">
            <?php if ($daftarVideo === []): ?>
                <p style="color: var(--text-muted);">No videos yet.</p>
            <?php else: ?>
                <?php foreach ($daftarVideo as $video): ?>
                    <div class="video-item" data-youtube-id="<?= e($video['kode_youtube']) ?>" data-song-id="<?= (int) $video['lagu_id'] ?>">
                        <iframe
                            src="https://www.youtube.com/embed/<?= e(rawurlencode($video['kode_youtube'])) ?>"
                            title="<?= e($video['judul']) ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                        <p><?= e($video['judul']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
