<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isDashboard = $currentPage === 'dashboard.php';
$isSiswa = in_array($currentPage, ['siswa.php', 'siswa_tambah.php'], true);
$isUangKas = in_array($currentPage, ['uang_kas.php', 'pembayaran.php'], true);
$isSetoran = $currentPage === 'konfirmasi.php';
$namaKelas = $officer['nama_kelas'] ?? 'Kelas';
$namaBendahara = $officer['nama_lengkap'] ?? 'Bendahara';
$jurusanSingkatan = '';

if (isset($pdo) && !empty($officer['jurusan'])) {
    $departmentStmt = $pdo->prepare('SELECT singkatan FROM departments WHERE nama_jurusan = ? LIMIT 1');
    $departmentStmt->execute([$officer['jurusan']]);
    $jurusanSingkatan = (string) ($departmentStmt->fetchColumn() ?: '');
}

$labelKelas = trim($namaKelas . ($jurusanSingkatan !== '' ? ' · ' . $jurusanSingkatan : ''));
?>
<div class="class-sidebar-overlay" id="classSidebarOverlay"></div>

<aside class="class-sidebar class-sidebar-modern" id="classSidebar">
    <div class="class-brand class-sidebar-brand-modern">
        <div class="class-brand-logo"><img src="/simosis/img/logo%20simosi.jpeg" alt="Logo SIMOSIS"></div>
        <div class="class-brand-text"><strong>SIMOSIS</strong><small>Bendahara Kelas</small></div>
    </div>

    <div class="class-sidebar-user-modern">
        <span class="class-user-avatar-modern"><?= htmlspecialchars(strtoupper(substr($namaBendahara, 0, 1))) ?></span>
        <div><strong><?= htmlspecialchars($namaBendahara) ?></strong><small><i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($labelKelas) ?></small></div>
    </div>

    <nav class="class-sidebar-menu class-sidebar-menu-modern">
        <div class="class-menu-title">Utama</div>
        <a href="dashboard.php" class="class-menu <?= $isDashboard ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>

        <div class="class-menu-title">Administrasi Kelas</div>
        <a href="siswa.php" class="class-menu <?= $isSiswa ? 'active' : '' ?>"><i class="bi bi-people-fill"></i><span>Data Siswa</span></a>

        <div class="class-menu-title">Keuangan</div>
        <a href="uang_kas.php" class="class-menu <?= $isUangKas ? 'active' : '' ?>"><i class="bi bi-cash-stack"></i><span>Uang Kas Kelas</span></a>
        <a href="konfirmasi.php" class="class-menu <?= $isSetoran ? 'active' : '' ?>"><i class="bi bi-send-check-fill"></i><span>Setoran OSIS</span></a>

        <div class="class-sidebar-divider-modern"></div>
        <a href="../auth/logout.php" class="class-menu class-menu-logout"><i class="bi bi-box-arrow-right"></i><span>Keluar dari akun</span></a>
    </nav>

    <div class="class-sidebar-footer class-sidebar-footer-modern"><span class="footer-status"></span>Sistem aktif <small><?= htmlspecialchars($namaKelas) ?> · SIMOSIS © 2026</small></div>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const menu = document.querySelector('.class-sidebar-menu-modern');
    const storageKey = 'simosis.classSidebar.scrollTop';
    if (!menu) return;
    const savedPosition = sessionStorage.getItem(storageKey);
    if (savedPosition !== null) menu.scrollTop = Number(savedPosition) || 0;
    menu.querySelectorAll('a[href]').forEach(function (link) {
        link.addEventListener('click', function () { sessionStorage.setItem(storageKey, String(menu.scrollTop)); });
    });
});
</script>
