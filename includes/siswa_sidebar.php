<?php
$siswaPath = $_SERVER['PHP_SELF'] ?? '';
$dashboardActive = strpos($siswaPath, '/simosis/siswa/dashboard.php') !== false;

$siswaName = $_SESSION['nama'] ?? $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Siswa';
?>
<div class="sidebar-overlay" id="osisSidebarOverlay"></div>

<aside class="sidebar osis-sidebar osis-sidebar-modern" id="osisSidebar">
    <div class="sidebar-brand osis-sidebar-brand">
        <div class="osis-brand-logo"><img src="/simosis/img/logo%20simosi.jpeg" alt="Logo SIMOSIS"></div>
        <div class="brand-text"><h5>SIMOSIS</h5><span>Portal Siswa</span></div>
    </div>

    <div class="osis-sidebar-user">
        <span class="osis-user-avatar"><?= htmlspecialchars(strtoupper(substr($siswaName, 0, 1))) ?></span>
        <div><strong><?= htmlspecialchars($siswaName) ?></strong><small><i class="bi bi-patch-check-fill"></i> Siswa</small></div>
    </div>

    <nav class="sidebar-menu osis-sidebar-menu">
        <div class="menu-title">Utama</div>
        <a href="/simosis/siswa/dashboard.php" class="menu-item <?= $dashboardActive ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>

        <div class="osis-sidebar-divider"></div>
        <a href="/simosis/auth/logout.php" class="menu-item logout-item"><i class="bi bi-box-arrow-right"></i><span>Keluar dari akun</span></a>
    </nav>

    <div class="sidebar-footer osis-sidebar-footer"><span class="footer-status"></span>Sistem aktif <small>SIMOSIS © 2026</small></div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const menu = document.querySelector('.osis-sidebar-menu');
    const storageKey = 'simosis.siswaSidebar.scrollTop';
    if (menu) {
        const savedPosition = sessionStorage.getItem(storageKey);
        if (savedPosition !== null) menu.scrollTop = Number(savedPosition) || 0;
        menu.querySelectorAll('a[href]').forEach(function (link) {
            link.addEventListener('click', function () { sessionStorage.setItem(storageKey, String(menu.scrollTop)); });
        });
    }

    const sidebar = document.getElementById('osisSidebar');
    const toggle = document.getElementById('osisMenuToggle');
    const overlay = document.getElementById('osisSidebarOverlay');

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (sidebar) sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            if (sidebar) sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    document.querySelectorAll('.osis-sidebar-menu a').forEach(function (menuItem) {
        menuItem.addEventListener('click', function () {
            if (window.innerWidth <= 991) {
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
            }
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
            if (sidebar) sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
        }
    });
});
</script>
