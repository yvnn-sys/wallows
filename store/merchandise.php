<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Store | Wallows';
$deskripsiHalaman = 'Official Wallows merchandise, vinyl, and apparel.';

$daftarKategori = ambil_semua(
    $koneksi,
    'SELECT id, nama FROM kategori_produk ORDER BY nama ASC'
);

$daftarProduk = ambil_semua(
    $koneksi,
    'SELECT p.id, p.nama, p.harga, p.stok, p.tautan_gambar, p.label_produk, k.nama AS kategori
     FROM produk p
     JOIN kategori_produk k ON k.id = p.kategori_produk_id
     WHERE p.status_aktif = 1
     ORDER BY p.nama ASC, p.id ASC'
);

$gayaHalaman = '
        .price { font-family: "Space Grotesk", sans-serif; font-size: 1.4rem; font-weight: bold; color: var(--ochre); margin-bottom: 20px; }
        #product-grid .product-media {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            aspect-ratio: 1 / 1;
            margin-bottom: 20px;
            border-radius: var(--radius-md);
            overflow: hidden;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.07), rgba(255, 255, 255, 0.01));
        }
        #product-grid .product-media::after {
            content: "WALLOWS";
            position: absolute;
            font-family: "Space Grotesk", sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.35em;
            color: rgba(255, 255, 255, 0.22);
        }
        #product-grid .product-media img {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            margin: 0;
            border-radius: 0;
            object-fit: cover;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            align-self: center;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            min-height: 20px;
            padding: 4px 10px;
            border-radius: 4px;
            margin-bottom: 14px;
            font-family: "Space Grotesk", sans-serif;
            line-height: 1;
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid transparent;
        }
        #product-grid .card h3 {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 2.5em;
        }
        #product-grid .buy-btn {
            margin-top: auto;
        }
        #product-grid .btn-primary {
            border: 2px solid transparent;
        }
        .badge-new {
            background: var(--ochre);
            color: #121212;
            border-color: rgba(215, 130, 94, 0.4);
        }
        .badge-limited {
            background: #ffffff;
            color: #121212;
            border-color: rgba(255, 255, 255, 0.4);
        }
        .badge-bundle {
            background: #6B9E8F;
            color: #121212;
            border-color: rgba(107, 158, 143, 0.4);
        }
        .badge-empty {
            visibility: hidden;
        }
        .card:hover .badge {
            transform: scale(1.06) translateY(-2px);
        }
';

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>OFFICIAL STORE</h1>
        <p>Merchandise, vinyls, and exclusive apparel.</p>
    </header>

    <section class="section fade-in-element">
        <div class="category-filter" data-category-filter data-target="#product-grid" role="group" aria-label="Filter products by category">
            <button type="button" class="filter-chip is-active" data-filter="all" aria-pressed="true">All <span class="filter-count"></span></button>
            <?php foreach ($daftarKategori as $kategori): ?>
                <button type="button" class="filter-chip" data-filter="<?= e(slug_kategori($kategori['nama'])) ?>" aria-pressed="false">
                    <?= e($kategori['nama']) ?> <span class="filter-count"></span>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="grid" id="product-grid">
            <?php if ($daftarProduk === []): ?>
                <p style="color: var(--text-muted);">No products yet.</p>
            <?php else: ?>
                <?php foreach ($daftarProduk as $produk): ?>
                    <?php
                    $labelBadge = '';
                    $kelasBadge = 'badge-empty';

                    if ($produk['label_produk'] === 'new') {
                        $labelBadge = 'New';
                        $kelasBadge = 'badge-new';
                    } elseif ($produk['label_produk'] === 'limited') {
                        $labelBadge = 'Limited';
                        $kelasBadge = 'badge-limited';
                    } elseif ($produk['kategori'] === 'Bundle') {
                        $labelBadge = 'Bundle';
                        $kelasBadge = 'badge-bundle';
                    }

                    $adaStok = (int) $produk['stok'] > 0;
                    $kelasTombol = $produk['kategori'] === 'Bundle' ? 'btn-primary' : 'btn-outline-dark';
                    ?>
                    <div class="card" data-product-id="<?= (int) $produk['id'] ?>" data-category="<?= e(slug_kategori($produk['kategori'])) ?>" style="text-align: center;">
                        <?php if (!empty($produk['tautan_gambar'])): ?>
                            <div class="product-media">
                                <img src="<?= e(url_gambar($produk['tautan_gambar'])) ?>" alt="<?= e($produk['nama']) ?>" onerror="this.remove()">
                            </div>
                        <?php else: ?>
                            <div class="product-media"></div>
                        <?php endif; ?>
                        <span class="badge <?= e($kelasBadge) ?>" <?= $labelBadge === '' ? 'aria-hidden="true"' : '' ?>><?= e($labelBadge) ?></span>
                        <h3><?= e($produk['nama']) ?></h3>
                        <p class="price"><?= e(usd($produk['harga'])) ?></p>
                        <?php if ($adaStok): ?>
                            <button
                                class="btn <?= e($kelasTombol) ?> buy-btn"
                                style="width:100%; display:block;"
                                data-item-type="product"
                                data-product-id="<?= (int) $produk['id'] ?>"
                                data-name="<?= e($produk['nama']) ?>"
                                data-price="<?= e((string) $produk['harga']) ?>"
                            >Buy Now</button>
                        <?php else: ?>
                            <button class="btn btn-outline-dark" style="width:100%; display:block;" disabled>Sold Out</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="category-empty" data-filter-empty>No products in this category yet.</div>
    </section>

    <div id="checkout-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div id="checkout-form-container">
                <div class="modal-header">
                    <h2>Checkout</h2>
                    <p id="modal-product-title">Product Name</p>
                    <p id="modal-product-price" class="modal-price">$0.00</p>
                </div>
                <form id="checkout-form" action="<?= e(url_halaman('proses_checkout.php')) ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="item_type" id="checkout-item-type" value="">
                    <input type="hidden" name="product_id" id="checkout-product-id" value="">
                    <input type="hidden" name="concert_ticket_id" id="checkout-concert-ticket-id" value="">
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
                    <div id="checkout-error" style="display:none; color:#e08484; margin: 0 0 12px;"></div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">Place Order</button>
                </form>
            </div>
            <div id="checkout-success" class="success-container" style="display: none;">
                <div class="success-icon">✓</div>
                <h2>Order Placed!</h2>
                <p>Thank you for your purchase. Order <strong id="success-order-number">-</strong> has been received and is waiting for confirmation. We will contact you at <strong id="success-email">email@example.com</strong>.</p>
                <button id="success-close-btn" class="btn btn-outline-dark" style="width: 100%; margin-top: 24px;">Continue Shopping</button>
            </div>
        </div>
    </div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
