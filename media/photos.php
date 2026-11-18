<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Photos | Wallows';
$deskripsiHalaman = 'Live performances, behind the scenes, and more.';

$daftarKategori = ambil_semua(
    $koneksi,
    'SELECT id, nama FROM kategori_foto ORDER BY nama ASC'
);

$daftarFoto = ambil_semua(
    $koneksi,
    'SELECT f.id, f.keterangan_foto, f.lokasi_file_gambar, f.kategori_foto_id, k.nama AS kategori
     FROM foto f
     LEFT JOIN kategori_foto k ON k.id = f.kategori_foto_id
     ORDER BY f.id DESC'
);

$gayaHalaman = '
        .masonry {
            display: block;
            column-count: 3;
            column-gap: 20px;
        }
        .masonry img {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 20px;
            display: block;
            transition: transform 0.3s ease, opacity 0.3s ease;
            cursor: pointer;
        }
        .masonry img:hover { transform: scale(1.02); opacity: 0.9; }

        .lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 9999; align-items: center; justify-content: center; }
        .lightbox.open { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 12px; object-fit: contain; }
        .lightbox-close { position: absolute; top: 20px; right: 30px; font-size: 2rem; color: white; cursor: pointer; background: none; border: none; line-height: 1; }
        @media (max-width: 900px) { .masonry { column-count: 2; } }
        @media (max-width: 600px) { .masonry { column-count: 1; } }
';

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>PHOTO GALLERY</h1>
        <p>Live performances, behind the scenes, and more.</p>
    </header>

    <section class="section fade-in-element">
        <div class="category-filter" data-category-filter data-target="#photo-grid" role="group" aria-label="Filter photos by category">
            <button type="button" class="filter-chip is-active" data-filter="all" aria-pressed="true">All <span class="filter-count"></span></button>
            <?php foreach ($daftarKategori as $kategori): ?>
                <button type="button" class="filter-chip" data-filter="<?= e(slug_kategori($kategori['nama'])) ?>" aria-pressed="false">
                    <?= e($kategori['nama']) ?> <span class="filter-count"></span>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="masonry" id="photo-grid">
            <?php if ($daftarFoto === []): ?>
                <p style="color: var(--text-muted);">No photos yet.</p>
            <?php else: ?>
                <?php foreach ($daftarFoto as $foto): ?>
                    <img
                        src="<?= e(url_gambar($foto['lokasi_file_gambar'])) ?>"
                        alt="<?= e($foto['keterangan_foto'] ?: 'Wallows photo') ?>"
                        data-photo-id="<?= (int) $foto['id'] ?>"
                        data-category="<?= e($foto['kategori'] !== null ? slug_kategori($foto['kategori']) : 'uncategorized') ?>"
                        loading="lazy"
                        onclick="openLightbox(this.src)"
                    >
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="category-empty" data-filter-empty>No photos in this category yet.</div>
    </section>

    <div class="lightbox" id="lightbox" onclick="closeLightbox(event)">
        <button class="lightbox-close" onclick="closeLightbox()">✕</button>
        <img id="lightbox-img" src="" alt="Full size photo">
    </div>

    <script>
        function openLightbox(src) {
            document.getElementById('lightbox-img').src = src;
            document.getElementById('lightbox').classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeLightbox(e) {
            if (!e || e.target !== document.getElementById('lightbox-img')) {
                document.getElementById('lightbox').classList.remove('open');
                document.body.style.overflow = '';
            }
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
    </script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
