<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Kelola Bahan Baku';

$bahan_list = $pdo->query("SELECT * FROM bahan ORDER BY nama_bahan")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Kelola Bahan Baku</h1>
    <a href="tambah.php" class="btn btn-primary">+ Tambah Bahan</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Nama Bahan</th>
                <th>Stok</th>
                <th>Satuan</th>
                <th>Stok Minimum</th>
                <th>Harga Satuan</th>
                <th>Nilai Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bahan_list)): ?>
                <tr><td colspan="7" style="text-align:center; color:#888;">Belum ada data bahan baku.</td></tr>
            <?php else: ?>
                <?php foreach ($bahan_list as $b): ?>
                    <?php $stok_menipis = $b['stok'] <= $b['stok_minimum']; ?>
                    <tr style="<?= $stok_menipis ? 'background:#fff8e6;' : '' ?>">
                        <td><?= htmlspecialchars($b['nama_bahan']) ?></td>
                        <td>
                            <?= number_format($b['stok'], 2) ?>
                            <?php if ($stok_menipis): ?>
                                <span style="color:#9a6b00; font-size:0.78rem; font-weight:600;"> ⚠ Menipis</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($b['satuan']) ?></td>
                        <td><?= number_format($b['stok_minimum'], 2) ?></td>
                        <td><?= format_rupiah($b['harga_satuan']) ?></td>
                        <td><?= format_rupiah($b['stok'] * $b['harga_satuan']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="hapus.php?id=<?= $b['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus bahan &quot;<?= htmlspecialchars(addslashes($b['nama_bahan'])) ?>&quot;?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
