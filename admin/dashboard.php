<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';

$ringkasan = [
    'pesanan_pending' => 0,
    'pendapatan' => 0,
    'pesan_baru' => 0,
    'stok_menipis' => 0,
    'tiket_terjual' => 0,
];

$daftarQuery = [
    'pesanan_pending' => "SELECT COUNT(*) AS nilai FROM pesanan WHERE status = 'pending'",
    'pendapatan' => "SELECT COALESCE(SUM(total_harga), 0) AS nilai FROM pesanan WHERE status IN ('paid', 'shipped', 'completed')",
    'pesan_baru' => "SELECT COUNT(*) AS nilai FROM pesan_kontak WHERE status = 'new'",
    'stok_menipis' => "SELECT COUNT(*) AS nilai FROM produk WHERE status_aktif = 1 AND stok < 10",
    'tiket_terjual' => "SELECT COALESCE(SUM(terjual), 0) AS nilai FROM tiket_konser",
];

foreach ($daftarQuery as $kunci => $sql) {
    $hasil = safe_query($koneksi, $sql);
    if ($hasil !== null) {
        $baris = $hasil->fetch_assoc();
        $ringkasan[$kunci] = $baris['nilai'] ?? 0;
        $hasil->free();
    }
}

$pesananTerbaru = [];
$hasilPesanan = safe_query(
    $koneksi,
    "SELECT nomor_pesanan, nama_pelanggan, total_harga, status, dibuat_pada
     FROM pesanan
     ORDER BY dibuat_pada DESC, id DESC
     LIMIT 5"
);

if ($hasilPesanan !== null) {
    while ($baris = $hasilPesanan->fetch_assoc()) {
        $pesananTerbaru[] = $baris;
    }
    $hasilPesanan->free();
}

$pesanTerbaru = [];
$hasilPesan = safe_query(
    $koneksi,
    "SELECT nama, topik, status, dibuat_pada
     FROM pesan_kontak
     ORDER BY dibuat_pada DESC, id DESC
     LIMIT 5"
);

if ($hasilPesan !== null) {
    while ($baris = $hasilPesan->fetch_assoc()) {
        $pesanTerbaru[] = $baris;
    }
    $hasilPesan->free();
}

$sukses = flash_get('sukses');

require __DIR__ . '/includes/header.php';
?>

<?php if ($sukses !== null): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($sukses) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-warning">
            <div class="inner">
                <h3><?= (int) $ringkasan['pesanan_pending'] ?></h3>
                <p>Pesanan menunggu</p>
            </div>
            <i class="small-box-icon bi bi-receipt"></i>
            <span class="small-box-footer">Modul pesanan segera <i class="bi bi-clock"></i></span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success">
            <div class="inner">
                <h3 class="fs-4"><?= e(rupiah($ringkasan['pendapatan'])) ?></h3>
                <p>Pendapatan tercatat</p>
            </div>
            <i class="small-box-icon bi bi-cash-coin"></i>
            <span class="small-box-footer">Status dibayar, dikirim, selesai</span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-danger">
            <div class="inner">
                <h3><?= (int) $ringkasan['pesan_baru'] ?></h3>
                <p>Pesan baru</p>
            </div>
            <i class="small-box-icon bi bi-chat-dots"></i>
            <span class="small-box-footer">Modul pesan segera <i class="bi bi-clock"></i></span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-info">
            <div class="inner">
                <h3><?= (int) $ringkasan['stok_menipis'] ?></h3>
                <p>Produk stok menipis</p>
            </div>
            <i class="small-box-icon bi bi-box-seam"></i>
            <span class="small-box-footer">Stok di bawah 10</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Pesanan terbaru</h3>
                <div class="card-tools">
                    <span class="badge text-bg-secondary">
                        Tiket terjual: <?= number_format((float) $ringkasan['tiket_terjual'], 0, ',', '.') ?>
                    </span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pesananTerbaru === []): ?>
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-3">Belum ada pesanan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pesananTerbaru as $pesanan): ?>
                                <tr>
                                    <td><?= e($pesanan['nomor_pesanan']) ?></td>
                                    <td><?= e($pesanan['nama_pelanggan']) ?></td>
                                    <td><?= e(rupiah($pesanan['total_harga'])) ?></td>
                                    <td><?= badge_status((string) $pesanan['status']) ?></td>
                                    <td><?= e(tanggal_singkat($pesanan['dibuat_pada'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Pesan kontak terbaru</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Topik</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pesanTerbaru === []): ?>
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-3">Belum ada pesan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pesanTerbaru as $pesan): ?>
                                <tr>
                                    <td><?= e($pesan['nama']) ?></td>
                                    <td><?= e(label_topik((string) $pesan['topik'])) ?></td>
                                    <td><?= badge_status((string) $pesan['status']) ?></td>
                                    <td><?= e(tanggal_singkat($pesan['dibuat_pada'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
