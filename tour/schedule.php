<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Tour Dates | Wallows';
$deskripsiHalaman = 'Tour schedule and show details for Wallows.';

$daftarKonser = ambil_semua(
    $koneksi,
    'SELECT k.id, k.nama_tempat, k.kota, k.wilayah, k.tanggal_acara, k.jam_pintu_buka, k.jam_tampil,
            k.batas_usia, k.catatan, k.status_penjualan,
            b.id AS bagian_id, b.nama AS bagian_nama, b.tamu_khusus
     FROM konser k
     JOIN bagian_tur b ON b.id = k.bagian_tur_id
     ORDER BY k.tanggal_acara ASC, k.id ASC'
);

$daftarLeg = [];
foreach ($daftarKonser as $konser) {
    $idLeg = (int) $konser['bagian_id'];

    if (!isset($daftarLeg[$idLeg])) {
        $daftarLeg[$idLeg] = [
            'nama' => $konser['bagian_nama'],
            'tamu_khusus' => $konser['tamu_khusus'],
            'daftar_konser' => [],
        ];
    }

    $daftarLeg[$idLeg]['daftar_konser'][] = $konser;
}

$gayaHalaman = '
        .tour-row {
            display: grid;
            grid-template-columns: 96px minmax(0, 1fr) 200px;
            align-items: center;
            column-gap: 28px;
            padding: 22px 16px;
            border-bottom: 1px solid var(--border);
            border-radius: 10px;
            transition: background 0.2s ease;
        }
        .tour-row:hover { background: rgba(255,255,255,0.03); }
        .tour-date { font-family: "Space Grotesk", sans-serif; font-size: 1.3rem; font-weight: 800; color: var(--ochre); white-space: nowrap; }
        .tour-info { display: flex; flex-direction: column; align-items: flex-start; text-align: left; gap: 4px; padding: 0; min-width: 0; }
        .tour-location { font-size: 1.05rem; font-weight: 700; color: #ffffff; line-height: 1.3; }
        .tour-venue { font-size: 0.9rem; color: var(--text-muted); line-height: 1.4; }
        .tour-details-meta { font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; }
        .tour-cta { justify-self: end; display: flex; }
        .tour-cta .btn, .tour-cta > span { white-space: nowrap; text-align: center; }
        @media (max-width: 780px) {
            .tour-row { grid-template-columns: 80px minmax(0, 1fr); row-gap: 14px; column-gap: 20px; text-align: left; }
            .tour-date { width: auto; align-items: flex-start; }
            .tour-cta { grid-column: 2; justify-self: start; }
        }
        @media (max-width: 520px) {
            .tour-row { grid-template-columns: 1fr; row-gap: 10px; padding: 18px 12px; }
            .tour-cta { grid-column: 1; }
        }
';

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>WORLD TOUR ROADMAP</h1>
        <p>Detailed itinerary, set times, and support acts for every Wallows tour.</p>
    </header>

    <section class="section fade-in-element" style="max-width: 1000px;">
        <?php if ($daftarLeg === []): ?>
            <p style="color: var(--text-muted);">No tour dates yet.</p>
        <?php else: ?>
            <?php $nomorLeg = 0; ?>
            <?php foreach ($daftarLeg as $leg): ?>
                <?php $nomorLeg++; ?>
                <div class="tour-leg-header" <?= $nomorLeg > 1 ? 'style="margin-top: 50px;"' : '' ?>>
                    <h2><?= e($leg['nama']) ?></h2>
                    <?php if (!empty($leg['tamu_khusus'])): ?>
                        <span class="support-act">Special Guests: <?= e($leg['tamu_khusus']) ?></span>
                    <?php endif; ?>
                </div>

                <?php foreach ($leg['daftar_konser'] as $konser): ?>
                    <?php
                    $slugKonser = 'konser-' . (int) $konser['id'];
                    $sudahHabis = $konser['status_penjualan'] === 'sold_out';

                    $keteranganWaktu = 'Doors: ' . format_tanggal($konser['jam_pintu_buka'], 'g:i A')
                        . ' | Show: ' . format_tanggal($konser['jam_tampil'], 'g:i A');

                    if (!empty($konser['batas_usia'])) {
                        $keteranganWaktu .= ' · Age Limit: ' . $konser['batas_usia'];
                    }

                    if (!empty($konser['catatan'])) {
                        $keteranganWaktu .= ' (' . $konser['catatan'] . ')';
                    }
                    ?>
                    <div class="tour-row" data-concert-id="<?= (int) $konser['id'] ?>" data-slug="<?= e($slugKonser) ?>"
                        data-date="<?= e(format_tanggal($konser['tanggal_acara'], 'Y-m-d')) ?>"
                        data-doors="<?= e(format_tanggal($konser['jam_pintu_buka'], 'H:i')) ?>"
                        data-show="<?= e(format_tanggal($konser['jam_tampil'], 'H:i')) ?>"
                        data-age-limit="<?= e($konser['batas_usia']) ?>"
                        data-sale-status="<?= e($konser['status_penjualan']) ?>">
                        <div class="tour-date"><?= e(strtoupper(format_tanggal($konser['tanggal_acara'], 'M d'))) ?></div>
                        <div class="tour-info">
                            <div class="tour-location"><?= e($konser['kota']) ?>, <?= e($konser['wilayah']) ?></div>
                            <div class="tour-venue"><?= e($konser['nama_tempat']) ?></div>
                            <div class="tour-details-meta"><?= e($keteranganWaktu) ?></div>
                        </div>
                        <div class="tour-cta">
                            <?php if ($sudahHabis): ?>
                                <span style="padding:10px 24px; font-size:0.8rem; border:1px solid rgba(255,255,255,0.15); border-radius:var(--radius-sm); color:var(--text-light); text-transform: uppercase; font-weight: 600;">Sold Out</span>
                            <?php else: ?>
                                <a href="<?= e(url_halaman('tour/tickets.php')) ?>?show=<?= e($slugKonser) ?>#<?= e($slugKonser) ?>" class="btn btn-outline-dark" style="padding:10px 24px; font-size:0.8rem;">Tickets &amp; Packages</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
