<?php
$osisPath = $_SERVER['PHP_SELF'] ?? '';
$jabatanLogin = strtolower($_SESSION['jabatan'] ?? '');
$isTreasurer = $jabatanLogin === 'bendahara';
$dashboardUrl = $isTreasurer ? '/simosis/osis/bendahara/dashboard.php' : '/simosis/osis/anggota/dashboard.php';
$dashboardActive = strpos($osisPath, $dashboardUrl) !== false;
$osisName = $_SESSION['nama'] ?? $_SESSION['nama_lengkap'] ?? '';
$osisUserId = (int) ($_SESSION['user_id'] ?? 0);

/* Nama profil di sidebar memakai nama lengkap siswa, bukan username akun. */
if ($osisName === '' && isset($pdo) && $osisUserId > 0) {
    $nameStmt = $pdo->prepare('SELECT nama_lengkap FROM students WHERE user_id = ? LIMIT 1');
    $nameStmt->execute([$osisUserId]);
    $osisName = (string) ($nameStmt->fetchColumn() ?: 'Anggota OSIS');
}

$osisName = $osisName !== '' ? $osisName : 'Anggota OSIS';
$osisRole = $_SESSION['jabatan'] ?? 'Anggota OSIS';
?>
<div class="sidebar-overlay" id="osisSidebarOverlay"></div>

<aside class="sidebar osis-sidebar osis-sidebar-modern" id="osisSidebar">
    <div class="sidebar-brand osis-sidebar-brand">
        <div class="osis-brand-logo"><img src="/simosis/img/logo%20simosi.jpeg" alt="Logo SIMOSIS"></div>
        <div class="brand-text"><h5>SIMOSIS</h5><span>Panel Organisasi OSIS</span></div>
    </div>

    <div class="osis-sidebar-user">
        <span class="osis-user-avatar"><?= htmlspecialchars(strtoupper(substr($osisName, 0, 1))) ?></span>
        <div><strong><?= htmlspecialchars($osisName) ?></strong><small><i class="bi bi-patch-check-fill"></i> <?= htmlspecialchars($osisRole) ?></small></div>
    </div>

    <nav class="sidebar-menu osis-sidebar-menu">
        <div class="menu-title">Utama</div>
        <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="menu-item <?= $dashboardActive ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>

        <?php if ($isTreasurer): ?>
            <div class="menu-title">Kas OSIS</div>
            <a href="/simosis/osis/bendahara/uang_kas.php" class="menu-item <?= strpos($osisPath, '/osis/bendahara/uang_kas.php') !== false ? 'active' : '' ?>"><i class="bi bi-cash-stack"></i><span>Uang Kas OSIS</span></a>
            <a href="/simosis/osis/bendahara/pengaturan_kas.php" class="menu-item <?= strpos($osisPath, '/osis/bendahara/pengaturan_kas.php') !== false ? 'active' : '' ?>"><i class="bi bi-sliders"></i><span>Pengaturan Kas</span></a>
            <a href="/simosis/osis/bendahara/transaksi.php" class="menu-item <?= strpos($osisPath, '/osis/bendahara/transaksi.php') !== false ? 'active' : '' ?>"><i class="bi bi-arrow-left-right"></i><span>Transaksi Kas</span></a>
            <a href="/simosis/osis/bendahara/setoran.php" class="menu-item <?= strpos($osisPath, '/osis/bendahara/setoran.php') !== false ? 'active' : '' ?>"><i class="bi bi-inbox-fill"></i><span>Setoran Kelas</span></a>

            <div class="menu-title">Data OSIS</div>
            <a href="/simosis/osis/bendahara/anggota.php" class="menu-item <?= strpos($osisPath, '/osis/bendahara/anggota.php') !== false ? 'active' : '' ?>"><i class="bi bi-people-fill"></i><span>Anggota OSIS</span></a>
            <a href="/simosis/osis/bendahara/kelas.php" class="menu-item <?= (strpos($osisPath, '/osis/bendahara/kelas.php') !== false || strpos($osisPath, '/osis/bendahara/jurusan.php') !== false) ? 'active' : '' ?>"><i class="bi bi-building"></i><span>Kas Per Jurusan</span></a>
        <?php else: ?>
            <div class="menu-title">Organisasi</div>
            <a href="/simosis/osis/anggota/dashboard.php" class="menu-item <?= strpos($osisPath, '/osis/anggota/dashboard.php') !== false ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
            <a href="/simosis/osis/anggota/agenda.php" class="menu-item <?= strpos($osisPath, '/osis/anggota/agenda.php') !== false ? 'active' : '' ?>"><i class="bi bi-calendar-event-fill"></i><span>Agenda Kegiatan</span></a>
            <a href="/simosis/osis/anggota/pengumuman.php" class="menu-item <?= strpos($osisPath, '/osis/anggota/pengumuman.php') !== false ? 'active' : '' ?>"><i class="bi bi-megaphone-fill"></i><span>Pengumuman</span></a>
            <a href="/simosis/osis/anggota/profil.php" class="menu-item <?= strpos($osisPath, '/osis/anggota/profil.php') !== false ? 'active' : '' ?>"><i class="bi bi-person-vcard-fill"></i><span>Profil Keanggotaan</span></a>
        <?php endif; ?>

        <div class="osis-sidebar-divider"></div>
        <a href="/simosis/auth/logout.php" class="menu-item logout-item"><i class="bi bi-box-arrow-right"></i><span>Keluar dari akun</span></a>
    </nav>

    <div class="sidebar-footer osis-sidebar-footer"><span class="footer-status"></span>Sistem aktif <small>SIMOSIS © 2026</small></div>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const menu = document.querySelector('.osis-sidebar-menu');
    const storageKey = 'simosis.osisSidebar.scrollTop';
    if (!menu) return;
    const savedPosition = sessionStorage.getItem(storageKey);
    if (savedPosition !== null) menu.scrollTop = Number(savedPosition) || 0;
    menu.querySelectorAll('a[href]').forEach(function (link) {
        link.addEventListener('click', function () { sessionStorage.setItem(storageKey, String(menu.scrollTop)); });
    });
});
</script>
