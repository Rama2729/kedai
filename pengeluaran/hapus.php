<?php
require_once __DIR__ . '/../includes/auth_check.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with_message('index.php', 'error', 'Data tidak valid.');
}

$stmt = $pdo->prepare("SELECT deskripsi FROM pengeluaran WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    redirect_with_message('index.php', 'error', 'Data pengeluaran tidak ditemukan.');
}

$stmt_delete = $pdo->prepare("DELETE FROM pengeluaran WHERE id = ?");
$stmt_delete->execute([$id]);

redirect_with_message('index.php', 'success', 'Pengeluaran "' . $data['deskripsi'] . '" berhasil dihapus.');
