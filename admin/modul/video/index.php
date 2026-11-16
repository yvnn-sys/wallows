<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Video';
$activeMenu = 'galeri';

$sukses = flash_get('sukses');
$error = flash_get('error');

$daftarVideo = [];
$hasil = safe_query(
    $koneksi,
    'SELECT v.id, v.judul, v.kode_youtube, l.judul AS lagu
     FROM video v
     LEFT JOIN lagu l ON l.id = v.lagu_id
     ORDER BY v.id DESC'
);
if ($hasil !== null) {
    while ($baris = $hasil->fetch_assoc()) {
        $daftarVideo[] = $baris;
    }
    $hasil->free();
}

require __DIR__ . '/../../includes/header.php';
?>

<?php if ($sukses !== null): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($sukses) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if ($error !== null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php galeri_nav('video'); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Video YouTube</h3>
        <a href="<?= e(ADMIN_URL) ?>/modul/video/tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Video
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 140px;">Thumbnail</th>
                    <th>Judul</th>
                    <th>Kode / Link YouTube</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarVideo === []): ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">Belum ada video.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarVideo as $video): ?>
                        <tr>
                            <td>
                                <img
                                    src="<?= e(thumbnail_youtube($video['kode_youtube'])) ?>"
                                    alt="<?= e($video['judul']) ?>"
                                    class="rounded object-fit-cover"
                                    style="width: 120px; height: 68px;"
                                >
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($video['judul']) ?></div>
                                <?php if (!empty($video['lagu'])): ?>
                                    <div class="small text-secondary">Lagu: <?= e($video['lagu']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small font-monospace"><?= e($video['kode_youtube']) ?></div>
                                <a
                                    href="<?= e(url_youtube($video['kode_youtube'])) ?>"
                                    target="_blank"
                                    rel="noopener"
                                    class="small"
                                >Buka di YouTube <i class="bi bi-box-arrow-up-right"></i></a>
                            </td>
                            <td class="text-end text-nowrap">
                                <a
                                    href="<?= e(ADMIN_URL) ?>/modul/video/edit.php?id=<?= (int) $video['id'] ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                    title="Edit"
                                ><i class="bi bi-pencil-square"></i></a>
                                <form
                                    method="post"
                                    action="<?= e(ADMIN_URL) ?>/modul/video/hapus.php"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus video ini?');"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $video['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
