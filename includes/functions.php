<?php

function format_rupiah($angka) {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function format_tanggal($tanggal, $dengan_jam = false) {
    if (empty($tanggal)) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $ts = strtotime($tanggal);
    $hasil = date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    if ($dengan_jam) {
        $hasil .= ' ' . date('H:i', $ts);
    }
    return $hasil;
}

function generate_no_transaksi(PDO $pdo) {
    $tanggal = date('Ymd');
    $prefix = "TRX-{$tanggal}-";

    $stmt = $pdo->prepare(
        "SELECT no_transaksi FROM penjualan
         WHERE no_transaksi LIKE ?
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();

    if ($last) {
        $urut = (int) substr($last, -4) + 1;
    } else {
        $urut = 1;
    }

    return $prefix . str_pad($urut, 4, '0', STR_PAD_LEFT);
}

function redirect_with_message($url, $type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    header("Location: {$url}");
    exit;
}

function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function clean_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function get_rentang_tanggal($jenis, $tanggal_acuan = null) {
    $acuan = $tanggal_acuan ? strtotime($tanggal_acuan) : time();

    switch ($jenis) {
        case 'harian':
            $start = date('Y-m-d 00:00:00', $acuan);
            $end   = date('Y-m-d 23:59:59', $acuan);
            break;

        case 'mingguan':
            $start = date('Y-m-d 00:00:00', strtotime('monday this week', $acuan));
            $end   = date('Y-m-d 23:59:59', strtotime('sunday this week', $acuan));
            break;

        case 'bulanan':
            $start = date('Y-m-01 00:00:00', $acuan);
            $end   = date('Y-m-t 23:59:59', $acuan);
            break;

        default:
            $start = date('Y-m-d 00:00:00', $acuan);
            $end   = date('Y-m-d 23:59:59', $acuan);
    }

    return ['start' => $start, 'end' => $end];
}

function get_omset(PDO $pdo, $start, $end) {
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(total_harga), 0) FROM penjualan
         WHERE tanggal BETWEEN ? AND ? AND status = 'selesai'"
    );
    $stmt->execute([$start, $end]);
    return (float) $stmt->fetchColumn();
}

function get_total_pengeluaran(PDO $pdo, $start, $end) {
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(jumlah), 0) FROM pengeluaran
         WHERE tanggal BETWEEN ? AND ?"
    );
    $stmt->execute([$start, $end]);
    return (float) $stmt->fetchColumn();
}

function get_profit_summary(PDO $pdo, $start, $end) {
    $omset = get_omset($pdo, $start, $end);
    $pengeluaran = get_total_pengeluaran($pdo, $start, $end);

    return [
        'omset'       => $omset,
        'pengeluaran' => $pengeluaran,
        'profit'      => $omset - $pengeluaran,
    ];
}
