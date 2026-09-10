<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id          = (int) ($_POST['id'] ?? 0);
$nama_menu   = trim($_POST['nama_menu'] ?? '');
$kategori_id = (int) ($_POST['kategori_id'] ?? 0);
$harga_jual  = (float) ($_POST['harga_jual'] ?? 0);
$hpp         = (float) ($_POST['hpp'] ?? 0);
$status      = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

if ($nama_menu === '' || $kategori_id <= 0 || $harga_jual <= 0) {
    redirect_with_message($id > 0 ? "edit.php?id={$id}" : 'tambah.php', 'error', 'Nama menu, kategori, dan harga jual wajib diisi dengan benar.');
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE menu SET nama_menu = ?, kategori_id = ?, harga_jual = ?, hpp = ?, status = ? WHERE id = ?"
        );
        $stmt->execute([$nama_menu, $kategori_id, $harga_jual, $hpp, $status, $id]);
        redirect_with_message('index.php', 'success', 'Menu berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO menu (nama_menu, kategori_id, harga_jual, hpp, status) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nama_menu, $kategori_id, $harga_jual, $hpp, $status]);
        redirect_with_message('index.php', 'success', 'Menu baru berhasil ditambahkan.');
    }
} catch (Exception $e) {
    error_log('Gagal simpan menu: ' . $e->getMessage());
    redirect_with_message('index.php', 'error', 'Gagal menyimpan menu. Silakan coba lagi.');
}
