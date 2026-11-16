<?php
if (!defined('ADMIN_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

function opsi_status_penjualan(): array
{
    return [
        'presale' => 'Presale',
        'on_sale' => 'On Sale',
        'selling_fast' => 'Selling Fast',
        'sold_out' => 'Sold Out',
    ];
}

function badge_status_penjualan(string $status): string
{
    $peta = [
        'presale' => ['Presale', 'warning'],
        'on_sale' => ['On Sale', 'success'],
        'selling_fast' => ['Selling Fast', 'danger'],
        'sold_out' => ['Sold Out', 'secondary'],
    ];

    [$label, $warna] = $peta[$status] ?? [$status, 'secondary'];

    return '<span class="badge text-bg-' . $warna . '">' . e($label) . '</span>';
}

function opsi_kategori_tiket(): array
{
    return [
        'standard' => 'Standard',
        'vip' => 'VIP',
    ];
}

function badge_kategori_tiket(string $kategori): string
{
    $peta = [
        'standard' => ['Standard', 'primary'],
        'vip' => ['VIP', 'warning'],
    ];

    [$label, $warna] = $peta[$kategori] ?? [$kategori, 'secondary'];

    return '<span class="badge text-bg-' . $warna . '">' . e($label) . '</span>';
}

function format_tanggal_acara(?string $tanggal): string
{
    if (empty($tanggal)) {
        return '-';
    }

    $timestamp = strtotime($tanggal);

    return $timestamp === false ? '-' : date('d M Y', $timestamp);
}

function format_jam(?string $jam): string
{
    if (empty($jam)) {
        return '-';
    }

    $timestamp = strtotime($jam);

    return $timestamp === false ? '-' : date('H:i', $timestamp);
}

function persen_terjual(int $kuota, int $terjual): float
{
    if ($kuota <= 0) {
        return 0.0;
    }

    return min(100.0, round(($terjual / $kuota) * 100, 1));
}

function warna_progress(float $persen): string
{
    if ($persen >= 90) {
        return 'danger';
    }

    if ($persen >= 60) {
        return 'warning';
    }

    return 'success';
}

function ambil_satu_baris(mysqli $koneksi, string $sql, int $id): ?array
{
    try {
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $hasil = $stmt->get_result();
        $baris = $hasil->fetch_assoc();
        $stmt->close();
    } catch (mysqli_sql_exception $error) {
        error_log('Query detail admin gagal: ' . $error->getMessage());

        return null;
    }

    return $baris ?: null;
}
 