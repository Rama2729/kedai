<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = 'Pengeluaran';

$kategori_list = $pdo->query("SELECT id, nama_kategori FROM kategori_pengeluaran ORDER BY nama_kategori")->fetchAll();

$bulan_filter = $_GET['bulan'] ?? date('Y-m');
$rentang = get_rentang_tanggal('bulanan', $bulan_filter . '-01');

$stmt = $pdo->prepare(
    "SELECT pe.id, pe.tanggal, pe.deskripsi, pe.jumlah, k.nama_kategori, u.nama_lengkap AS input_oleh
     FROM pengeluaran pe
     LEFT JOIN kategori_pengeluaran k ON k.id = pe.kategori_id
     LEFT JOIN users u ON u.id = pe.input_by
     WHERE pe.tanggal BETWEEN ? AND ?
     ORDER BY pe.tanggal DESC"
);
$stmt->execute([$rentang['start'], $rentang['end']]);
$riwayat = $stmt->fetchAll();

$total_bulan_ini = array_sum(array_column($riwayat, 'jumlah'));

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Input Pengeluaran</h1>
</div>

<div style="display:grid; grid-template-columns: 380px 1fr; gap:20px; align-items:start;">

    <div class="table-wrapper" style="padding:20px;">
        <h3 style="margin-top:0;">Tambah Pengeluaran</h3>
        <form action="simpan.php" method="POST">
            <div class="form-group">
                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
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
                <label for="deskripsi">Deskripsi</label>
                <input type="text" id="deskripsi" name="deskripsi" placeholder="Contoh: Beli susu UHT 20 liter" required>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah (Rp)</label>
                <input type="number" id="jumlah" name="jumlah" min="1" placeholder="0" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Simpan Pengeluaran</button>
        </form>
    </div>

    <div>
        <div class="page-header" style="margin-bottom:12px;">
            <form method="GET" style="display:flex; gap:8px; align-items:center;">
                <label for="bulan" style="font-size:0.9rem;">Filter Bulan:</label>
                <input type="month" id="bulan" name="bulan" value="<?= htmlspecialchars($bulan_filter) ?>"
                       onchange="this.form.submit()">
            </form>
            <div class="stat-card pengeluaran" style="padding:10px 16px;">
                <div class="label">Total Bulan Ini</div>
                <div class="value" style="font-size:1.15rem;"><?= format_rupiah($total_bulan_ini) ?></div>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                        <th>Input Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riwayat)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#888;">Belum ada pengeluaran di bulan ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($riwayat as $r): ?>
                            <tr>
                                <td><?= format_tanggal($r['tanggal']) ?></td>
                                <td><?= htmlspecialchars($r['nama_kategori'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['deskripsi']) ?></td>
                                <td><?= format_rupiah($r['jumlah']) ?></td>
                                <td><?= htmlspecialchars($r['input_oleh'] ?? '-') ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <a href="hapus.php?id=<?= $r['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Hapus pengeluaran &quot;<?= htmlspecialchars(addslashes($r['deskripsi'])) ?>&quot;?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
