<?php
if (!defined('ADMIN_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

$pageTitle = $pageTitle ?? 'Dashboard';
$activeMenu = $activeMenu ?? '';
$adminNama = $_SESSION['admin_nama'] ?? 'Admin';
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(BASE_PATH) ?>/dist/css/adminlte.min.css">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Buka menu">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <a href="<?= e(BASE_PATH) ?>/index.html" class="nav-link" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Lihat situs
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <span class="d-none d-md-inline"><?= e($adminNama) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2">
                            <div class="fw-semibold"><?= e($adminNama) ?></div>
                            <div class="small text-secondary">Masuk sebagai admin</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="<?= e(ADMIN_URL) ?>/logout.php?token=<?= e(csrf_token()) ?>" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-1"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= e(ADMIN_URL) ?>/dashboard.php" class="brand-link">
                <span class="brand-text fw-light">Wallows Admin</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <nav class="mt-2" aria-label="Menu utama">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">
                    <li class="nav-item">
                        <a href="<?= e(ADMIN_URL) ?>/dashboard.php" class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-header">KONTEN</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-newspaper"></i>
                            <p>Berita <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-chat-dots"></i>
                            <p>Pesan Kontak <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-bag"></i>
                            <p>Produk <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-receipt"></i>
                            <p>Pesanan <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>

                    <li class="nav-header">KONSER &amp; MUSIK</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-ticket-perforated"></i>
                            <p>Konser &amp; Tiket <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-music-note-beamed"></i>
                            <p>Musik <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-images"></i>
                            <p>Galeri <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Anggota <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>

                    <li class="nav-header">PENGGUNA</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link disabled" aria-disabled="true">
                            <i class="nav-icon bi bi-person-gear"></i>
                            <p>Admin <span class="nav-badge badge text-bg-secondary me-2">segera</span></p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1 class="mb-0 fs-3"><?= e($pageTitle) ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="<?= e(ADMIN_URL) ?>/dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?= e($pageTitle) ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
