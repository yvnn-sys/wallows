<?php
require_once __DIR__ . '/../../includes/auth.php';

flash_set('error', 'Pesan kontak hanya dapat dibuat melalui formulir kontak di website.');
redirect(ADMIN_URL . '/modul/pesan_kontak/index.php');
