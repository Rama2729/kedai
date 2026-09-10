<?php
$current_folder = basename(dirname($_SERVER['SCRIPT_NAME']));

function is_active($folder, $current) {
    return $folder === $current ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">🥛</span>
        <span class="brand-text"><?= APP_SHORT_NAME ?></span>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>dashboard/index.php" class="nav-item <?= is_active('dashboard', $current_folder) ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="<?= BASE_URL ?>penjualan/index.php" class="nav-item <?= is_active('penjualan', $current_folder) ?>">
            <span class="nav-icon">🛒</span> Kasir / Penjualan
        </a>
        <a href="<?= BASE_URL ?>pengeluaran/index.php" class="nav-item <?= is_active('pengeluaran', $current_folder) ?>">
            <span class="nav-icon">💸</span> Pengeluaran
        </a>
        <a href="<?= BASE_URL ?>profit/index.php" class="nav-item <?= is_active('profit', $current_folder) ?>">
            <span class="nav-icon">📈</span> Omset &amp; Profit
        </a>

        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <div class="nav-divider">Master Data</div>
            <a href="<?= BASE_URL ?>menu/index.php" class="nav-item <?= is_active('menu', $current_folder) ?>">
                <span class="nav-icon">🍽️</span> Menu
            </a>
            <a href="<?= BASE_URL ?>bahan/index.php" class="nav-item <?= is_active('bahan', $current_folder) ?>">
                <span class="nav-icon">🧂</span> Bahan Baku
            </a>

            <div class="nav-divider">Laporan</div>
            <a href="<?= BASE_URL ?>laporan/harian.php" class="nav-item <?= is_active('laporan', $current_folder) ?>">
                <span class="nav-icon">🧾</span> Laporan Excel
            </a>
        <?php endif; ?>
    </nav>
</aside>
