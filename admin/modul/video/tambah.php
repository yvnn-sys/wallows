<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Tambah Video';
$activeMenu = 'galeri';

$error = null;
$judul = '';
$kodeYoutube = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $judul = trim((string) ($_POST['judul'] ?? ''));
        $kodeYoutube = trim((string) ($_POST['kode_youtube'] ?? ''));
        $kodeValid = kode_youtube_valid($kodeYoutube);

        if ($judul === '') {
            $error = 'Judul video wajib diisi.';
        } elseif (strlen($judul) > 200) {
            $error = 'Judul video maksimal 200 karakter.';
        } elseif ($kodeValid === null) {
            $error = 'Link atau ID video YouTube tidak valid. Contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx';
        } else {
            try {
                $stmt = $koneksi->prepare('INSERT INTO video (judul, kode_youtube) VALUES (?, ?)');
                $stmt->bind_param('ss', $judul, $kodeValid);
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Video berhasil ditambahkan.');
                redirect(ADMIN_URL . '/modul/video/index.php');
            } catch (mysqli_sql_exception $exception) {
                error_log('Tambah video gagal: ' . $exception->getMessage());
                $error = 'Video gagal disimpan.';
            }
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<?php galeri_nav('video'); ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title mb-0">Tambah Video</h3>
    </div>
    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/video/tambah.php">
        <div class="card-body">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <?php if ($error !== null): ?>
                <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label" for="video_judul">Judul video</label>
                <input
                    type="text"
                    class="form-control"
                    id="video_judul"
                    name="judul"
                    maxlength="200"
                    value="<?= e($judul) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label" for="video_kode">Link atau ID video YouTube</label>
                <input
                    type="text"
                    class="form-control"
                    id="video_kode"
                    name="kode_youtube"
                    maxlength="255"
                    placeholder="https://www.youtube.com/watch?v=xxxxxxxxxxx"
                    value="<?= e($kodeYoutube) ?>"
                    required
                >
                <div class="form-text">
                    Mendukung link youtube.com/watch, youtu.be, shorts, embed, atau ID video 11 karakter.
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan
            </button>
            <a href="<?= e(ADMIN_URL) ?>/modul/video/index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
