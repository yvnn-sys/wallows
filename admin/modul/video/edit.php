<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Edit Video';
$activeMenu = 'galeri';

$id = (int) ($_GET['id'] ?? 0);
$error = null;

$stmt = $koneksi->prepare('SELECT id, judul, kode_youtube FROM video WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($video === null) {
    flash_set('error', 'Video tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/video/index.php');
}

$judul = (string) $video['judul'];
$kodeYoutube = (string) $video['kode_youtube'];

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
                $update = $koneksi->prepare('UPDATE video SET judul = ?, kode_youtube = ? WHERE id = ?');
                $update->bind_param('ssi', $judul, $kodeValid, $id);
                $update->execute();
                $update->close();
                flash_set('sukses', 'Video berhasil diperbarui.');
                redirect(ADMIN_URL . '/modul/video/index.php');
            } catch (mysqli_sql_exception $exception) {
                error_log('Edit video gagal: ' . $exception->getMessage());
                $error = 'Video gagal diperbarui.';
            }
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<?php galeri_nav('video'); ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit Video</h3>
    </div>
    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/video/edit.php?id=<?= (int) $id ?>">
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
                    value="<?= e($kodeYoutube) ?>"
                    required
                >
                <div class="form-text">Kode tersimpan: <?= e($video['kode_youtube']) ?>.</div>
            </div>

            <img
                src="<?= e(thumbnail_youtube($video['kode_youtube'])) ?>"
                alt="<?= e($judul) ?>"
                class="rounded border object-fit-cover"
                style="width: 240px; height: 135px;"
            >
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="<?= e(ADMIN_URL) ?>/modul/video/index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
