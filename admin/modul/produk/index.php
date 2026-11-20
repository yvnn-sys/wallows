<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Produk';
$activeMenu = 'produk';

$kataKunci = isset($_GET['q']) && is_scalar($_GET['q']) ? trim((string) $_GET['q']) : '';

$where = '';
$params = [];
if ($kataKunci !== '') {
    $where = 'WHERE p.nama LIKE ? OR k.nama LIKE ?';
    $params = [pola_like($kataKunci), pola_like($kataKunci)];
}

$total = (int) db_nilai(
    $koneksi,
    'SELECT COUNT(*) FROM produk p JOIN kategori_produk k ON k.id = p.kategori_produk_id ' . $where,
    $params,
    0
);

$paginasi = hitung_paginasi($total, 10);

$daftar = db_semua(
    $koneksi,
    'SELECT p.id, p.nama, p.harga, p.stok, p.tautan_gambar, p.label_produk, p.status_aktif, k.nama AS kategori
     FROM produk p
     JOIN kategori_produk k ON k.id = p.kategori_produk_id
     ' . $where . '
     ORDER BY p.nama ASC, p.id ASC
     LIMIT ? OFFSET ?',
    array_merge($params, [$paginasi['per_halaman'], $paginasi['offset']])
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

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0">Daftar produk</h3>
        <div class="d-flex flex-wrap gap-2">
            <a href="../kategori_produk/index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-tags me-1"></i>Kelola kategori
            </a>
            <a href="form.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Tambah produk
            </a>
        </div>
    </div>
    <div class="card-body border-bottom">
        <form method="get" action="index.php" class="row g-2">
            <div class="col-sm-6 col-md-4">
                <input type="search" name="q" class="form-control" placeholder="Cari nama produk atau kategori" value="<?= e($kataKunci) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
            <?php if ($kataKunci !== ''): ?>
                <div class="col-auto">
                    <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:70px">Foto</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th class="text-end">Harga</th>
                    <th class="text-center">Stok</th>
                    <th>Label</th>
                    <th>Status</th>
                    <th class="text-end" style="width:130px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftar === []): ?>
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">Belum ada produk.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar as $baris): ?>
                        <tr>
                            <td>
                                <?php if (!empty($baris['tautan_gambar'])): ?>
                                    <img src="<?= e(url_gambar($baris['tautan_gambar'])) ?>" alt="Foto <?= e($baris['nama']) ?>" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                                <?php else: ?>
                                    <span class="text-secondary small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?= e($baris['nama']) ?></td>
                            <td><?= e($baris['kategori']) ?></td>
                            <td class="text-end"><?= e(usd($baris['harga'])) ?></td>
                            <td class="text-center">
                                <?php if ((int) $baris['stok'] < 10): ?>
                                    <span class="badge text-bg-danger"><?= (int) $baris['stok'] ?></span>
                                <?php else: ?>
                                    <?= (int) $baris['stok'] ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($baris['label_produk'] === 'new'): ?>
                                    <span class="badge text-bg-success">New</span>
                                <?php elseif ($baris['label_produk'] === 'limited'): ?>
                                    <span class="badge text-bg-warning">Limited</span>
                                <?php else: ?>
                                    <span class="text-secondary small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int) $baris['status_aktif'] === 1): ?>
                                    <span class="badge text-bg-primary">Aktif</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="form.php?id=<?= (int) $baris['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil-square"></i><span class="visually-hidden">Ubah</span>
                                </a>
                                <form method="post" action="hapus.php" class="d-inline" onsubmit="return confirm('Hapus produk ini?');">
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
    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="text-secondary small">
            Menampilkan <?= (int) $paginasi['dari'] ?>&ndash;<?= (int) $paginasi['sampai'] ?> dari <?= (int) $total ?> produk
        </span>
        <?= render_paginasi($paginasi, $kataKunci !== '' ? ['q' => $kataKunci] : []) ?>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
