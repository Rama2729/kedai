<?php
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$tanggal     = $_POST['tanggal'] ?? '';
$kategori_id = (int) ($_POST['kategori_id'] ?? 0);
$deskripsi   = trim($_POST['deskripsi'] ?? '');
$jumlah      = (float) ($_POST['jumlah'] ?? 0);

if ($tanggal === '' || $kategori_id <= 0 || $deskripsi === '' || $jumlah <= 0) {
    redirect_with_message('index.php', 'error', 'Semua field wajib diisi dengan benar.');
}

$dt = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$dt) {
    redirect_with_message('index.php', 'error', 'Format tanggal tidak valid.');
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO pengeluaran (tanggal, kategori_id, deskripsi, jumlah, input_by)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $tanggal . ' ' . date('H:i:s'),
        $kategori_id,
        $deskripsi,
        $jumlah,
        $_SESSION['user_id'],
    ]);

    redirect_with_message('index.php', 'success', 'Pengeluaran berhasil disimpan.');

} catch (Exception $e) {
    error_log('Gagal simpan pengeluaran: ' . $e->getMessage());
    redirect_with_message('index.php', 'error', 'Gagal menyimpan pengeluaran. Silakan coba lagi.');
}
