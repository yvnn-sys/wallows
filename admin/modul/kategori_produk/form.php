<?php
require_once __DIR__ . '/../../includes/auth.php';

$mentahId = $_POST['id'] ?? $_GET['id'] ?? 0;
$id = is_scalar($mentahId) ? (int) $mentahId : 0;
$mode = $id > 0 ? 'edit' : 'tambah';

$lama = null;
if ($id > 0) {
    $lama = db_satu($koneksi, 'SELECT id, nama FROM kategori_produk WHERE id = ?', [$id]);
    if ($lama === null) {
        flash_set('gagal', 'Kategori tidak ditemukan.');
        redirect(ADMIN_URL . '/modul/kategori_produk/index.php');
    }
}

$nilai = ['nama' => $lama['nama'] ?? ''];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !csrf_valid($token)) {
        flash_set('gagal', 'Sesi form sudah kedaluwarsa. Silakan coba lagi.');
        redirect(ADMIN_URL . '/modul/kategori_produk/index.php');
    }

    $nilai['nama'] = trim(input_teks('nama'));

    $errors = validasi_field([
        'nama' => ['nilai' => $nilai['nama'], 'label' => 'Nama kategori', 'wajib' => true, 'maks' => 80],
    ]);

    if ($errors === []) {
        $duplikat = $id > 0
            ? db_satu($koneksi, 'SELECT id FROM kategori_produk WHERE nama = ? AND id <> ? LIMIT 1', [$nilai['nama'], $id])
            : db_satu($koneksi, 'SELECT id FROM kategori_produk WHERE nama = ? LIMIT 1', [$nilai['nama']]);

        if ($duplikat !== null) {
            $errors['nama'] = 'Nama kategori sudah dipakai.';
        }
    }

    if ($errors === []) {
        try {
            if ($id > 0) {
                db_jalankan($koneksi, 'UPDATE kategori_produk SET nama = ? WHERE id = ?', [$nilai['nama'], $id]);
                flash_set('sukses', 'Kategori berhasil diperbarui.');
            } else {
                db_jalankan($koneksi, 'INSERT INTO kategori_produk (nama) VALUES (?)', [$nilai['nama']]);
                flash_set('sukses', 'Kategori baru berhasil ditambahkan.');
            }

            redirect(ADMIN_URL . '/modul/kategori_produk/index.php');
        } catch (mysqli_sql_exception $e) {
            error_log('Simpan kategori produk gagal: ' . $e->getMessage());
            $errors['umum'] = 'Kategori gagal disimpan. Periksa kembali isiannya.';
        }
    }
}

$pageTitle = $mode === 'edit' ? 'Ubah Kategori Produk' : 'Tambah Kategori Produk';
require __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-lg-6">
        <?php if (isset($errors['umum'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['umum']) ?></div>
        <?php endif; ?>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><?= e($pageTitle) ?></h3>
            </div>
            <form method="post" action="<?= e(ADMIN_URL) ?>/modul/kategori_produk/form.php" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <div class="card-body">
                    <label for="nama" class="form-label">Nama kategori</label>
                    <input
                        id="nama"
                        name="nama"
                        class="form-control<?= kelas_error($errors, 'nama') ?>"
                        maxlength="80"
                        value="<?= e($nilai['nama']) ?>"
                        required
                        autofocus
                    >
                    <?= pesan_error($errors, 'nama') ?>
                </div>
                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
