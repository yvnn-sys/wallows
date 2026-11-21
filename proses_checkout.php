<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/situs.php';

function jawab_json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function input_teks(string $kunci): string
{
    $nilai = $_POST[$kunci] ?? '';

    return is_scalar($nilai) ? trim((string) $nilai) : '';
}

function input_angka(string $kunci): int
{
    $nilai = filter_var($_POST[$kunci] ?? null, FILTER_VALIDATE_INT);

    return $nilai === false ? 0 : (int) $nilai;
}

function buat_nomor_pesanan(mysqli $koneksi): string
{
    for ($percobaan = 0; $percobaan < 5; $percobaan++) {
        $nomor = 'WLW-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $koneksi->prepare('SELECT id FROM pesanan WHERE nomor_pesanan = ? LIMIT 1');
        $stmt->bind_param('s', $nomor);
        $stmt->execute();
        $stmt->store_result();
        $dipakai = $stmt->num_rows > 0;
        $stmt->close();

        if (!$dipakai) {
            return $nomor;
        }
    }

    throw new RuntimeException('Nomor pesanan unik tidak dapat dibuat.');
}

function buat_kode_tiket(mysqli $koneksi): string
{
    for ($percobaan = 0; $percobaan < 5; $percobaan++) {
        $kode = 'TIX-' . strtoupper(bin2hex(random_bytes(5)));
        $stmt = $koneksi->prepare('SELECT id FROM item_pesanan WHERE kode_tiket = ? LIMIT 1');
        $stmt->bind_param('s', $kode);
        $stmt->execute();
        $stmt->store_result();
        $dipakai = $stmt->num_rows > 0;
        $stmt->close();

        if (!$dipakai) {
            return $kode;
        }
    }

    throw new RuntimeException('Kode tiket unik tidak dapat dibuat.');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    jawab_json(['ok' => false, 'message' => 'Metode permintaan tidak diizinkan.'], 405);
}

if (!csrf_valid(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
    jawab_json(['ok' => false, 'message' => 'Sesi checkout sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.'], 419);
}

$jenisItem = input_teks('item_type');
$jumlah = input_angka('quantity');
$nama = input_teks('customer_name');
$email = input_teks('customer_email');
$alamat = input_teks('shipping_address');
$buatAkun = ($_POST['create_account'] ?? '') === '1';
$sandi = input_teks('password');

if (!in_array($jenisItem, ['product', 'ticket'], true)) {
    jawab_json(['ok' => false, 'message' => 'Jenis item tidak valid.'], 422);
}

$idItem = $jenisItem === 'product' ? input_angka('product_id') : input_angka('concert_ticket_id');

if ($idItem < 1) {
    jawab_json(['ok' => false, 'message' => 'Item yang dipilih tidak valid.'], 422);
}

if ($nama === '' || panjang_teks($nama) > 100) {
    jawab_json(['ok' => false, 'message' => 'Nama harus diisi dan maksimal 100 karakter.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || panjang_teks($email) > 150) {
    jawab_json(['ok' => false, 'message' => 'Alamat email tidak valid.'], 422);
}

if ($jumlah < 1 || $jumlah > 10) {
    jawab_json(['ok' => false, 'message' => 'Jumlah harus antara 1 dan 10.'], 422);
}

if ($jenisItem === 'product' && ($alamat === '' || panjang_teks($alamat) > 1000)) {
    jawab_json(['ok' => false, 'message' => 'Alamat pengiriman wajib diisi untuk merchandise.'], 422);
}

if ($buatAkun && panjang_teks($sandi) < 8) {
    jawab_json(['ok' => false, 'message' => 'Sandi akun minimal 8 karakter.'], 422);
}

$koneksi->begin_transaction();

try {
    if ($jenisItem === 'product') {
        $stmt = $koneksi->prepare('SELECT id, nama, harga, stok, status_aktif FROM produk WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $idItem);
        $stmt->execute();
        $produk = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($produk === null || (int) $produk['status_aktif'] !== 1) {
            throw new DomainException('Produk ini sedang tidak tersedia.');
        }

        $stok = (int) $produk['stok'];
        if ($stok < $jumlah) {
            throw new DomainException('Stok tidak mencukupi. Tersisa ' . $stok . ' item.');
        }

        $harga = (float) $produk['harga'];
        $namaItem = (string) $produk['nama'];
        $total = round($harga * $jumlah, 2);
    } else {
        $stmt = $koneksi->prepare(
            'SELECT tk.id, tk.harga, tk.kuota, tk.terjual, jt.nama AS jenis_nama, k.nama_tempat,
                    k.status_penjualan
             FROM tiket_konser tk
             JOIN jenis_tiket jt ON jt.id = tk.jenis_tiket_id
             JOIN konser k ON k.id = tk.konser_id
             WHERE tk.id = ?
             FOR UPDATE'
        );
        $stmt->bind_param('i', $idItem);
        $stmt->execute();
        $tiket = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($tiket === null) {
            throw new DomainException('Tiket yang dipilih tidak ditemukan.');
        }

        if ((string) $tiket['status_penjualan'] === 'sold_out') {
            throw new DomainException('Penjualan tiket untuk konser ini sudah ditutup.');
        }

        $sisa = (int) $tiket['kuota'] - (int) $tiket['terjual'];
        if ($sisa < $jumlah) {
            throw new DomainException('Kuota tiket tidak mencukupi. Tersisa ' . max(0, $sisa) . ' tiket.');
        }

        $harga = (float) $tiket['harga'];
        $namaItem = 'Tiket ' . $tiket['jenis_nama'] . ' - ' . $tiket['nama_tempat'];
        if (panjang_teks($namaItem) > 200) {
            $namaItem = function_exists('mb_substr')
                ? mb_substr($namaItem, 0, 200, 'UTF-8')
                : substr($namaItem, 0, 200);
        }
        $total = round($harga * $jumlah, 2);
    }

    $pelangganId = null;
    $stmt = $koneksi->prepare('SELECT id FROM pelanggan WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $pelanggan = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($pelanggan !== null) {
        $pelangganId = (int) $pelanggan['id'];
    } elseif ($buatAkun) {
        $hash = password_hash($sandi, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare('INSERT INTO pelanggan (nama, email, kata_sandi) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $nama, $email, $hash);
        $stmt->execute();
        $pelangganId = $koneksi->insert_id;
        $stmt->close();
    }

    $nomorPesanan = buat_nomor_pesanan($koneksi);
    $alamatPesanan = $jenisItem === 'product' ? $alamat : null;
    $stmt = $koneksi->prepare(
        "INSERT INTO pesanan
            (pelanggan_id, nomor_pesanan, nama_pelanggan, email_pelanggan, alamat_pengiriman, total_harga, status)
         VALUES (?, ?, ?, ?, ?, ?, 'pending')"
    );
    $stmt->bind_param('issssd', $pelangganId, $nomorPesanan, $nama, $email, $alamatPesanan, $total);
    $stmt->execute();
    $pesananId = $koneksi->insert_id;
    $stmt->close();

    if ($jenisItem === 'product') {
        $stmt = $koneksi->prepare(
            'INSERT INTO item_pesanan
                (pesanan_id, produk_id, tiket_konser_id, nama_item, harga_satuan, jumlah, kode_tiket)
             VALUES (?, ?, NULL, ?, ?, ?, NULL)'
        );
        $stmt->bind_param('iisdi', $pesananId, $idItem, $namaItem, $harga, $jumlah);
        $stmt->execute();
        $stmt->close();

        $stmt = $koneksi->prepare('UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?');
        $stmt->bind_param('iii', $jumlah, $idItem, $jumlah);
        $stmt->execute();

        if ($stmt->affected_rows !== 1) {
            throw new DomainException('Stok produk berubah saat checkout. Silakan coba lagi.');
        }

        $stmt->close();
    } else {
        for ($i = 0; $i < $jumlah; $i++) {
            $kodeTiket = buat_kode_tiket($koneksi);
            $stmt = $koneksi->prepare(
                'INSERT INTO item_pesanan
                    (pesanan_id, produk_id, tiket_konser_id, nama_item, harga_satuan, jumlah, kode_tiket)
                 VALUES (?, NULL, ?, ?, ?, 1, ?)'
            );
            $stmt->bind_param('iisds', $pesananId, $idItem, $namaItem, $harga, $kodeTiket);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $koneksi->prepare('UPDATE tiket_konser SET terjual = terjual + ? WHERE id = ? AND kuota - terjual >= ?');
        $stmt->bind_param('iii', $jumlah, $idItem, $jumlah);
        $stmt->execute();

        if ($stmt->affected_rows !== 1) {
            throw new DomainException('Kuota tiket berubah saat checkout. Silakan coba lagi.');
        }

        $stmt->close();
    }

    $koneksi->commit();

    jawab_json([
        'ok' => true,
        'message' => 'Pesanan berhasil dibuat.',
        'nomor_pesanan' => $nomorPesanan,
        'total' => $total,
    ]);
} catch (DomainException $error) {
    $koneksi->rollback();
    jawab_json(['ok' => false, 'message' => $error->getMessage()], 422);
} catch (mysqli_sql_exception $error) {
    $koneksi->rollback();
    error_log('Checkout gagal: ' . $error->getMessage());
    jawab_json(['ok' => false, 'message' => 'Pesanan gagal disimpan. Silakan coba lagi.'], 500);
} catch (Throwable $error) {
    $koneksi->rollback();
    error_log('Checkout gagal: ' . $error->getMessage());
    jawab_json(['ok' => false, 'message' => 'Pesanan gagal diproses. Silakan coba lagi.'], 500);
}
