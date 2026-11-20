<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';

$pageTitle = 'Berita';
$activeMenu = 'berita';
$halamanIni = ADMIN_URL . '/modul/berita/index.php';
$gambarBaru = null;

function opsi_status_berita(): array
{
    return [
        'draft' => 'Draft',
        'published' => 'Published',
    ];
}

function badge_status_berita(string $status): string
{
    $peta = [
        'draft' => ['Draft', 'secondary'],
        'published' => ['Published', 'success'],
    ];

    [$label, $warna] = $peta[$status] ?? [$status, 'secondary'];

    return '<span class="badge text-bg-' . $warna . '">' . e($label) . '</span>';
}

function buat_slug_berita(string $judul): string
{
    $teks = trim($judul);

    if (function_exists('iconv')) {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $teks);
        if ($ascii !== false) {
            $teks = $ascii;
        }
    }

    $teks = strtolower($teks);
    $teks = preg_replace('/[^a-z0-9]+/', '-', $teks) ?? '';
    $teks = trim($teks, '-');

    if ($teks === '') {
        $teks = 'berita';
    }

    return substr($teks, 0, 200);
}

function slug_berita_unik(mysqli $koneksi, string $judul, int $id = 0): string
{
    $dasar = buat_slug_berita($judul);
    $kandidat = $dasar;
    $nomor = 2;

    while (true) {
        $stmt = $koneksi->prepare('SELECT id FROM berita WHERE alamat_halaman = ? AND id <> ? LIMIT 1');
        $stmt->bind_param('si', $kandidat, $id);
        $stmt->execute();
        $sudahAda = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($sudahAda === null) {
            return $kandidat;
        }

        $akhiran = '-' . $nomor;
        $kandidat = substr($dasar, 0, 200 - strlen($akhiran)) . $akhiran;
        $nomor++;
    }
}

function normalisasi_tanggal_terbit(string $nilai): ?string
{
    $nilai = trim($nilai);
    if ($nilai === '') {
        return null;
    }

    $nilai = str_replace('T', ' ', $nilai);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $nilai)) {
        $nilai .= ':00';
    }

    $tanggal = DateTime::createFromFormat('Y-m-d H:i:s', $nilai);
    $kesalahan = DateTime::getLastErrors();

    if (
        $tanggal === false
        || ($kesalahan !== false && ($kesalahan['warning_count'] > 0 || $kesalahan['error_count'] > 0))
    ) {
        return null;
    }

    return $tanggal->format('Y-m-d H:i:s');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect($halamanIni);
    }

    $aksi = (string) ($_POST['aksi'] ?? '');

    try {
        if ($aksi === 'simpan_berita') {
            $id = (int) ($_POST['id'] ?? 0);
            $judul = trim((string) ($_POST['judul'] ?? ''));
            $isi = trim((string) ($_POST['isi'] ?? ''));
            $status = (string) ($_POST['status'] ?? 'draft');
            $tanggalInput = trim((string) ($_POST['tanggal_terbit'] ?? ''));
            $teksTombol = trim((string) ($_POST['teks_tombol'] ?? ''));
            $tautanTombol = trim((string) ($_POST['tautan_tombol'] ?? ''));
            $hapusGambar = isset($_POST['hapus_gambar']);

            $opsiStatus = opsi_status_berita();
            $masalah = [];

            if ($judul === '') {
                $masalah[] = 'judul';
            } elseif (strlen($judul) > 200) {
                $masalah[] = 'judul maksimal 200 karakter';
            }

            if ($isi === '') {
                $masalah[] = 'isi berita';
            }

            if (!isset($opsiStatus[$status])) {
                $masalah[] = 'status';
            }

            $tanggalNilai = normalisasi_tanggal_terbit($tanggalInput);
            if ($tanggalNilai === null) {
                $masalah[] = 'tanggal terbit';
            }

            if (strlen($teksTombol) > 50) {
                $masalah[] = 'teks tombol maksimal 50 karakter';
            }

            if (strlen($tautanTombol) > 255) {
                $masalah[] = 'tautan tombol maksimal 255 karakter';
            }

            if ($masalah !== []) {
                flash_set('error', 'Periksa kembali: ' . implode(', ', $masalah) . '.');
                redirect($halamanIni);
            }

            $beritaLama = $id > 0
                ? musik_berita_ambil_satu($koneksi, 'SELECT * FROM berita WHERE id = ?', $id)
                : null;

            if ($id > 0 && $beritaLama === null) {
                flash_set('error', 'Berita yang ingin diedit tidak ditemukan.');
                redirect($halamanIni);
            }

            $gambarLama = $beritaLama['tautan_gambar'] ?? null;
            $gambar = $gambarLama;
            $gambarBaru = null;

            if (isset($_FILES['tautan_gambar'])) {
                $unggah = proses_upload_gambar($_FILES['tautan_gambar'], 'berita');
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

            $slug = slug_berita_unik($koneksi, $judul, $id);
            $adminId = (int) ($_SESSION['admin_id'] ?? 0);
            if ($id > 0 && $beritaLama !== null && $beritaLama['admin_id'] !== null) {
                $adminNilai = (int) $beritaLama['admin_id'];
            } else {
                $adminNilai = $adminId > 0 ? $adminId : null;
            }
            $teksTombolNilai = $teksTombol === '' ? null : $teksTombol;
            $tautanTombolNilai = $tautanTombol === '' ? null : $tautanTombol;

            if ($id > 0) {
                $stmt = $koneksi->prepare(
                    'UPDATE berita SET admin_id = ?, judul = ?, alamat_halaman = ?, isi = ?,
                     tautan_gambar = ?, teks_tombol = ?, tautan_tombol = ?, status = ?, tanggal_terbit = ?
                     WHERE id = ?'
                );
                $stmt->bind_param(
                    'issssssssi',
                    $adminNilai,
                    $judul,
                    $slug,
                    $isi,
                    $gambar,
                    $teksTombolNilai,
                    $tautanTombolNilai,
                    $status,
                    $tanggalNilai,
                    $id
                );
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $koneksi->prepare(
                    'INSERT INTO berita (admin_id, judul, alamat_halaman, isi, tautan_gambar,
                     teks_tombol, tautan_tombol, status, tanggal_terbit)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'issssssss',
                    $adminNilai,
                    $judul,
                    $slug,
                    $isi,
                    $gambar,
                    $teksTombolNilai,
                    $tautanTombolNilai,
                    $status,
                    $tanggalNilai
                );
                $stmt->execute();
                $stmt->close();
            }

            if ($gambarLama !== null && $gambarLama !== $gambar) {
                hapus_gambar_upload($gambarLama);
            }

            flash_set('sukses', 'Berita berhasil disimpan. Alamat halaman: ' . $slug . '.');
            redirect($halamanIni);
        }

        if ($aksi === 'hapus_berita') {
            $id = (int) ($_POST['id'] ?? 0);
            $berita = musik_berita_ambil_satu($koneksi, 'SELECT * FROM berita WHERE id = ?', $id);

            if ($berita === null) {
                flash_set('error', 'Berita yang ingin dihapus tidak ditemukan.');
                redirect($halamanIni);
            }

            $stmt = $koneksi->prepare('DELETE FROM berita WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            hapus_gambar_upload($berita['tautan_gambar'] ?? null);

            flash_set('sukses', 'Berita "' . $berita['judul'] . '" berhasil dihapus.');
            redirect($halamanIni);
        }

        flash_set('error', 'Aksi tidak dikenali.');
    } catch (mysqli_sql_exception $error) {
        error_log('Aksi berita gagal: ' . $error->getMessage());

        if ($gambarBaru !== null) {
            hapus_gambar_upload($gambarBaru);
        }

        $pesan = 'Gagal memproses data berita.';
        if ($error->getCode() === 1062) {
            $pesan = 'Alamat halaman (slug) sudah dipakai berita lain. Silakan ubah judul.';
        } elseif ($error->getCode() === 1452) {
            $pesan = 'Penulis berita tidak ditemukan. Silakan masuk ulang.';
        }

        flash_set('error', $pesan);
    }

    redirect($halamanIni);
}

/**
 * Helper lokal agar tidak bergantung pada modul Musik.
 */
function musik_berita_ambil_satu(mysqli $koneksi, string $sql, int $id): ?array
{
    try {
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $baris = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (mysqli_sql_exception $error) {
        error_log('Query detail berita gagal: ' . $error->getMessage());

        return null;
    }

    return $baris ?: null;
}

$sukses = flash_get('sukses');
$error = flash_get('error');

$editId = (int) ($_GET['edit'] ?? 0);
$formTambah = isset($_GET['tambah']);
$editBerita = $editId > 0
    ? musik_berita_ambil_satu($koneksi, 'SELECT * FROM berita WHERE id = ?', $editId)
    : null;

$daftarBerita = [];
$hasilBerita = safe_query(
    $koneksi,
    'SELECT b.*, a.nama AS penulis
     FROM berita b
     LEFT JOIN admin a ON a.id = b.admin_id
     ORDER BY b.tanggal_terbit DESC, b.id DESC'
);
if ($hasilBerita !== null) {
    while ($baris = $hasilBerita->fetch_assoc()) {
        $daftarBerita[] = $baris;
    }
    $hasilBerita->free();
}

$nilai = [
    'id' => (int) ($editBerita['id'] ?? 0),
    'judul' => (string) ($editBerita['judul'] ?? ''),
    'isi' => (string) ($editBerita['isi'] ?? ''),
    'status' => (string) ($editBerita['status'] ?? 'draft'),
    'tanggal_terbit' => !empty($editBerita['tanggal_terbit'])
        ? date('Y-m-d\TH:i', strtotime((string) $editBerita['tanggal_terbit']))
        : date('Y-m-d\TH:i'),
    'teks_tombol' => (string) ($editBerita['teks_tombol'] ?? ''),
    'tautan_tombol' => (string) ($editBerita['tautan_tombol'] ?? ''),
    'tautan_gambar' => (string) ($editBerita['tautan_gambar'] ?? ''),
    'alamat_halaman' => (string) ($editBerita['alamat_halaman'] ?? ''),
];

$tampilForm = $formTambah || $editBerita !== null;

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

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-newspaper me-1"></i> Daftar Berita</h3>
        <div class="card-tools">
            <a href="<?= e($halamanIni) ?>?tambah=1" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Berita
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;">Gambar</th>
                    <th>Judul</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Tanggal terbit</th>
                    <th>Penulis</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarBerita === []): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">
                            Belum ada berita. Klik <strong>Tambah Berita</strong> untuk mulai mengisi.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarBerita as $berita): ?>
                        <tr>
                            <td>
                                <?php if (!empty($berita['tautan_gambar'])): ?>
                                    <img
                                        src="<?= e(url_gambar($berita['tautan_gambar'])) ?>"
                                        alt="Gambar <?= e($berita['judul']) ?>"
                                        class="img-thumbnail"
                                        style="width: 64px; height: 48px; object-fit: cover;"
                                    >
                                <?php else: ?>
                                    <span class="d-inline-flex align-items-center justify-content-center bg-secondary-subtle text-secondary rounded" style="width: 64px; height: 48px;">
                                        <i class="bi bi-image"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($berita['judul']) ?></div>
                                <?php
                                $ringkas = trim(preg_replace('/\s+/', ' ', strip_tags((string) $berita['isi'])) ?? '');
                                if (strlen($ringkas) > 90) {
                                    $ringkas = substr($ringkas, 0, 90) . '...';
                                }
                                ?>
                                <div class="small text-secondary"><?= e($ringkas) ?></div>
                            </td>
                            <td><code><?= e($berita['alamat_halaman']) ?></code></td>
                            <td><?= badge_status_berita((string) $berita['status']) ?></td>
                            <td><?= e(tanggal_singkat($berita['tanggal_terbit'])) ?></td>
                            <td><?= !empty($berita['penulis']) ? e($berita['penulis']) : '<span class="text-secondary">-</span>' ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= e($halamanIni) ?>?edit=<?= (int) $berita['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form
                                    method="post"
                                    action="<?= e($halamanIni) ?>"
                                    class="d-inline"
                                    data-konfirmasi="Hapus berita &quot;<?= e($berita['judul']) ?>&quot;?"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="aksi" value="hapus_berita">
                                    <input type="hidden" name="id" value="<?= (int) $berita['id'] ?>">
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
                <?= $nilai['id'] > 0 ? 'Edit Berita' : 'Tambah Berita' ?>
            </h3>
        </div>
        <form method="post" action="<?= e($halamanIni) ?>" enctype="multipart/form-data">
            <div class="card-body">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="aksi" value="simpan_berita">
                <input type="hidden" name="id" value="<?= (int) $nilai['id'] ?>">

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="berita_judul" class="form-label">Judul berita <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="berita_judul"
                            name="judul"
                            maxlength="200"
                            value="<?= e($nilai['judul']) ?>"
                            required
                        >
                        <div class="form-text">
                            Slug dibuat otomatis dari judul dan dipastikan unik.
                            <?php if ($nilai['alamat_halaman'] !== ''): ?>
                                Slug saat ini: <code><?= e($nilai['alamat_halaman']) ?></code>.
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="berita_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="berita_status" name="status" required>
                            <?php foreach (opsi_status_berita() as $kunci => $label): ?>
                                <option value="<?= e($kunci) ?>" <?= $nilai['status'] === $kunci ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="berita_tanggal" class="form-label">Tanggal terbit <span class="text-danger">*</span></label>
                        <input
                            type="datetime-local"
                            class="form-control"
                            id="berita_tanggal"
                            name="tanggal_terbit"
                            value="<?= e($nilai['tanggal_terbit']) ?>"
                            required
                        >
                    </div>

                    <div class="col-12 mb-3">
                        <label for="berita_isi" class="form-label">Isi berita <span class="text-danger">*</span></label>
                        <textarea
                            class="form-control"
                            id="berita_isi"
                            name="isi"
                            rows="10"
                            required
                            placeholder="Tulis isi berita di sini..."
                        ><?= e($nilai['isi']) ?></textarea>
                    </div>

                    <div class="col-md-7 mb-3">
                        <label for="berita_gambar" class="form-label">Gambar berita</label>
                        <input
                            type="file"
                            class="form-control"
                            id="berita_gambar"
                            name="tautan_gambar"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >
                        <div class="form-text">
                            JPG, PNG, atau WebP. Maksimal 2 MB. Nama berkas dibuat otomatis.
                        </div>
                    </div>

                    <div class="col-md-5 mb-3">
                        <div class="form-label">Gambar saat ini</div>
                        <?php if ($nilai['tautan_gambar'] !== ''): ?>
                            <div class="d-flex align-items-center gap-3">
                                <img
                                    src="<?= e(url_gambar($nilai['tautan_gambar'])) ?>"
                                    alt="Gambar berita"
                                    class="img-thumbnail"
                                    style="width: 112px; height: 72px; object-fit: cover;"
                                >
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="hapus_gambar_berita" name="hapus_gambar">
                                    <label class="form-check-label" for="hapus_gambar_berita">Hapus gambar ini</label>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-secondary mb-0">Belum ada gambar berita.</p>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="berita_teks_tombol" class="form-label">Teks tombol</label>
                        <input
                            type="text"
                            class="form-control"
                            id="berita_teks_tombol"
                            name="teks_tombol"
                            maxlength="50"
                            value="<?= e($nilai['teks_tombol']) ?>"
                            placeholder="Contoh: Dengarkan"
                        >
                    </div>

                    <div class="col-md-8 mb-3">
                        <label for="berita_tautan_tombol" class="form-label">Tautan tombol</label>
                        <input
                            type="text"
                            class="form-control"
                            id="berita_tautan_tombol"
                            name="tautan_tombol"
                            maxlength="255"
                            value="<?= e($nilai['tautan_tombol']) ?>"
                            placeholder="https://... atau tour/schedule.php"
                        >
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
