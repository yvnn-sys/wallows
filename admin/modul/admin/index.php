<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Admin';
$activeMenu = 'admin';

$kataKunci = isset($_GET['q']) && is_scalar($_GET['q']) ? trim((string) $_GET['q']) : '';

$where = '';
$params = [];
if ($kataKunci !== '') {
    $where = 'WHERE nama LIKE ? OR email LIKE ?';
    $params = [pola_like($kataKunci), pola_like($kataKunci)];
}

$total = (int) db_nilai($koneksi, 'SELECT COUNT(*) FROM admin ' . $where, $params, 0);
$paginasi = hitung_paginasi($total, 10);

$daftar = db_semua(
    $koneksi,
    'SELECT id, nama, email FROM admin ' . $where . ' ORDER BY nama ASC, id ASC LIMIT ? OFFSET ?',
    array_merge($params, [$paginasi['per_halaman'], $paginasi['offset']])
);

$sukses = flash_get('sukses');
$gagal = flash_get('gagal');
$idSaya = (int) ($_SESSION['admin_id'] ?? 0);

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
        <h3 class="card-title mb-0">Daftar admin</h3>
        <a href="form.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah admin
        </a>
    </div>
    <div class="card-body border-bottom">
        <form method="get" action="index.php" class="row g-2">
            <div class="col-sm-6 col-md-4">
                <input type="search" name="q" class="form-control" placeholder="Cari nama atau email" value="<?= e($kataKunci) ?>">
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
                    <th style="width:70px">ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th class="text-end" style="width:130px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftar === []): ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">Belum ada akun admin.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar as $baris): ?>
                        <?php $akunSaya = (int) $baris['id'] === $idSaya; ?>
                        <tr>
                            <td class="text-secondary">#<?= (int) $baris['id'] ?></td>
                            <td class="fw-semibold">
                                <?= e($baris['nama']) ?>
                                <?php if ($akunSaya): ?>
                                    <span class="badge text-bg-info ms-1">Anda</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($baris['email']) ?></td>
                            <td class="text-end">
                                <a href="form.php?id=<?= (int) $baris['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil-square"></i><span class="visually-hidden">Ubah</span>
                                </a>
                                <?php if ($akunSaya): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Akun yang sedang dipakai tidak bisa dihapus">
                                        <i class="bi bi-trash"></i><span class="visually-hidden">Hapus</span>
                                    </button>
                                <?php else: ?>
                                    <form method="post" action="hapus.php" class="d-inline" onsubmit="return confirm('Hapus akun admin ini?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $baris['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash"></i><span class="visually-hidden">Hapus</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="text-secondary small">
            Menampilkan <?= (int) $paginasi['dari'] ?>&ndash;<?= (int) $paginasi['sampai'] ?> dari <?= (int) $total ?> admin
        </span>
        <?= render_paginasi($paginasi, $kataKunci !== '' ? ['q' => $kataKunci] : []) ?>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
