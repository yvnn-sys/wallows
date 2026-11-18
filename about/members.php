<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Members | Wallows';
$deskripsiHalaman = 'Meet the three members of Wallows.';

$daftarAnggota = ambil_semua(
    $koneksi,
    'SELECT id, nama, posisi, biodata, foto
     FROM anggota
     ORDER BY id ASC'
);

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>THE MEMBERS</h1>
        <p>Three friends who've been making music together since childhood.</p>
    </header>

    <section class="section fade-in-element">
        <div style="display:flex; flex-direction:column; gap:60px;">
            <?php if ($daftarAnggota === []): ?>
                <p style="color: var(--text-muted);">No member data yet.</p>
            <?php else: ?>
                <?php $nomor = 0; ?>
                <?php foreach ($daftarAnggota as $anggota): ?>
                    <?php
                    $nomor++;
                    $gambarDiKanan = $nomor % 2 === 0;
                    $urutanGambar = $gambarDiKanan ? 2 : 1;
                    $urutanTeks = $gambarDiKanan ? 1 : 2;
                    ?>
                    <div style="display:flex; flex-wrap:wrap; gap:40px; align-items:center;">
                        <?php if (!empty($anggota['foto'])): ?>
                            <img src="<?= e(url_gambar($anggota['foto'])) ?>" alt="<?= e($anggota['nama']) ?>" style="width:300px; border-radius:20px; object-fit:cover; order:<?= $urutanGambar ?>;" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <div style="flex:1; min-width:260px; order:<?= $urutanTeks ?>;">
                            <h2 data-member-id="<?= (int) $anggota['id'] ?>" style="font-size:2.2rem; margin-bottom:8px;"><?= e($anggota['nama']) ?></h2>
                            <p style="color:var(--ochre); margin-bottom:20px; font-weight:600; letter-spacing:0.05em; text-transform:uppercase;"><?= e($anggota['posisi']) ?></p>
                            <p style="color:var(--text-muted); line-height:1.8;"><?= nl2br(e($anggota['biodata'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
