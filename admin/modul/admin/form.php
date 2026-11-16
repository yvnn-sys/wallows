<?php
require_once __DIR__ . '/../../includes/auth.php';

$mentahId = $_POST['id'] ?? $_GET['id'] ?? 0;
$id = is_scalar($mentahId) ? (int) $mentahId : 0;
$mode = $id > 0 ? 'edit' : 'tambah';

$lama = null;
if ($id > 0) {
    $lama = db_satu($koneksi, 'SELECT id, nama, email FROM admin WHERE id = ?', [$id]);
    if ($lama === null) {
        flash_set('gagal', 'Akun admin tidak ditemukan.');
        redirect(ADMIN_URL . '/modul/admin/index.php');
    }
}

$nilai = [
    'nama' => $lama['nama'] ?? '',
    'email' => $lama['email'] ?? '',
];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !csrf_valid($token)) {
        flash_set('gagal', 'Sesi form sudah kedaluwarsa. Silakan coba lagi.');
        redirect(ADMIN_URL . '/modul/admin/index.php');
    }

    $nama = trim(input_teks('nama'));
    $email = trim(input_teks('email'));
    $sandi = input_teks('kata_sandi');

    $nilai = ['nama' => $nama, 'email' => $email];

    $aturanSandi = [
        'nilai' => $sandi,
        'label' => 'Kata sandi',
        'min' => 8,
    ];

    if ($id === 0) {
        $aturanSandi['wajib'] = true;
    }

    $errors = validasi_field([
        'nama' => ['nilai' => $nama, 'label' => 'Nama', 'wajib' => true, 'maks' => 100],
        'email' => ['nilai' => $email, 'label' => 'Email', 'wajib' => true, 'email' => true, 'maks' => 150],
        'kata_sandi' => $aturanSandi,
    ]);

    if (!isset($errors['email'])) {
        $duplikat = $id > 0
            ? db_satu($koneksi, 'SELECT id FROM admin WHERE email = ? AND id <> ? LIMIT 1', [$email, $id])
            : db_satu($koneksi, 'SELECT id FROM admin WHERE email = ? LIMIT 1', [$email]);

        if ($duplikat !== null) {
            $errors['email'] = 'Email sudah dipakai oleh akun admin lain.';
        }
    }

    if ($errors === []) {
        try {
            if ($id > 0) {
                if ($sandi !== '') {
                    $hash = password_hash($sandi, PASSWORD_DEFAULT);
                    db_jalankan(
                        $koneksi,
                        'UPDATE admin SET nama = ?, email = ?, kata_sandi = ? WHERE id = ?',
                        [$nama, $email, $hash, $id]
                    );
                } else {
                    db_jalankan(
                        $koneksi,
                        'UPDATE admin SET nama = ?, email = ? WHERE id = ?',
                        [$nama, $email, $id]
                    );
                }

                if ($id === (int) ($_SESSION['admin_id'] ?? 0)) {
                    $_SESSION['admin_nama'] = $nama;
                }

                flash_set('sukses', 'Akun admin berhasil diperbarui.');
            } else {
                $hash = password_hash($sandi, PASSWORD_DEFAULT);
                db_jalankan(
                    $koneksi,
                    'INSERT INTO admin (nama, email, kata_sandi) VALUES (?, ?, ?)',
                    [$nama, $email, $hash]
                );

                flash_set('sukses', 'Akun admin baru berhasil ditambahkan.');
            }

            redirect(ADMIN_URL . '/modul/admin/index.php');
        } catch (mysqli_sql_exception $e) {
            error_log('Simpan akun admin gagal: ' . $e->getMessage());
            $errors['umum'] = 'Akun admin gagal disimpan. Periksa kembali isiannya.';
        }
    }
}

$pageTitle = $mode === 'edit' ? 'Ubah Admin' : 'Tambah Admin';
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
            <form method="post" action="<?= e(ADMIN_URL) ?>/modul/admin/form.php" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input
                            id="nama"
                            name="nama"
                            class="form-control<?= kelas_error($errors, 'nama') ?>"
                            maxlength="100"
                            value="<?= e((string) $nilai['nama']) ?>"
                            required
                            autofocus
                        >
                        <?= pesan_error($errors, 'nama') ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="form-control<?= kelas_error($errors, 'email') ?>"
                            maxlength="150"
                            value="<?= e((string) $nilai['email']) ?>"
                            required
                        >
                        <?= pesan_error($errors, 'email') ?>
                    </div>

                    <div class="mb-3">
                        <label for="kata_sandi" class="form-label">
                            Kata sandi
                            <?php if ($mode === 'edit'): ?>
                                <span class="text-secondary fw-normal">(opsional)</span>
                            <?php endif; ?>
                        </label>
                        <input
                            id="kata_sandi"
                            name="kata_sandi"
                            type="password"
                            class="form-control<?= kelas_error($errors, 'kata_sandi') ?>"
                            autocomplete="new-password"
                            <?= $mode === 'tambah' ? 'required' : '' ?>
                        >
                        <div class="form-text">
                            Minimal 8 karakter.
                            <?php if ($mode === 'edit'): ?>
                                Kosongkan jika tidak ingin mengganti kata sandi.
                            <?php endif; ?>
                        </div>
                        <?= pesan_error($errors, 'kata_sandi') ?>
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
