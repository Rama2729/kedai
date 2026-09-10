<header class="navbar">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Buka menu">☰</button>

    <div class="navbar-right">
        <span class="navbar-datetime" id="navbarDatetime"></span>
        <div class="navbar-user">
            <span class="user-name"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?></span>
            <span class="user-role">(<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)</span>
            <a href="<?= BASE_URL ?>auth/logout.php" class="btn-logout" onclick="return confirm('Yakin ingin logout?')">Logout</a>
        </div>
    </div>
</header>
