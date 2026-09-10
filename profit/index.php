<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = 'Omset & Profit';

$jenis_periode = $_GET['periode'] ?? 'bulanan';

if ($jenis_periode === 'custom') {
    $start_date = $_GET['start'] ?? date('Y-m-01');
    $end_date   = $_GET['end'] ?? date('Y-m-d');
    $start = $start_date . ' 00:00:00';
    $end   = $end_date . ' 23:59:59';
} else {
    $tanggal_acuan = $_GET['tanggal'] ?? date('Y-m-d');
    $rentang = get_rentang_tanggal($jenis_periode, $tanggal_acuan);
    $start = $rentang['start'];
    $end   = $rentang['end'];
    $start_date = date('Y-m-d', strtotime($start));
    $end_date   = date('Y-m-d', strtotime($end));
}

$summary = get_profit_summary($pdo, $start, $end);
$margin = $summary['omset'] > 0 ? ($summary['profit'] / $summary['omset']) * 100 : 0;

$stmt_omset_harian = $pdo->prepare(
    "SELECT DATE(tanggal) AS tgl, SUM(total_harga) AS total
     FROM penjualan WHERE tanggal BETWEEN ? AND ? AND status = 'selesai'
     GROUP BY DATE(tanggal)"
);
$stmt_omset_harian->execute([$start, $end]);
$omset_per_tanggal = array_column($stmt_omset_harian->fetchAll(), 'total', 'tgl');

$stmt_pengeluaran_harian = $pdo->prepare(
    "SELECT DATE(tanggal) AS tgl, SUM(jumlah) AS total
     FROM pengeluaran WHERE tanggal BETWEEN ? AND ?
     GROUP BY DATE(tanggal)"
);
$stmt_pengeluaran_harian->execute([$start, $end]);
$pengeluaran_per_tanggal = array_column($stmt_pengeluaran_harian->fetchAll(), 'total', 'tgl');

$chart_labels = [];
$chart_omset = [];
$chart_pengeluaran = [];
$chart_profit = [];

$cursor = strtotime($start_date);
$batas  = strtotime($end_date);
while ($cursor <= $batas) {
    $tgl = date('Y-m-d', $cursor);
    $o = (float) ($omset_per_tanggal[$tgl] ?? 0);
    $p = (float) ($pengeluaran_per_tanggal[$tgl] ?? 0);

    $chart_labels[] = date('d/m', $cursor);
    $chart_omset[] = $o;
    $chart_pengeluaran[] = $p;
    $chart_profit[] = $o - $p;

    $cursor = strtotime('+1 day', $cursor);
}

$stmt_kategori = $pdo->prepare(
    "SELECT COALESCE(k.nama_kategori, 'Tanpa Kategori') AS nama_kategori, SUM(pe.jumlah) AS total
     FROM pengeluaran pe
     LEFT JOIN kategori_pengeluaran k ON k.id = pe.kategori_id
     WHERE pe.tanggal BETWEEN ? AND ?
     GROUP BY k.id
     ORDER BY total DESC"
);
$stmt_kategori->execute([$start, $end]);
$breakdown_kategori = $stmt_kategori->fetchAll();

$stmt_top_menu = $pdo->prepare(
    "SELECT dp.nama_menu, SUM(dp.qty) AS total_qty, SUM(dp.subtotal) AS total_penjualan
     FROM detail_penjualan dp
     JOIN penjualan p ON p.id = dp.penjualan_id
     WHERE p.status = 'selesai' AND p.tanggal BETWEEN ? AND ?
     GROUP BY dp.menu_id
     ORDER BY total_penjualan DESC
     LIMIT 5"
);
$stmt_top_menu->execute([$start, $end]);
$top_menu = $stmt_top_menu->fetchAll();

$extra_js = [];
include __DIR__ . '/../includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

<div class="page-header">
    <h1>Omset &amp; Profit</h1>
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="<?= BASE_URL ?>laporan/export_excel.php?jenis=custom&start=<?= $start_date ?>&end=<?= $end_date ?>" class="btn btn-primary btn-sm">
            📥 Export ke Excel
        </a>
    <?php endif; ?>
</div>

<div class="table-wrapper" style="padding:16px 20px; margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:14px; align-items:end; flex-wrap:wrap;">
        <div class="form-group" style="margin-bottom:0;">
            <label for="periode">Periode</label>
            <select id="periode" name="periode" onchange="toggleCustomDate(this.value)">
                <option value="harian" <?= $jenis_periode === 'harian' ? 'selected' : '' ?>>Harian</option>
                <option value="mingguan" <?= $jenis_periode === 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
                <option value="bulanan" <?= $jenis_periode === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                <option value="custom" <?= $jenis_periode === 'custom' ? 'selected' : '' ?>>Rentang Custom</option>
            </select>
        </div>

        <div class="form-group" id="groupTanggalAcuan" style="margin-bottom:0; <?= $jenis_periode === 'custom' ? 'display:none;' : '' ?>">
            <label for="tanggal">Tanggal Acuan</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($_GET['tanggal'] ?? date('Y-m-d')) ?>">
        </div>

        <div class="form-group" id="groupCustomStart" style="margin-bottom:0; <?= $jenis_periode !== 'custom' ? 'display:none;' : '' ?>">
            <label for="start">Dari Tanggal</label>
            <input type="date" id="start" name="start" value="<?= htmlspecialchars($start_date) ?>">
        </div>

        <div class="form-group" id="groupCustomEnd" style="margin-bottom:0; <?= $jenis_periode !== 'custom' ? 'display:none;' : '' ?>">
            <label for="end">Sampai Tanggal</label>
            <input type="date" id="end" name="end" value="<?= htmlspecialchars($end_date) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Terapkan</button>
    </form>
</div>

<p style="color:var(--color-text-muted); font-size:0.88rem; margin-top:-10px;">
    Menampilkan data dari <strong><?= format_tanggal($start_date) ?></strong> sampai <strong><?= format_tanggal($end_date) ?></strong>
</p>

<div class="stat-cards">
    <div class="stat-card omset">
        <div class="label">Total Omset</div>
        <div class="value"><?= format_rupiah($summary['omset']) ?></div>
    </div>
    <div class="stat-card pengeluaran">
        <div class="label">Total Pengeluaran</div>
        <div class="value"><?= format_rupiah($summary['pengeluaran']) ?></div>
    </div>
    <div class="stat-card profit">
        <div class="label">Profit Bersih</div>
        <div class="value"><?= format_rupiah($summary['profit']) ?></div>
    </div>
    <div class="stat-card" style="border-left-color:#8b5cf6;">
        <div class="label">Margin Profit</div>
        <div class="value"><?= number_format($margin, 1) ?>%</div>
    </div>
</div>

<div class="table-wrapper" style="padding:20px; margin-bottom:24px;">
    <h3 style="margin-top:0;">Tren Omset vs Pengeluaran vs Profit</h3>
    <canvas id="chartTren" height="90"></canvas>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
    
    <div>
        <h3>Pengeluaran per Kategori</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Kategori</th><th>Total</th><th>%</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($breakdown_kategori)): ?>
                        <tr><td colspan="3" style="text-align:center; color:#888;">Tidak ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($breakdown_kategori as $b): ?>
                            <?php $persen = $summary['pengeluaran'] > 0 ? ($b['total'] / $summary['pengeluaran']) * 100 : 0; ?>
                            <tr>
                                <td><?= htmlspecialchars($b['nama_kategori']) ?></td>
                                <td><?= format_rupiah($b['total']) ?></td>
                                <td><?= number_format($persen, 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <h3>Top 5 Menu Terlaris</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Menu</th><th>Qty Terjual</th><th>Total Penjualan</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($top_menu)): ?>
                        <tr><td colspan="3" style="text-align:center; color:#888;">Tidak ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($top_menu as $tm): ?>
                            <tr>
                                <td><?= htmlspecialchars($tm['nama_menu']) ?></td>
                                <td><?= $tm['total_qty'] ?></td>
                                <td><?= format_rupiah($tm['total_penjualan']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleCustomDate(value) {
    const isCustom = value === 'custom';
    document.getElementById('groupTanggalAcuan').style.display = isCustom ? 'none' : '';
    document.getElementById('groupCustomStart').style.display = isCustom ? '' : 'none';
    document.getElementById('groupCustomEnd').style.display = isCustom ? '' : 'none';
}

const chartLabels      = <?= json_encode($chart_labels) ?>;
const chartOmset       = <?= json_encode($chart_omset) ?>;
const chartPengeluaran = <?= json_encode($chart_pengeluaran) ?>;
const chartProfit      = <?= json_encode($chart_profit) ?>;

if (typeof Chart !== 'undefined') {
    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                { label: 'Omset', data: chartOmset, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', tension: 0.3 },
                { label: 'Pengeluaran', data: chartPengeluaran, borderColor: '#d9534f', backgroundColor: 'rgba(217,83,79,0.1)', tension: 0.3 },
                { label: 'Profit', data: chartProfit, borderColor: '#3e9142', backgroundColor: 'rgba(62,145,66,0.1)', tension: 0.3 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { ticks: { callback: value => formatRupiah(value) } } }
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
