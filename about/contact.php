<?php
require_once __DIR__ . '/../includes/situs.php';

$judulHalaman = 'Contact | Wallows';
$deskripsiHalaman = 'Get in touch with the team behind Wallows.';

$pilihanTopik = [
    'fan_message' => 'Fan Message',
    'feedback' => 'Website Feedback',
    'merch' => 'Merch / Order Question',
    'other' => 'Other',
];

$nilai = [
    'nama' => '',
    'email' => '',
    'topik' => '',
    'pesan' => '',
];

$pesanSukses = null;
$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai['nama'] = is_scalar($_POST['name'] ?? null) ? trim((string) $_POST['name']) : '';
    $nilai['email'] = is_scalar($_POST['email'] ?? null) ? trim((string) $_POST['email']) : '';
    $nilai['topik'] = is_scalar($_POST['topic'] ?? null) ? trim((string) $_POST['topic']) : '';
    $nilai['pesan'] = is_scalar($_POST['message'] ?? null) ? trim((string) $_POST['message']) : '';

    if ($nilai['nama'] === '') {
        $pesanError = 'Please fill in your name.';
    } elseif (!filter_var($nilai['email'], FILTER_VALIDATE_EMAIL)) {
        $pesanError = 'Please fill in a valid email address.';
    } elseif (!isset($pilihanTopik[$nilai['topik']])) {
        $pesanError = 'Please choose a topic.';
    } elseif ($nilai['pesan'] === '') {
        $pesanError = 'Please write your message.';
    } else {
        $nama = $nilai['nama'];
        $email = $nilai['email'];
        $topik = $nilai['topik'];
        $pesan = $nilai['pesan'];

        try {
            $stmt = $koneksi->prepare('INSERT INTO pesan_kontak (nama, email, topik, pesan) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $nama, $email, $topik, $pesan);
            $stmt->execute();
            $stmt->close();

            $pesanSukses = 'Thanks, ' . $nama . '! Your message has been sent. We will reply to ' . $email . ' as soon as we can.';
            $nilai = ['nama' => '', 'email' => '', 'topik' => '', 'pesan' => ''];
        } catch (mysqli_sql_exception $error) {
            error_log('Simpan pesan kontak gagal: ' . $error->getMessage());
            $pesanError = 'Your message could not be saved. Please try again.';
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

    <header class="page-header">
        <h1>CONTACT</h1>
        <p>Get in touch with the team behind Wallows.</p>
    </header>

    <section class="section fade-in-element" id="contact-unified">
        <div class="modern-contact-wrap">
            <h2 class="modern-contact-title">Contact us</h2>
            <p class="modern-contact-subtitle">Get in touch and let us know how we can help</p>

            <?php if ($pesanSukses !== null): ?>
                <div class="success-container">
                    <div class="success-icon">✓</div>
                    <h2>Message Sent!</h2>
                    <p><?= e($pesanSukses) ?></p>
                    <a href="<?= e(url_halaman('about/contact.php')) ?>" class="btn btn-outline-dark" style="width: 100%; margin-top: 24px;">Send Another Message</a>
                </div>
            <?php else: ?>
                <?php if ($pesanError !== ''): ?>
                    <div style="color: #e08484; margin-bottom: 20px;"><?= e($pesanError) ?></div>
                <?php endif; ?>

                <form id="contact-form" class="modern-contact-form" action="<?= e(url_halaman('about/contact.php')) ?>" method="post">
                    <div class="form-row-2">
                        <div class="form-group-modern">
                            <input type="text" id="contact-name" name="name" maxlength="100" class="form-control-modern" placeholder="Name" value="<?= e($nilai['nama']) ?>" required>
                        </div>
                        <div class="form-group-modern">
                            <input type="email" id="contact-email" name="email" maxlength="150" class="form-control-modern" placeholder="E-mail" value="<?= e($nilai['email']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <select id="contact-topic" name="topic" class="form-control-modern" required>
                            <option value="" disabled <?= $nilai['topik'] === '' ? 'selected' : '' ?>>Topic</option>
                            <?php foreach ($pilihanTopik as $nilaiTopik => $labelTopik): ?>
                                <option value="<?= e($nilaiTopik) ?>" <?= $nilai['topik'] === $nilaiTopik ? 'selected' : '' ?>>
                                    <?= e($labelTopik) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fa-solid fa-chevron-down select-icon"></i>
                    </div>

                    <div class="form-group-modern">
                        <textarea id="contact-message" name="message" class="form-control-modern" placeholder="Your message" rows="4" maxlength="2000" required><?= e($nilai['pesan']) ?></textarea>
                    </div>

                    <button type="submit" class="modern-submit-btn">
                        <span class="btn-text">Send Message</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
