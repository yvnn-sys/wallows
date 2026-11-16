<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Foto';
$activeMenu = 'galeri';

$sukses = flash_get('sukses');
$error = flash_get('error');

$filterKategori = (int) ($_GET['kategori'] ?? 0);

$daftarKategori = [];
$hasilKategori = safe_query($koneksi, 'SELECT id, nama FROM kategori_foto ORDER BY nama ASC');
if ($hasilKategori !== null) {
    while ($baris = $hasilKategori->fetch_assoc()) {
        $daftarKategori[] = $baris;
    }
    $hasilKategori->free();
}

$daftarFoto = [];
$sql = 'SELECT f.id, f.keterangan_foto, f.lokasi_file_gambar, f.kategori_foto_id, kf.nama AS kategori
        FROM foto f
        LEFT JOIN kategori_foto kf ON kf.id = f.kategori_foto_id';
if ($filterKategori > 0) {
    $sql .= ' WHERE f.kategori_foto_id = ' . $filterKategori;
}
$sql .= ' ORDER BY f.id DESC';

$hasilFoto = safe_query($koneksi, $sql);
if ($hasilFoto !== null) {
    while ($baris = $hasilFoto->fetch_assoc()) {
        $daftarFoto[] = $baris;
    }
    $hasilFoto->free();
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

<?php galeri_nav('foto'); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="card-title mb-0">Foto Galeri</h3>
        <div class="d-flex gap-2">
            <form method="get" action="<?= e(ADMIN_URL) ?>/modul/foto/index.php" class="d-flex gap-2">
                <select class="form-select form-select-sm" name="kategori" onchange="this.form.submit()">
                    <option value="0">Semua kategori</option>
                    <?php foreach ($daftarKategori as $kategori): ?>
                        <option value="<?= (int) $kategori['id'] ?>" <?= $filterKategori === (int) $kategori['id'] ? 'selected' : '' ?>>
                            <?= e($kategori['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="<?= e(ADMIN_URL) ?>/modul/foto/tambah.php" class="btn btn-primary text-nowrap">
                <i class="bi bi-plus-lg me-1"></i> Tambah Foto
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 110px;">Gambar</th>
                    <th>Judul / Keterangan</th>
                    <th>Kategori</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarFoto === []): ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">Belum ada foto untuk ditampilkan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarFoto as $foto): ?>
                        <tr>
                            <td>
                                <?php if (!empty($foto['lokasi_file_gambar'])): ?>
                                    <img
                                        src="<?= e(tautan_media($foto['lokasi_file_gambar'])) ?>"
                                        alt="<?= e($foto['keterangan_foto']) ?>"
                                        class="rounded object-fit-cover"
                                        style="width: 90px; height: 60px;"
                                    >
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($foto['keterangan_foto'] ?: 'Tanpa keterangan') ?></div>
                                <div class="small text-secondary text-break"><?= e($foto['lokasi_file_gambar']) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($foto['kategori'])): ?>
                                    <span class="badge text-bg-primary"><?= e($foto['kategori']) ?></span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Tanpa kategori</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a
                                    href="<?= e(ADMIN_URL) ?>/modul/foto/edit.php?id=<?= (int) $foto['id'] ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                    title="Edit"
                                ><i class="bi bi-pencil-square"></i></a>
                                <form
                                    method="post"
                                    action="<?= e(ADMIN_URL) ?>/modul/foto/hapus.php"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus foto ini?');"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $foto['id'] ?>">
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
