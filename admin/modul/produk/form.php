<?php
require_once __DIR__ . '/../../includes/auth.php';

$mentahId = $_POST['id'] ?? $_GET['id'] ?? 0;
$id = is_scalar($mentahId) ? (int) $mentahId : 0;
$mode = $id > 0 ? 'edit' : 'tambah';

$lama = null;
if ($id > 0) {
    $lama = db_satu($koneksi, 'SELECT * FROM produk WHERE id = ?', [$id]);
    if ($lama === null) {
        flash_set('gagal', 'Produk tidak ditemukan.');
        redirect(ADMIN_URL . '/modul/produk/index.php');
    }
}

$kategori = db_semua($koneksi, 'SELECT id, nama FROM kategori_produk ORDER BY nama ASC');

$nilai = $lama ?? [
    'nama' => '',
    'kategori_produk_id' => (int) ($kategori[0]['id'] ?? 0),
    'harga' => '0',
    'stok' => '0',
    'tautan_gambar' => null,
    'label_produk' => null,
    'status_aktif' => 1,
];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !csrf_valid($token)) {
        flash_set('gagal', 'Sesi form sudah kedaluwarsa. Silakan coba lagi.');
        redirect(ADMIN_URL . '/modul/produk/index.php');
    }

    $nama = trim(input_teks('nama'));
    $kategoriId = input_int('kategori_produk_id');
    $hargaMentah = trim(input_teks('harga'));
    $stokMentah = trim(input_teks('stok'));
    $label = input_pilihan('label_produk', ['new', 'limited'], null);
    $statusAktif = isset($_POST['status_aktif']) ? 1 : 0;

    $nilai = [
        'nama' => $nama,
        'kategori_produk_id' => $kategoriId,
        'harga' => $hargaMentah,
        'stok' => $stokMentah,
        'tautan_gambar' => $lama['tautan_gambar'] ?? null,
        'label_produk' => $label,
        'status_aktif' => $statusAktif,
    ];

    $errors = validasi_field([
        'nama' => ['nilai' => $nama, 'label' => 'Nama produk', 'wajib' => true, 'maks' => 200],
    ]);

    if ($kategoriId <= 0 || (int) db_nilai($koneksi, 'SELECT COUNT(*) FROM kategori_produk WHERE id = ?', [$kategoriId], 0) < 1) {
        $errors['kategori_produk_id'] = 'Pilih kategori produk yang tersedia.';
    }

    if ($hargaMentah === '' || !is_numeric($hargaMentah) || (float) $hargaMentah < 0) {
        $errors['harga'] = 'Harga harus berupa angka 0 atau lebih.';
    }

    if (preg_match('/^\d+$/', $stokMentah) !== 1) {
        $errors['stok'] = 'Stok harus bilangan bulat 0 atau lebih.';
    }

    if ($errors === []) {
        $harga = (float) $hargaMentah;
        $stok = (int) $stokMentah;
        $gambarBaru = null;

        try {
            $gambarBaru = unggah_gambar($_FILES['tautan_gambar'] ?? []);
            if ($gambarBaru !== null) {
                $nilai['tautan_gambar'] = $gambarBaru;
            }

            if ($id > 0) {
                db_jalankan(
                    $koneksi,
                    'UPDATE produk
                     SET nama = ?, kategori_produk_id = ?, harga = ?, stok = ?, tautan_gambar = ?, label_produk = ?, status_aktif = ?
                     WHERE id = ?',
                    [$nama, $kategoriId, $harga, $stok, $nilai['tautan_gambar'], $label, $statusAktif, $id]
                );

                if ($gambarBaru !== null && !empty($lama['tautan_gambar'])) {
                    hapus_gambar($lama['tautan_gambar']);
                }

                flash_set('sukses', 'Produk berhasil diperbarui.');
            } else {
                db_jalankan(
                    $koneksi,
                    'INSERT INTO produk (nama, kategori_produk_id, harga, stok, tautan_gambar, label_produk, status_aktif)
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [$nama, $kategoriId, $harga, $stok, $nilai['tautan_gambar'], $label, $statusAktif]
                );

                flash_set('sukses', 'Produk baru berhasil ditambahkan.');
            }

            redirect(ADMIN_URL . '/modul/produk/index.php');
        } catch (mysqli_sql_exception $e) {
            error_log('Simpan produk gagal: ' . $e->getMessage());
            if ($gambarBaru !== null) {
                hapus_gambar($gambarBaru);
            }
            $errors['umum'] = 'Produk gagal disimpan. Periksa kembali isiannya.';
        } catch (RuntimeException $e) {
            $errors['tautan_gambar'] = $e->getMessage();
        }
    }
}

$pageTitle = $mode === 'edit' ? 'Ubah Produk' : 'Tambah Produk';
require __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <?php if (isset($errors['umum'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['umum']) ?></div>
        <?php endif; ?>

        <?php if ($kategori === []): ?>
            <div class="alert alert-warning" role="alert">
                Belum ada kategori produk. Tambahkan kategori terlebih dahulu di
                <a href="../kategori_produk/index.php" class="alert-link">halaman kategori</a>.
            </div>
        <?php endif; ?>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><?= e($pageTitle) ?></h3>
            </div>
            <form method="post" action="<?= e(ADMIN_URL) ?>/modul/produk/form.php" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama produk</label>
                        <input
                            id="nama"
                            name="nama"
                            class="form-control<?= kelas_error($errors, 'nama') ?>"
                            maxlength="200"
                            value="<?= e((string) $nilai['nama']) ?>"
                            required
                            autofocus
                        >
                        <?= pesan_error($errors, 'nama') ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kategori_produk_id" class="form-label">Kategori</label>
                            <select id="kategori_produk_id" name="kategori_produk_id" class="form-select<?= kelas_error($errors, 'kategori_produk_id') ?>" required>
                                <option value="">&mdash; Pilih kategori &mdash;</option>
                                <?php foreach ($kategori as $item): ?>
                                    <option value="<?= (int) $item['id'] ?>" <?= (int) $nilai['kategori_produk_id'] === (int) $item['id'] ? 'selected' : '' ?>>
                                        <?= e($item['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= pesan_error($errors, 'kategori_produk_id') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="label_produk" class="form-label">Label</label>
                            <select id="label_produk" name="label_produk" class="form-select">
                                <option value="">Tanpa label</option>
                                <option value="new" <?= $nilai['label_produk'] === 'new' ? 'selected' : '' ?>>New</option>
                                <option value="limited" <?= $nilai['label_produk'] === 'limited' ? 'selected' : '' ?>>Limited</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="harga" class="form-label">Harga (Rp)</label>
                            <input
                                id="harga"
                                name="harga"
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control<?= kelas_error($errors, 'harga') ?>"
                                value="<?= e((string) $nilai['harga']) ?>"
                                required
                            >
                            <?= pesan_error($errors, 'harga') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input
                                id="stok"
                                name="stok"
                                type="number"
                                min="0"
                                step="1"
                                class="form-control<?= kelas_error($errors, 'stok') ?>"
                                value="<?= e((string) $nilai['stok']) ?>"
                                required
                            >
                            <?= pesan_error($errors, 'stok') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tautan_gambar" class="form-label">Foto produk</label>
                        <input
                            id="tautan_gambar"
                            name="tautan_gambar"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            class="form-control<?= kelas_error($errors, 'tautan_gambar') ?>"
                        >
                        <div class="form-text">JPG, PNG, WEBP, atau GIF. Maksimal 2 MB.</div>
                        <?= pesan_error($errors, 'tautan_gambar') ?>

                        <?php if (!empty($nilai['tautan_gambar'])): ?>
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img src="<?= e(url_gambar($nilai['tautan_gambar'])) ?>" alt="Foto produk saat ini" class="img-thumbnail" style="max-height:120px">
                                <span class="text-secondary small">Foto saat ini</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-check">
                        <input
                            id="status_aktif"
                            name="status_aktif"
                            type="checkbox"
                            value="1"
                            class="form-check-input"
                            <?= (int) $nilai['status_aktif'] === 1 ? 'checked' : '' ?>
                        >
                        <label for="status_aktif" class="form-check-label">Tampilkan produk di toko</label>
                    </div>
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
