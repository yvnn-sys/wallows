<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';

$pageTitle = 'Anggota - Tambah';
$activeMenu = 'anggota';

$error = null;
$nama = '';
$posisi = '';
$biodata = '';

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
                $biodataNilai = $biodata === '' ? null : $biodata;
                $fotoNilai = $upload['path'];

                try {
                    $stmt = $koneksi->prepare('INSERT INTO anggota (nama, posisi, biodata, foto) VALUES (?, ?, ?, ?)');
                    $stmt->bind_param('ssss', $nama, $posisi, $biodataNilai, $fotoNilai);
                    $stmt->execute();
                    $stmt->close();
                    flash_set('sukses', 'Anggota berhasil ditambahkan.');
                    redirect(ADMIN_URL . '/modul/anggota/index.php');
                } catch (mysqli_sql_exception $exception) {
                    if ($fotoNilai !== null) {
                        hapus_file_upload($fotoNilai);
                    }
                    error_log('Tambah anggota gagal: ' . $exception->getMessage());
                    $error = 'Data anggota gagal disimpan.';
                }
            }
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title mb-0">Tambah Anggota</h3>
    </div>
    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/anggota/tambah.php" enctype="multipart/form-data">
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
                        placeholder="Contoh: Vokalis dan gitaris"
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
                    <label class="form-label" for="anggota_foto">Foto (opsional)</label>
                    <input
                        type="file"
                        class="form-control"
                        id="anggota_foto"
                        name="foto"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <div class="form-text">Format JPG, PNG, atau WEBP. Ukuran maksimal 2 MB.</div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan
            </button>
            <a href="<?= e(ADMIN_URL) ?>/modul/anggota/index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
