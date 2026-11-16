<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Kategori Foto';
$activeMenu = 'galeri';

$sukses = flash_get('sukses');
$error = flash_get('error');

$daftarKategori = [];
$hasil = safe_query(
    $koneksi,
    'SELECT kf.id, kf.nama, COUNT(f.id) AS jumlah_foto
     FROM kategori_foto kf
     LEFT JOIN foto f ON f.kategori_foto_id = kf.id
     GROUP BY kf.id, kf.nama
     ORDER BY kf.nama ASC'
);
if ($hasil !== null) {
    while ($baris = $hasil->fetch_assoc()) {
        $daftarKategori[] = $baris;
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

<?php galeri_nav('kategori_foto'); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Kategori Foto</h3>
        <a href="<?= e(ADMIN_URL) ?>/modul/kategori_foto/tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Nama Kategori</th>
                    <th>Jumlah Foto</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarKategori === []): ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">Belum ada kategori foto.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarKategori as $kategori): ?>
                        <tr>
                            <td><?= (int) $kategori['id'] ?></td>
                            <td class="fw-semibold"><?= e($kategori['nama']) ?></td>
                            <td>
                                <span class="badge text-bg-secondary"><?= (int) $kategori['jumlah_foto'] ?> foto</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a
                                    href="<?= e(ADMIN_URL) ?>/modul/kategori_foto/edit.php?id=<?= (int) $kategori['id'] ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                    title="Edit"
                                ><i class="bi bi-pencil-square"></i></a>
                                <form
                                    method="post"
                                    action="<?= e(ADMIN_URL) ?>/modul/kategori_foto/hapus.php"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus kategori ini?');"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $kategori['id'] ?>">
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
