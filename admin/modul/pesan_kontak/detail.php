<?php
require_once __DIR__ . '/../../includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$error = null;

function muat_pesan(mysqli $koneksi, int $id): ?array
{
    $stmt = $koneksi->prepare(
        'SELECT pk.id, pk.nama, pk.email, pk.topik, pk.pesan, pk.status, pk.dibuat_pada,
                pk.dibalas_oleh, a.nama AS nama_admin
         FROM pesan_kontak pk
         LEFT JOIN admin a ON a.id = pk.dibalas_oleh
         WHERE pk.id = ?
         LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $baris ?: null;
}

$pesan = muat_pesan($koneksi, $id);

if ($pesan === null) {
    flash_set('error', 'Pesan kontak tidak ditemukan.');
    redirect(ADMIN_URL . '/modul/pesan_kontak/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        flash_set('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect(ADMIN_URL . '/modul/pesan_kontak/detail.php?id=' . $id);
    }

    if ((string) ($_POST['aksi'] ?? '') === 'replied') {
        try {
            $adminId = (int) ($_SESSION['admin_id'] ?? 0);
            $stmt = $koneksi->prepare("UPDATE pesan_kontak SET status = 'replied', dibalas_oleh = ? WHERE id = ?");
            $stmt->bind_param('ii', $adminId, $id);
            $stmt->execute();
            $stmt->close();
            flash_set('sukses', 'Status pesan berhasil diubah menjadi dibalas.');
        } catch (mysqli_sql_exception $exception) {
            error_log('Ubah status pesan gagal: ' . $exception->getMessage());
            flash_set('error', 'Status pesan gagal diubah.');
        }

        redirect(ADMIN_URL . '/modul/pesan_kontak/detail.php?id=' . $id);
    }
}

if ((string) $pesan['status'] === 'new') {
    try {
        $stmt = $koneksi->prepare("UPDATE pesan_kontak SET status = 'read' WHERE id = ? AND status = 'new'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $pesan = muat_pesan($koneksi, $id) ?? $pesan;
    } catch (mysqli_sql_exception $exception) {
        error_log('Tandai pesan dibaca gagal: ' . $exception->getMessage());
        $error = 'Pesan tidak dapat ditandai sebagai dibaca.';
    }
}

$sukses = flash_get('sukses');
$error = $error ?? flash_get('error');

$pageTitle = 'Pesan Kontak - Detail';
$activeMenu = 'pesan_kontak';

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

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Isi Pesan</h3>
                <?= badge_status((string) $pesan['status']) ?>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="fw-semibold fs-5"><?= e($pesan['nama']) ?></div>
                    <a href="mailto:<?= e($pesan['email']) ?>"><?= e($pesan['email']) ?></a>
                </div>

                <div class="mb-3">
                    <span class="badge text-bg-info"><?= e(label_topik((string) $pesan['topik'])) ?></span>
                    <span class="text-secondary small ms-2"><?= e(tanggal_singkat($pesan['dibuat_pada'])) ?></span>
                </div>

                <div class="border rounded p-3 bg-body-tertiary" style="white-space: pre-line;"><?= e($pesan['pesan']) ?></div>
            </div>
            <div class="card-footer d-flex flex-wrap gap-2">
                <a href="<?= e(ADMIN_URL) ?>/modul/pesan_kontak/index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>

                <?php if ((string) $pesan['status'] !== 'replied'): ?>
                    <form method="post" action="<?= e(ADMIN_URL) ?>/modul/pesan_kontak/detail.php?id=<?= (int) $pesan['id'] ?>" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="aksi" value="replied">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check2-circle me-1"></i> Tandai Sudah Dibalas
                        </button>
                    </form>
                <?php endif; ?>

                <form
                    method="post"
                    action="<?= e(ADMIN_URL) ?>/modul/pesan_kontak/hapus.php"
                    class="d-inline ms-auto"
                    onsubmit="return confirm('Hapus pesan ini?');"
                >
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $pesan['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Informasi</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Status</dt>
                    <dd class="col-7"><?= badge_status((string) $pesan['status']) ?></dd>

                    <dt class="col-5">Topik</dt>
                    <dd class="col-7"><?= e(label_topik((string) $pesan['topik'])) ?></dd>

                    <dt class="col-5">Masuk</dt>
                    <dd class="col-7"><?= e(tanggal_singkat($pesan['dibuat_pada'])) ?></dd>

                    <dt class="col-5">Dibalas oleh</dt>
                    <dd class="col-7"><?= e($pesan['nama_admin'] ?? '-') ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
