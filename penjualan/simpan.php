<?php
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$items        = $input['items'] ?? [];
$metode_bayar = $input['metode_bayar'] ?? 'tunai';
$bayar        = (float) ($input['bayar'] ?? 0);
$keterangan   = trim($input['keterangan'] ?? '');

if (empty($items) || !is_array($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Keranjang kosong.']);
    exit;
}

if (!in_array($metode_bayar, ['tunai', 'qris', 'transfer'], true)) {
    $metode_bayar = 'tunai';
}

try {
    $pdo->beginTransaction();

    $stmt_menu = $pdo->prepare("SELECT id, nama_menu, harga_jual FROM menu WHERE id = ? AND status = 'aktif'");

    $detail_items = [];
    $total_harga  = 0;

    foreach ($items as $item) {
        $menu_id = (int) ($item['menu_id'] ?? 0);
        $qty     = (int) ($item['qty'] ?? 0);

        if ($menu_id <= 0 || $qty <= 0) {
            continue;
        }

        $stmt_menu->execute([$menu_id]);
        $menu = $stmt_menu->fetch();

        if (!$menu) {
            throw new Exception("Menu dengan ID {$menu_id} tidak ditemukan atau sudah nonaktif.");
        }

        $subtotal = $menu['harga_jual'] * $qty;
        $total_harga += $subtotal;

        $detail_items[] = [
            'menu_id'      => $menu['id'],
            'nama_menu'    => $menu['nama_menu'],
            'qty'          => $qty,
            'harga_satuan' => $menu['harga_jual'],
            'subtotal'     => $subtotal,
        ];
    }

    if (empty($detail_items)) {
        throw new Exception('Tidak ada item valid dalam keranjang.');
    }

    if ($bayar < $total_harga) {
        throw new Exception('Jumlah bayar kurang dari total belanja.');
    }

    $kembalian = $bayar - $total_harga;
    $no_transaksi = generate_no_transaksi($pdo);

    $stmt_header = $pdo->prepare(
        "INSERT INTO penjualan (no_transaksi, tanggal, total_harga, bayar, kembalian, metode_bayar, kasir_id, keterangan, status)
         VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, 'selesai')"
    );
    $stmt_header->execute([
        $no_transaksi,
        $total_harga,
        $bayar,
        $kembalian,
        $metode_bayar,
        $_SESSION['user_id'],
        $keterangan,
    ]);
    $penjualan_id = $pdo->lastInsertId();

    $stmt_detail = $pdo->prepare(
        "INSERT INTO detail_penjualan (penjualan_id, menu_id, nama_menu, qty, harga_satuan, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    foreach ($detail_items as $d) {
        $stmt_detail->execute([
            $penjualan_id,
            $d['menu_id'],
            $d['nama_menu'],
            $d['qty'],
            $d['harga_satuan'],
            $d['subtotal'],
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'status'       => 'success',
        'no_transaksi' => $no_transaksi,
        'total'        => $total_harga,
        'kembalian'    => $kembalian,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
