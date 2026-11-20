<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/musik.php';

$pageTitle = 'Lagu';
$activeMenu = 'musik';
$musikTab = 'lagu';
$halamanIni = ADMIN_URL . '/modul/lagu/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect($halamanIni);
    }

    $aksi = (string) ($_POST['aksi'] ?? '');

    try {
        if ($aksi === 'simpan_lagu') {
            $id = (int) ($_POST['id'] ?? 0);
            $albumId = (int) ($_POST['album_id'] ?? 0);
            $judul = trim((string) ($_POST['judul'] ?? ''));
            $artisTamu = trim((string) ($_POST['artis_tamu'] ?? ''));
            $durasiInput = trim((string) ($_POST['durasi_detik'] ?? ''));
            $trekInput = trim((string) ($_POST['peringkat_popularitas'] ?? ''));
            $tahunInput = trim((string) ($_POST['tahun_rilis'] ?? ''));
            $eksplisit = isset($_POST['eksplisit']) ? 1 : 0;

            $masalah = [];

            if ($judul === '') {
                $masalah[] = 'judul lagu';
            } elseif (strlen($judul) > 150) {
                $masalah[] = 'judul maksimal 150 karakter';
            }

            if (strlen($artisTamu) > 100) {
                $masalah[] = 'artis tamu maksimal 100 karakter';
            }

            $durasi = parse_durasi($durasiInput);
            if ($durasi === null) {
                $masalah[] = 'durasi (isi detik atau format menit:detik)';
            } elseif ($durasi < 1 || $durasi > 32767) {
                $masalah[] = 'durasi harus antara 1 dan 32767 detik';
            }

            $trekNilai = null;
            if ($trekInput !== '') {
                if (!ctype_digit($trekInput) || (int) $trekInput < 1 || (int) $trekInput > 127) {
                    $masalah[] = 'nomor trek (1-127)';
                } else {
                    $trekNilai = (int) $trekInput;
                }
            }

            $tahunNilai = null;
            if ($tahunInput !== '') {
                if (!preg_match('/^\d{4}$/', $tahunInput) || (int) $tahunInput < 1901 || (int) $tahunInput > 2155) {
                    $masalah[] = 'tahun rilis';
                } else {
                    $tahunNilai = (int) $tahunInput;
                }
            }

            if ($albumId > 0) {
                $stmt = $koneksi->prepare('SELECT id FROM album WHERE id = ? LIMIT 1');
                $stmt->bind_param('i', $albumId);
                $stmt->execute();
                $albumAda = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($albumAda === null) {
                    $masalah[] = 'album yang dipilih tidak ditemukan';
                }
            }

            if ($trekNilai !== null) {
                $stmt = $koneksi->prepare(
                    'SELECT id FROM lagu WHERE peringkat_popularitas = ? AND id <> ? LIMIT 1'
                );
                $stmt->bind_param('ii', $trekNilai, $id);
                $stmt->execute();
                $trekDipakai = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($trekDipakai !== null) {
                    $masalah[] = 'nomor trek ' . $trekNilai . ' sudah dipakai lagu lain';
                }
            }

            if ($masalah !== []) {
                flash_set('error', 'Periksa kembali: ' . implode(', ', $masalah) . '.');
                redirect($halamanIni);
            }

            $laguLama = $id > 0 ? musik_ambil_satu($koneksi, 'SELECT * FROM lagu WHERE id = ?', $id) : null;
            if ($id > 0 && $laguLama === null) {
                flash_set('error', 'Lagu yang ingin diedit tidak ditemukan.');
                redirect($halamanIni);
            }

            $albumNilai = $albumId > 0 ? $albumId : null;
            $artisNilai = $artisTamu === '' ? null : $artisTamu;

            if ($id > 0) {
                $stmt = $koneksi->prepare(
                    'UPDATE lagu SET album_id = ?, judul = ?, artis_tamu = ?, durasi_detik = ?,
                     peringkat_popularitas = ?, tahun_rilis = ?, eksplisit = ? WHERE id = ?'
                );
                $stmt->bind_param(
                    'issiiiii',
                    $albumNilai,
                    $judul,
                    $artisNilai,
                    $durasi,
                    $trekNilai,
                    $tahunNilai,
                    $eksplisit,
                    $id
                );
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $koneksi->prepare(
                    'INSERT INTO lagu (album_id, judul, artis_tamu, durasi_detik,
                     peringkat_popularitas, tahun_rilis, eksplisit)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'issiiii',
                    $albumNilai,
                    $judul,
                    $artisNilai,
                    $durasi,
                    $trekNilai,
                    $tahunNilai,
                    $eksplisit
                );
                $stmt->execute();
                $stmt->close();
            }

            flash_set('sukses', 'Lagu berhasil disimpan.');
            redirect($halamanIni);
        }

        if ($aksi === 'hapus_lagu') {
            $id = (int) ($_POST['id'] ?? 0);
            $lagu = musik_ambil_satu($koneksi, 'SELECT * FROM lagu WHERE id = ?', $id);

            if ($lagu === null) {
                flash_set('error', 'Lagu yang ingin dihapus tidak ditemukan.');
                redirect($halamanIni);
            }

            $jumlahLirik = hitung_baris($koneksi, 'SELECT COUNT(*) AS jumlah FROM lirik WHERE lagu_id = ?', $id);

            $stmt = $koneksi->prepare('DELETE FROM lagu WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            if ($jumlahLirik > 0) {
                flash_set(
                    'sukses',
                    'Lagu "' . $lagu['judul'] . '" dihapus. Sesuai aturan ON DELETE CASCADE, '
                    . $jumlahLirik . ' lirik yang terhubung ikut terhapus.'
                );
            } else {
                flash_set('sukses', 'Lagu "' . $lagu['judul'] . '" berhasil dihapus.');
            }

            redirect($halamanIni);
        }

        flash_set('error', 'Aksi tidak dikenali.');
    } catch (mysqli_sql_exception $error) {
        error_log('Aksi lagu gagal: ' . $error->getMessage());

        $pesan = 'Gagal memproses data lagu.';
        if ($error->getCode() === 1062) {
            $pesan = 'Nomor trek/peringkat popularitas sudah dipakai lagu lain.';
        } elseif ($error->getCode() === 1452) {
            $pesan = 'Album yang dipilih tidak ditemukan.';
        } elseif ($error->getCode() === 1451) {
            $pesan = 'Lagu masih dipakai data lain sehingga tidak bisa dihapus.';
        }

        flash_set('error', $pesan);
    }

    redirect($halamanIni);
}

$sukses = flash_get('sukses');
$error = flash_get('error');

$editId = (int) ($_GET['edit'] ?? 0);
$formTambah = isset($_GET['tambah']);
$editLagu = $editId > 0 ? musik_ambil_satu($koneksi, 'SELECT * FROM lagu WHERE id = ?', $editId) : null;

$daftarAlbum = [];
$hasilAlbum = safe_query(
    $koneksi,
    'SELECT id, judul, tahun_rilis FROM album ORDER BY judul ASC'
);
if ($hasilAlbum !== null) {
    while ($baris = $hasilAlbum->fetch_assoc()) {
        $daftarAlbum[] = $baris;
    }
    $hasilAlbum->free();
}

$daftarLagu = [];
$hasilLagu = safe_query(
    $koneksi,
    'SELECT l.*, a.judul AS album_judul, a.tahun_rilis AS album_tahun,
            (SELECT COUNT(*) FROM lirik lr WHERE lr.lagu_id = l.id) AS jumlah_lirik,
            (SELECT lr.id FROM lirik lr WHERE lr.lagu_id = l.id LIMIT 1) AS lirik_id
     FROM lagu l
     LEFT JOIN album a ON a.id = l.album_id
     ORDER BY a.judul ASC, l.peringkat_popularitas ASC, l.judul ASC'
);
if ($hasilLagu !== null) {
    while ($baris = $hasilLagu->fetch_assoc()) {
        $daftarLagu[] = $baris;
    }
    $hasilLagu->free();
}

$nilai = [
    'id' => (int) ($editLagu['id'] ?? 0),
    'album_id' => (string) ($editLagu['album_id'] ?? ''),
    'judul' => (string) ($editLagu['judul'] ?? ''),
    'artis_tamu' => (string) ($editLagu['artis_tamu'] ?? ''),
    'durasi' => isset($editLagu['durasi_detik']) ? format_durasi((int) $editLagu['durasi_detik']) : '',
    'peringkat_popularitas' => (string) ($editLagu['peringkat_popularitas'] ?? ''),
    'tahun_rilis' => (string) ($editLagu['tahun_rilis'] ?? ''),
    'eksplisit' => (int) ($editLagu['eksplisit'] ?? 0),
];

$tampilForm = $formTambah || $editLagu !== null;

require __DIR__ . '/../../includes/header.php';
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

<?php render_musik_nav($musikTab); ?>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-music-note-beamed me-1"></i> Daftar Lagu</h3>
        <div class="card-tools">
            <a href="<?= e($halamanIni) ?>?tambah=1" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Lagu
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 70px;">Trek</th>
                    <th>Judul</th>
                    <th>Album</th>
                    <th>Artis tamu</th>
                    <th class="text-center">Durasi</th>
                    <th class="text-center">Tahun</th>
                    <th class="text-center">Lirik</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarLagu === []): ?>
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">
                            Belum ada lagu. Klik <strong>Tambah Lagu</strong> untuk mulai mengisi.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarLagu as $lagu): ?>
                        <?php
                        $jumlahLirik = (int) ($lagu['jumlah_lirik'] ?? 0);
                        $pesanHapus = 'Hapus lagu "' . $lagu['judul'] . '"?';
                        if ($jumlahLirik > 0) {
                            $pesanHapus .= ' Lagu ini memiliki ' . $jumlahLirik
                                . ' lirik. Sesuai aturan ON DELETE CASCADE, lirik tersebut ikut terhapus.';
                        }
                        ?>
                        <tr>
                            <td class="text-center">
                                <?= $lagu['peringkat_popularitas'] !== null ? (int) $lagu['peringkat_popularitas'] : '-' ?>
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    <?= e($lagu['judul']) ?>
                                    <?php if ((int) $lagu['eksplisit'] === 1): ?>
                                        <span class="badge text-bg-secondary" title="Eksplisit">E</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($lagu['album_id'])): ?>
                                    <?= e($lagu['album_judul']) ?>
                                    <?php if (!empty($lagu['album_tahun'])): ?>
                                        <span class="text-secondary small">(<?= (int) $lagu['album_tahun'] ?>)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-secondary fst-italic">Tanpa album / single</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $lagu['artis_tamu'] !== null && $lagu['artis_tamu'] !== '' ? e($lagu['artis_tamu']) : '-' ?></td>
                            <td class="text-center"><?= e(format_durasi((int) $lagu['durasi_detik'])) ?></td>
                            <td class="text-center"><?= !empty($lagu['tahun_rilis']) ? (int) $lagu['tahun_rilis'] : '-' ?></td>
                            <td class="text-center">
                                <?php if ($jumlahLirik > 0): ?>
                                    <a
                                        href="<?= e(url_musik('lirik')) ?>?edit=<?= (int) $lagu['lirik_id'] ?>"
                                        class="badge text-bg-success text-decoration-none"
                                    >
                                        <i class="bi bi-check2 me-1"></i>Tersedia
                                    </a>
                                <?php else: ?>
                                    <a
                                        href="<?= e(url_musik('lirik')) ?>?tambah=1&amp;lagu_id=<?= (int) $lagu['id'] ?>"
                                        class="badge text-bg-light text-decoration-none border"
                                    >
                                        <i class="bi bi-plus-lg me-1"></i>Tambah
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="<?= e($halamanIni) ?>?edit=<?= (int) $lagu['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="post" action="<?= e($halamanIni) ?>" class="d-inline" data-konfirmasi="<?= e($pesanHapus) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="aksi" value="hapus_lagu">
                                    <input type="hidden" name="id" value="<?= (int) $lagu['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($tampilForm): ?>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <?= $nilai['id'] > 0 ? 'Edit Lagu' : 'Tambah Lagu' ?>
            </h3>
        </div>
        <form method="post" action="<?= e($halamanIni) ?>">
            <div class="card-body">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="aksi" value="simpan_lagu">
                <input type="hidden" name="id" value="<?= (int) $nilai['id'] ?>">

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="lagu_judul" class="form-label">Judul lagu <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="lagu_judul"
                            name="judul"
                            maxlength="150"
                            value="<?= e($nilai['judul']) ?>"
                            required
                        >
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="lagu_album" class="form-label">Album</label>
                        <select class="form-select" id="lagu_album" name="album_id">
                            <option value="0">Tanpa album / single</option>
                            <?php foreach ($daftarAlbum as $album): ?>
                                <option
                                    value="<?= (int) $album['id'] ?>"
                                    <?= (int) $nilai['album_id'] === (int) $album['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($album['judul']) ?> (<?= (int) $album['tahun_rilis'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            Belum ada pilihan? <a href="<?= e(url_musik('album')) ?>?tambah=1">Tambah album</a>.
                        </div>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label for="lagu_durasi" class="form-label">Durasi <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="lagu_durasi"
                            name="durasi_detik"
                            value="<?= e($nilai['durasi']) ?>"
                            placeholder="Contoh: 3:34 atau 214"
                            required
                        >
                        <div class="form-text">Isi menit:detik atau jumlah detik.</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="lagu_trek" class="form-label">Nomor trek</label>
                        <input
                            type="number"
                            class="form-control"
                            id="lagu_trek"
                            name="peringkat_popularitas"
                            min="1"
                            max="127"
                            step="1"
                            value="<?= e($nilai['peringkat_popularitas']) ?>"
                        >
                        <div class="form-text">Disimpan di kolom peringkat_popularitas dan harus unik.</div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="lagu_tahun" class="form-label">Tahun rilis</label>
                        <input
                            type="number"
                            class="form-control"
                            id="lagu_tahun"
                            name="tahun_rilis"
                            min="1901"
                            max="2155"
                            step="1"
                            value="<?= e($nilai['tahun_rilis']) ?>"
                        >
                        <div class="form-text">Untuk lagu single.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="lagu_artis" class="form-label">Artis tamu</label>
                        <input
                            type="text"
                            class="form-control"
                            id="lagu_artis"
                            name="artis_tamu"
                            maxlength="100"
                            value="<?= e($nilai['artis_tamu']) ?>"
                        >
                    </div>

                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                value="1"
                                id="lagu_eksplisit"
                                name="eksplisit"
                                <?= $nilai['eksplisit'] === 1 ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="lagu_eksplisit">Lagu eksplisit</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
                <a href="<?= e($halamanIni) ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
