<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard/index.php');
    exit;
}

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-header">
            <h1><?= APP_SHORT_NAME ?></h1>
            <p>Sistem Pembukuan Kasir</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <form action="login_process.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
    </div>
</body>
</html>
