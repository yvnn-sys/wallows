<?php
require_once __DIR__ . '/includes/init.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . ADMIN_URL . '/dashboard.php');
    exit;
}

$error = flash_get('error');
$info = null;

$hasilJumlah = safe_query($koneksi, 'SELECT COUNT(*) AS jumlah FROM admin');
if ($hasilJumlah !== null) {
    $barisJumlah = $hasilJumlah->fetch_assoc();
    $jumlahAdmin = (int) ($barisJumlah['jumlah'] ?? 0);
    $hasilJumlah->free();

    if ($jumlahAdmin === 0) {
        $info = 'Belum ada akun admin. Jalankan perintah: php tools/buat_admin.php "Nama Admin" email@contoh.com';
    }
}
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk &middot; <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(BASE_PATH) ?>/dist/css/adminlte.min.css">
</head>
<body class="login-page bg-body-secondary">
<main class="login-box">
    <div class="login-logo mb-3">
        <span class="fw-bold">Wallows</span> Admin
    </div>

    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Masuk untuk mengelola situs</p>

            <?php if ($error !== null): ?>
                <div class="alert alert-danger py-2" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if ($info !== null): ?>
                <div class="alert alert-warning py-2 small" role="alert"><?= e($info) ?></div>
            <?php endif; ?>

            <form action="<?= e(ADMIN_URL) ?>/proses_login.php" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <label class="visually-hidden" for="email">Email</label>
                <div class="input-group mb-3">
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="form-control"
                        placeholder="Email"
                        autocomplete="username"
                        required
                        autofocus
                    >
                    <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                </div>

                <label class="visually-hidden" for="password">Sandi</label>
                <div class="input-group mb-3">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-control"
                        placeholder="Sandi"
                        autocomplete="current-password"
                        required
                    >
                    <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Masuk</button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-center text-secondary small mt-3 mb-0">Wallows Official Web</p>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(BASE_PATH) ?>/dist/js/adminlte.min.js"></script>
</body>
</html>
