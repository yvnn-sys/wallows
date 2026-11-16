<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';

$pageTitle = 'Anggota';
$activeMenu = 'anggota';

$sukses = flash_get('sukses');
$error = flash_get('error');

$daftarAnggota = [];
$hasil = safe_query(
    $koneksi,
    'SELECT id, nama, posisi, biodata, foto FROM anggota ORDER BY id ASC'
);
if ($hasil !== null) {
    while ($baris = $hasil->fetch_assoc()) {
        $daftarAnggota[] = $baris;
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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Data Anggota Band</h3>
        <a href="<?= e(ADMIN_URL) ?>/modul/anggota/tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Anggota
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;">Urutan</th>
                    <th style="width: 100px;">Foto</th>
                    <th>Nama</th>
                    <th>Peran / Posisi</th>
                    <th>Biodata</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarAnggota === []): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">Belum ada data anggota.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarAnggota as $urutan => $anggota): ?>
                        <tr>
                            <td><?= (int) ($urutan + 1) ?></td>
                            <td>
                                <?php if (!empty($anggota['foto'])): ?>
                                    <img
                                        src="<?= e(tautan_media($anggota['foto'])) ?>"
                                        alt="<?= e($anggota['nama']) ?>"
                                        class="rounded-circle object-fit-cover border"
                                        style="width: 56px; height: 56px;"
                                    >
                                <?php else: ?>
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary-subtle text-secondary" style="width: 56px; height: 56px;">
                                        <i class="bi bi-person"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?= e($anggota['nama']) ?></td>
                            <td><?= e($anggota['posisi']) ?></td>
                            <td class="small text-secondary" style="max-width: 360px;">
                                <?= e($anggota['biodata'] ?: '-') ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a
                                    href="<?= e(ADMIN_URL) ?>/modul/anggota/edit.php?id=<?= (int) $anggota['id'] ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                    title="Edit"
                                ><i class="bi bi-pencil-square"></i></a>
                                <form
                                    method="post"
                                    action="<?= e(ADMIN_URL) ?>/modul/anggota/hapus.php"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus data anggota ini?');"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $anggota['id'] ?>">
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
