<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    redirect_with_message('login.php', 'error', 'Username dan password wajib diisi.');
}

$stmt = $pdo->prepare("SELECT id, username, password, nama_lengkap, role, status FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    redirect_with_message('login.php', 'error', 'Username atau password salah.');
}

if ($user['status'] !== 'aktif') {
    redirect_with_message('login.php', 'error', 'Akun Anda tidak aktif. Hubungi administrator.');
}

if (!password_verify($password, $user['password'])) {
    redirect_with_message('login.php', 'error', 'Username atau password salah.');
}

session_regenerate_id(true);

$_SESSION['user_id']      = $user['id'];
$_SESSION['username']     = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['role']         = $user['role'];

redirect_with_message(BASE_URL . 'dashboard/index.php', 'success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');
