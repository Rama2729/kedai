<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Laporan Bulanan';

$bulan = $_GET['bulan'] ?? date('Y-m');
$rentang = get_rentang_tanggal('bulanan', $bulan . '-01');
$summary = get_profit_summary($pdo, $rentang['start'], $rentang['end']);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Laporan Bulanan</h1>
    <a href="<?= BASE_URL ?>profit/index.php" class="btn btn-secondary btn-sm">&larr; Kembali ke Profit</a>
</div>

<div class="table-wrapper" style="padding:20px; margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:14px; align-items:end;">
        <div class="form-group" style="margin-bottom:0;">
            <label for="bulan">Pilih Bulan</label>
            <input type="month" id="bulan" name="bulan" value="<?= htmlspecialchars($bulan) ?>">
        </div>
        <button type="submit" class="btn btn-secondary">Lihat Preview</button>
    </form>
</div>

<h3>Preview: <?= format_tanggal($rentang['start']) ?> &ndash; <?= format_tanggal($rentang['end']) ?></h3>
<div class="stat-cards">
    <div class="stat-card omset">
        <div class="label">Omset</div>
        <div class="value"><?= format_rupiah($summary['omset']) ?></div>
    </div>
    <div class="stat-card pengeluaran">
        <div class="label">Pengeluaran</div>
        <div class="value"><?= format_rupiah($summary['pengeluaran']) ?></div>
    </div>
    <div class="stat-card profit">
        <div class="label">Profit</div>
        <div class="value"><?= format_rupiah($summary['profit']) ?></div>
    </div>
</div>

<a href="export_excel.php?jenis=bulanan&bulan=<?= htmlspecialchars($bulan) ?>" class="btn btn-primary">
    📥 Download Laporan Excel (.xlsx)
</a>

<?php include __DIR__ . '/../includes/footer.php'; ?>
