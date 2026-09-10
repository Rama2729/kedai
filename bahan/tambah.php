<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Tambah Bahan Baku';

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Tambah Bahan Baku</h1>
    <a href="index.php" class="btn btn-secondary btn-sm">&larr; Kembali</a>
</div>

<div class="table-wrapper" style="padding:20px; max-width:480px;">
    <form action="simpan.php" method="POST">
        <div class="form-group">
            <label for="nama_bahan">Nama Bahan</label>
            <input type="text" id="nama_bahan" name="nama_bahan" required placeholder="Contoh: Susu UHT">
        </div>

        <div class="form-group">
            <label for="satuan">Satuan</label>
            <input type="text" id="satuan" name="satuan" required placeholder="Contoh: liter, kg, pcs">
        </div>

        <div class="form-group">
            <label for="stok">Stok Awal</label>
            <input type="number" id="stok" name="stok" min="0" step="0.01" value="0" required>
        </div>

        <div class="form-group">
            <label for="stok_minimum">Stok Minimum (untuk peringatan)</label>
            <input type="number" id="stok_minimum" name="stok_minimum" min="0" step="0.01" value="0">
        </div>

        <div class="form-group">
            <label for="harga_satuan">Harga per Satuan (Rp)</label>
            <input type="number" id="harga_satuan" name="harga_satuan" min="0" value="0" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Bahan</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
