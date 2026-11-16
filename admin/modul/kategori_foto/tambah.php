<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Tambah Kategori Foto';
$activeMenu = 'galeri';

$error = null;
$nama = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $nama = trim((string) ($_POST['nama'] ?? ''));

        if ($nama === '') {
            $error = 'Nama kategori wajib diisi.';
        } elseif (strlen($nama) > 80) {
            $error = 'Nama kategori maksimal 80 karakter.';
        } else {
            try {
                $stmt = $koneksi->prepare('INSERT INTO kategori_foto (nama) VALUES (?)');
                $stmt->bind_param('s', $nama);
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Kategori foto berhasil ditambahkan.');
                redirect(ADMIN_URL . '/modul/kategori_foto/index.php');
            } catch (mysqli_sql_exception $exception) {
                error_log('Tambah kategori foto gagal: ' . $exception->getMessage());
                $error = $exception->getCode() === 1062
                    ? 'Nama kategori sudah ada. Gunakan nama lain.'
                    : 'Kategori foto gagal disimpan.';
            }
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<?php galeri_nav('kategori_foto'); ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title mb-0">Tambah Kategori Foto</h3>
    </div>
    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/kategori_foto/tambah.php">
        <div class="card-body">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <?php if ($error !== null): ?>
                <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label" for="kategori_nama">Nama kategori</label>
                <input
                    type="text"
                    class="form-control"
                    id="kategori_nama"
                    name="nama"
                    maxlength="80"
                    value="<?= e($nama) ?>"
                    required
                >
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan
            </button>
            <a href="<?= e(ADMIN_URL) ?>/modul/kategori_foto/index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
