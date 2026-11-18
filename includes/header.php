<?php
if (!defined('BASE_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

$judulHalaman = $judulHalaman ?? 'Wallows | Official Site';
$deskripsiHalaman = $deskripsiHalaman ?? 'The official website of indie rock band Wallows.';
$gayaHalaman = $gayaHalaman ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judulHalaman) ?></title>
    <meta name="description" content="<?= e($deskripsiHalaman) ?>">
    <script>document.documentElement.classList.add('js');</script>
    <link rel="icon" type="image/svg+xml" href="<?= e(url_halaman('images/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(url_halaman('style.css')) ?>">
    <?php if ($gayaHalaman !== ''): ?>
        <style>
<?= $gayaHalaman ?>
        </style>
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <nav>
        <a href="<?= e(url_halaman('index.php')) ?>" class="logo">WALLOWS</a>
        <ul class="nav-links">
            <li><a href="<?= e(url_halaman('index.php')) ?>">Home</a></li>
            <li><a href="<?= e(url_halaman('about/profile.php')) ?>">About</a>
                <ul class="dropdown-menu">
                    <li><a href="<?= e(url_halaman('about/profile.php')) ?>">Profile</a></li>
                    <li><a href="<?= e(url_halaman('about/members.php')) ?>">Members</a></li>
                    <li><a href="<?= e(url_halaman('about/contact.php')) ?>">Contact</a></li>
                </ul>
            </li>
            <li><a href="<?= e(url_halaman('music/albums.php')) ?>">Music</a>
                <ul class="dropdown-menu">
                    <li><a href="<?= e(url_halaman('music/albums.php')) ?>">Albums</a></li>
                    <li><a href="<?= e(url_halaman('music/songs.php')) ?>">Popular Songs</a></li>
                    <li><a href="<?= e(url_halaman('music/lyrics.php')) ?>">Lyrics</a></li>
                </ul>
            </li>
            <li><a href="<?= e(url_halaman('media/photos.php')) ?>">Media</a>
                <ul class="dropdown-menu">
                    <li><a href="<?= e(url_halaman('media/photos.php')) ?>">Photos</a></li>
                    <li><a href="<?= e(url_halaman('media/videos.php')) ?>">Videos</a></li>
                </ul>
            </li>
            <li><a href="<?= e(url_halaman('tour/schedule.php')) ?>">Tour</a>
                <ul class="dropdown-menu">
                    <li><a href="<?= e(url_halaman('tour/schedule.php')) ?>">Schedule</a></li>
                    <li><a href="<?= e(url_halaman('tour/tickets.php')) ?>">Tickets</a></li>
                </ul>
            </li>
            <li><a href="<?= e(url_halaman('store/merchandise.php')) ?>">Store</a></li>
            <li><a href="<?= e(url_halaman('news.php')) ?>">News</a></li>
        </ul>
        <div class="nav-controls">
            <div class="menu-toggle">☰</div>
        </div>
    </nav>
