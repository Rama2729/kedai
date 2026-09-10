<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with_message('index.php', 'error', 'Menu tidak valid.');
}

$stmt = $pdo->prepare("SELECT nama_menu FROM menu WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    redirect_with_message('index.php', 'error', 'Menu tidak ditemukan.');
}

try {
    $stmt_delete = $pdo->prepare("DELETE FROM menu WHERE id = ?");
    $stmt_delete->execute([$id]);

    redirect_with_message('index.php', 'success', 'Menu "' . $data['nama_menu'] . '" berhasil dihapus.');

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $stmt_nonaktif = $pdo->prepare("UPDATE menu SET status = 'nonaktif' WHERE id = ?");
        $stmt_nonaktif->execute([$id]);

        redirect_with_message(
            'index.php',
            'warning',
            'Menu "' . $data['nama_menu'] . '" sudah pernah terjual sehingga tidak bisa dihapus permanen. Menu ini otomatis dinonaktifkan agar tidak muncul lagi di kasir.'
        );
    }

    error_log('Gagal hapus menu: ' . $e->getMessage());
    redirect_with_message('index.php', 'error', 'Gagal menghapus menu. Silakan coba lagi.');
}
