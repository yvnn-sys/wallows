<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'About | Wallows';
$deskripsiHalaman = 'The story behind Wallows and the members of the band.';

$daftarAnggota = ambil_semua(
    $koneksi,
    'SELECT id, nama, posisi, biodata, foto
     FROM anggota
     ORDER BY id ASC'
);

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>ABOUT WALLOWS</h1>
        <p>The story behind the indie rock sensation.</p>
    </header>

    <section class="section fade-in-element">
        <div style="display: flex; flex-wrap: wrap; gap: 40px; align-items: center;">
            <div style="flex: 1; min-width: 300px;">
                <img src="<?= e(url_gambar('images/origin.jpg')) ?>" alt="Band performing live"
                    style="width: 100%; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);"
                    onerror="this.style.display='none'">
            </div>
            <div style="flex: 1; min-width: 300px;">
                <h2 style="font-size: 2rem; margin-bottom: 20px; color: var(--ochre);">Origins</h2>
                <p style="color: var(--text-muted); line-height: 1.8; margin-bottom: 20px;">
                    Wallows is an American alternative rock band from Los Angeles, California. Formed by childhood
                    friends, the trio has been playing music together since they were 11 years old. What started in a
                    small bedroom jam session has evolved into a global phenomenon.
                </p>
                <p style="color: var(--text-muted); line-height: 1.8; margin-bottom: 30px;">
                    Known for their energetic live performances, nostalgic soundscapes, and deeply relatable lyrics,
                    they continue to push the boundaries of modern indie music.
                </p>
                <a href="<?= e(url_halaman('about/members.php')) ?>" class="btn btn-primary">Meet the Members</a>
            </div>
        </div>
    </section>

    <section class="section fade-in-element">
        <div class="section-header">
            <h2>THE BAND</h2>
            <p>Three friends. One sound.</p>
        </div>
        <div class="grid">
            <?php if ($daftarAnggota === []): ?>
                <p style="color: var(--text-muted);">No member data yet.</p>
            <?php else: ?>
                <?php foreach ($daftarAnggota as $anggota): ?>
                    <div class="card" data-member-id="<?= (int) $anggota['id'] ?>" style="text-align:center;">
                        <?php if (!empty($anggota['foto'])): ?>
                            <img src="<?= e(url_gambar($anggota['foto'])) ?>" alt="<?= e($anggota['nama']) ?>" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <h3><?= e($anggota['nama']) ?></h3>
                        <p style="color: var(--text-muted); margin-top: 10px;"><?= e($anggota['posisi']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
