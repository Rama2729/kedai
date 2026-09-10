<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = 'Kasir / Penjualan';

$kategori_list = $pdo->query("SELECT id, nama_kategori FROM kategori_menu ORDER BY nama_kategori")->fetchAll();

$menu_list = $pdo->query(
    "SELECT m.id, m.nama_menu, m.harga_jual, m.kategori_id, k.nama_kategori
     FROM menu m
     LEFT JOIN kategori_menu k ON k.id = m.kategori_id
     WHERE m.status = 'aktif'
     ORDER BY k.nama_kategori, m.nama_menu"
)->fetchAll();

$rentang = get_rentang_tanggal('harian');
$stmt = $pdo->prepare(
    "SELECT p.id, p.no_transaksi, p.tanggal, p.total_harga, p.metode_bayar, p.status, u.nama_lengkap AS kasir
     FROM penjualan p
     LEFT JOIN users u ON u.id = p.kasir_id
     WHERE p.tanggal BETWEEN ? AND ?
     ORDER BY p.tanggal DESC"
);
$stmt->execute([$rentang['start'], $rentang['end']]);
$riwayat = $stmt->fetchAll();

$extra_js = ['assets/js/penjualan.js'];
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Kasir - Input Pesanan</h1>
</div>

<div class="kasir-layout">
    
    <div>
        <div class="filter-kategori" style="margin-bottom:14px; display:flex; gap:8px; flex-wrap:wrap;">
            <button type="button" class="btn btn-sm btn-secondary filter-btn active" data-kategori="semua">Semua</button>
            <?php foreach ($kategori_list as $kat): ?>
                <button type="button" class="btn btn-sm btn-secondary filter-btn" data-kategori="<?= $kat['id'] ?>">
                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="produk-grid" id="produkGrid">
            <?php foreach ($menu_list as $m): ?>
                <div class="produk-card"
                     data-kategori="<?= $m['kategori_id'] ?>"
                     data-id="<?= $m['id'] ?>"
                     data-nama="<?= htmlspecialchars($m['nama_menu']) ?>"
                     data-harga="<?= $m['harga_jual'] ?>">
                    <div class="nama"><?= htmlspecialchars($m['nama_menu']) ?></div>
                    <div class="harga"><?= format_rupiah($m['harga_jual']) ?></div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($menu_list)): ?>
                <p style="color:#888;">Belum ada menu aktif. Tambahkan menu terlebih dahulu di halaman Menu.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="keranjang-box">
        <h3 style="margin-top:0;">Keranjang</h3>
        <div id="keranjangList">
            <p id="keranjangKosong" style="color:#888; font-size:0.88rem;">Belum ada item dipilih.</p>
        </div>

        <div class="keranjang-total">
            <span>Total</span>
            <span id="keranjangTotal">Rp 0</span>
        </div>

        <div class="form-group">
            <label for="metodeBayar">Metode Bayar</label>
            <select id="metodeBayar">
                <option value="tunai">Tunai</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Transfer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="jumlahBayar">Jumlah Bayar</label>
            <input type="number" id="jumlahBayar" min="0" placeholder="0">
        </div>

        <div class="form-group">
            <label>Kembalian</label>
            <div id="kembalianText" style="font-weight:700; font-size:1.05rem;">Rp 0</div>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan (opsional)</label>
            <input type="text" id="keterangan" placeholder="Contoh: bungkus, meja 2, dll">
        </div>

        <button type="button" id="btnBayar" class="btn btn-primary btn-block">Proses Bayar</button>
        <button type="button" id="btnKosongkan" class="btn btn-secondary btn-block" style="margin-top:8px;">Kosongkan Keranjang</button>
    </div>
</div>

<h3 style="margin-top:32px;">Riwayat Transaksi Hari Ini</h3>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Jam</th>
                <th>Kasir</th>
                <th>Metode</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="riwayatBody">
            <?php if (empty($riwayat)): ?>
                <tr><td colspan="7" style="text-align:center; color:#888;">Belum ada transaksi hari ini.</td></tr>
            <?php else: ?>
                <?php foreach ($riwayat as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['no_transaksi']) ?></td>
                        <td><?= date('H:i', strtotime($r['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($r['kasir'] ?? '-') ?></td>
                        <td><?= strtoupper($r['metode_bayar']) ?></td>
                        <td><?= format_rupiah($r['total_harga']) ?></td>
                        <td>
                            <?php if ($r['status'] === 'selesai'): ?>
                                <span style="color:var(--color-success); font-weight:600;">Selesai</span>
                            <?php else: ?>
                                <span style="color:var(--color-danger); font-weight:600;">Dibatalkan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'selesai' && ($_SESSION['role'] ?? '') === 'admin'): ?>
                                <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <a href="hapus.php?id=<?= $r['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Batalkan transaksi <?= htmlspecialchars($r['no_transaksi']) ?>?')">Batalkan</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
