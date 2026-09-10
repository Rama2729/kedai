<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'Edit Transaksi';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with_message('index.php', 'error', 'Transaksi tidak ditemukan.');
}

$stmt = $pdo->prepare("SELECT * FROM penjualan WHERE id = ?");
$stmt->execute([$id]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    redirect_with_message('index.php', 'error', 'Transaksi tidak ditemukan.');
}

if ($transaksi['status'] !== 'selesai') {
    redirect_with_message('index.php', 'error', 'Transaksi yang sudah dibatalkan tidak bisa diedit.');
}

$stmt_detail = $pdo->prepare("SELECT * FROM detail_penjualan WHERE penjualan_id = ?");
$stmt_detail->execute([$id]);
$detail_items = $stmt_detail->fetchAll();

$menu_list = $pdo->query("SELECT id, nama_menu, harga_jual FROM menu WHERE status = 'aktif' ORDER BY nama_menu")->fetchAll();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_ids     = $_POST['menu_id'] ?? [];
    $qtys         = $_POST['qty'] ?? [];
    $metode_bayar = $_POST['metode_bayar'] ?? 'tunai';
    $bayar        = (float) ($_POST['bayar'] ?? 0);
    $keterangan   = trim($_POST['keterangan'] ?? '');

    try {
        $pdo->beginTransaction();

        $stmt_menu = $pdo->prepare("SELECT id, nama_menu, harga_jual FROM menu WHERE id = ?");
        $new_total = 0;
        $new_items = [];

        foreach ($menu_ids as $i => $menu_id) {
            $menu_id = (int) $menu_id;
            $qty = (int) ($qtys[$i] ?? 0);
            if ($menu_id <= 0 || $qty <= 0) continue;

            $stmt_menu->execute([$menu_id]);
            $menu = $stmt_menu->fetch();
            if (!$menu) continue;

            $subtotal = $menu['harga_jual'] * $qty;
            $new_total += $subtotal;
            $new_items[] = [
                'menu_id' => $menu['id'],
                'nama_menu' => $menu['nama_menu'],
                'qty' => $qty,
                'harga_satuan' => $menu['harga_jual'],
                'subtotal' => $subtotal,
            ];
        }

        if (empty($new_items)) {
            throw new Exception('Transaksi harus memiliki minimal 1 item.');
        }

        if ($bayar < $new_total) {
            throw new Exception('Jumlah bayar kurang dari total belanja yang baru.');
        }

        $kembalian = $bayar - $new_total;

        $pdo->prepare("DELETE FROM detail_penjualan WHERE penjualan_id = ?")->execute([$id]);

        $stmt_insert = $pdo->prepare(
            "INSERT INTO detail_penjualan (penjualan_id, menu_id, nama_menu, qty, harga_satuan, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        foreach ($new_items as $d) {
            $stmt_insert->execute([$id, $d['menu_id'], $d['nama_menu'], $d['qty'], $d['harga_satuan'], $d['subtotal']]);
        }

        $stmt_update = $pdo->prepare(
            "UPDATE penjualan SET total_harga = ?, bayar = ?, kembalian = ?, metode_bayar = ?, keterangan = ? WHERE id = ?"
        );
        $stmt_update->execute([$new_total, $bayar, $kembalian, $metode_bayar, $keterangan, $id]);

        $pdo->commit();
        redirect_with_message('index.php', 'success', 'Transaksi ' . $transaksi['no_transaksi'] . ' berhasil diperbarui.');

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Transaksi: <?= htmlspecialchars($transaksi['no_transaksi']) ?></h1>
    <a href="index.php" class="btn btn-secondary btn-sm">&larr; Kembali ke Kasir</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="table-wrapper" style="padding:20px; max-width:700px;">
    <form method="POST" id="formEdit">
        <table style="margin-bottom:16px;">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th style="width:100px;">Qty</th>
                    <th style="width:40px;"></th>
                </tr>
            </thead>
            <tbody id="itemBody">
                <?php foreach ($detail_items as $idx => $item): ?>
                    <tr>
                        <td>
                            <select name="menu_id[]" class="form-group" style="width:100%; padding:8px;">
                                <?php foreach ($menu_list as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $item['menu_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['nama_menu']) ?> (<?= format_rupiah($m['harga_jual']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="qty[]" value="<?= $item['qty'] ?>" min="1" style="width:100%; padding:8px;"></td>
                        <td><button type="button" class="btn-hapus-item" style="border:none;background:none;color:var(--color-danger);cursor:pointer;">&times;</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="button" id="btnTambahItem" class="btn btn-sm btn-secondary" style="margin-bottom:20px;">+ Tambah Item</button>

        <div class="form-group">
            <label for="metode_bayar">Metode Bayar</label>
            <select name="metode_bayar" id="metode_bayar">
                <option value="tunai" <?= $transaksi['metode_bayar'] === 'tunai' ? 'selected' : '' ?>>Tunai</option>
                <option value="qris" <?= $transaksi['metode_bayar'] === 'qris' ? 'selected' : '' ?>>QRIS</option>
                <option value="transfer" <?= $transaksi['metode_bayar'] === 'transfer' ? 'selected' : '' ?>>Transfer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="bayar">Jumlah Bayar</label>
            <input type="number" name="bayar" id="bayar" value="<?= $transaksi['bayar'] ?>" min="0">
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <input type="text" name="keterangan" id="keterangan" value="<?= htmlspecialchars($transaksi['keterangan'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<template id="templateBaris">
    <tr>
        <td>
            <select name="menu_id[]" style="width:100%; padding:8px;">
                <?php foreach ($menu_list as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_menu']) ?> (<?= format_rupiah($m['harga_jual']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" name="qty[]" value="1" min="1" style="width:100%; padding:8px;"></td>
        <td><button type="button" class="btn-hapus-item" style="border:none;background:none;color:var(--color-danger);cursor:pointer;">&times;</button></td>
    </tr>
</template>

<script>
document.getElementById('btnTambahItem').addEventListener('click', function () {
    const template = document.getElementById('templateBaris');
    const clone = template.content.cloneNode(true);
    document.getElementById('itemBody').appendChild(clone);
});

document.getElementById('itemBody').addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-hapus-item')) {
        e.target.closest('tr').remove();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
