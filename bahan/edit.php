<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Edit Bahan Baku';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with_message('index.php', 'error', 'Bahan tidak ditemukan.');
}

$stmt = $pdo->prepare("SELECT * FROM bahan WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    redirect_with_message('index.php', 'error', 'Bahan tidak ditemukan.');
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Bahan: <?= htmlspecialchars($data['nama_bahan']) ?></h1>
    <a href="index.php" class="btn btn-secondary btn-sm">&larr; Kembali</a>
</div>

<div class="table-wrapper" style="padding:20px; max-width:480px;">
    <form action="simpan.php" method="POST">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">

        <div class="form-group">
            <label for="nama_bahan">Nama Bahan</label>
            <input type="text" id="nama_bahan" name="nama_bahan" value="<?= htmlspecialchars($data['nama_bahan']) ?>" required>
        </div>

        <div class="form-group">
            <label for="satuan">Satuan</label>
            <input type="text" id="satuan" name="satuan" value="<?= htmlspecialchars($data['satuan']) ?>" required>
        </div>

        <div class="form-group">
            <label for="stok">Stok Saat Ini</label>
            <input type="number" id="stok" name="stok" min="0" step="0.01" value="<?= $data['stok'] ?>" required>
        </div>

        <div class="form-group">
            <label for="stok_minimum">Stok Minimum</label>
            <input type="number" id="stok_minimum" name="stok_minimum" min="0" step="0.01" value="<?= $data['stok_minimum'] ?>">
        </div>

        <div class="form-group">
            <label for="harga_satuan">Harga per Satuan (Rp)</label>
            <input type="number" id="harga_satuan" name="harga_satuan" min="0" value="<?= $data['harga_satuan'] ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
