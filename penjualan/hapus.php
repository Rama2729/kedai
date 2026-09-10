<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with_message('index.php', 'error', 'Transaksi tidak valid.');
}

$stmt = $pdo->prepare("SELECT no_transaksi, status FROM penjualan WHERE id = ?");
$stmt->execute([$id]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    redirect_with_message('index.php', 'error', 'Transaksi tidak ditemukan.');
}

if ($transaksi['status'] === 'batal') {
    redirect_with_message('index.php', 'warning', 'Transaksi ini sudah dibatalkan sebelumnya.');
}

$stmt_update = $pdo->prepare("UPDATE penjualan SET status = 'batal' WHERE id = ?");
$stmt_update->execute([$id]);

redirect_with_message('index.php', 'success', 'Transaksi ' . $transaksi['no_transaksi'] . ' berhasil dibatalkan.');
