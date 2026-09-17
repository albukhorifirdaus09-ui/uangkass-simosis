<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

$month = max(1, min(12, (int) ($_GET['bulan'] ?? date('n'))));
$year = max(2020, (int) ($_GET['tahun'] ?? date('Y')));
$months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

$stmt = $pdo->prepare("SELECT COALESCE(NULLIF(TRIM(c.jurusan), ''), 'Belum diatur') AS jurusan, COALESCE(NULLIF(MAX(dep.singkatan), ''), COALESCE(NULLIF(MAX(c.jurusan), ''), 'Belum diatur')) AS singkatan, COUNT(DISTINCT c.id) AS total_kelas, COUNT(DISTINCT s.id) AS total_siswa, COUNT(DISTINCT CASE WHEN p.status = 'lunas' THEN s.id END) AS total_lunas, COALESCE(SUM(CASE WHEN p.status = 'lunas' THEN p.nominal ELSE 0 END), 0) AS total_masuk FROM classes c INNER JOIN students s ON s.kelas_id = c.id LEFT JOIN departments dep ON dep.nama_jurusan = c.jurusan LEFT JOIN student_dues d ON d.class_id = c.id AND d.jenis_kas = 'kelas' AND d.bulan = ? AND d.tahun = ? AND d.status = 'aktif' LEFT JOIN student_due_payments p ON p.student_due_id = d.id AND p.student_id = s.id GROUP BY c.jurusan ORDER BY jurusan");
$stmt->execute([$month, $year]);
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalJurusan = count($departments);
$totalKelas = array_sum(array_map(static fn($department) => (int) $department['total_kelas'], $departments));
$totalSiswa = array_sum(array_map(static fn($department) => (int) $department['total_siswa'], $departments));
$totalLunas = array_sum(array_map(static fn($department) => (int) $department['total_lunas'], $departments));
$totalMasuk = array_sum(array_map(static fn($department) => (float) $department['total_masuk'], $departments));
$persentaseLunas = $totalSiswa > 0 ? round(($totalLunas / $totalSiswa) * 100) : 0;
$pageTitle = 'Kas Per Jurusan/Kelas';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container cash-admin-page-modern">
        <section class="cash-admin-hero"><div><span class="cash-admin-kicker"><i class="bi bi-cash-stack"></i> Keuangan Kelas</span><h2>Kas Per Jurusan / Kelas</h2><p>Pantau rekap pembayaran kas siswa berdasarkan jurusan dan lanjutkan ke detail setiap kelas.</p></div><div class="cash-admin-period"><i class="bi bi-calendar3"></i><?= $months[$month] ?> <?= $year ?></div></section>

        <form class="cash-admin-filter" method="get"><div class="cash-filter-label"><i class="bi bi-funnel"></i><span>Periode rekap</span></div><select name="bulan" class="form-select" aria-label="Bulan"><?php foreach ($months as $number => $name): ?><option value="<?= $number ?>" <?= $month === $number ? 'selected' : '' ?>><?= $name ?></option><?php endforeach; ?></select><input name="tahun" class="form-control" type="number" min="2020" value="<?= $year ?>" aria-label="Tahun"><button class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Tampilkan</button></form>

        <section class="cash-admin-stat-grid"><article><span class="cash-admin-stat-icon blue"><i class="bi bi-building"></i></span><div><small>Jurusan</small><strong><?= $totalJurusan ?></strong><p>Jurusan dengan siswa</p></div></article><article><span class="cash-admin-stat-icon teal"><i class="bi bi-door-open"></i></span><div><small>Total kelas</small><strong><?= $totalKelas ?></strong><p>Kelas yang dipantau</p></div></article><article><span class="cash-admin-stat-icon green"><i class="bi bi-person-check"></i></span><div><small>Siswa lunas</small><strong><?= $totalLunas ?>/<?= $totalSiswa ?></strong><p><?= $persentaseLunas ?>% pembayaran tercatat</p></div></article><article><span class="cash-admin-stat-icon violet"><i class="bi bi-wallet2"></i></span><div><small>Kas masuk</small><strong>Rp <?= number_format($totalMasuk, 0, ',', '.') ?></strong><p>Untuk <?= $months[$month] ?> <?= $year ?></p></div></article></section>

        <section class="cash-admin-list-card"><div class="cash-admin-list-head"><div><span class="eyebrow">Rekap jurusan</span><h4>Pembayaran kas per jurusan</h4><p>Pilih jurusan untuk melihat rekap kelas dan status siswa.</p></div><div class="cash-admin-search"><i class="bi bi-search"></i><input type="search" id="cashDepartmentSearch" placeholder="Cari jurusan..."></div></div><div class="cash-admin-card-grid">
            <?php foreach ($departments as $department): $students = (int) $department['total_siswa']; $paid = (int) $department['total_lunas']; $percent = $students > 0 ? round(($paid / $students) * 100) : 0; ?><article class="cash-admin-department-card" data-search="<?= htmlspecialchars(strtolower($department['jurusan'] . ' ' . $department['singkatan'])) ?>"><div class="cash-admin-card-top"><span class="cash-admin-department-logo"><?= htmlspecialchars($department['singkatan']) ?></span><span class="cash-admin-percent <?= $percent === 100 ? 'complete' : '' ?>"><?= $percent ?>%</span></div><h5><?= htmlspecialchars($department['jurusan']) ?></h5><p><i class="bi bi-door-open"></i><?= (int) $department['total_kelas'] ?> kelas <span>•</span><i class="bi bi-people"></i><?= $students ?> siswa</p><div class="cash-admin-progress"><i style="width: <?= $percent ?>%"></i></div><div class="cash-admin-card-details"><span><strong><?= $paid ?></strong> sudah bayar</span><span><strong>Rp <?= number_format((float) $department['total_masuk'], 0, ',', '.') ?></strong> kas masuk</span></div><a href="jurusan.php?jurusan=<?= urlencode($department['jurusan']) ?>&bulan=<?= $month ?>&tahun=<?= $year ?>" class="cash-admin-detail-button">Lihat rekap kelas <i class="bi bi-arrow-right"></i></a></article><?php endforeach; ?>
            <?php if (!$departments): ?><div class="cash-admin-empty"><i class="bi bi-building-add"></i><strong>Belum ada jurusan dengan siswa</strong><span>Data jurusan akan muncul setelah kelas dan siswa dibuat.</span></div><?php endif; ?><div id="cashDepartmentNotFound" class="cash-admin-empty d-none"><i class="bi bi-search"></i><strong>Jurusan tidak ditemukan</strong><span>Coba kata kunci lain.</span></div>
        </div></section>
    </section>
</main>
<script>document.getElementById('cashDepartmentSearch')?.addEventListener('input', function () { const keyword = this.value.trim().toLowerCase(); const cards = Array.from(document.querySelectorAll('.cash-admin-department-card')); let found = 0; cards.forEach(card => { const visible = card.dataset.search.includes(keyword); card.classList.toggle('d-none', !visible); if (visible) found++; }); document.getElementById('cashDepartmentNotFound')?.classList.toggle('d-none', found !== 0 || keyword === ''); });</script>
<?php require_once '../../includes/footer.php'; ?>
