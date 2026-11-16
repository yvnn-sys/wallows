<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Edit Foto';
$activeMenu = 'galeri';

$id = (int) ($_GET['id'] ?? 0);
$error = null;

$stmt = $koneksi->prepare('SELECT id, keterangan_foto, lokasi_file_gambar, kategori_foto_id FROM foto WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$foto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($foto === null) {
    flash_set('error', 'Foto tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/foto/index.php');
}

$daftarKategori = [];
$hasilKategori = safe_query($koneksi, 'SELECT id, nama FROM kategori_foto ORDER BY nama ASC');
if ($hasilKategori !== null) {
    while ($baris = $hasilKategori->fetch_assoc()) {
        $daftarKategori[] = $baris;
    }
    $hasilKategori->free();
}

$keterangan = (string) ($foto['keterangan_foto'] ?? '');
$kategoriId = (int) ($foto['kategori_foto_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $keterangan = trim((string) ($_POST['keterangan'] ?? ''));
        $kategoriId = (int) ($_POST['kategori_foto_id'] ?? 0);

        $kategoriValid = false;
        foreach ($daftarKategori as $kategori) {
            if ((int) $kategori['id'] === $kategoriId) {
                $kategoriValid = true;
                break;
            }
        }

        if ($keterangan === '') {
            $error = 'Judul atau keterangan foto wajib diisi.';
        } elseif (strlen($keterangan) > 150) {
            $error = 'Judul atau keterangan foto maksimal 150 karakter.';
        } elseif (!$kategoriValid) {
            $error = 'Pilih kategori foto yang valid.';
        } else {
            $upload = upload_gambar($_FILES['gambar'] ?? []);
            $pathBaru = null;

            if ($upload['error'] !== null) {
                $error = $upload['error'];
            } else {
                $pathBaru = $upload['path'];
                $pathSimpan = $pathBaru ?? (string) $foto['lokasi_file_gambar'];

                try {
                    $update = $koneksi->prepare(
                        'UPDATE foto SET keterangan_foto = ?, lokasi_file_gambar = ?, kategori_foto_id = ? WHERE id = ?'
                    );
                    $update->bind_param('ssii', $keterangan, $pathSimpan, $kategoriId, $id);
                    $update->execute();
                    $update->close();

                    if ($pathBaru !== null) {
                        hapus_file_upload($foto['lokasi_file_gambar']);
                    }

                    flash_set('sukses', 'Foto berhasil diperbarui.');
                    redirect(ADMIN_URL . '/modul/foto/index.php');
                } catch (mysqli_sql_exception $exception) {
                    if ($pathBaru !== null) {
                        hapus_file_upload($pathBaru);
                    }
                    error_log('Edit foto gagal: ' . $exception->getMessage());
                    $error = $exception->getCode() === 1452
                        ? 'Kategori foto tidak ditemukan.'
                        : 'Foto gagal diperbarui.';
                }
            }
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<?php galeri_nav('foto'); ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit Foto</h3>
    </div>
    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/foto/edit.php?id=<?= (int) $id ?>" enctype="multipart/form-data">
        <div class="card-body">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <?php if ($error !== null): ?>
                <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label" for="foto_keterangan">Judul / keterangan foto</label>
                    <input
                        type="text"
                        class="form-control"
                        id="foto_keterangan"
                        name="keterangan"
                        maxlength="150"
                        value="<?= e($keterangan) ?>"
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label" for="foto_kategori">Kategori</label>
                    <select class="form-select" id="foto_kategori" name="kategori_foto_id" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($daftarKategori as $kategori): ?>
                            <option
                                value="<?= (int) $kategori['id'] ?>"
                                <?= $kategoriId === (int) $kategori['id'] ? 'selected' : '' ?>
                            ><?= e($kategori['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Gambar saat ini</label>
                    <div>
                        <?php if (!empty($foto['lokasi_file_gambar'])): ?>
                            <img
                                src="<?= e(tautan_media($foto['lokasi_file_gambar'])) ?>"
                                alt="<?= e($keterangan) ?>"
                                class="rounded border object-fit-cover"
                                style="width: 180px; height: 120px;"
                            >
                        <?php else: ?>
                            <span class="text-secondary">Belum ada gambar.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label" for="foto_gambar">Ganti gambar (opsional)</label>
                    <input
                        type="file"
                        class="form-control"
                        id="foto_gambar"
                        name="gambar"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <div class="form-text">Kosongkan bila gambar tidak diganti. Format JPG, PNG, atau WEBP, maksimal 2 MB.</div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="<?= e(ADMIN_URL) ?>/modul/foto/index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
