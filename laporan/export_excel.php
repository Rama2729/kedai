<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$jenis = $_GET['jenis'] ?? 'harian';
$label_periode = '';

switch ($jenis) {
    case 'harian':
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $rentang = get_rentang_tanggal('harian', $tanggal);
        $label_periode = 'Harian - ' . format_tanggal($tanggal);
        $nama_file = 'Laporan_Harian_' . $tanggal;
        break;

    case 'mingguan':
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $rentang = get_rentang_tanggal('mingguan', $tanggal);
        $label_periode = 'Mingguan - ' . format_tanggal($rentang['start']) . ' s/d ' . format_tanggal($rentang['end']);
        $nama_file = 'Laporan_Mingguan_' . date('Y-m-d', strtotime($rentang['start']));
        break;

    case 'bulanan':
        $bulan = $_GET['bulan'] ?? date('Y-m');
        $rentang = get_rentang_tanggal('bulanan', $bulan . '-01');
        $label_periode = 'Bulanan - ' . format_tanggal($rentang['start']) . ' s/d ' . format_tanggal($rentang['end']);
        $nama_file = 'Laporan_Bulanan_' . $bulan;
        break;

    case 'custom':
        $start_date = $_GET['start'] ?? date('Y-m-01');
        $end_date   = $_GET['end'] ?? date('Y-m-d');
        $rentang = ['start' => $start_date . ' 00:00:00', 'end' => $end_date . ' 23:59:59'];
        $label_periode = 'Custom - ' . format_tanggal($start_date) . ' s/d ' . format_tanggal($end_date);
        $nama_file = 'Laporan_Custom_' . $start_date . '_sd_' . $end_date;
        break;

    default:
        die('Jenis laporan tidak valid.');
}

$start = $rentang['start'];
$end   = $rentang['end'];

$summary = get_profit_summary($pdo, $start, $end);

$stmt_penjualan = $pdo->prepare(
    "SELECT p.no_transaksi, p.tanggal, p.total_harga, p.bayar, p.kembalian, p.metode_bayar, u.nama_lengkap AS kasir, p.keterangan
     FROM penjualan p
     LEFT JOIN users u ON u.id = p.kasir_id
     WHERE p.tanggal BETWEEN ? AND ? AND p.status = 'selesai'
     ORDER BY p.tanggal ASC"
);
$stmt_penjualan->execute([$start, $end]);
$data_penjualan = $stmt_penjualan->fetchAll();

$stmt_item = $pdo->prepare(
    "SELECT p.no_transaksi, p.tanggal, dp.nama_menu, dp.qty, dp.harga_satuan, dp.subtotal
     FROM detail_penjualan dp
     JOIN penjualan p ON p.id = dp.penjualan_id
     WHERE p.tanggal BETWEEN ? AND ? AND p.status = 'selesai'
     ORDER BY p.tanggal ASC"
);
$stmt_item->execute([$start, $end]);
$data_item = $stmt_item->fetchAll();

$stmt_pengeluaran = $pdo->prepare(
    "SELECT pe.tanggal, k.nama_kategori, pe.deskripsi, pe.jumlah, u.nama_lengkap AS input_oleh
     FROM pengeluaran pe
     LEFT JOIN kategori_pengeluaran k ON k.id = pe.kategori_id
     LEFT JOIN users u ON u.id = pe.input_by
     WHERE pe.tanggal BETWEEN ? AND ?
     ORDER BY pe.tanggal ASC"
);
$stmt_pengeluaran->execute([$start, $end]);
$data_pengeluaran = $stmt_pengeluaran->fetchAll();

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator(APP_NAME)
    ->setTitle('Laporan ' . $label_periode);

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F6F4F']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];

$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Ringkasan');

$sheet1->setCellValue('A1', APP_NAME);
$sheet1->mergeCells('A1:B1');
$sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$sheet1->setCellValue('A2', 'Laporan Omset & Profit');
$sheet1->mergeCells('A2:B2');
$sheet1->setCellValue('A3', 'Periode: ' . $label_periode);
$sheet1->mergeCells('A3:B3');
$sheet1->setCellValue('A4', 'Dicetak: ' . date('d/m/Y H:i'));
$sheet1->mergeCells('A4:B4');

$sheet1->setCellValue('A6', 'Total Omset');
$sheet1->setCellValue('B6', $summary['omset']);
$sheet1->setCellValue('A7', 'Total Pengeluaran');
$sheet1->setCellValue('B7', $summary['pengeluaran']);
$sheet1->setCellValue('A8', 'Profit Bersih');
$sheet1->setCellValue('B8', $summary['profit']);
$sheet1->setCellValue('A9', 'Margin Profit (%)');
$sheet1->setCellValue('B9', $summary['omset'] > 0 ? round(($summary['profit'] / $summary['omset']) * 100, 1) : 0);

$sheet1->getStyle('A6:A9')->getFont()->setBold(true);
$sheet1->getStyle('B6:B8')->getNumberFormat()->setFormatCode('"Rp" #,##0');
$sheet1->getStyle('B9')->getNumberFormat()->setFormatCode('0.0"%"');

$sheet1->setCellValue('A11', 'Rekap Pengeluaran per Kategori');
$sheet1->mergeCells('A11:B11');
$sheet1->getStyle('A11')->getFont()->setBold(true);

$stmt_kat = $pdo->prepare(
    "SELECT COALESCE(k.nama_kategori, 'Tanpa Kategori') AS nama_kategori, SUM(pe.jumlah) AS total
     FROM pengeluaran pe
     LEFT JOIN kategori_pengeluaran k ON k.id = pe.kategori_id
     WHERE pe.tanggal BETWEEN ? AND ?
     GROUP BY k.id ORDER BY total DESC"
);
$stmt_kat->execute([$start, $end]);
$rekap_kategori = $stmt_kat->fetchAll();

$sheet1->setCellValue('A12', 'Kategori');
$sheet1->setCellValue('B12', 'Total');
$sheet1->getStyle('A12:B12')->applyFromArray($headerStyle);

$row = 13;
foreach ($rekap_kategori as $rk) {
    $sheet1->setCellValue("A{$row}", $rk['nama_kategori']);
    $sheet1->setCellValue("B{$row}", $rk['total']);
    $sheet1->getStyle("B{$row}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
    $row++;
}

$sheet1->getColumnDimension('A')->setWidth(28);
$sheet1->getColumnDimension('B')->setWidth(20);

$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Detail Penjualan');

$headers2 = ['No. Transaksi', 'Tanggal', 'Jam', 'Kasir', 'Metode Bayar', 'Total', 'Bayar', 'Kembalian', 'Keterangan'];
$col = 'A';
foreach ($headers2 as $h) {
    $sheet2->setCellValue("{$col}1", $h);
    $col++;
}
$sheet2->getStyle('A1:I1')->applyFromArray($headerStyle);

$row = 2;
foreach ($data_penjualan as $p) {
    $sheet2->setCellValue("A{$row}", $p['no_transaksi']);
    $sheet2->setCellValue("B{$row}", date('d/m/Y', strtotime($p['tanggal'])));
    $sheet2->setCellValue("C{$row}", date('H:i', strtotime($p['tanggal'])));
    $sheet2->setCellValue("D{$row}", $p['kasir'] ?? '-');
    $sheet2->setCellValue("E{$row}", strtoupper($p['metode_bayar']));
    $sheet2->setCellValue("F{$row}", $p['total_harga']);
    $sheet2->setCellValue("G{$row}", $p['bayar']);
    $sheet2->setCellValue("H{$row}", $p['kembalian']);
    $sheet2->setCellValue("I{$row}", $p['keterangan'] ?? '');
    $sheet2->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
    $row++;
}

if (empty($data_penjualan)) {
    $sheet2->setCellValue('A2', 'Tidak ada data penjualan pada periode ini.');
    $sheet2->mergeCells('A2:I2');
}

foreach (range('A', 'I') as $c) {
    $sheet2->getColumnDimension($c)->setWidth(16);
}
$sheet2->getColumnDimension('I')->setWidth(25);

$last_row = $row;
$sheet2->setCellValue("E{$last_row}", 'TOTAL');
$sheet2->setCellValue("F{$last_row}", '=SUM(F2:F' . ($row - 1) . ')');
$sheet2->getStyle("E{$last_row}:F{$last_row}")->getFont()->setBold(true);
$sheet2->getStyle("F{$last_row}")->getNumberFormat()->setFormatCode('"Rp" #,##0');

$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Detail Item Terjual');

$headers3 = ['No. Transaksi', 'Tanggal', 'Nama Menu', 'Qty', 'Harga Satuan', 'Subtotal'];
$col = 'A';
foreach ($headers3 as $h) {
    $sheet3->setCellValue("{$col}1", $h);
    $col++;
}
$sheet3->getStyle('A1:F1')->applyFromArray($headerStyle);

$row = 2;
foreach ($data_item as $it) {
    $sheet3->setCellValue("A{$row}", $it['no_transaksi']);
    $sheet3->setCellValue("B{$row}", date('d/m/Y', strtotime($it['tanggal'])));
    $sheet3->setCellValue("C{$row}", $it['nama_menu']);
    $sheet3->setCellValue("D{$row}", $it['qty']);
    $sheet3->setCellValue("E{$row}", $it['harga_satuan']);
    $sheet3->setCellValue("F{$row}", $it['subtotal']);
    $sheet3->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
    $row++;
}

if (empty($data_item)) {
    $sheet3->setCellValue('A2', 'Tidak ada data item pada periode ini.');
    $sheet3->mergeCells('A2:F2');
}

foreach (range('A', 'F') as $c) {
    $sheet3->getColumnDimension($c)->setWidth(18);
}

$sheet4 = $spreadsheet->createSheet();
$sheet4->setTitle('Detail Pengeluaran');

$headers4 = ['Tanggal', 'Kategori', 'Deskripsi', 'Jumlah', 'Input Oleh'];
$col = 'A';
foreach ($headers4 as $h) {
    $sheet4->setCellValue("{$col}1", $h);
    $col++;
}
$sheet4->getStyle('A1:E1')->applyFromArray($headerStyle);

$row = 2;
foreach ($data_pengeluaran as $pe) {
    $sheet4->setCellValue("A{$row}", date('d/m/Y', strtotime($pe['tanggal'])));
    $sheet4->setCellValue("B{$row}", $pe['nama_kategori'] ?? '-');
    $sheet4->setCellValue("C{$row}", $pe['deskripsi']);
    $sheet4->setCellValue("D{$row}", $pe['jumlah']);
    $sheet4->setCellValue("E{$row}", $pe['input_oleh'] ?? '-');
    $sheet4->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
    $row++;
}

if (empty($data_pengeluaran)) {
    $sheet4->setCellValue('A2', 'Tidak ada data pengeluaran pada periode ini.');
    $sheet4->mergeCells('A2:E2');
}

$last_row4 = $row;
$sheet4->setCellValue("C{$last_row4}", 'TOTAL');
$sheet4->setCellValue("D{$last_row4}", '=SUM(D2:D' . ($row - 1) . ')');
$sheet4->getStyle("C{$last_row4}:D{$last_row4}")->getFont()->setBold(true);
$sheet4->getStyle("D{$last_row4}")->getNumberFormat()->setFormatCode('"Rp" #,##0');

foreach (range('A', 'E') as $c) {
    $sheet4->getColumnDimension($c)->setWidth(20);
}
$sheet4->getColumnDimension('C')->setWidth(30);

$spreadsheet->setActiveSheetIndex(0);

$filename = $nama_file . '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
