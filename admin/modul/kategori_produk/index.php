<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Kategori Produk';
$activeMenu = 'produk';

$daftar = db_semua(
    $koneksi,
    'SELECT k.id, k.nama, COUNT(p.id) AS jumlah_produk
     FROM kategori_produk k
     LEFT JOIN produk p ON p.kategori_produk_id = k.id
     GROUP BY k.id, k.nama
     ORDER BY k.nama ASC'
);

$sukses = flash_get('sukses');
$gagal = flash_get('gagal');

require __DIR__ . '/../../includes/header.php';
?>

<?php if ($sukses !== null): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($sukses) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if ($gagal !== null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= e($gagal) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar kategori</h3>
                <div class="card-tools">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-bag me-1"></i>Kembali ke produk
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama kategori</th>
                            <th class="text-center">Jumlah produk</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($daftar === []): ?>
                            <tr>
                                <td colspan="3" class="text-center text-secondary py-4">Belum ada kategori.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftar as $baris): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($baris['nama']) ?></td>
                                    <td class="text-center"><?= (int) $baris['jumlah_produk'] ?></td>
                                    <td class="text-end">
                                        <a href="form.php?id=<?= (int) $baris['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil-square"></i><span class="visually-hidden">Ubah</span>
                                        </a>
                                        <form method="post" action="hapus.php" class="d-inline" onsubmit="return confirm('Hapus kategori ini?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $baris['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i><span class="visually-hidden">Hapus</span>
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
    </div>

    <div class="col-lg-5">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Tambah kategori</h3>
            </div>
            <form method="post" action="form.php">
                <?= csrf_field() ?>
                <div class="card-body">
                    <label for="nama" class="form-label">Nama kategori</label>
                    <input id="nama" name="nama" class="form-control" maxlength="80" required>
                    <div class="form-text">Contoh: Apparel, Vinyl, Poster.</div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
