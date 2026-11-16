<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Edit Kategori Foto';
$activeMenu = 'galeri';

$id = (int) ($_GET['id'] ?? 0);
$error = null;

$stmt = $koneksi->prepare('SELECT id, nama FROM kategori_foto WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$kategori = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($kategori === null) {
    flash_set('error', 'Kategori foto tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/kategori_foto/index.php');
}

$nama = (string) $kategori['nama'];

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
                $update = $koneksi->prepare('UPDATE kategori_foto SET nama = ? WHERE id = ?');
                $update->bind_param('si', $nama, $id);
                $update->execute();
                $update->close();
                flash_set('sukses', 'Kategori foto berhasil diperbarui.');
                redirect(ADMIN_URL . '/modul/kategori_foto/index.php');
            } catch (mysqli_sql_exception $exception) {
                error_log('Edit kategori foto gagal: ' . $exception->getMessage());
                $error = $exception->getCode() === 1062
                    ? 'Nama kategori sudah ada. Gunakan nama lain.'
                    : 'Kategori foto gagal diperbarui.';
            }
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<?php galeri_nav('kategori_foto'); ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit Kategori Foto</h3>
    </div>
    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/kategori_foto/edit.php?id=<?= (int) $id ?>">
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
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="<?= e(ADMIN_URL) ?>/modul/kategori_foto/index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
