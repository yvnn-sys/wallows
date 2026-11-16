<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Pesan Kontak';
$activeMenu = 'pesan_kontak';

$sukses = flash_get('sukses');
$error = flash_get('error');

$labelStatus = [
    'new' => 'Baru',
    'read' => 'Dibaca',
    'replied' => 'Dibalas',
];

$filter = (string) ($_GET['status'] ?? '');
if (!array_key_exists($filter, $labelStatus)) {
    $filter = '';
}

$jumlahStatus = ['new' => 0, 'read' => 0, 'replied' => 0, 'semua' => 0];
$hasilHitung = safe_query(
    $koneksi,
    'SELECT status, COUNT(*) AS jumlah FROM pesan_kontak GROUP BY status'
);
if ($hasilHitung !== null) {
    while ($baris = $hasilHitung->fetch_assoc()) {
        $status = (string) $baris['status'];
        $jumlah = (int) $baris['jumlah'];
        if (isset($jumlahStatus[$status])) {
            $jumlahStatus[$status] = $jumlah;
        }
        $jumlahStatus['semua'] += $jumlah;
    }
    $hasilHitung->free();
}

$daftarPesan = [];
try {
    if ($filter === '') {
        $stmt = $koneksi->prepare(
            'SELECT id, nama, email, topik, status, dibuat_pada
             FROM pesan_kontak
             ORDER BY dibuat_pada DESC, id DESC'
        );
    } else {
        $stmt = $koneksi->prepare(
            'SELECT id, nama, email, topik, status, dibuat_pada
             FROM pesan_kontak
             WHERE status = ?
             ORDER BY dibuat_pada DESC, id DESC'
        );
        $stmt->bind_param('s', $filter);
    }
    $stmt->execute();
    $hasil = $stmt->get_result();
    while ($baris = $hasil->fetch_assoc()) {
        $daftarPesan[] = $baris;
    }
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    error_log('Daftar pesan kontak gagal: ' . $exception->getMessage());
    $error = $error ?? 'Daftar pesan gagal dimuat.';
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Pesan dari Formulir Kontak</h3>
    </div>
    <div class="card-body border-bottom">
        <ul class="nav nav-pills flex-wrap gap-2">
            <?php
            $tabFilter = ['' => 'Semua'] + $labelStatus;
            foreach ($tabFilter as $nilai => $label):
                $jumlah = $nilai === '' ? $jumlahStatus['semua'] : ($jumlahStatus[$nilai] ?? 0);
                $url = ADMIN_URL . '/modul/pesan_kontak/index.php';
                if ($nilai !== '') {
                    $url .= '?status=' . urlencode($nilai);
                }
                ?>
                <li class="nav-item">
                    <a
                        href="<?= e($url) ?>"
                        class="nav-link <?= $filter === $nilai ? 'active' : '' ?>"
                    >
                        <?= e($label) ?>
                        <span class="badge text-bg-light ms-1"><?= (int) $jumlah ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Pengirim</th>
                    <th>Topik</th>
                    <th>Status</th>
                    <th>Waktu Masuk</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarPesan === []): ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">
                            Tidak ada pesan kontak untuk filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarPesan as $pesan): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($pesan['nama']) ?></div>
                                <div class="small text-secondary"><?= e($pesan['email']) ?></div>
                            </td>
                            <td><span class="badge text-bg-info"><?= e(label_topik((string) $pesan['topik'])) ?></span></td>
                            <td><?= badge_status((string) $pesan['status']) ?></td>
                            <td class="small text-nowrap"><?= e(tanggal_singkat($pesan['dibuat_pada'])) ?></td>
                            <td class="text-end text-nowrap">
                                <a
                                    href="<?= e(ADMIN_URL) ?>/modul/pesan_kontak/detail.php?id=<?= (int) $pesan['id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Lihat detail"
                                ><i class="bi bi-eye"></i></a>
                                <form
                                    method="post"
                                    action="<?= e(ADMIN_URL) ?>/modul/pesan_kontak/hapus.php"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus pesan ini?');"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $pesan['id'] ?>">
                                    <input type="hidden" name="status" value="<?= e($filter) ?>">
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
