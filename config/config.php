<?php

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Kedai Susu Murni dan Aneka Ketan Susu');
define('APP_SHORT_NAME', 'Kedai Susu Murni');

define('BASE_URL', '/kedai-pembukuan/');

define('BASE_PATH', dirname(__DIR__));

define('EXPORT_PATH', BASE_PATH . '/laporan/temp/');

define('MATA_UANG', 'Rp');

define('APP_MODE', 'development');

if (APP_MODE === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

require_once BASE_PATH . '/config/database.php';
