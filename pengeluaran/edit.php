<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = 'Edit Pengeluaran';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with_message('index.php', 'error', 'Data tidak ditemukan.');
}

$stmt = $pdo->prepare("SELECT * FROM pengeluaran WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    redirect_with_message('index.php', 'error', 'Data pengeluaran tidak ditemukan.');
}

$kategori_list = $pdo->query("SELECT id, nama_kategori FROM kategori_pengeluaran ORDER BY nama_kategori")->fetchAll();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal     = $_POST['tanggal'] ?? '';
    $kategori_id = (int) ($_POST['kategori_id'] ?? 0);
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $jumlah      = (float) ($_POST['jumlah'] ?? 0);

    if ($tanggal === '' || $kategori_id <= 0 || $deskripsi === '' || $jumlah <= 0) {
        $error = 'Semua field wajib diisi dengan benar.';
    } else {
        $jam_asli = date('H:i:s', strtotime($data['tanggal']));

        $stmt_update = $pdo->prepare(
            "UPDATE pengeluaran SET tanggal = ?, kategori_id = ?, deskripsi = ?, jumlah = ? WHERE id = ?"
        );
        $stmt_update->execute([$tanggal . ' ' . $jam_asli, $kategori_id, $deskripsi, $jumlah, $id]);

        redirect_with_message('index.php', 'success', 'Pengeluaran berhasil diperbarui.');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Pengeluaran</h1>
    <a href="index.php" class="btn btn-secondary btn-sm">&larr; Kembali</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="table-wrapper" style="padding:20px; max-width:480px;">
    <form method="POST">
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= date('Y-m-d', strtotime($data['tanggal'])) ?>" required>
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
            <label for="deskripsi">Deskripsi</label>
            <input type="text" id="deskripsi" name="deskripsi" value="<?= htmlspecialchars($data['deskripsi']) ?>" required>
        </div>

        <div class="form-group">
            <label for="jumlah">Jumlah (Rp)</label>
            <input type="number" id="jumlah" name="jumlah" value="<?= $data['jumlah'] ?>" min="1" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
