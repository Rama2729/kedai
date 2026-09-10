<?php

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?><?= APP_SHORT_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/navbar.php'; ?>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>
