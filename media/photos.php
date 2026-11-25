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
        .photo-card {
            margin: 0 0 20px;
            cursor: pointer;
            break-inside: avoid;
            -webkit-column-break-inside: avoid;
            page-break-inside: avoid;
        }
        .photo-card img {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 0;
            display: block;
            transition: transform 0.3s ease, opacity 0.3s ease;
            cursor: pointer;
        }
        .photo-card img:hover { transform: scale(1.02); opacity: 0.9; }
        .photo-caption { margin-top: 10px; }
        .photo-title {
            display: block;
            font-family: "Space Grotesk", sans-serif;
            font-size: 0.92rem;
            font-weight: 500;
            line-height: 1.35;
            color: var(--text-main);
            overflow-wrap: anywhere;
        }
        .photo-category {
            display: inline-block;
            margin-top: 6px;
            padding: 2px 9px;
            font-family: "Space Grotesk", sans-serif;
            font-size: 0.66rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--ochre);
            border: 1px solid rgba(215, 130, 94, 0.45);
            border-radius: 999px;
        }

        .lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 9999; align-items: center; justify-content: center; }
        .lightbox.open { display: flex; }
        .lightbox-figure {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 90vw;
            margin: 0;
        }
        .lightbox img { max-width: 90vw; max-height: 78vh; border-radius: 12px; object-fit: contain; }
        .lightbox-caption {
            margin-top: 16px;
            max-width: 60ch;
            text-align: center;
            font-size: 0.95rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.82);
        }
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
                    <?php
                    $keteranganFoto = trim((string) ($foto['keterangan_foto'] ?? ''));
                    $teksAlternatif = $keteranganFoto !== '' ? $keteranganFoto : 'Wallows photo';
                    $namaKategori = trim((string) ($foto['kategori'] ?? ''));
                    $slugKategori = $namaKategori !== '' ? slug_kategori($namaKategori) : 'uncategorized';
                    ?>
                    <figure
                        class="photo-card"
                        data-photo-id="<?= (int) $foto['id'] ?>"
                        data-category="<?= e($slugKategori) ?>"
                        onclick="openLightbox(this.querySelector('img'))"
                    >
                        <img
                            src="<?= e(url_gambar($foto['lokasi_file_gambar'])) ?>"
                            alt="<?= e($teksAlternatif) ?>"
                            data-caption="<?= e($keteranganFoto) ?>"
                            loading="lazy"
                        >
                        <?php if ($keteranganFoto !== '' || $namaKategori !== ''): ?>
                            <figcaption class="photo-caption">
                                <?php if ($keteranganFoto !== ''): ?>
                                    <span class="photo-title"><?= e($keteranganFoto) ?></span>
                                <?php endif; ?>
                                <?php if ($namaKategori !== ''): ?>
                                    <span class="photo-category"><?= e($namaKategori) ?></span>
                                <?php endif; ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="category-empty" data-filter-empty>No photos in this category yet.</div>
    </section>

    <div class="lightbox" id="lightbox" onclick="closeLightbox(event)">
        <button class="lightbox-close" onclick="closeLightbox()">✕</button>
        <figure class="lightbox-figure">
            <img id="lightbox-img" src="" alt="Full size photo">
            <figcaption class="lightbox-caption" id="lightbox-caption" hidden></figcaption>
        </figure>
    </div>

    <script>
        function openLightbox(gambar) {
            if (!gambar) return;

            const lightbox = document.getElementById('lightbox');
            const pratinjau = document.getElementById('lightbox-img');
            const keterangan = document.getElementById('lightbox-caption');
            const teksKeterangan = (gambar.dataset.caption || '').trim();

            pratinjau.src = gambar.src;
            pratinjau.alt = gambar.alt;
            keterangan.textContent = teksKeterangan;
            keterangan.hidden = teksKeterangan === '';

            lightbox.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeLightbox(e) {
            if (e && e.target !== e.currentTarget) return;

            document.getElementById('lightbox').classList.remove('open');
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
    </script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
