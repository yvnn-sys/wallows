<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';

$pageTitle = 'Anggota - Edit';
$activeMenu = 'anggota';

$id = (int) ($_GET['id'] ?? 0);
$error = null;

$stmt = $koneksi->prepare('SELECT id, nama, posisi, biodata, foto FROM anggota WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$anggota = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($anggota === null) {
    flash_set('error', 'Data anggota tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/anggota/index.php');
}

$nama = (string) $anggota['nama'];
$posisi = (string) $anggota['posisi'];
$biodata = (string) ($anggota['biodata'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $posisi = trim((string) ($_POST['posisi'] ?? ''));
        $biodata = trim((string) ($_POST['biodata'] ?? ''));

        if ($nama === '') {
            $error = 'Nama anggota wajib diisi.';
        } elseif (strlen($nama) > 100) {
            $error = 'Nama anggota maksimal 100 karakter.';
        } elseif ($posisi === '') {
            $error = 'Peran atau posisi wajib diisi.';
        } elseif (strlen($posisi) > 150) {
            $error = 'Peran atau posisi maksimal 150 karakter.';
        } else {
            $upload = upload_gambar($_FILES['foto'] ?? []);

            if ($upload['error'] !== null) {
                $error = $upload['error'];
            } else {
                $fotoBaru = $upload['path'];
                $fotoNilai = $fotoBaru ?? (string) ($anggota['foto'] ?? '');
                $biodataNilai = $biodata === '' ? null : $biodata;

                try {
                    $update = $koneksi->prepare('UPDATE anggota SET nama = ?, posisi = ?, biodata = ?, foto = ? WHERE id = ?');
                    $update->bind_param('ssssi', $nama, $posisi, $biodataNilai, $fotoNilai, $id);
                    $update->execute();
                    $update->close();

                    if ($fotoBaru !== null) {
                        hapus_file_upload($anggota['foto']);
                    }

                    flash_set('sukses', 'Data anggota berhasil diperbarui.');
                    redirect(ADMIN_URL . '/modul/anggota/index.php');
                } catch (mysqli_sql_exception $exception) {
                    if ($fotoBaru !== null) {
                        hapus_file_upload($fotoBaru);
                    }
                    error_log('Edit anggota gagal: ' . $exception->getMessage());
                    $error = 'Data anggota gagal diperbarui.';
                }
            }
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit Anggota</h3>
    </div>
    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/anggota/edit.php?id=<?= (int) $id ?>" enctype="multipart/form-data">
        <div class="card-body">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <?php if ($error !== null): ?>
                <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="anggota_nama">Nama</label>
                    <input
                        type="text"
                        class="form-control"
                        id="anggota_nama"
                        name="nama"
                        maxlength="100"
                        value="<?= e($nama) ?>"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label" for="anggota_posisi">Peran / posisi</label>
                    <input
                        type="text"
                        class="form-control"
                        id="anggota_posisi"
                        name="posisi"
                        maxlength="150"
                        value="<?= e($posisi) ?>"
                        required
                    >
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label" for="anggota_biodata">Biodata</label>
                    <textarea
                        class="form-control"
                        id="anggota_biodata"
                        name="biodata"
                        rows="4"
                    ><?= e($biodata) ?></textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Foto saat ini</label>
                    <div class="mb-2">
                        <?php if (!empty($anggota['foto'])): ?>
                            <img
                                src="<?= e(tautan_media($anggota['foto'])) ?>"
                                alt="<?= e($nama) ?>"
                                class="rounded-circle object-fit-cover border"
                                style="width: 96px; height: 96px;"
                            >
                        <?php else: ?>
                            <span class="text-secondary">Belum ada foto.</span>
                        <?php endif; ?>
                    </div>

                    <label class="form-label" for="anggota_foto">Ganti foto (opsional)</label>
                    <input
                        type="file"
                        class="form-control"
                        id="anggota_foto"
                        name="foto"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <div class="form-text">Kosongkan bila foto tidak diganti. Maksimal 2 MB.</div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="<?= e(ADMIN_URL) ?>/modul/anggota/index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
