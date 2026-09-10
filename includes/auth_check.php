<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect_with_message(BASE_URL . 'auth/login.php', 'error', 'Silakan login terlebih dahulu.');
}

function require_admin() {
    if (($_SESSION['role'] ?? '') !== 'admin') {
        redirect_with_message(BASE_URL . 'dashboard/index.php', 'error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}
