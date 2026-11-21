<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/fungsi.php';

$pageTitle = 'Detail Pesanan';
$activeMenu = 'pesanan';

$mentahId = $_GET['id'] ?? 0;
$id = is_scalar($mentahId) ? (int) $mentahId : 0;

if ($id <= 0) {
    flash_set('gagal', 'Pesanan tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/pesanan/index.php');
}

$pesanan = db_satu(
    $koneksi,
    'SELECT ps.*, pl.nama AS nama_akun, pl.email AS email_akun
     FROM pesanan ps
     LEFT JOIN pelanggan pl ON pl.id = ps.pelanggan_id
     WHERE ps.id = ?
     LIMIT 1',
    [$id]
);

if ($pesanan === null) {
    flash_set('gagal', 'Pesanan tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/pesanan/index.php');
}

$item = db_semua(
    $koneksi,
    'SELECT ip.*, pr.nama AS nama_produk, pr.stok AS stok_produk
     FROM item_pesanan ip
     LEFT JOIN produk pr ON pr.id = ip.produk_id
     WHERE ip.pesanan_id = ?
     ORDER BY ip.id ASC',
    [$id]
);

$produkPilihan = db_semua(
    $koneksi,
    'SELECT id, nama, harga, stok FROM produk WHERE status_aktif = 1 ORDER BY nama ASC'
);

$pilihanStatus = pilihan_status_pesanan();
$bisaUbahItem = !in_array((string) $pesanan['status'], ['cancelled', 'shipped', 'completed'], true);
$sukses = flash_get('sukses');
$gagal = flash_get('gagal');

$namaPelanggan = ($pesanan['nama_akun'] ?? '') !== ''
    ? (string) $pesanan['nama_akun']
    : (string) $pesanan['nama_pelanggan'];
$emailPelanggan = ($pesanan['email_akun'] ?? '') !== ''
    ? (string) $pesanan['email_akun']
    : (string) $pesanan['email_pelanggan'];
$alamat = trim((string) ($pesanan['alamat_pengiriman'] ?? ''));

require __DIR__ . '/../../includes/header.php';
?>

<?php if ($sukses !== null): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($sukses) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if ($gagal !== null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= e($gagal) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Pesanan <?= e($pesanan['nomor_pesanan']) ?></h3>
                <?= badge_status((string) $pesanan['status']) ?>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nama pelanggan</dt>
                    <dd class="col-sm-8">
                        <?= e($namaPelanggan) ?>
                        <?php if (empty($pesanan['pelanggan_id'])): ?>
                            <span class="badge text-bg-secondary ms-1">Tanpa akun</span>
                        <?php else: ?>
                            <span class="badge text-bg-info ms-1">Akun terdaftar</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8"><?= e($emailPelanggan) ?></dd>

                    <dt class="col-sm-4">Alamat pengiriman</dt>
                    <dd class="col-sm-8">
                        <?= $alamat !== '' ? nl2br(e($alamat)) : '<span class="text-secondary">-</span>' ?>
                    </dd>

                    <dt class="col-sm-4">Dibuat pada</dt>
                    <dd class="col-sm-8"><?= e(tanggal_singkat($pesanan['dibuat_pada'])) ?></dd>

                    <dt class="col-sm-4">Total</dt>
                    <dd class="col-sm-8 fw-semibold"><?= e(usd($pesanan['total_harga'])) ?></dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Item pesanan</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-end">Harga satuan</th>
                            <th class="text-center" style="width:200px">Jumlah</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end" style="width:70px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($item === []): ?>
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">Belum ada item pada pesanan ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($item as $baris): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= e($baris['nama_item']) ?></div>
                                        <?php if ($baris['nama_produk'] !== null): ?>
                                            <div class="small text-secondary">
                                                Produk: <?= e($baris['nama_produk']) ?> (stok <?= (int) $baris['stok_produk'] ?>)
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($baris['kode_tiket'])): ?>
                                            <div class="small text-secondary">Kode tiket: <?= e($baris['kode_tiket']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= e(usd($baris['harga_satuan'])) ?></td>
                                    <td class="text-center">
                                        <?php if ($bisaUbahItem && $baris['tiket_konser_id'] === null): ?>
                                            <form method="post" action="item_proses.php" class="d-flex justify-content-center gap-1">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="aksi" value="ubah">
                                                <input type="hidden" name="pesanan_id" value="<?= (int) $pesanan['id'] ?>">
                                                <input type="hidden" name="item_id" value="<?= (int) $baris['id'] ?>">
                                                <input type="number" name="jumlah" min="1" step="1" value="<?= (int) $baris['jumlah'] ?>" class="form-control form-control-sm" style="width:90px">
                                                <button type="submit" class="btn btn-outline-primary btn-sm">Simpan</button>
                                            </form>
                                        <?php else: ?>
                                            <?= (int) $baris['jumlah'] ?>
                                            <?php if (!empty($baris['kode_tiket'])): ?>
                                                <div class="small text-secondary">Satu kode per baris</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= e(usd((float) $baris['harga_satuan'] * (int) $baris['jumlah'])) ?></td>
                                    <td class="text-end">
                                        <?php if ($bisaUbahItem): ?>
                                            <form method="post" action="item_proses.php" onsubmit="return confirm('Hapus item ini?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="pesanan_id" value="<?= (int) $pesanan['id'] ?>">
                                                <input type="hidden" name="item_id" value="<?= (int) $baris['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i><span class="visually-hidden">Hapus item</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end"><?= e(usd($pesanan['total_harga'])) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($bisaUbahItem): ?>
                <div class="card-footer">
                    <form method="post" action="item_proses.php" class="row g-2 align-items-end">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="tambah">
                        <input type="hidden" name="pesanan_id" value="<?= (int) $pesanan['id'] ?>">
                        <div class="col-md-6">
                            <label for="produk_id" class="form-label">Tambah item produk</label>
                            <select id="produk_id" name="produk_id" class="form-select" required <?= $produkPilihan === [] ? 'disabled' : '' ?>>
                                <option value="">&mdash; Pilih produk &mdash;</option>
                                <?php foreach ($produkPilihan as $produk): ?>
                                    <option value="<?= (int) $produk['id'] ?>">
                                        <?= e($produk['nama']) ?> &mdash; <?= e(usd($produk['harga'])) ?> (stok <?= (int) $produk['stok'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input id="jumlah" type="number" name="jumlah" min="1" step="1" value="1" class="form-control" style="width:90px">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary" <?= $produkPilihan === [] ? 'disabled' : '' ?>>
                                <i class="bi bi-plus-lg me-1"></i>Tambah
                            </button>
                        </div>
                        <?php if ($produkPilihan === []): ?>
                            <div class="col-12">
                                <div class="form-text text-danger">Belum ada produk aktif yang bisa ditambahkan.</div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            <?php else: ?>
                    <div class="card-footer text-secondary small">
                        Item tidak dapat diubah karena pesanan sudah dibatalkan, dikirim, atau selesai.
                    </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ubah status</h3>
            </div>
            <?php if ($pesanan['status'] === 'cancelled'): ?>
                <div class="card-body">
                    <div class="alert alert-secondary mb-0" role="alert">
                        Pesanan ini sudah dibatalkan dan stoknya telah dikembalikan. Status tidak dapat diaktifkan kembali.
                    </div>
                </div>
            <?php else: ?>
                <form method="post" action="status.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $pesanan['id'] ?>">
                    <div class="card-body">
                        <label for="status" class="form-label">Status pesanan</label>
                        <select id="status" name="status" class="form-select">
                            <?php foreach ($pilihanStatus as $kunci => $label): ?>
                                <option value="<?= e($kunci) ?>" <?= $pesanan['status'] === $kunci ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Status Dibatalkan akan mengembalikan stok produk dan kuota tiket secara otomatis.</div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i>Simpan status
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tindakan</h3>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar
                </a>
                <form method="post" action="hapus.php" onsubmit="return confirm('Hapus pesanan ini beserta seluruh itemnya?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $pesanan['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i>Hapus pesanan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
