<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with_message('index.php', 'error', 'Bahan tidak valid.');
}

$stmt = $pdo->prepare("SELECT nama_bahan FROM bahan WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    redirect_with_message('index.php', 'error', 'Bahan tidak ditemukan.');
}

$stmt_delete = $pdo->prepare("DELETE FROM bahan WHERE id = ?");
$stmt_delete->execute([$id]);

redirect_with_message('index.php', 'success', 'Bahan "' . $data['nama_bahan'] . '" berhasil dihapus.');
