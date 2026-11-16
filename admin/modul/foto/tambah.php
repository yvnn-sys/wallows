<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../includes/galeri.php';

$pageTitle = 'Galeri - Tambah Foto';
$activeMenu = 'galeri';

$error = null;
$keterangan = '';
$kategoriId = 0;

$daftarKategori = [];
$hasilKategori = safe_query($koneksi, 'SELECT id, nama FROM kategori_foto ORDER BY nama ASC');
if ($hasilKategori !== null) {
    while ($baris = $hasilKategori->fetch_assoc()) {
        $daftarKategori[] = $baris;
    }
    $hasilKategori->free();
}

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
            if ($upload['error'] !== null) {
                $error = $upload['error'];
            } elseif ($upload['path'] === null) {
                $error = 'Pilih file gambar yang akan diunggah.';
            } else {
                try {
                    $stmt = $koneksi->prepare(
                        'INSERT INTO foto (keterangan_foto, lokasi_file_gambar, kategori_foto_id) VALUES (?, ?, ?)'
                    );
                    $stmt->bind_param('ssi', $keterangan, $upload['path'], $kategoriId);
                    $stmt->execute();
                    $stmt->close();
                    flash_set('sukses', 'Foto berhasil ditambahkan.');
                    redirect(ADMIN_URL . '/modul/foto/index.php');
                } catch (mysqli_sql_exception $exception) {
                    hapus_file_upload($upload['path']);
                    error_log('Tambah foto gagal: ' . $exception->getMessage());
                    $error = $exception->getCode() === 1452
                        ? 'Kategori foto tidak ditemukan.'
                        : 'Foto gagal disimpan.';
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
        <h3 class="card-title mb-0">Tambah Foto</h3>
    </div>
    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/foto/tambah.php" enctype="multipart/form-data">
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
                    <div class="form-text">Tambahkan kategori di tab Kategori Foto bila belum ada.</div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label" for="foto_gambar">File gambar</label>
                    <input
                        type="file"
                        class="form-control"
                        id="foto_gambar"
                        name="gambar"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >
                    <div class="form-text">Format JPG, PNG, atau WEBP. Ukuran maksimal 2 MB.</div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan
            </button>
            <a href="<?= e(ADMIN_URL) ?>/modul/foto/index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
