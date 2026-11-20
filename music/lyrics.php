<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Lyrics | Wallows';
$deskripsiHalaman = 'Lyrics from Wallows songs.';

$daftarLirik = ambil_semua(
    $koneksi,
    'SELECT li.id, li.nama_bagian, li.isi_lengkap, li.tautan_genius,
            l.judul, l.artis_tamu, l.eksplisit, l.tahun_rilis AS tahun_lagu,
            a.judul AS album, a.tahun_rilis AS tahun_album
     FROM lirik li
     JOIN lagu l ON l.id = li.lagu_id
     LEFT JOIN album a ON a.id = l.album_id
     ORDER BY l.peringkat_popularitas ASC, li.id ASC'
);

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>LYRICS</h1>
        <p>Words that hit different.</p>
    </header>

    <section class="section fade-in-element" style="max-width:800px;">
        <?php if ($daftarLirik === []): ?>
            <p style="color: var(--text-muted);">No lyrics data yet.</p>
        <?php else: ?>
            <?php foreach ($daftarLirik as $lirik): ?>
                <?php
                $judulLagu = $lirik['judul'];
                if (!empty($lirik['artis_tamu'])) {
                    $judulLagu .= ' (feat. ' . $lirik['artis_tamu'] . ')';
                }

                $namaAlbum = $lirik['album'] ?? 'Single';
                $tahunLirik = $lirik['tahun_album'] ?: $lirik['tahun_lagu'];
                $tautanGenius = $lirik['tautan_genius'] ?: 'https://genius.com';

                $barisLirik = preg_split('/\r\n|\r|\n/', trim((string) $lirik['isi_lengkap'])) ?: [];
                $pratinjauLirik = implode("\n", array_slice($barisLirik, 0, 4));
                ?>
                <div class="lyric-block" data-song-id="<?= (int) $lirik['id'] ?>" data-section-order="1"
                    data-title="<?= e($judulLagu) ?>"
                    data-album="<?= e($namaAlbum) ?>" data-year="<?= e((string) $tahunLirik) ?>"
                    data-lyrics-id="lirik-<?= (int) $lirik['id'] ?>"
                    data-genius-url="<?= e($tautanGenius) ?>"
                    data-explicit="<?= (int) $lirik['eksplisit'] === 1 ? 'true' : 'false' ?>"
                    role="button" tabindex="0" aria-haspopup="dialog"
                    aria-label="Read full lyrics for <?= e($judulLagu) ?> by Wallows">
                    <h3><?= e($lirik['judul']) ?> — <?= e($lirik['nama_bagian']) ?></h3>
                    <p><?= nl2br(e($pratinjauLirik)) ?></p>
                    <span class="lyric-open">Read full lyrics <i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <p style="color:var(--text-muted); font-size:0.85rem; text-align:center; margin-top:20px;">
            Click any lyric card to read the full lyrics. Lyrics sourced from
            <a href="https://genius.com" target="_blank" rel="noopener" style="color:var(--ochre);">Genius</a>
            for educational purposes.
        </p>
    </section>

    <div class="lyrics-modal" id="lyrics-modal" role="dialog" aria-modal="true"
        aria-labelledby="lyrics-modal-title" aria-hidden="true">
        <div class="lyrics-modal-content" role="document">
            <button type="button" class="lyrics-modal-close" id="lyrics-modal-close"
                aria-label="Close full lyrics">&times;</button>
            <div class="lyrics-modal-header">
                <p class="lyrics-modal-eyebrow">Wallows · Full Lyrics</p>
                <h2 id="lyrics-modal-title"></h2>
                <div class="lyrics-modal-meta-row">
                    <p class="lyrics-modal-meta" id="lyrics-modal-meta"></p>
                    <span class="lyrics-explicit" id="lyrics-modal-explicit" hidden>Explicit</span>
                </div>
            </div>
            <div class="lyrics-modal-body" id="lyrics-modal-body" tabindex="0"></div>
            <div class="lyrics-modal-footer">
                <span class="lyrics-modal-source">Source: Genius</span>
                <a class="btn btn-primary lyrics-modal-genius" id="lyrics-modal-genius"
                    href="https://genius.com" target="_blank" rel="noopener">View on Genius</a>
            </div>
        </div>
    </div>

    <?php foreach ($daftarLirik as $lirik): ?>
        <template id="lirik-<?= (int) $lirik['id'] ?>">
            <p><?= nl2br(e($lirik['isi_lengkap'])) ?></p>
        </template>
    <?php endforeach; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
