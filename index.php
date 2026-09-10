<?php
require_once __DIR__ . '/config/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard/index.php');
} else {
    header('Location: ' . BASE_URL . 'auth/login.php');
}
exit;
