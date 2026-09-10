<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Laporan Mingguan';

$tanggal_acuan = $_GET['tanggal'] ?? date('Y-m-d');

$rentang = get_rentang_tanggal('mingguan', $tanggal_acuan);
$summary = get_profit_summary($pdo, $rentang['start'], $rentang['end']);

$start_label = date('Y-m-d', strtotime($rentang['start']));
$end_label   = date('Y-m-d', strtotime($rentang['end']));

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Laporan Mingguan</h1>
    <a href="<?= BASE_URL ?>profit/index.php" class="btn btn-secondary btn-sm">&larr; Kembali ke Profit</a>
</div>

<div class="table-wrapper" style="padding:20px; margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:14px; align-items:end;">
        <div class="form-group" style="margin-bottom:0;">
            <label for="tanggal">Pilih Tanggal (di dalam minggu yang diinginkan)</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal_acuan) ?>">
        </div>
        <button type="submit" class="btn btn-secondary">Lihat Preview</button>
    </form>
    <p style="color:var(--color-text-muted); font-size:0.85rem; margin-top:10px; margin-bottom:0;">
        Minggu dihitung Senin s/d Minggu. Tanggal yang kamu pilih akan otomatis dicarikan minggunya.
    </p>
</div>

<h3>Preview Minggu: <?= format_tanggal($start_label) ?> &ndash; <?= format_tanggal($end_label) ?></h3>
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

<a href="export_excel.php?jenis=mingguan&tanggal=<?= htmlspecialchars($tanggal_acuan) ?>" class="btn btn-primary">
    📥 Download Laporan Excel (.xlsx)
</a>

<?php include __DIR__ . '/../includes/footer.php'; ?>
