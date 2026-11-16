<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../includes/musik.php';

$pageTitle = 'Album';
$activeMenu = 'musik';
$musikTab = 'album';
$halamanIni = ADMIN_URL . '/modul/album/index.php';
$gambarBaru = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect($halamanIni);
    }

    $aksi = (string) ($_POST['aksi'] ?? '');

    try {
        if ($aksi === 'simpan_album') {
            $id = (int) ($_POST['id'] ?? 0);
            $judul = trim((string) ($_POST['judul'] ?? ''));
            $jenis = (string) ($_POST['jenis'] ?? 'album');
            $tahunRilis = trim((string) ($_POST['tahun_rilis'] ?? ''));
            $tahunAkhir = trim((string) ($_POST['tahun_rilis_akhir'] ?? ''));
            $jumlahLagu = (int) ($_POST['jumlah_lagu'] ?? 0);
            $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
            $spotify = trim((string) ($_POST['tautan_spotify'] ?? ''));
            $hapusGambar = isset($_POST['hapus_gambar']);

            $opsiJenis = opsi_jenis_album();
            $masalah = [];

            if ($judul === '') {
                $masalah[] = 'judul';
            } elseif (strlen($judul) > 150) {
                $masalah[] = 'judul maksimal 150 karakter';
            }

            if (!isset($opsiJenis[$jenis])) {
                $masalah[] = 'jenis album';
            }

            if (!preg_match('/^\d{4}$/', $tahunRilis) || (int) $tahunRilis < 1901 || (int) $tahunRilis > 2155) {
                $masalah[] = 'tahun rilis';
            }

            if ($tahunAkhir !== '') {
                if (!preg_match('/^\d{4}$/', $tahunAkhir) || (int) $tahunAkhir < 1901 || (int) $tahunAkhir > 2155) {
                    $masalah[] = 'tahun rilis akhir';
                } elseif ((int) $tahunAkhir < (int) $tahunRilis) {
                    $masalah[] = 'tahun rilis akhir tidak boleh lebih awal dari tahun rilis';
                }
            }

            if ($jumlahLagu < 0 || $jumlahLagu > 127) {
                $masalah[] = 'jumlah lagu (0-127)';
            }

            if (strlen($deskripsi) > 150) {
                $masalah[] = 'deskripsi maksimal 150 karakter';
            }

            if (strlen($spotify) > 255) {
                $masalah[] = 'tautan Spotify maksimal 255 karakter';
            }

            if ($masalah !== []) {
                flash_set('error', 'Periksa kembali: ' . implode(', ', $masalah) . '.');
                redirect($halamanIni);
            }

            $albumLama = $id > 0 ? musik_ambil_satu($koneksi, 'SELECT * FROM album WHERE id = ?', $id) : null;
            if ($id > 0 && $albumLama === null) {
                flash_set('error', 'Album yang ingin diedit tidak ditemukan.');
                redirect($halamanIni);
            }

            $gambarLama = $albumLama['gambar_sampul'] ?? null;
            $gambar = $gambarLama;
            $gambarBaru = null;

            if (isset($_FILES['gambar_sampul'])) {
                $unggah = proses_upload_gambar($_FILES['gambar_sampul'], 'album');
                if ($unggah['error'] !== null) {
                    flash_set('error', $unggah['error']);
                    redirect($halamanIni);
                }

                if ($unggah['path'] !== null) {
                    $gambarBaru = $unggah['path'];
                    $gambar = $gambarBaru;
                }
            }

            if ($gambarBaru === null && $hapusGambar) {
                $gambar = null;
            }

            if ($jenis !== 'single_collection') {
                $tahunAkhir = '';
            }

            $tahunAkhirNilai = $tahunAkhir === '' ? null : (int) $tahunAkhir;
            $deskripsiNilai = $deskripsi === '' ? null : $deskripsi;
            $spotifyNilai = $spotify === '' ? null : $spotify;
            $jumlahLaguNilai = $jumlahLagu;

            if ($id > 0) {
                $stmt = $koneksi->prepare(
                    'UPDATE album SET judul = ?, jenis = ?, tahun_rilis = ?, tahun_rilis_akhir = ?,
                     jumlah_lagu = ?, deskripsi = ?, gambar_sampul = ?, tautan_spotify = ? WHERE id = ?'
                );
                $stmt->bind_param(
                    'ssiiisssi',
                    $judul,
                    $jenis,
                    $tahunRilis,
                    $tahunAkhirNilai,
                    $jumlahLaguNilai,
                    $deskripsiNilai,
                    $gambar,
                    $spotifyNilai,
                    $id
                );
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $koneksi->prepare(
                    'INSERT INTO album (judul, jenis, tahun_rilis, tahun_rilis_akhir, jumlah_lagu,
                     deskripsi, gambar_sampul, tautan_spotify)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'ssiiisss',
                    $judul,
                    $jenis,
                    $tahunRilis,
                    $tahunAkhirNilai,
                    $jumlahLaguNilai,
                    $deskripsiNilai,
                    $gambar,
                    $spotifyNilai
                );
                $stmt->execute();
                $stmt->close();
            }

            if ($gambarLama !== null && $gambarLama !== $gambar) {
                hapus_gambar_upload($gambarLama);
            }

            flash_set('sukses', 'Album berhasil disimpan.');
            redirect($halamanIni);
        }

        if ($aksi === 'hapus_album') {
            $id = (int) ($_POST['id'] ?? 0);
            $album = musik_ambil_satu($koneksi, 'SELECT * FROM album WHERE id = ?', $id);

            if ($album === null) {
                flash_set('error', 'Album yang ingin dihapus tidak ditemukan.');
                redirect($halamanIni);
            }

            $jumlahLagu = hitung_baris($koneksi, 'SELECT COUNT(*) AS jumlah FROM lagu WHERE album_id = ?', $id);

            $stmt = $koneksi->prepare('DELETE FROM album WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            hapus_gambar_upload($album['gambar_sampul'] ?? null);

            if ($jumlahLagu > 0) {
                flash_set(
                    'sukses',
                    'Album "' . $album['judul'] . '" dihapus. Sesuai aturan ON DELETE SET NULL, '
                    . $jumlahLagu . ' lagu tetap tersimpan tanpa album.'
                );
            } else {
                flash_set('sukses', 'Album "' . $album['judul'] . '" berhasil dihapus.');
            }

            redirect($halamanIni);
        }

        flash_set('error', 'Aksi tidak dikenali.');
    } catch (mysqli_sql_exception $error) {
        error_log('Aksi album gagal: ' . $error->getMessage());

        if ($gambarBaru !== null) {
            hapus_gambar_upload($gambarBaru);
        }

        $pesan = 'Gagal memproses data album.';
        if ($error->getCode() === 1062) {
            $pesan = 'Data album sudah ada (duplikat).';
        } elseif ($error->getCode() === 1451) {
            $pesan = 'Album masih dipakai data lain sehingga tidak bisa dihapus.';
        }

        flash_set('error', $pesan);
    }

    redirect($halamanIni);
}

$sukses = flash_get('sukses');
$error = flash_get('error');

$editId = (int) ($_GET['edit'] ?? 0);
$formTambah = isset($_GET['tambah']);
$editAlbum = $editId > 0 ? musik_ambil_satu($koneksi, 'SELECT * FROM album WHERE id = ?', $editId) : null;

$daftarAlbum = [];
$hasilAlbum = safe_query(
    $koneksi,
    'SELECT a.*,
            (SELECT COUNT(*) FROM lagu l WHERE l.album_id = a.id) AS jumlah_tercatat
     FROM album a
     ORDER BY a.tahun_rilis DESC, a.judul ASC'
);
if ($hasilAlbum !== null) {
    while ($baris = $hasilAlbum->fetch_assoc()) {
        $daftarAlbum[] = $baris;
    }
    $hasilAlbum->free();
}

$nilai = [
    'id' => (int) ($editAlbum['id'] ?? 0),
    'judul' => (string) ($editAlbum['judul'] ?? ''),
    'jenis' => (string) ($editAlbum['jenis'] ?? 'album'),
    'tahun_rilis' => (string) ($editAlbum['tahun_rilis'] ?? date('Y')),
    'tahun_rilis_akhir' => (string) ($editAlbum['tahun_rilis_akhir'] ?? ''),
    'jumlah_lagu' => (string) ($editAlbum['jumlah_lagu'] ?? '0'),
    'deskripsi' => (string) ($editAlbum['deskripsi'] ?? ''),
    'tautan_spotify' => (string) ($editAlbum['tautan_spotify'] ?? ''),
    'gambar_sampul' => (string) ($editAlbum['gambar_sampul'] ?? ''),
];

$tampilForm = $formTambah || $editAlbum !== null;

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
        <h3 class="card-title"><i class="bi bi-disc me-1"></i> Daftar Album</h3>
        <div class="card-tools">
            <a href="<?= e($halamanIni) ?>?tambah=1" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Album
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Sampul</th>
                    <th>Judul</th>
                    <th>Jenis</th>
                    <th>Tahun</th>
                    <th class="text-center">Jumlah lagu</th>
                    <th class="text-center">Lagu tercatat</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarAlbum === []): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">
                            Belum ada album. Klik <strong>Tambah Album</strong> untuk mulai mengisi.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarAlbum as $album): ?>
                        <?php
                        $jumlahTercatat = (int) ($album['jumlah_tercatat'] ?? 0);
                        $pesanHapus = 'Hapus album "' . $album['judul'] . '"?';
                        if ($jumlahTercatat > 0) {
                            $pesanHapus .= ' Album ini masih memiliki ' . $jumlahTercatat
                                . ' lagu. Sesuai aturan ON DELETE SET NULL, lagu tersebut tetap ada tanpa album.';
                        }
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($album['gambar_sampul'])): ?>
                                    <img
                                        src="<?= e(url_gambar($album['gambar_sampul'])) ?>"
                                        alt="Sampul <?= e($album['judul']) ?>"
                                        class="img-thumbnail"
                                        style="width: 52px; height: 52px; object-fit: cover;"
                                    >
                                <?php else: ?>
                                    <span class="d-inline-flex align-items-center justify-content-center bg-secondary-subtle text-secondary rounded" style="width: 52px; height: 52px;">
                                        <i class="bi bi-disc fs-5"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($album['judul']) ?></div>
                                <?php if (!empty($album['deskripsi'])): ?>
                                    <div class="small text-secondary"><?= e($album['deskripsi']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($album['tautan_spotify'])): ?>
                                    <a href="<?= e($album['tautan_spotify']) ?>" class="small" target="_blank" rel="noopener">
                                        <i class="bi bi-spotify me-1"></i>Spotify
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?= badge_jenis_album((string) $album['jenis']) ?></td>
                            <td>
                                <?= (int) $album['tahun_rilis'] ?>
                                <?php if (($album['jenis'] ?? '') === 'single_collection' && !empty($album['tahun_rilis_akhir'])): ?>
                                    &ndash; <?= (int) $album['tahun_rilis_akhir'] ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= (int) $album['jumlah_lagu'] ?></td>
                            <td class="text-center"><?= $jumlahTercatat ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= e($halamanIni) ?>?edit=<?= (int) $album['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="post" action="<?= e($halamanIni) ?>" class="d-inline" data-konfirmasi="<?= e($pesanHapus) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="aksi" value="hapus_album">
                                    <input type="hidden" name="id" value="<?= (int) $album['id'] ?>">
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
                <?= $nilai['id'] > 0 ? 'Edit Album' : 'Tambah Album' ?>
            </h3>
        </div>
        <form method="post" action="<?= e($halamanIni) ?>" enctype="multipart/form-data">
            <div class="card-body">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="aksi" value="simpan_album">
                <input type="hidden" name="id" value="<?= (int) $nilai['id'] ?>">

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="album_judul" class="form-label">Judul album <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="album_judul"
                            name="judul"
                            maxlength="150"
                            value="<?= e($nilai['judul']) ?>"
                            required
                        >
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="album_jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select" id="album_jenis" name="jenis" required>
                            <?php foreach (opsi_jenis_album() as $kunci => $label): ?>
                                <option value="<?= e($kunci) ?>" <?= $nilai['jenis'] === $kunci ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="album_tahun" class="form-label">Tahun rilis <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control"
                            id="album_tahun"
                            name="tahun_rilis"
                            min="1901"
                            max="2155"
                            step="1"
                            value="<?= e($nilai['tahun_rilis']) ?>"
                            required
                        >
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="album_tahun_akhir" class="form-label">Tahun rilis akhir</label>
                        <input
                            type="number"
                            class="form-control"
                            id="album_tahun_akhir"
                            name="tahun_rilis_akhir"
                            min="1901"
                            max="2155"
                            step="1"
                            value="<?= e($nilai['tahun_rilis_akhir']) ?>"
                        >
                        <div class="form-text">Hanya untuk jenis kumpulan single. Boleh dikosongkan.</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="album_jumlah" class="form-label">Jumlah lagu <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control"
                            id="album_jumlah"
                            name="jumlah_lagu"
                            min="0"
                            max="127"
                            step="1"
                            value="<?= e($nilai['jumlah_lagu']) ?>"
                            required
                        >
                        <div class="form-text">Kolom jumlah_lagu pada tabel album.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="album_deskripsi" class="form-label">Deskripsi</label>
                        <input
                            type="text"
                            class="form-control"
                            id="album_deskripsi"
                            name="deskripsi"
                            maxlength="150"
                            value="<?= e($nilai['deskripsi']) ?>"
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="album_spotify" class="form-label">Tautan Spotify</label>
                        <input
                            type="url"
                            class="form-control"
                            id="album_spotify"
                            name="tautan_spotify"
                            maxlength="255"
                            value="<?= e($nilai['tautan_spotify']) ?>"
                            placeholder="https://open.spotify.com/album/..."
                        >
                    </div>

                    <div class="col-md-7 mb-3">
                        <label for="album_sampul" class="form-label">Gambar sampul</label>
                        <input
                            type="file"
                            class="form-control"
                            id="album_sampul"
                            name="gambar_sampul"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >
                        <div class="form-text">
                            JPG, PNG, atau WebP. Maksimal 2 MB. Nama berkas dibuat otomatis.
                        </div>
                    </div>

                    <div class="col-md-5 mb-3">
                        <div class="form-label">Sampul saat ini</div>
                        <?php if ($nilai['gambar_sampul'] !== ''): ?>
                            <div class="d-flex align-items-center gap-3">
                                <img
                                    src="<?= e(url_gambar($nilai['gambar_sampul'])) ?>"
                                    alt="Sampul album"
                                    class="img-thumbnail"
                                    style="width: 72px; height: 72px; object-fit: cover;"
                                >
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="hapus_sampul" name="hapus_gambar">
                                    <label class="form-check-label" for="hapus_sampul">Hapus sampul ini</label>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-secondary mb-0">Belum ada gambar sampul.</p>
                        <?php endif; ?>
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
