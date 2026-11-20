<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/fungsi.php';

$pageTitle = 'Pesanan';
$activeMenu = 'pesanan';

$kataKunci = isset($_GET['q']) && is_scalar($_GET['q']) ? trim((string) $_GET['q']) : '';
$pilihanStatus = pilihan_status_pesanan();
$statusMentah = $_GET['status'] ?? '';
$statusFilter = is_scalar($statusMentah) && isset($pilihanStatus[(string) $statusMentah]) ? (string) $statusMentah : '';

$where = [];
$params = [];

if ($kataKunci !== '') {
    $where[] = '(ps.nomor_pesanan LIKE ? OR ps.nama_pelanggan LIKE ? OR ps.email_pelanggan LIKE ? OR pl.nama LIKE ?)';
    foreach (range(1, 4) as $_) {
        $params[] = pola_like($kataKunci);
    }
}

if ($statusFilter !== '') {
    $where[] = 'ps.status = ?';
    $params[] = $statusFilter;
}

$klausa = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);

$total = (int) db_nilai(
    $koneksi,
    'SELECT COUNT(*)
     FROM pesanan ps
     LEFT JOIN pelanggan pl ON pl.id = ps.pelanggan_id
     ' . $klausa,
    $params,
    0
);

$paginasi = hitung_paginasi($total, 10);

$daftar = db_semua(
    $koneksi,
    'SELECT ps.id, ps.nomor_pesanan, ps.nama_pelanggan, ps.email_pelanggan, ps.total_harga, ps.status, ps.dibuat_pada,
            pl.nama AS nama_akun, pl.email AS email_akun
     FROM pesanan ps
     LEFT JOIN pelanggan pl ON pl.id = ps.pelanggan_id
     ' . $klausa . '
     ORDER BY ps.dibuat_pada DESC, ps.id DESC
     LIMIT ? OFFSET ?',
    array_merge($params, [$paginasi['per_halaman'], $paginasi['offset']])
);

$sukses = flash_get('sukses');
$gagal = flash_get('gagal');

$queryPaginasi = [];
if ($kataKunci !== '') {
    $queryPaginasi['q'] = $kataKunci;
}
if ($statusFilter !== '') {
    $queryPaginasi['status'] = $statusFilter;
}

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
    <div class="card-header">
        <h3 class="card-title">Daftar pesanan</h3>
    </div>
    <div class="card-body border-bottom">
        <form method="get" action="index.php" class="row g-2">
            <div class="col-sm-6 col-md-4">
                <input type="search" name="q" class="form-control" placeholder="Cari nomor, nama, atau email" value="<?= e($kataKunci) ?>">
            </div>
            <div class="col-sm-5 col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <?php foreach ($pilihanStatus as $kunci => $label): ?>
                        <option value="<?= e($kunci) ?>" <?= $statusFilter === $kunci ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
            <?php if ($kataKunci !== '' || $statusFilter !== ''): ?>
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
                    <th>Nomor</th>
                    <th>Pelanggan</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th class="text-end" style="width:130px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftar === []): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">Belum ada pesanan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar as $baris): ?>
                        <?php
                        $namaTampil = ($baris['nama_akun'] ?? '') !== ''
                            ? (string) $baris['nama_akun']
                            : (string) $baris['nama_pelanggan'];
                        $emailTampil = ($baris['email_akun'] ?? '') !== ''
                            ? (string) $baris['email_akun']
                            : (string) $baris['email_pelanggan'];
                        ?>
                        <tr>
                            <td class="fw-semibold"><?= e($baris['nomor_pesanan']) ?></td>
                            <td>
                                <div><?= e($namaTampil) ?></div>
                                <div class="small text-secondary"><?= e($emailTampil) ?></div>
                            </td>
                            <td class="text-end"><?= e(usd($baris['total_harga'])) ?></td>
                            <td><?= badge_status((string) $baris['status']) ?></td>
                            <td><?= e(tanggal_singkat($baris['dibuat_pada'])) ?></td>
                            <td class="text-end">
                                <a href="detail.php?id=<?= (int) $baris['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i><span class="visually-hidden">Detail</span>
                                </a>
                                <form method="post" action="hapus.php" class="d-inline" onsubmit="return confirm('Hapus pesanan ini beserta seluruh itemnya?');">
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
            Menampilkan <?= (int) $paginasi['dari'] ?>&ndash;<?= (int) $paginasi['sampai'] ?> dari <?= (int) $total ?> pesanan
        </span>
        <?= render_paginasi($paginasi, $queryPaginasi) ?>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
