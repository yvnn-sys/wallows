<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/musik.php';

$pageTitle = 'Lirik';
$activeMenu = 'musik';
$musikTab = 'lirik';
$halamanIni = ADMIN_URL . '/modul/lirik/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect($halamanIni);
    }

    $aksi = (string) ($_POST['aksi'] ?? '');

    try {
        if ($aksi === 'simpan_lirik') {
            $id = (int) ($_POST['id'] ?? 0);
            $laguId = (int) ($_POST['lagu_id'] ?? 0);
            $namaBagian = trim((string) ($_POST['nama_bagian'] ?? ''));
            $isiLengkap = trim((string) ($_POST['isi_lengkap'] ?? ''));
            $tautanGenius = trim((string) ($_POST['tautan_genius'] ?? ''));

            $masalah = [];

            if ($laguId <= 0) {
                $masalah[] = 'lagu';
            } else {
                $stmt = $koneksi->prepare('SELECT id FROM lagu WHERE id = ? LIMIT 1');
                $stmt->bind_param('i', $laguId);
                $stmt->execute();
                $laguAda = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($laguAda === null) {
                    $masalah[] = 'lagu yang dipilih tidak ditemukan';
                }
            }

            if ($namaBagian === '') {
                $masalah[] = 'nama bagian';
            } elseif (strlen($namaBagian) > 50) {
                $masalah[] = 'nama bagian maksimal 50 karakter';
            }

            if ($isiLengkap === '') {
                $masalah[] = 'isi lirik';
            }

            if ($tautanGenius !== '') {
                if (strlen($tautanGenius) > 255) {
                    $masalah[] = 'tautan Genius maksimal 255 karakter';
                } elseif (filter_var($tautanGenius, FILTER_VALIDATE_URL) === false) {
                    $masalah[] = 'tautan Genius harus berupa URL yang valid';
                }
            }

            if ($laguId > 0) {
                $stmt = $koneksi->prepare('SELECT id FROM lirik WHERE lagu_id = ? AND id <> ? LIMIT 1');
                $stmt->bind_param('ii', $laguId, $id);
                $stmt->execute();
                $lirikDipakai = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($lirikDipakai !== null) {
                    $masalah[] = 'lagu tersebut sudah memiliki lirik (satu lagu satu lirik)';
                }
            }

            if ($masalah !== []) {
                flash_set('error', 'Periksa kembali: ' . implode(', ', $masalah) . '.');
                redirect($halamanIni);
            }

            $lirikLama = $id > 0 ? musik_ambil_satu($koneksi, 'SELECT * FROM lirik WHERE id = ?', $id) : null;
            if ($id > 0 && $lirikLama === null) {
                flash_set('error', 'Lirik yang ingin diedit tidak ditemukan.');
                redirect($halamanIni);
            }

            $tautanNilai = $tautanGenius === '' ? null : $tautanGenius;

            if ($id > 0) {
                $stmt = $koneksi->prepare(
                    'UPDATE lirik SET lagu_id = ?, nama_bagian = ?, isi_lengkap = ?, tautan_genius = ? WHERE id = ?'
                );
                $stmt->bind_param('isssi', $laguId, $namaBagian, $isiLengkap, $tautanNilai, $id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $koneksi->prepare(
                    'INSERT INTO lirik (lagu_id, nama_bagian, isi_lengkap, tautan_genius) VALUES (?, ?, ?, ?)'
                );
                $stmt->bind_param('isss', $laguId, $namaBagian, $isiLengkap, $tautanNilai);
                $stmt->execute();
                $stmt->close();
            }

            flash_set('sukses', 'Lirik berhasil disimpan.');
            redirect($halamanIni);
        }

        if ($aksi === 'hapus_lirik') {
            $id = (int) ($_POST['id'] ?? 0);
            $lirik = musik_ambil_satu(
                $koneksi,
                'SELECT lr.*, l.judul AS lagu_judul FROM lirik lr JOIN lagu l ON l.id = lr.lagu_id WHERE lr.id = ?',
                $id
            );

            if ($lirik === null) {
                flash_set('error', 'Lirik yang ingin dihapus tidak ditemukan.');
                redirect($halamanIni);
            }

            $stmt = $koneksi->prepare('DELETE FROM lirik WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            flash_set('sukses', 'Lirik untuk lagu "' . $lirik['lagu_judul'] . '" berhasil dihapus.');
            redirect($halamanIni);
        }

        flash_set('error', 'Aksi tidak dikenali.');
    } catch (mysqli_sql_exception $error) {
        error_log('Aksi lirik gagal: ' . $error->getMessage());

        $pesan = 'Gagal memproses data lirik.';
        if ($error->getCode() === 1062) {
            $pesan = 'Lagu tersebut sudah memiliki lirik. Satu lagu hanya boleh memiliki satu lirik.';
        } elseif ($error->getCode() === 1452) {
            $pesan = 'Lagu yang dipilih tidak ditemukan.';
        }

        flash_set('error', $pesan);
    }

    redirect($halamanIni);
}

$sukses = flash_get('sukses');
$error = flash_get('error');

$editId = (int) ($_GET['edit'] ?? 0);
$formTambah = isset($_GET['tambah']);
$laguPreselect = (int) ($_GET['lagu_id'] ?? 0);
$editLirik = $editId > 0
    ? musik_ambil_satu(
        $koneksi,
        'SELECT lr.*, l.judul AS lagu_judul FROM lirik lr JOIN lagu l ON l.id = lr.lagu_id WHERE lr.id = ?',
        $editId
    )
    : null;

$daftarLagu = [];
$hasilLagu = safe_query(
    $koneksi,
    'SELECT l.id, l.judul, a.judul AS album_judul, lr.id AS lirik_id
     FROM lagu l
     LEFT JOIN album a ON a.id = l.album_id
     LEFT JOIN lirik lr ON lr.lagu_id = l.id
     ORDER BY l.judul ASC'
);
if ($hasilLagu !== null) {
    while ($baris = $hasilLagu->fetch_assoc()) {
        $daftarLagu[] = $baris;
    }
    $hasilLagu->free();
}

$daftarLirik = [];
$hasilLirik = safe_query(
    $koneksi,
    'SELECT lr.*, l.judul AS lagu_judul, a.judul AS album_judul
     FROM lirik lr
     JOIN lagu l ON l.id = lr.lagu_id
     LEFT JOIN album a ON a.id = l.album_id
     ORDER BY l.judul ASC'
);
if ($hasilLirik !== null) {
    while ($baris = $hasilLirik->fetch_assoc()) {
        $daftarLirik[] = $baris;
    }
    $hasilLirik->free();
}

$nilai = [
    'id' => (int) ($editLirik['id'] ?? 0),
    'lagu_id' => (string) ($editLirik['lagu_id'] ?? ($laguPreselect > 0 ? $laguPreselect : '')),
    'nama_bagian' => (string) ($editLirik['nama_bagian'] ?? 'Verse 1'),
    'isi_lengkap' => (string) ($editLirik['isi_lengkap'] ?? ''),
    'tautan_genius' => (string) ($editLirik['tautan_genius'] ?? ''),
];

$tampilForm = $formTambah || $editLirik !== null;

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
        <h3 class="card-title"><i class="bi bi-file-text me-1"></i> Daftar Lirik</h3>
        <div class="card-tools">
            <a href="<?= e($halamanIni) ?>?tambah=1" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Lirik
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Lagu</th>
                    <th>Album</th>
                    <th>Bagian</th>
                    <th style="min-width: 280px;">Isi lirik</th>
                    <th>Tautan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarLirik === []): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">
                            Belum ada lirik. Klik <strong>Tambah Lirik</strong> untuk mulai mengisi.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarLirik as $lirik): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($lirik['lagu_judul']) ?></div>
                            </td>
                            <td>
                                <?= !empty($lirik['album_judul']) ? e($lirik['album_judul']) : '<span class="text-secondary fst-italic">Tanpa album</span>' ?>
                            </td>
                            <td><?= e($lirik['nama_bagian']) ?></td>
                            <td>
                                <details>
                                    <summary class="text-primary" style="cursor: pointer;">Lihat lirik</summary>
                                    <div class="mt-2 small" style="max-height: 220px; overflow-y: auto;">
                                        <?= nl2br(e($lirik['isi_lengkap'])) ?>
                                    </div>
                                </details>
                            </td>
                            <td>
                                <?php if (!empty($lirik['tautan_genius'])): ?>
                                    <a href="<?= e($lirik['tautan_genius']) ?>" target="_blank" rel="noopener" class="small">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Genius
                                    </a>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="<?= e($halamanIni) ?>?edit=<?= (int) $lirik['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form
                                    method="post"
                                    action="<?= e($halamanIni) ?>"
                                    class="d-inline"
                                    data-konfirmasi="Hapus lirik untuk lagu &quot;<?= e($lirik['lagu_judul']) ?>&quot;?"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="aksi" value="hapus_lirik">
                                    <input type="hidden" name="id" value="<?= (int) $lirik['id'] ?>">
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
                <?= $nilai['id'] > 0 ? 'Edit Lirik' : 'Tambah Lirik' ?>
            </h3>
        </div>
        <form method="post" action="<?= e($halamanIni) ?>">
            <div class="card-body">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="aksi" value="simpan_lirik">
                <input type="hidden" name="id" value="<?= (int) $nilai['id'] ?>">

                <?php if ($daftarLagu === []): ?>
                    <div class="alert alert-warning mb-0">
                        Belum ada data lagu. Tambahkan lagu lebih dulu di
                        <a href="<?= e(url_musik('lagu')) ?>?tambah=1">menu Lagu</a>.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="lirik_lagu" class="form-label">Lagu <span class="text-danger">*</span></label>
                            <select class="form-select" id="lirik_lagu" name="lagu_id" required>
                                <option value="">Pilih lagu</option>
                                <?php foreach ($daftarLagu as $lagu): ?>
                                    <?php
                                    $dipakaiLain = !empty($lagu['lirik_id']) && (int) $lagu['lirik_id'] !== (int) $nilai['id'];
                                    ?>
                                    <option
                                        value="<?= (int) $lagu['id'] ?>"
                                        <?= (int) $nilai['lagu_id'] === (int) $lagu['id'] ? 'selected' : '' ?>
                                        <?= $dipakaiLain ? 'disabled' : '' ?>
                                    >
                                        <?= e($lagu['judul']) ?><?= !empty($lagu['album_judul']) ? ' — ' . e($lagu['album_judul']) : '' ?><?= $dipakaiLain ? ' (sudah ada lirik)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Satu lagu hanya boleh memiliki satu lirik sesuai UNIQUE lagu_id.</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="lirik_bagian" class="form-label">Nama bagian <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="lirik_bagian"
                                name="nama_bagian"
                                maxlength="50"
                                value="<?= e($nilai['nama_bagian']) ?>"
                                required
                            >
                            <div class="form-text">Contoh: Verse 1, Chorus, Bridge.</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="lirik_isi" class="form-label">Isi lirik <span class="text-danger">*</span></label>
                            <textarea
                                class="form-control font-monospace"
                                id="lirik_isi"
                                name="isi_lengkap"
                                rows="14"
                                required
                                placeholder="Tulis lirik lengkap di sini..."
                            ><?= e($nilai['isi_lengkap']) ?></textarea>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="lirik_genius" class="form-label">Tautan Genius</label>
                            <input
                                type="url"
                                class="form-control"
                                id="lirik_genius"
                                name="tautan_genius"
                                maxlength="255"
                                value="<?= e($nilai['tautan_genius']) ?>"
                                placeholder="https://genius.com/..."
                            >
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer d-flex gap-2">
                <?php if ($daftarLagu !== []): ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                <?php endif; ?>
                <a href="<?= e($halamanIni) ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
