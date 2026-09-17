<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireOsisTreasurer();

$month = max(1, min(12, (int) ($_GET['bulan'] ?? date('n'))));
$year = max(2020, (int) ($_GET['tahun'] ?? date('Y')));
$months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

$dueStmt = $pdo->prepare("
    SELECT
        id,
        nominal,
        frekuensi,
        minggu_ke
    FROM student_dues
    WHERE bulan = ?
      AND tahun = ?
      AND jenis_kas = 'kelas'
      AND status = 'aktif'
    ORDER BY
        minggu_ke ASC
");

$dueStmt->execute([
    $month,
    $year
]);

$dues = $dueStmt->fetchAll(PDO::FETCH_ASSOC);

/* Samakan sumber dan nama jurusan dengan halaman Admin. */
$cards = $pdo->query("
    SELECT
        COALESCE(NULLIF(TRIM(c.jurusan), ''), 'Belum diatur') AS jurusan,
        COALESCE(NULLIF(MAX(d.singkatan), ''), COALESCE(NULLIF(MAX(c.jurusan), ''), 'Belum diatur')) AS singkatan,
        COUNT(DISTINCT c.id) AS kelas
    FROM classes c
    INNER JOIN students s
        ON s.kelas_id = c.id
    LEFT JOIN departments d
        ON d.nama_jurusan = c.jurusan
    GROUP BY c.jurusan
    ORDER BY jurusan ASC
")->fetchAll(PDO::FETCH_ASSOC);
$totalKelas = array_sum(array_map(static fn($card) => (int) $card['kelas'], $cards));

$pageTitle = 'Kas Per Jurusan';
require_once '../../includes/header.php';
require_once '../../includes/osis_sidebar.php';
?>
<div class="osis-main-content">
<header class="osis-topbar"><button class="btn btn-light d-lg-none" id="osisMenuToggle"><i class="bi bi-list"></i></button><div><strong>Kas Per Jurusan</strong><small class="d-block text-muted">Rekap pembayaran tiap jurusan</small></div><span class="text-muted small"><?= htmlspecialchars($_SESSION['username']) ?></span></header>
<main class="role-page osis-department-page"><div class="role-wrap">
<div class="role-hero osis-department-hero"><div><span class="text-uppercase small">Kas kelas</span><h2>Kas Per Jurusan</h2><p class="mb-0">Pilih jurusan untuk melihat rekap pembayaran kelas X, XI, dan XII.</p></div><div class="osis-department-period"><i class="bi bi-calendar3"></i><span><?= $months[$month] ?> <?= $year ?></span></div></div>
<form class="payment-filter role-card osis-department-filter" method="get"><div class="osis-department-filter-label"><i class="bi bi-funnel"></i><span>Periode rekap</span></div><div class="row g-2 align-items-end"><div class="col-sm-5"><label class="form-label">Bulan</label><select name="bulan" class="form-select"><?php foreach ($months as $number => $name): ?><option value="<?= $number ?>" <?= $month === $number ? 'selected' : '' ?>><?= $name ?></option><?php endforeach; ?></select></div><div class="col-sm-5"><label class="form-label">Tahun</label><input name="tahun" class="form-control" type="number" min="2020" value="<?= $year ?>"></div><div class="col-sm-2"><button class="btn btn-primary w-100"><i class="bi bi-arrow-repeat"></i> Tampilkan</button></div></div></form>
<section class="osis-department-stat-grid"><article class="primary"><span><i class="bi bi-building"></i></span><small>Jurusan tersedia</small><strong><?= count($cards) ?></strong><p>jurusan memiliki data siswa</p></article><article><span class="blue"><i class="bi bi-door-open-fill"></i></span><small>Total kelas</small><strong><?= $totalKelas ?></strong><p>kelas dapat direkap</p></article><article><span class="teal"><i class="bi bi-calendar-check"></i></span><small>Periode aktif</small><strong><?= htmlspecialchars($months[$month]) ?></strong><p>Tahun <?= $year ?></p></article></section>
<section class="osis-department-list-card"><div class="osis-department-list-head"><div><span><i class="bi bi-grid-3x3-gap"></i> Rekap jurusan</span><h4>Pilih jurusan</h4><p>Klik kartu jurusan untuk melihat detail rekap kelas.</p></div><div class="osis-department-search"><i class="bi bi-search"></i><input type="search" id="osisDepartmentSearch" placeholder="Cari jurusan..."></div></div><div class="cash-month-grid department-grid osis-department-grid">
<?php foreach ($cards as $card): ?><article class="cash-month-card department-card osis-department-card" data-search="<?= htmlspecialchars(strtolower($card['jurusan'] . ' ' . $card['singkatan']), ENT_QUOTES, 'UTF-8') ?>"><div class="cash-month-head"><div><span class="eyebrow">Jurusan SMK</span><h3><?= htmlspecialchars($card['singkatan']) ?></h3></div><span class="cash-month-icon"><i class="bi bi-mortarboard-fill"></i></span></div><h5><?= htmlspecialchars($card['jurusan']) ?></h5><p class="cash-rate"><?= $card['kelas'] ?> <span>kelas tersedia</span></p><a class="cash-detail-btn" href="jurusan.php?jurusan=<?= urlencode($card['jurusan']) ?>&bulan=<?= $month ?>&tahun=<?= $year ?>"><i class="bi bi-list-ul"></i><span>Lihat Rekap Kelas</span><i class="bi bi-arrow-right"></i></a></article><?php endforeach; ?>
<?php if (!$cards): ?><div class="empty-state"><i class="bi bi-mortarboard"></i><h4>Belum ada jurusan</h4><p>Data jurusan mengikuti kolom jurusan pada tabel kelas.</p></div><?php endif; ?></div>
</section>
</div></main></div>
<script>document.addEventListener('DOMContentLoaded', function () { const search = document.getElementById('osisDepartmentSearch'); if (!search) return; search.addEventListener('input', function () { const keyword = this.value.toLowerCase().trim(); document.querySelectorAll('.osis-department-card').forEach(function (card) { card.style.display = card.dataset.search.includes(keyword) ? '' : 'none'; }); }); });</script>
<?php require_once '../../includes/footer.php'; ?>
