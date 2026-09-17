<?php
$currentPath = $_SERVER['PHP_SELF'] ?? '';
$adminName = $_SESSION['nama'] ?? $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Administrator';
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar admin-sidebar" id="sidebar">
    <div class="sidebar-brand admin-sidebar-brand">
        <div class="admin-brand-logo"><img src="/simosis/img/logo%20simosi.jpeg" alt="Logo SIMOSIS"></div>
        <div class="brand-text"><h5>SIMOSIS</h5><span>Administrasi Sekolah</span></div>
    </div>

    <div class="admin-sidebar-user">
        <span class="admin-user-avatar"><?= htmlspecialchars(strtoupper(substr($adminName, 0, 1))) ?></span>
        <div><strong><?= htmlspecialchars($adminName) ?></strong><small><i class="bi bi-shield-check"></i> Administrator</small></div>
    </div>

    <nav class="sidebar-menu admin-sidebar-menu">
        <div class="menu-title">Utama</div>
        <a href="/simosis/admin/dashboard.php" class="menu-item <?= $currentPath === '/simosis/admin/dashboard.php' ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>

        <div class="menu-title">Data Sekolah</div>
        <a href="/simosis/admin/siswa/index.php" class="menu-item <?= strpos($currentPath, '/admin/siswa/') !== false ? 'active' : '' ?>"><i class="bi bi-people-fill"></i><span>Data Siswa</span></a>
        <a href="/simosis/admin/kenaikan_kelas/index.php" class="menu-item <?= strpos($currentPath, '/admin/kenaikan_kelas/') !== false ? 'active' : '' ?>"><i class="bi bi-arrow-up-right-circle-fill"></i><span>Kenaikan Kelas</span></a>
        <a href="/simosis/admin/jurusan/index.php" class="menu-item <?= strpos($currentPath, '/admin/jurusan/') !== false ? 'active' : '' ?>"><i class="bi bi-building"></i><span>Data Jurusan</span></a>
        <a href="/simosis/admin/pengurus_kelas/index.php" class="menu-item <?= strpos($currentPath, '/admin/pengurus_kelas/') !== false ? 'active' : '' ?>"><i class="bi bi-person-workspace"></i><span>Bendahara Kelas</span></a>
        <a href="/simosis/admin/tahun_ajaran/index.php" class="menu-item <?= strpos($currentPath, '/admin/tahun_ajaran/') !== false ? 'active' : '' ?>"><i class="bi bi-calendar-event"></i><span>Tahun Ajaran</span></a>

        <div class="menu-title">Organisasi OSIS</div>
        <a href="/simosis/admin/osis/index.php" class="menu-item <?= strpos($currentPath, '/admin/osis/') !== false ? 'active' : '' ?>"><i class="bi bi-person-badge-fill"></i><span>Anggota OSIS</span></a>
        <a href="/simosis/admin/osis/riwayat.php" class="menu-item <?= $currentPath === '/simosis/admin/osis/riwayat.php' ? 'active' : '' ?>"><i class="bi bi-clock-history"></i><span>Riwayat Anggota OSIS</span></a>
        <a href="/simosis/admin/jabatan_osis/index.php" class="menu-item <?= strpos($currentPath, '/admin/jabatan_osis/') !== false ? 'active' : '' ?>"><i class="bi bi-award-fill"></i><span>Jabatan OSIS</span></a>

        <div class="menu-title">Keuangan</div>
        <a href="/simosis/admin/keuangan/index.php" class="menu-item <?= strpos($currentPath, '/admin/keuangan/') !== false ? 'active' : '' ?>"><i class="bi bi-wallet2"></i><span>Kas OSIS</span></a>
        <a href="/simosis/admin/kas_siswa/index.php" class="menu-item <?= strpos($currentPath, '/admin/kas_siswa/') !== false ? 'active' : '' ?>"><i class="bi bi-cash-stack"></i><span>Kas Per Jurusan/Kelas</span></a>

        <div class="admin-sidebar-divider"></div>
        <a href="/simosis/auth/logout.php" class="menu-item logout-item"><i class="bi bi-box-arrow-right"></i><span>Keluar dari akun</span></a>
    </nav>

    <div class="sidebar-footer admin-sidebar-footer"><span class="footer-status"></span>Sistem aktif <small>SIMOSIS © 2026</small></div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const menu = document.querySelector('.admin-sidebar-menu');
    const storageKey = 'simosis.adminSidebar.scrollTop';

    if (!menu) return;

    const savedPosition = sessionStorage.getItem(storageKey);
    if (savedPosition !== null) {
        menu.scrollTop = Number(savedPosition) || 0;
    }

    menu.querySelectorAll('a[href]').forEach(function (link) {
        link.addEventListener('click', function () {
            sessionStorage.setItem(storageKey, String(menu.scrollTop));
        });
    });
});
</script>
