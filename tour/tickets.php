<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Tickets | Wallows';
$deskripsiHalaman = 'Ticket and VIP package information for Wallows shows.';

$daftarPaket = ambil_semua(
    $koneksi,
    'SELECT j.id, j.nama, j.kategori, j.deskripsi, j.fasilitas, MIN(t.harga) AS harga_min
     FROM jenis_tiket j
     LEFT JOIN tiket_konser t ON t.jenis_tiket_id = j.id
     GROUP BY j.id, j.nama, j.kategori, j.deskripsi, j.fasilitas
     ORDER BY j.id ASC'
);

$barisTiket = ambil_semua(
    $koneksi,
    'SELECT k.id, k.nama_tempat, k.kota, k.wilayah, k.tanggal_acara, k.status_penjualan,
            t.id AS tiket_id, t.harga, t.kuota, t.terjual,
            j.nama AS jenis_nama, j.kategori AS jenis_kategori
     FROM konser k
     JOIN tiket_konser t ON t.konser_id = k.id
     JOIN jenis_tiket j ON j.id = t.jenis_tiket_id
     ORDER BY k.tanggal_acara ASC, j.kategori ASC, t.id ASC'
);

$daftarKonser = [];
foreach ($barisTiket as $tiket) {
    $idKonser = (int) $tiket['id'];

    if (!isset($daftarKonser[$idKonser])) {
        $daftarKonser[$idKonser] = [
            'id' => $tiket['id'],
            'nama_tempat' => $tiket['nama_tempat'],
            'kota' => $tiket['kota'],
            'wilayah' => $tiket['wilayah'],
            'tanggal_acara' => $tiket['tanggal_acara'],
            'status_penjualan' => $tiket['status_penjualan'],
            'daftar_tiket' => [],
        ];
    }

    $daftarKonser[$idKonser]['daftar_tiket'][] = $tiket;
}

$petaStatus = [
    'presale' => ['Presale', 'presale'],
    'on_sale' => ['On Sale', 'on-sale'],
    'selling_fast' => ['Selling Fast', 'selling-fast'],
    'sold_out' => ['Sold Out', 'sold-out'],
];

$gayaHalaman = '
        .ticket-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            transition: background 0.3s ease;
            scroll-margin-top: calc(var(--nav-h) + 50px);
        }
        .ticket-card:hover { background: var(--bg-card-hover); }
        .ticket-view-all {
            display: none;
            margin-top: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ochre);
            text-decoration: none;
        }
        .ticket-view-all:hover { text-decoration: underline; }
        .ticket-card.is-highlighted {
            animation: ticket-highlight 5s ease;
        }
        @keyframes ticket-highlight {
            0%, 55% {
                border-color: var(--ochre);
                background: rgba(215, 130, 94, 0.08);
                box-shadow: 0 0 0 2px rgba(215, 130, 94, 0.45), 0 14px 34px rgba(215, 130, 94, 0.18);
            }
            100% {
                border-color: var(--border);
                background: var(--bg-card);
                box-shadow: none;
            }
        }
        .ticket-city { font-family: "Space Grotesk", sans-serif; font-size: 1.4rem; font-weight: 700; }
        .ticket-detail { font-size: 0.9rem; color: var(--text-muted); margin-top: 4px; }
';

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>TICKETS &amp; VIP PACKAGES</h1>
        <p>Choose your experience and secure your spot at a Wallows show near you.</p>
    </header>

    <section class="section fade-in-element" style="max-width: 1000px; padding-bottom: 40px;">
        <div class="packages-header">
            <h2>EXPERIENCE PACKAGES</h2>
            <p>Upgrade your night with exclusive pre-show perks and merchandise.</p>
        </div>
        <div class="packages-grid" style="grid-template-columns: 1fr; gap: 30px;">
            <?php if ($daftarPaket === []): ?>
                <p style="color: var(--text-muted);">No ticket packages yet.</p>
            <?php else: ?>
                <?php foreach ($daftarPaket as $paket): ?>
                    <?php
                    $premium = $paket['kategori'] === 'vip';
                    $ikonFasilitas = $premium ? 'fa-star' : 'fa-check';
                    $labelBadge = $premium ? 'Official VIP' : 'Standard';
                    $daftarFasilitas = preg_split('/\r\n|\r|\n/', trim((string) $paket['fasilitas'])) ?: [];
                    ?>
                    <div class="package-card <?= $premium ? 'premium' : '' ?>" style="flex-direction: row; text-align: left; align-items: flex-start; justify-content: space-between; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 250px;">
                            <div class="package-badge <?= $premium ? 'premium' : '' ?>" style="display: inline-block;"><?= e($labelBadge) ?></div>
                            <h3><?= e($paket['nama']) ?></h3>
                            <p class="package-price" style="font-size: 1.4rem; margin-bottom: 15px;">
                                <?= $paket['harga_min'] !== null ? 'from ' . e(rupiah($paket['harga_min'])) : 'Price TBA' ?>
                            </p>
                            <p style="color: var(--text-muted); font-size: 0.9rem;"><?= nl2br(e($paket['deskripsi'])) ?></p>
                        </div>
                        <div style="flex: 1; min-width: 250px;">
                            <ul class="package-features" style="margin-top: 0;">
                                <?php foreach ($daftarFasilitas as $fasilitas): ?>
                                    <li><i class="fa-solid <?= e($ikonFasilitas) ?>"></i> <?= e($fasilitas) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="section fade-in-element" style="max-width: 900px; padding-top: 20px;">
        <div class="packages-header" style="margin-bottom: 30px;">
            <h2 id="ticket-availability-title">TICKET AVAILABILITY</h2>
            <p id="ticket-availability-subtitle">Select your city, then choose Standard or VIP when you hit Buy.</p>
            <a href="<?= e(url_halaman('tour/tickets.php')) ?>" id="ticket-view-all" class="ticket-view-all">← View all tour dates</a>
        </div>

        <?php if ($daftarKonser === []): ?>
            <p style="color: var(--text-muted);">No tickets on sale yet.</p>
        <?php else: ?>
            <?php foreach ($daftarKonser as $konser): ?>
                <?php
                $tiketStandard = null;
                $tiketVip = null;

                foreach ($konser['daftar_tiket'] as $tiket) {
                    if ($tiket['jenis_kategori'] === 'vip' && $tiketVip === null) {
                        $tiketVip = $tiket;
                    } elseif ($tiket['jenis_kategori'] === 'standard' && $tiketStandard === null) {
                        $tiketStandard = $tiket;
                    }
                }

                [$labelStatus, $kelasStatus] = $petaStatus[$konser['status_penjualan']] ?? ['On Sale', 'on-sale'];
                $sudahHabis = $konser['status_penjualan'] === 'sold_out';

                $jenisTersedia = [];
                if ($tiketStandard !== null) {
                    $jenisTersedia[] = 'Standard';
                }
                if ($tiketVip !== null) {
                    $jenisTersedia[] = 'VIP';
                }

                $teksKetersediaan = $jenisTersedia === []
                    ? 'Tickets coming soon'
                    : implode(' & ', $jenisTersedia) . ' Available';

                if ($sudahHabis) {
                    $teksKetersediaan = 'Sold Out';
                }

                $kotaLengkap = $konser['kota'] . ', ' . $konser['wilayah'];
                $detailAcara = format_tanggal($konser['tanggal_acara'], 'M d') . ' · ' . $konser['nama_tempat'];
                ?>
                <div class="ticket-card" id="konser-<?= (int) $konser['id'] ?>" data-concert-id="<?= (int) $konser['id'] ?>" data-sale-status="<?= e($konser['status_penjualan']) ?>" <?= $sudahHabis ? 'style="opacity:0.6;"' : '' ?>>
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <div class="ticket-city"><?= e($kotaLengkap) ?></div>
                            <span class="ticket-status-badge <?= e($kelasStatus) ?>"><?= e($labelStatus) ?></span>
                        </div>
                        <div class="ticket-detail"><?= e($detailAcara) ?> · <?= e($teksKetersediaan) ?></div>
                    </div>
                    <div class="ticket-buy-group">
                        <?php if ($sudahHabis): ?>
                            <span style="padding:10px 20px; border:1px solid rgba(255,255,255,0.12); border-radius:var(--radius-sm); font-size:0.8rem; color:var(--text-light); text-transform: uppercase; font-weight: 600;">Sold Out</span>
                        <?php else: ?>
                            <button
                                class="btn btn-primary ticket-select-btn"
                                style="padding: 10px 24px; font-size: 0.8rem;"
                                data-item-type="ticket"
                                data-city="<?= e($kotaLengkap) ?>"
                                data-event="<?= e($detailAcara) ?>"
                                data-standard-ticket-id="<?= $tiketStandard !== null ? (int) $tiketStandard['tiket_id'] : '' ?>"
                                data-standard-name="<?= $tiketStandard !== null ? e($tiketStandard['jenis_nama'] . ' - ' . $konser['kota']) : '' ?>"
                                data-standard-price="<?= $tiketStandard !== null ? e((string) $tiketStandard['harga']) : '' ?>"
                                data-vip-ticket-id="<?= $tiketVip !== null ? (int) $tiketVip['tiket_id'] : '' ?>"
                                data-vip-name="<?= $tiketVip !== null ? e($tiketVip['jenis_nama'] . ' - ' . $konser['kota']) : '' ?>"
                                data-vip-price="<?= $tiketVip !== null ? e((string) $tiketVip['harga']) : '' ?>"
                            >Buy Tickets</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="section fade-in-element" style="max-width: 900px; padding-top: 20px;">
        <div class="packages-header" style="margin-bottom: 30px;">
            <h2>TICKETING FAQ</h2>
            <p>Got questions about VIP packages, resale, or age restrictions? Read below.</p>
        </div>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="faq-item" style="background: var(--bg-card); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
                <h4 style="color: var(--ochre); font-size: 1.1rem; margin-bottom: 8px; font-family: 'Space Grotesk', sans-serif;">When will VIP details (soundcheck time, entry) be sent?</h4>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">All VIP package details, schedule, and entry instructions will be emailed to the ticket buyer 3-5 days before the event. Please check your spam folder if you do not receive it.</p>
            </div>
            <div class="faq-item" style="background: var(--bg-card); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
                <h4 style="color: var(--ochre); font-size: 1.1rem; margin-bottom: 8px; font-family: 'Space Grotesk', sans-serif;">Can I purchase tickets on behalf of a friend?</h4>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">Yes, but for VIP packages, the photo ID of the original purchaser may be required at check-in. If you bought tickets as a gift, please make sure they bring a copy of your ID and order confirmation.</p>
            </div>
            <div class="faq-item" style="background: var(--bg-card); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
                <h4 style="color: var(--ochre); font-size: 1.1rem; margin-bottom: 8px; font-family: 'Space Grotesk', sans-serif;">Are tickets refundable?</h4>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">All ticket sales are final and non-refundable unless the show is cancelled or rescheduled. In case of cancellation, refunds will be issued automatically by the ticketing service provider.</p>
            </div>
        </div>
    </section>

    <div id="checkout-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div id="checkout-form-container">
                <div class="modal-header">
                    <h2>Checkout</h2>
                    <p id="modal-product-title">Product Name</p>
                    <p id="modal-product-price" class="modal-price">Rp 0</p>
                </div>
                <form id="checkout-form" action="#" method="post">
                    <input type="hidden" name="item_type" id="checkout-item-type" value="">
                    <input type="hidden" name="product_id" id="checkout-product-id" value="">
                    <input type="hidden" name="concert_ticket_id" id="checkout-concert-ticket-id" value="">
                    <div class="form-group" id="checkout-package-group" style="display: none;">
                        <span class="form-label">Choose Package</span>
                        <p id="checkout-package-context" class="package-context"></p>
                        <div class="package-options">
                            <label class="package-option" data-package="standard">
                                <input type="radio" name="ticket_package" value="standard" class="package-radio" checked>
                                <span class="package-option-info">
                                    <span class="package-option-badge">Standard</span>
                                    <span class="package-option-name">General Admission</span>
                                    <span class="package-option-desc">Standard entry to the floor or seating area.</span>
                                </span>
                                <span class="package-option-price">-</span>
                            </label>
                            <label class="package-option package-option-premium" data-package="vip">
                                <input type="radio" name="ticket_package" value="vip" class="package-radio">
                                <span class="package-option-info">
                                    <span class="package-option-badge">Official VIP</span>
                                    <span class="package-option-name">The "Especially You" VIP Package</span>
                                    <span class="package-option-desc">Early entry, signed poster, tote bag, laminate &amp; more.</span>
                                </span>
                                <span class="package-option-price">-</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="checkout-name" class="form-label">Full Name</label>
                        <input type="text" id="checkout-name" name="customer_name" class="form-control" placeholder="YOUR NAME" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="checkout-email" class="form-label">Email Address</label>
                        <input type="email" id="checkout-email" name="customer_email" class="form-control" placeholder="YOUR EMAIL" maxlength="150" required>
                    </div>
                    <div class="form-group">
                        <label for="checkout-quantity" class="form-label">Quantity</label>
                        <input type="number" id="checkout-quantity" name="quantity" class="form-control" value="1" min="1" max="10" required>
                    </div>
                    <div class="form-group" id="checkout-address-group">
                        <label for="checkout-address" class="form-label">Delivery Address</label>
                        <textarea id="checkout-address" name="shipping_address" class="form-control" placeholder="YOUR SHIPPING ADDRESS" required></textarea>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" id="checkout-create-account" name="create_account" value="1">
                        <label for="checkout-create-account">Create an account with this email (optional)</label>
                    </div>
                    <div class="form-group" id="checkout-password-group" style="display: none;">
                        <label for="checkout-password" class="form-label">Password</label>
                        <input type="password" id="checkout-password" name="password" class="form-control" placeholder="MIN. 8 CHARACTERS" minlength="8" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">Place Order</button>
                </form>
            </div>
            <div id="checkout-success" class="success-container" style="display: none;">
                <div class="success-icon">✓</div>
                <h2>Order Placed!</h2>
                <p>Thank you for your purchase. A confirmation email has been sent to <strong id="success-email">email@example.com</strong>.</p>
                <button id="success-close-btn" class="btn btn-outline-dark" style="width: 100%; margin-top: 24px;">Continue Shopping</button>
            </div>
        </div>
    </div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
