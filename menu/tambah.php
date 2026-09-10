<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Tambah Menu';

$kategori_list = $pdo->query("SELECT id, nama_kategori FROM kategori_menu ORDER BY nama_kategori")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Tambah Menu Baru</h1>
    <a href="index.php" class="btn btn-secondary btn-sm">&larr; Kembali</a>
</div>

<div class="table-wrapper" style="padding:20px; max-width:480px;">
    <form action="simpan.php" method="POST">
        <div class="form-group">
            <label for="nama_menu">Nama Menu</label>
            <input type="text" id="nama_menu" name="nama_menu" required placeholder="Contoh: Susu Murni Coklat">
        </div>

        <div class="form-group">
            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori_list as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="harga_jual">Harga Jual (Rp)</label>
            <input type="number" id="harga_jual" name="harga_jual" min="0" required placeholder="0">
        </div>

        <div class="form-group">
            <label for="hpp">HPP - Harga Pokok Produksi (Rp)</label>
            <input type="number" id="hpp" name="hpp" min="0" value="0" placeholder="0">
            <small style="color:var(--color-text-muted); font-size:0.8rem;">Opsional, dipakai untuk menghitung margin per item.</small>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="aktif" selected>Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Menu</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
