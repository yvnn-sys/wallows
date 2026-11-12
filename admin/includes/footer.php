<?php
if (!defined('ADMIN_URL')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}
?>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <strong>Wallows Official Web</strong> &middot; Panel Admin
        <div class="float-end d-none d-sm-inline"><?= date('Y') ?></div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(BASE_PATH) ?>/dist/js/adminlte.min.js"></script>
</body>
</html>
