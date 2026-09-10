<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = 'Dashboard';

$rentang_hari_ini = get_rentang_tanggal('harian');
$summary = get_profit_summary($pdo, $rentang_hari_ini['start'], $rentang_hari_ini['end']);

$rentang_bulan_ini = get_rentang_tanggal('bulanan');
$summary_bulan = get_profit_summary($pdo, $rentang_bulan_ini['start'], $rentang_bulan_ini['end']);

$stmt = $pdo->query(
    "SELECT p.no_transaksi, p.tanggal, p.total_harga, p.metode_bayar, u.nama_lengkap AS kasir
     FROM penjualan p
     LEFT JOIN users u ON u.id = p.kasir_id
     WHERE p.status = 'selesai'
     ORDER BY p.tanggal DESC LIMIT 5"
);
$transaksi_terakhir = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
</div>

<h3>Ringkasan Hari Ini</h3>
<div class="stat-cards">
    <div class="stat-card omset">
        <div class="label">Omset Hari Ini</div>
        <div class="value"><?= format_rupiah($summary['omset']) ?></div>
    </div>
    <div class="stat-card pengeluaran">
        <div class="label">Pengeluaran Hari Ini</div>
        <div class="value"><?= format_rupiah($summary['pengeluaran']) ?></div>
    </div>
    <div class="stat-card profit">
        <div class="label">Profit Hari Ini</div>
        <div class="value"><?= format_rupiah($summary['profit']) ?></div>
    </div>
</div>

<h3>Ringkasan Bulan Ini</h3>
<div class="stat-cards">
    <div class="stat-card omset">
        <div class="label">Omset Bulan Ini</div>
        <div class="value"><?= format_rupiah($summary_bulan['omset']) ?></div>
    </div>
    <div class="stat-card pengeluaran">
        <div class="label">Pengeluaran Bulan Ini</div>
        <div class="value"><?= format_rupiah($summary_bulan['pengeluaran']) ?></div>
    </div>
    <div class="stat-card profit">
        <div class="label">Profit Bulan Ini</div>
        <div class="value"><?= format_rupiah($summary_bulan['profit']) ?></div>
    </div>
</div>

<h3>Transaksi Terakhir</h3>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Metode Bayar</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transaksi_terakhir)): ?>
                <tr><td colspan="5" style="text-align:center; color:#888;">Belum ada transaksi.</td></tr>
            <?php else: ?>
                <?php foreach ($transaksi_terakhir as $trx): ?>
                    <tr>
                        <td><?= htmlspecialchars($trx['no_transaksi']) ?></td>
                        <td><?= format_tanggal($trx['tanggal'], true) ?></td>
                        <td><?= htmlspecialchars($trx['kasir'] ?? '-') ?></td>
                        <td><?= strtoupper($trx['metode_bayar']) ?></td>
                        <td><?= format_rupiah($trx['total_harga']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
