<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/tiket_konser.php';

$pageTitle = 'Konser & Tiket';
$activeMenu = 'konser_tiket';

$daftarTab = ['konser', 'tiket', 'jenis', 'bagian'];
$tab = (string) ($_GET['tab'] ?? 'konser');
if (!in_array($tab, $daftarTab, true)) {
    $tab = 'konser';
}

function url_tab(string $tab): string
{
    return ADMIN_URL . '/konser_tiket.php?tab=' . $tab;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tujuan = url_tab($tab);

    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        flash_set('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect($tujuan);
    }

    $aksi = (string) ($_POST['aksi'] ?? '');

    try {
        switch ($aksi) {
            case 'simpan_bagian':
                $id = (int) ($_POST['id'] ?? 0);
                $nama = trim((string) ($_POST['nama'] ?? ''));
                $tamu = trim((string) ($_POST['tamu_khusus'] ?? ''));
                $tamuNilai = $tamu === '' ? null : $tamu;

                if ($nama === '') {
                    flash_set('error', 'Nama bagian tur wajib diisi.');
                    break;
                }

                if ($id > 0) {
                    $stmt = $koneksi->prepare('UPDATE bagian_tur SET nama = ?, tamu_khusus = ? WHERE id = ?');
                    $stmt->bind_param('ssi', $nama, $tamuNilai, $id);
                } else {
                    $stmt = $koneksi->prepare('INSERT INTO bagian_tur (nama, tamu_khusus) VALUES (?, ?)');
                    $stmt->bind_param('ss', $nama, $tamuNilai);
                }
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Bagian tur berhasil disimpan.');
                break;

            case 'hapus_bagian':
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $koneksi->prepare('SELECT COUNT(*) AS jumlah FROM konser WHERE bagian_tur_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $jumlahKonser = (int) ($stmt->get_result()->fetch_assoc()['jumlah'] ?? 0);
                $stmt->close();

                if ($jumlahKonser > 0) {
                    flash_set('error', 'Bagian tur masih memiliki ' . $jumlahKonser . ' konser. Hapus atau pindahkan konsernya lebih dulu.');
                    break;
                }

                $stmt = $koneksi->prepare('DELETE FROM bagian_tur WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Bagian tur berhasil dihapus.');
                break;

            case 'simpan_konser':
                $id = (int) ($_POST['id'] ?? 0);
                $bagianTurId = (int) ($_POST['bagian_tur_id'] ?? 0);
                $namaTempat = trim((string) ($_POST['nama_tempat'] ?? ''));
                $kota = trim((string) ($_POST['kota'] ?? ''));
                $wilayah = trim((string) ($_POST['wilayah'] ?? ''));
                $tanggalAcara = trim((string) ($_POST['tanggal_acara'] ?? ''));
                $jamPintu = trim((string) ($_POST['jam_pintu_buka'] ?? ''));
                $jamTampil = trim((string) ($_POST['jam_tampil'] ?? ''));
                $batasUsia = trim((string) ($_POST['batas_usia'] ?? ''));
                $catatan = trim((string) ($_POST['catatan'] ?? ''));
                $statusPenjualan = (string) ($_POST['status_penjualan'] ?? 'on_sale');

                $masalah = [];
                if ($bagianTurId <= 0) {
                    $masalah[] = 'bagian tur';
                }
                if ($namaTempat === '') {
                    $masalah[] = 'nama tempat';
                }
                if ($kota === '') {
                    $masalah[] = 'kota';
                }
                if ($wilayah === '') {
                    $masalah[] = 'wilayah';
                }
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalAcara)) {
                    $masalah[] = 'tanggal acara';
                }
                if (!array_key_exists($statusPenjualan, opsi_status_penjualan())) {
                    $masalah[] = 'status penjualan';
                }
                if ($jamPintu !== '' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $jamPintu)) {
                    $masalah[] = 'jam pintu buka';
                }
                if ($jamTampil !== '' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $jamTampil)) {
                    $masalah[] = 'jam tampil';
                }

                if ($masalah !== []) {
                    flash_set('error', 'Data belum lengkap/valid: ' . implode(', ', $masalah) . '.');
                    break;
                }

                $jamPintuNilai = $jamPintu === '' ? null : $jamPintu;
                $jamTampilNilai = $jamTampil === '' ? null : $jamTampil;
                $batasUsiaNilai = $batasUsia === '' ? null : $batasUsia;
                $catatanNilai = $catatan === '' ? null : $catatan;

                if ($id > 0) {
                    $stmt = $koneksi->prepare(
                        'UPDATE konser SET bagian_tur_id = ?, nama_tempat = ?, kota = ?, wilayah = ?,
                         tanggal_acara = ?, jam_pintu_buka = ?, jam_tampil = ?, batas_usia = ?,
                         catatan = ?, status_penjualan = ? WHERE id = ?'
                    );
                    $stmt->bind_param(
                        'isssssssssi',
                        $bagianTurId,
                        $namaTempat,
                        $kota,
                        $wilayah,
                        $tanggalAcara,
                        $jamPintuNilai,
                        $jamTampilNilai,
                        $batasUsiaNilai,
                        $catatanNilai,
                        $statusPenjualan,
                        $id
                    );
                } else {
                    $stmt = $koneksi->prepare(
                        'INSERT INTO konser (bagian_tur_id, nama_tempat, kota, wilayah, tanggal_acara,
                         jam_pintu_buka, jam_tampil, batas_usia, catatan, status_penjualan)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->bind_param(
                        'isssssssss',
                        $bagianTurId,
                        $namaTempat,
                        $kota,
                        $wilayah,
                        $tanggalAcara,
                        $jamPintuNilai,
                        $jamTampilNilai,
                        $batasUsiaNilai,
                        $catatanNilai,
                        $statusPenjualan
                    );
                }
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Konser berhasil disimpan.');
                break;

            case 'hapus_konser':
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $koneksi->prepare('DELETE FROM konser WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Konser beserta data tiketnya berhasil dihapus.');
                break;

            case 'simpan_jenis':
                $id = (int) ($_POST['id'] ?? 0);
                $nama = trim((string) ($_POST['nama'] ?? ''));
                $kategori = (string) ($_POST['kategori'] ?? 'standard');
                $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
                $fasilitas = trim((string) ($_POST['fasilitas'] ?? ''));

                if ($nama === '') {
                    flash_set('error', 'Nama jenis tiket wajib diisi.');
                    break;
                }
                if (!array_key_exists($kategori, opsi_kategori_tiket())) {
                    flash_set('error', 'Kategori tiket tidak valid.');
                    break;
                }

                $deskripsiNilai = $deskripsi === '' ? null : $deskripsi;
                $fasilitasNilai = $fasilitas === '' ? null : $fasilitas;

                if ($id > 0) {
                    $stmt = $koneksi->prepare('UPDATE jenis_tiket SET nama = ?, kategori = ?, deskripsi = ?, fasilitas = ? WHERE id = ?');
                    $stmt->bind_param('ssssi', $nama, $kategori, $deskripsiNilai, $fasilitasNilai, $id);
                } else {
                    $stmt = $koneksi->prepare('INSERT INTO jenis_tiket (nama, kategori, deskripsi, fasilitas) VALUES (?, ?, ?, ?)');
                    $stmt->bind_param('ssss', $nama, $kategori, $deskripsiNilai, $fasilitasNilai);
                }
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Jenis tiket berhasil disimpan.');
                break;

            case 'hapus_jenis':
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $koneksi->prepare('SELECT COUNT(*) AS jumlah FROM tiket_konser WHERE jenis_tiket_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $jumlahPakai = (int) ($stmt->get_result()->fetch_assoc()['jumlah'] ?? 0);
                $stmt->close();

                if ($jumlahPakai > 0) {
                    flash_set('error', 'Jenis tiket masih dipakai pada ' . $jumlahPakai . ' data tiket konser.');
                    break;
                }

                $stmt = $koneksi->prepare('DELETE FROM jenis_tiket WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Jenis tiket berhasil dihapus.');
                break;

            case 'simpan_tiket':
                $id = (int) ($_POST['id'] ?? 0);
                $konserId = (int) ($_POST['konser_id'] ?? 0);
                $jenisTiketId = (int) ($_POST['jenis_tiket_id'] ?? 0);
                $harga = (float) str_replace(',', '.', (string) ($_POST['harga'] ?? '0'));
                $kuota = (int) ($_POST['kuota'] ?? 0);
                $terjual = (int) ($_POST['terjual'] ?? 0);

                if ($konserId <= 0 || $jenisTiketId <= 0) {
                    flash_set('error', 'Konser dan jenis tiket wajib dipilih.');
                    break;
                }
                if ($harga < 0) {
                    flash_set('error', 'Harga tiket tidak boleh negatif.');
                    break;
                }
                if ($kuota < 0 || $terjual < 0) {
                    flash_set('error', 'Kuota dan jumlah terjual tidak boleh negatif.');
                    break;
                }
                if ($terjual > $kuota) {
                    flash_set('error', 'Jumlah terjual tidak boleh melebihi kuota.');
                    break;
                }

                $stmt = $koneksi->prepare(
                    'SELECT COUNT(*) AS jumlah FROM tiket_konser WHERE konser_id = ? AND jenis_tiket_id = ? AND id <> ?'
                );
                $stmt->bind_param('iii', $konserId, $jenisTiketId, $id);
                $stmt->execute();
                $duplikat = (int) ($stmt->get_result()->fetch_assoc()['jumlah'] ?? 0);
                $stmt->close();

                if ($duplikat > 0) {
                    flash_set('error', 'Jenis tiket tersebut sudah terdaftar untuk konser ini.');
                    break;
                }

                if ($id > 0) {
                    $stmt = $koneksi->prepare(
                        'UPDATE tiket_konser SET konser_id = ?, jenis_tiket_id = ?, harga = ?, kuota = ?, terjual = ? WHERE id = ?'
                    );
                    $stmt->bind_param('iidiii', $konserId, $jenisTiketId, $harga, $kuota, $terjual, $id);
                } else {
                    $stmt = $koneksi->prepare(
                        'INSERT INTO tiket_konser (konser_id, jenis_tiket_id, harga, kuota, terjual) VALUES (?, ?, ?, ?, ?)'
                    );
                    $stmt->bind_param('iidii', $konserId, $jenisTiketId, $harga, $kuota, $terjual);
                }
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Data tiket berhasil disimpan.');
                break;

            case 'hapus_tiket':
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $koneksi->prepare('DELETE FROM tiket_konser WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                flash_set('sukses', 'Data tiket berhasil dihapus.');
                break;

            default:
                flash_set('error', 'Aksi tidak dikenali.');
        }
    } catch (mysqli_sql_exception $error) {
        error_log('Aksi konser/tiket gagal: ' . $error->getMessage());
        $pesan = 'Gagal memproses data.';

        if ($error->getCode() === 1062) {
            $pesan = 'Data yang dimasukkan sudah ada (duplikat).';
        } elseif ($error->getCode() === 1451) {
            $pesan = 'Data masih dipakai oleh data lain sehingga tidak bisa dihapus.';
        } elseif ($error->getCode() === 1452) {
            $pesan = 'Data relasi tidak ditemukan. Periksa konser dan jenis tiket.';
        }

        flash_set('error', $pesan);
    }

    redirect($tujuan);
}

$sukses = flash_get('sukses');
$error = flash_get('error');

$editId = (int) ($_GET['edit'] ?? 0);
$formTambah = isset($_GET['tambah']);

$editBagian = null;
$editKonser = null;
$editJenis = null;
$editTiket = null;

if ($editId > 0) {
    match ($tab) {
        'bagian' => $editBagian = ambil_satu_baris($koneksi, 'SELECT * FROM bagian_tur WHERE id = ?', $editId),
        'konser' => $editKonser = ambil_satu_baris($koneksi, 'SELECT * FROM konser WHERE id = ?', $editId),
        'jenis' => $editJenis = ambil_satu_baris($koneksi, 'SELECT * FROM jenis_tiket WHERE id = ?', $editId),
        'tiket' => $editTiket = ambil_satu_baris($koneksi, 'SELECT * FROM tiket_konser WHERE id = ?', $editId),
        default => null,
    };
}

$bagianTur = [];
$hasilBagian = safe_query(
    $koneksi,
    'SELECT bt.id, bt.nama, bt.tamu_khusus, COUNT(k.id) AS jumlah_konser
     FROM bagian_tur bt
     LEFT JOIN konser k ON k.bagian_tur_id = bt.id
     GROUP BY bt.id, bt.nama, bt.tamu_khusus
     ORDER BY bt.nama ASC'
);
if ($hasilBagian !== null) {
    while ($baris = $hasilBagian->fetch_assoc()) {
        $bagianTur[] = $baris;
    }
    $hasilBagian->free();
}

$jenisTiket = [];
$hasilJenis = safe_query(
    $koneksi,
    'SELECT jt.id, jt.nama, jt.kategori, jt.deskripsi, jt.fasilitas, COUNT(tk.id) AS dipakai
     FROM jenis_tiket jt
     LEFT JOIN tiket_konser tk ON tk.jenis_tiket_id = jt.id
     GROUP BY jt.id, jt.nama, jt.kategori, jt.deskripsi, jt.fasilitas
     ORDER BY jt.kategori ASC, jt.nama ASC'
);
if ($hasilJenis !== null) {
    while ($baris = $hasilJenis->fetch_assoc()) {
        $jenisTiket[] = $baris;
    }
    $hasilJenis->free();
}

$daftarKonser = [];
$hasilKonser = safe_query(
    $koneksi,
    'SELECT k.id, k.bagian_tur_id, k.nama_tempat, k.kota, k.wilayah, k.tanggal_acara,
            k.jam_pintu_buka, k.jam_tampil, k.batas_usia, k.catatan, k.status_penjualan,
            bt.nama AS bagian_tur,
            COALESCE(SUM(tk.kuota), 0) AS total_kuota,
            COALESCE(SUM(tk.terjual), 0) AS total_terjual,
            COUNT(tk.id) AS jumlah_jenis
     FROM konser k
     JOIN bagian_tur bt ON bt.id = k.bagian_tur_id
     LEFT JOIN tiket_konser tk ON tk.konser_id = k.id
     GROUP BY k.id, k.bagian_tur_id, k.nama_tempat, k.kota, k.wilayah, k.tanggal_acara,
              k.jam_pintu_buka, k.jam_tampil, k.batas_usia, k.catatan, k.status_penjualan, bt.nama
     ORDER BY k.tanggal_acara ASC, k.id ASC'
);
if ($hasilKonser !== null) {
    while ($baris = $hasilKonser->fetch_assoc()) {
        $daftarKonser[] = $baris;
    }
    $hasilKonser->free();
}

$filterKonser = (int) ($_GET['konser'] ?? 0);
$daftarTiket = [];
$sqlTiket = 'SELECT tk.id, tk.konser_id, tk.jenis_tiket_id, tk.harga, tk.kuota, tk.terjual,
                    k.nama_tempat, k.kota, k.tanggal_acara,
                    jt.nama AS jenis_nama, jt.kategori
             FROM tiket_konser tk
             JOIN konser k ON k.id = tk.konser_id
             JOIN jenis_tiket jt ON jt.id = tk.jenis_tiket_id';
if ($filterKonser > 0) {
    $sqlTiket .= ' WHERE tk.konser_id = ' . $filterKonser;
}
$sqlTiket .= ' ORDER BY k.tanggal_acara ASC, jt.nama ASC';
$hasilTiket = safe_query($koneksi, $sqlTiket);
if ($hasilTiket !== null) {
    while ($baris = $hasilTiket->fetch_assoc()) {
        $daftarTiket[] = $baris;
    }
    $hasilTiket->free();
}

$ringkasan = [
    'konser' => count($daftarKonser),
    'kuota' => 0,
    'terjual' => 0,
    'pendapatan' => 0,
];

foreach ($daftarKonser as $baris) {
    $ringkasan['kuota'] += (int) $baris['total_kuota'];
    $ringkasan['terjual'] += (int) $baris['total_terjual'];
}

$hasilPendapatan = safe_query(
    $koneksi,
    "SELECT COALESCE(SUM(ip.harga_satuan * ip.jumlah), 0) AS nilai
     FROM item_pesanan ip
     JOIN pesanan ps ON ps.id = ip.pesanan_id
     WHERE ip.tiket_konser_id IS NOT NULL AND ps.status IN ('paid', 'shipped', 'completed')"
);
if ($hasilPendapatan !== null) {
    $ringkasan['pendapatan'] = (float) ($hasilPendapatan->fetch_assoc()['nilai'] ?? 0);
    $hasilPendapatan->free();
}

$ringkasan['sisa'] = max(0, $ringkasan['kuota'] - $ringkasan['terjual']);

$labelTab = [
    'konser' => 'Jadwal Konser',
    'tiket' => 'Tiket & Kuota',
    'jenis' => 'Jenis Tiket',
    'bagian' => 'Bagian Tur',
];

$tampilFormKonser = $tab === 'konser' && ($formTambah || $editKonser !== null);
$tampilFormTiket = $tab === 'tiket' && ($formTambah || $editTiket !== null);
$tampilFormJenis = $tab === 'jenis' && ($formTambah || $editJenis !== null);
$tampilFormBagian = $tab === 'bagian' && ($formTambah || $editBagian !== null);

require __DIR__ . '/includes/header.php';
?>

<?php if ($sukses !== null): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($sukses) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if ($error !== null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-primary">
            <div class="inner">
                <h3><?= (int) $ringkasan['konser'] ?></h3>
                <p>Total konser</p>
            </div>
            <i class="small-box-icon bi bi-music-note-list"></i>
            <span class="small-box-footer">Jadwal tur terdaftar</span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-info">
            <div class="inner">
                <h3><?= number_format($ringkasan['kuota'], 0, ',', '.') ?></h3>
                <p>Total kuota tiket</p>
            </div>
            <i class="small-box-icon bi bi-ticket-perforated"></i>
            <span class="small-box-footer">Seluruh jenis tiket</span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success">
            <div class="inner">
                <h3><?= number_format($ringkasan['terjual'], 0, ',', '.') ?></h3>
                <p>Tiket terjual</p>
            </div>
            <i class="small-box-icon bi bi-bag-check"></i>
            <span class="small-box-footer">Sisa <?= number_format($ringkasan['sisa'], 0, ',', '.') ?> tiket</span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-warning">
            <div class="inner">
                <h3 class="fs-4"><?= e(usd($ringkasan['pendapatan'])) ?></h3>
                <p>Pendapatan tiket</p>
            </div>
            <i class="small-box-icon bi bi-cash-coin"></i>
            <span class="small-box-footer">Dibayar, dikirim, selesai</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
            <?php foreach ($labelTab as $kunci => $label): ?>
                <li class="nav-item">
                    <a
                        href="<?= e(url_tab($kunci)) ?>"
                        class="nav-link <?= $tab === $kunci ? 'active' : '' ?>"
                        role="tab"
                    ><?= e($label) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card-body">
        <?php if ($tab === 'konser'): ?>
            <?php if ($tampilFormKonser): ?>
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?= $editKonser !== null ? 'Edit Konser' : 'Tambah Konser' ?>
                        </h3>
                    </div>
                    <form method="post" action="<?= e(ADMIN_URL) ?>/konser_tiket.php?tab=konser">
                        <div class="card-body">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="aksi" value="simpan_konser">
                            <input type="hidden" name="id" value="<?= (int) ($editKonser['id'] ?? 0) ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="konser_bagian">Bagian tur</label>
                                    <select class="form-select" id="konser_bagian" name="bagian_tur_id" required>
                                        <option value="">Pilih bagian tur</option>
                                        <?php foreach ($bagianTur as $bagian): ?>
                                            <option
                                                value="<?= (int) $bagian['id'] ?>"
                                                <?= (int) ($editKonser['bagian_tur_id'] ?? 0) === (int) $bagian['id'] ? 'selected' : '' ?>
                                            ><?= e($bagian['nama']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Belum ada pilihan? Tambahkan di tab Bagian Tur.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="konser_tempat">Nama tempat</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="konser_tempat"
                                        name="nama_tempat"
                                        maxlength="150"
                                        value="<?= e($editKonser['nama_tempat'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="konser_kota">Kota</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="konser_kota"
                                        name="kota"
                                        maxlength="100"
                                        value="<?= e($editKonser['kota'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="konser_wilayah">Wilayah / negara bagian</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="konser_wilayah"
                                        name="wilayah"
                                        maxlength="100"
                                        value="<?= e($editKonser['wilayah'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="konser_tanggal">Tanggal acara</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="konser_tanggal"
                                        name="tanggal_acara"
                                        value="<?= e($editKonser['tanggal_acara'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="konser_pintu">Jam pintu buka</label>
                                    <input
                                        type="time"
                                        class="form-control"
                                        id="konser_pintu"
                                        name="jam_pintu_buka"
                                        value="<?= e(substr((string) ($editKonser['jam_pintu_buka'] ?? ''), 0, 5)) ?>"
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="konser_tampil">Jam tampil</label>
                                    <input
                                        type="time"
                                        class="form-control"
                                        id="konser_tampil"
                                        name="jam_tampil"
                                        value="<?= e(substr((string) ($editKonser['jam_tampil'] ?? ''), 0, 5)) ?>"
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="konser_usia">Batas usia</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="konser_usia"
                                        name="batas_usia"
                                        maxlength="100"
                                        placeholder="Semua usia / 18+"
                                        value="<?= e($editKonser['batas_usia'] ?? '') ?>"
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="konser_status">Status penjualan</label>
                                    <select class="form-select" id="konser_status" name="status_penjualan">
                                        <?php foreach (opsi_status_penjualan() as $nilai => $label): ?>
                                            <option
                                                value="<?= e($nilai) ?>"
                                                <?= ($editKonser['status_penjualan'] ?? 'on_sale') === $nilai ? 'selected' : '' ?>
                                            ><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="konser_catatan">Catatan</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="konser_catatan"
                                        name="catatan"
                                        maxlength="150"
                                        value="<?= e($editKonser['catatan'] ?? '') ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="<?= e(url_tab('konser')) ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 fs-5">Daftar konser</h4>
                <a href="<?= e(url_tab('konser')) ?>&tambah=1" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Konser
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Konser</th>
                            <th>Bagian tur</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Tiket</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($daftarKonser === []): ?>
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">
                                    Belum ada konser. Tambahkan bagian tur lalu buat konser baru.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftarKonser as $konser): ?>
                                <?php
                                $kuota = (int) $konser['total_kuota'];
                                $terjual = (int) $konser['total_terjual'];
                                $persen = persen_terjual($kuota, $terjual);
                                ?>
                                <tr>
                                    <td class="text-nowrap"><?= e(format_tanggal_acara($konser['tanggal_acara'])) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($konser['nama_tempat']) ?></div>
                                        <div class="small text-secondary">
                                            <?= e($konser['kota']) ?>, <?= e($konser['wilayah']) ?>
                                        </div>
                                    </td>
                                    <td><?= e($konser['bagian_tur']) ?></td>
                                    <td class="small text-nowrap">
                                        Pintu <?= e(format_jam($konser['jam_pintu_buka'])) ?><br>
                                        Tampil <?= e(format_jam($konser['jam_tampil'])) ?>
                                    </td>
                                    <td><?= badge_status_penjualan((string) $konser['status_penjualan']) ?></td>
                                    <td style="min-width: 150px;">
                                        <div class="small">
                                            <?= number_format($terjual, 0, ',', '.') ?> /
                                            <?= number_format($kuota, 0, ',', '.') ?>
                                            (<?= (int) $konser['jumlah_jenis'] ?> jenis)
                                        </div>
                                        <div class="progress mt-1" style="height: 6px;">
                                            <div
                                                class="progress-bar bg-<?= e(warna_progress($persen)) ?>"
                                                style="width: <?= e((string) $persen) ?>%"
                                            ></div>
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a
                                            href="<?= e(url_tab('tiket')) ?>&konser=<?= (int) $konser['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Kelola tiket"
                                        ><i class="bi bi-ticket-perforated"></i></a>
                                        <a
                                            href="<?= e(url_tab('konser')) ?>&edit=<?= (int) $konser['id'] ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit"
                                        ><i class="bi bi-pencil-square"></i></a>
                                        <form
                                            method="post"
                                            action="<?= e(ADMIN_URL) ?>/konser_tiket.php?tab=konser"
                                            class="d-inline"
                                            onsubmit="return confirm('Hapus konser ini beserta data tiketnya?');"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="aksi" value="hapus_konser">
                                            <input type="hidden" name="id" value="<?= (int) $konser['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($tab === 'tiket'): ?>
            <?php if ($tampilFormTiket): ?>
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?= $editTiket !== null ? 'Edit Tiket Konser' : 'Tambah Tiket Konser' ?>
                        </h3>
                    </div>
                    <form method="post" action="<?= e(ADMIN_URL) ?>/konser_tiket.php?tab=tiket">
                        <div class="card-body">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="aksi" value="simpan_tiket">
                            <input type="hidden" name="id" value="<?= (int) ($editTiket['id'] ?? 0) ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="tiket_konser">Konser</label>
                                    <select class="form-select" id="tiket_konser" name="konser_id" required>
                                        <option value="">Pilih konser</option>
                                        <?php foreach ($daftarKonser as $konser): ?>
                                            <option
                                                value="<?= (int) $konser['id'] ?>"
                                                <?= (int) ($editTiket['konser_id'] ?? $filterKonser) === (int) $konser['id'] ? 'selected' : '' ?>
                                            ><?= e(format_tanggal_acara($konser['tanggal_acara'])) ?> &middot; <?= e($konser['nama_tempat']) ?> (<?= e($konser['kota']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="tiket_jenis">Jenis tiket</label>
                                    <select class="form-select" id="tiket_jenis" name="jenis_tiket_id" required>
                                        <option value="">Pilih jenis tiket</option>
                                        <?php foreach ($jenisTiket as $jenis): ?>
                                            <option
                                                value="<?= (int) $jenis['id'] ?>"
                                                <?= (int) ($editTiket['jenis_tiket_id'] ?? 0) === (int) $jenis['id'] ? 'selected' : '' ?>
                                            ><?= e($jenis['nama']) ?> (<?= e(opsi_kategori_tiket()[$jenis['kategori']] ?? $jenis['kategori']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="tiket_harga">Harga (USD)</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="tiket_harga"
                                        name="harga"
                                        min="0"
                                        step="0.01"
                                        value="<?= e($editTiket !== null ? usd_input($editTiket['harga']) : '') ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="tiket_kuota">Kuota</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="tiket_kuota"
                                        name="kuota"
                                        min="0"
                                        value="<?= e((string) ($editTiket['kuota'] ?? '')) ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="tiket_terjual">Terjual</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="tiket_terjual"
                                        name="terjual"
                                        min="0"
                                        value="<?= e((string) ($editTiket['terjual'] ?? 0)) ?>"
                                        required
                                    >
                                    <div class="form-text">Sisa = kuota - terjual.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="<?= e(url_tab('tiket')) ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0 fs-5">Daftar tiket &amp; kuota</h4>
                <div class="d-flex gap-2">
                    <form method="get" action="<?= e(ADMIN_URL) ?>/konser_tiket.php" class="d-flex gap-2">
                        <input type="hidden" name="tab" value="tiket">
                        <select class="form-select form-select-sm" name="konser" onchange="this.form.submit()">
                            <option value="0">Semua konser</option>
                            <?php foreach ($daftarKonser as $konser): ?>
                                <option value="<?= (int) $konser['id'] ?>" <?= $filterKonser === (int) $konser['id'] ? 'selected' : '' ?>>
                                    <?= e(format_tanggal_acara($konser['tanggal_acara'])) ?> &middot; <?= e($konser['nama_tempat']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <a href="<?= e(url_tab('tiket')) ?>&tambah=1<?= $filterKonser > 0 ? '&konser=' . $filterKonser : '' ?>" class="btn btn-primary text-nowrap">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Tiket
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Konser</th>
                            <th>Jenis</th>
                            <th>Harga</th>
                            <th>Kuota</th>
                            <th>Terjual</th>
                            <th>Sisa</th>
                            <th>Terjual (%)</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($daftarTiket === []): ?>
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-4">
                                    Belum ada data tiket untuk ditampilkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftarTiket as $tiket): ?>
                                <?php
                                $kuota = (int) $tiket['kuota'];
                                $terjual = (int) $tiket['terjual'];
                                $sisa = max(0, $kuota - $terjual);
                                $persen = persen_terjual($kuota, $terjual);
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= e($tiket['nama_tempat']) ?></div>
                                        <div class="small text-secondary">
                                            <?= e(format_tanggal_acara($tiket['tanggal_acara'])) ?> &middot; <?= e($tiket['kota']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?= badge_kategori_tiket((string) $tiket['kategori']) ?></div>
                                        <div class="small"><?= e($tiket['jenis_nama']) ?></div>
                                    </td>
                                    <td class="text-nowrap"><?= e(usd($tiket['harga'])) ?></td>
                                    <td><?= number_format($kuota, 0, ',', '.') ?></td>
                                    <td><?= number_format($terjual, 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge text-bg-<?= $sisa > 0 ? 'secondary' : 'danger' ?>">
                                            <?= number_format($sisa, 0, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td style="min-width: 130px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div
                                                    class="progress-bar bg-<?= e(warna_progress($persen)) ?>"
                                                    style="width: <?= e((string) $persen) ?>%"
                                                ></div>
                                            </div>
                                            <span class="small"><?= e((string) $persen) ?>%</span>
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a
                                            href="<?= e(url_tab('tiket')) ?>&edit=<?= (int) $tiket['id'] ?><?= $filterKonser > 0 ? '&konser=' . $filterKonser : '' ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit"
                                        ><i class="bi bi-pencil-square"></i></a>
                                        <form
                                            method="post"
                                            action="<?= e(ADMIN_URL) ?>/konser_tiket.php?tab=tiket"
                                            class="d-inline"
                                            onsubmit="return confirm('Hapus data tiket ini?');"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="aksi" value="hapus_tiket">
                                            <input type="hidden" name="id" value="<?= (int) $tiket['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($tab === 'jenis'): ?>
            <?php if ($tampilFormJenis): ?>
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?= $editJenis !== null ? 'Edit Jenis Tiket' : 'Tambah Jenis Tiket' ?>
                        </h3>
                    </div>
                    <form method="post" action="<?= e(ADMIN_URL) ?>/konser_tiket.php?tab=jenis">
                        <div class="card-body">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="aksi" value="simpan_jenis">
                            <input type="hidden" name="id" value="<?= (int) ($editJenis['id'] ?? 0) ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="jenis_nama">Nama jenis tiket</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="jenis_nama"
                                        name="nama"
                                        maxlength="150"
                                        placeholder="Contoh: VIP Package"
                                        value="<?= e($editJenis['nama'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="jenis_kategori">Kategori</label>
                                    <select class="form-select" id="jenis_kategori" name="kategori">
                                        <?php foreach (opsi_kategori_tiket() as $nilai => $label): ?>
                                            <option
                                                value="<?= e($nilai) ?>"
                                                <?= ($editJenis['kategori'] ?? 'standard') === $nilai ? 'selected' : '' ?>
                                            ><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="jenis_deskripsi">Deskripsi</label>
                                    <textarea
                                        class="form-control"
                                        id="jenis_deskripsi"
                                        name="deskripsi"
                                        rows="3"
                                        placeholder="Akses area umum, dll."
                                    ><?= e($editJenis['deskripsi'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="jenis_fasilitas">Fasilitas</label>
                                    <textarea
                                        class="form-control"
                                        id="jenis_fasilitas"
                                        name="fasilitas"
                                        rows="3"
                                        placeholder="Satu fasilitas per baris"
                                    ><?= e($editJenis['fasilitas'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="<?= e(url_tab('jenis')) ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 fs-5">Jenis tiket</h4>
                <a href="<?= e(url_tab('jenis')) ?>&tambah=1" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Jenis
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Fasilitas</th>
                            <th>Dipakai</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($jenisTiket === []): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    Belum ada jenis tiket.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jenisTiket as $jenis): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($jenis['nama']) ?></td>
                                    <td><?= badge_kategori_tiket((string) $jenis['kategori']) ?></td>
                                    <td class="small"><?= nl2br(e($jenis['deskripsi'] ?? '-')) ?></td>
                                    <td class="small"><?= nl2br(e($jenis['fasilitas'] ?? '-')) ?></td>
                                    <td>
                                        <span class="badge text-bg-secondary">
                                            <?= (int) $jenis['dipakai'] ?> konser
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a
                                            href="<?= e(url_tab('jenis')) ?>&edit=<?= (int) $jenis['id'] ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit"
                                        ><i class="bi bi-pencil-square"></i></a>
                                        <form
                                            method="post"
                                            action="<?= e(ADMIN_URL) ?>/konser_tiket.php?tab=jenis"
                                            class="d-inline"
                                            onsubmit="return confirm('Hapus jenis tiket ini?');"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="aksi" value="hapus_jenis">
                                            <input type="hidden" name="id" value="<?= (int) $jenis['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($tab === 'bagian'): ?>
            <?php if ($tampilFormBagian): ?>
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?= $editBagian !== null ? 'Edit Bagian Tur' : 'Tambah Bagian Tur' ?>
                        </h3>
                    </div>
                    <form method="post" action="<?= e(ADMIN_URL) ?>/konser_tiket.php?tab=bagian">
                        <div class="card-body">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="aksi" value="simpan_bagian">
                            <input type="hidden" name="id" value="<?= (int) ($editBagian['id'] ?? 0) ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="bagian_nama">Nama bagian tur</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="bagian_nama"
                                        name="nama"
                                        maxlength="100"
                                        placeholder="Contoh: Model Tour"
                                        value="<?= e($editBagian['nama'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="bagian_tamu">Tamu khusus</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="bagian_tamu"
                                        name="tamu_khusus"
                                        maxlength="200"
                                        placeholder="Contoh: Benee"
                                        value="<?= e($editBagian['tamu_khusus'] ?? '') ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="<?= e(url_tab('bagian')) ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 fs-5">Bagian tur</h4>
                <a href="<?= e(url_tab('bagian')) ?>&tambah=1" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Bagian
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tamu khusus</th>
                            <th>Jumlah konser</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bagianTur === []): ?>
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">
                                    Belum ada bagian tur.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bagianTur as $bagian): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($bagian['nama']) ?></td>
                                    <td><?= e($bagian['tamu_khusus'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge text-bg-secondary">
                                            <?= (int) $bagian['jumlah_konser'] ?> konser
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a
                                            href="<?= e(url_tab('bagian')) ?>&edit=<?= (int) $bagian['id'] ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit"
                                        ><i class="bi bi-pencil-square"></i></a>
                                        <form
                                            method="post"
                                            action="<?= e(ADMIN_URL) ?>/konser_tiket.php?tab=bagian"
                                            class="d-inline"
                                            onsubmit="return confirm('Hapus bagian tur ini?');"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="aksi" value="hapus_bagian">
                                            <input type="hidden" name="id" value="<?= (int) $bagian['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
