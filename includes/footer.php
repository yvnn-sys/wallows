<?php
if (!defined('BASE_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}
?>
    <footer class="site-footer">
        <div class="footer-social">
            <a href="https://www.instagram.com/wallowsmusic/" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://x.com/wallowsmusic" target="_blank" rel="noopener"><i class="fa-brands fa-twitter"></i></a>
            <a href="https://www.youtube.com/channel/UCjNGsoJEu1AnHiP7kOYiXCw" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i></a>
            <a href="https://open.spotify.com/artist/0NIPkIjTV8mB795yEIiPYL" target="_blank" rel="noopener"><i class="fa-brands fa-spotify"></i></a>
        </div>

        <p class="footer-copy">&copy; 2026 Wallows. All Rights Reserved.</p>
    </footer>

    <script src="<?= e(url_halaman('script.js')) ?>"></script>
</body>

</html>
