<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Edit Menu';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with_message('index.php', 'error', 'Menu tidak ditemukan.');
}

$stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    redirect_with_message('index.php', 'error', 'Menu tidak ditemukan.');
}

$kategori_list = $pdo->query("SELECT id, nama_kategori FROM kategori_menu ORDER BY nama_kategori")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Menu: <?= htmlspecialchars($data['nama_menu']) ?></h1>
    <a href="index.php" class="btn btn-secondary btn-sm">&larr; Kembali</a>
</div>

<div class="table-wrapper" style="padding:20px; max-width:480px;">
    <form action="simpan.php" method="POST">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">

        <div class="form-group">
            <label for="nama_menu">Nama Menu</label>
            <input type="text" id="nama_menu" name="nama_menu" value="<?= htmlspecialchars($data['nama_menu']) ?>" required>
        </div>

        <div class="form-group">
            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id" required>
                <?php foreach ($kategori_list as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $k['id'] == $data['kategori_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="harga_jual">Harga Jual (Rp)</label>
            <input type="number" id="harga_jual" name="harga_jual" min="0" value="<?= $data['harga_jual'] ?>" required>
        </div>

        <div class="form-group">
            <label for="hpp">HPP - Harga Pokok Produksi (Rp)</label>
            <input type="number" id="hpp" name="hpp" min="0" value="<?= $data['hpp'] ?>">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="aktif" <?= $data['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= $data['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
            <small style="color:var(--color-text-muted); font-size:0.8rem;">
                Nonaktifkan menu yang sudah tidak dijual tanpa menghapus riwayat penjualannya.
            </small>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
