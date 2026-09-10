<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Kelola Menu';

$menu_list = $pdo->query(
    "SELECT m.id, m.nama_menu, m.harga_jual, m.hpp, m.status, k.nama_kategori
     FROM menu m
     LEFT JOIN kategori_menu k ON k.id = m.kategori_id
     ORDER BY k.nama_kategori, m.nama_menu"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Kelola Menu</h1>
    <a href="tambah.php" class="btn btn-primary">+ Tambah Menu</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Nama Menu</th>
                <th>Kategori</th>
                <th>Harga Jual</th>
                <th>HPP</th>
                <th>Margin</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($menu_list)): ?>
                <tr><td colspan="7" style="text-align:center; color:#888;">Belum ada menu. Klik "+ Tambah Menu" untuk mulai.</td></tr>
            <?php else: ?>
                <?php foreach ($menu_list as $m): ?>
                    <?php $margin_rp = $m['harga_jual'] - $m['hpp']; ?>
                    <tr>
                        <td><?= htmlspecialchars($m['nama_menu']) ?></td>
                        <td><?= htmlspecialchars($m['nama_kategori'] ?? '-') ?></td>
                        <td><?= format_rupiah($m['harga_jual']) ?></td>
                        <td><?= format_rupiah($m['hpp']) ?></td>
                        <td><?= format_rupiah($margin_rp) ?></td>
                        <td>
                            <?php if ($m['status'] === 'aktif'): ?>
                                <span style="color:var(--color-success); font-weight:600;">Aktif</span>
                            <?php else: ?>
                                <span style="color:var(--color-text-muted); font-weight:600;">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="hapus.php?id=<?= $m['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus menu &quot;<?= htmlspecialchars(addslashes($m['nama_menu'])) ?>&quot;?\n\nJika menu ini sudah pernah terjual, sistem akan menyarankan menonaktifkan saja.')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
