<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id           = (int) ($_POST['id'] ?? 0);
$nama_bahan   = trim($_POST['nama_bahan'] ?? '');
$satuan       = trim($_POST['satuan'] ?? '');
$stok         = (float) ($_POST['stok'] ?? 0);
$stok_minimum = (float) ($_POST['stok_minimum'] ?? 0);
$harga_satuan = (float) ($_POST['harga_satuan'] ?? 0);

if ($nama_bahan === '' || $satuan === '' || $stok < 0) {
    redirect_with_message($id > 0 ? "edit.php?id={$id}" : 'tambah.php', 'error', 'Nama bahan, satuan, dan stok wajib diisi dengan benar.');
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE bahan SET nama_bahan = ?, satuan = ?, stok = ?, stok_minimum = ?, harga_satuan = ? WHERE id = ?"
        );
        $stmt->execute([$nama_bahan, $satuan, $stok, $stok_minimum, $harga_satuan, $id]);
        redirect_with_message('index.php', 'success', 'Data bahan berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO bahan (nama_bahan, satuan, stok, stok_minimum, harga_satuan) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nama_bahan, $satuan, $stok, $stok_minimum, $harga_satuan]);
        redirect_with_message('index.php', 'success', 'Bahan baru berhasil ditambahkan.');
    }
} catch (Exception $e) {
    error_log('Gagal simpan bahan: ' . $e->getMessage());
    redirect_with_message('index.php', 'error', 'Gagal menyimpan data bahan. Silakan coba lagi.');
}
