<?php
require_once '../config/auth.php';
require_once '../config/database.php';

$officer = requireActiveClassOfficer($pdo);
$classId = (int) $officer['class_id'];
$year = (int) date('Y');
$month = (int) date('n');
$monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
$stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE kelas_id = ?');
$stmt->execute([$classId]);
$totalSiswa = (int) $stmt->fetchColumn();

$settingStmt = $pdo->prepare("SELECT * FROM cash_settings WHERE jenis_kas = 'kelas' AND class_id = ? AND tahun = ? AND status = 'aktif' ORDER BY id DESC LIMIT 1");
$settingStmt->execute([$classId, $year]);
$setting = $settingStmt->fetch(PDO::FETCH_ASSOC);
$settingId = (int) ($setting['id'] ?? 0);
$frekuensi = $setting['frekuensi'] ?? null;
$nominalKas = (float) ($setting['nominal'] ?? 0);

$currentDues = [];
$totalKasTerkumpul = 0.0;
$totalTarget = 0.0;
$paidCurrent = 0;
$recentPayments = [];
$chartRows = [];
$totalPeriods = 0;

if ($settingId > 0) {
    $dueStmt = $pdo->prepare("SELECT id, nominal, frekuensi, bulan, minggu_ke, tanggal_mulai, tanggal_selesai FROM student_dues WHERE setting_id = ? AND jenis_kas = 'kelas' AND class_id = ? AND tahun = ? AND status = 'aktif' ORDER BY bulan, minggu_ke, id");
    $dueStmt->execute([$settingId, $classId, $year]);
    $allDues = $dueStmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPeriods = count($allDues);
    foreach ($allDues as $due) {
        if ($due['frekuensi'] === 'bulanan' && (int) $due['bulan'] === $month) $currentDues[] = $due;
        if ($due['frekuensi'] === 'mingguan' && !empty($due['tanggal_mulai']) && !empty($due['tanggal_selesai']) && date('Y-m-d') >= $due['tanggal_mulai'] && date('Y-m-d') <= $due['tanggal_selesai']) $currentDues[] = $due;
    }
    if (!$currentDues && $frekuensi === 'mingguan') {
        foreach ($allDues as $due) if ((int) $due['bulan'] === $month) { $currentDues[] = $due; break; }
    }

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(p.nominal), 0) FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id WHERE d.setting_id = ? AND d.jenis_kas = 'kelas' AND d.class_id = ? AND d.tahun = ? AND p.status = 'lunas'");
    $stmt->execute([$settingId, $classId, $year]);
    $totalKasTerkumpul = (float) $stmt->fetchColumn();
    $totalTarget = array_sum(array_map(static fn($due) => (float) $due['nominal'], $allDues)) * $totalSiswa;

    if ($currentDues) {
        $dueIds = array_column($currentDues, 'id');
        $placeholders = implode(',', array_fill(0, count($dueIds), '?'));
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT p.student_id) FROM student_due_payments p WHERE p.student_due_id IN ($placeholders) AND p.status = 'lunas'");
        $stmt->execute($dueIds);
        $paidCurrent = (int) $stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("SELECT p.tanggal_bayar, p.nominal, s.nama_lengkap FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id INNER JOIN students s ON s.id = p.student_id WHERE d.setting_id = ? AND d.jenis_kas = 'kelas' AND d.class_id = ? AND d.tahun = ? AND p.status = 'lunas' ORDER BY p.tanggal_bayar DESC, p.id DESC LIMIT 6");
    $stmt->execute([$settingId, $classId, $year]);
    $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT DATE_FORMAT(p.tanggal_bayar, '%b') AS label, SUM(p.nominal) AS nominal FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id WHERE d.setting_id = ? AND d.jenis_kas = 'kelas' AND d.class_id = ? AND d.tahun = ? AND p.status = 'lunas' AND p.tanggal_bayar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY YEAR(p.tanggal_bayar), MONTH(p.tanggal_bayar), DATE_FORMAT(p.tanggal_bayar, '%b') ORDER BY YEAR(p.tanggal_bayar), MONTH(p.tanggal_bayar)");
    $stmt->execute([$settingId, $classId, $year]);
    $chartRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$unpaidCurrent = max(0, $totalSiswa - $paidCurrent);
$paymentPercent = $totalSiswa > 0 ? round(($paidCurrent / $totalSiswa) * 100) : 0;
$collectionPercent = $totalTarget > 0 ? min(100, round(($totalKasTerkumpul / $totalTarget) * 100)) : 0;
$currentPeriodLabel = $frekuensi === 'mingguan' ? 'Per minggu' : ($frekuensi === 'bulanan' ? 'Per bulan' : 'Belum diatur');
$confirmationStmt = $pdo->prepare("SELECT status, submitted_at FROM class_due_confirmations WHERE class_id = ? ORDER BY submitted_at DESC, id DESC LIMIT 1");
$confirmationStmt->execute([$classId]);
$lastConfirmation = $confirmationStmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Dashboard Bendahara Kelas';
require_once '../includes/header.php';
require_once '../includes/class_sidebar.php';
?>
<main class="class-main">
    <header class="class-topbar"><div class="class-topbar-left"><button type="button" class="class-menu-toggle" id="classMenuToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button><div><h5 class="mb-0">Dashboard</h5><small>Bendahara Kelas</small></div></div><div class="class-user"><div class="class-user-avatar"><i class="bi bi-person-fill"></i></div><div><strong><?= htmlspecialchars($officer['nama_lengkap']) ?></strong><small>Bendahara Kelas</small></div></div></header>
    <section class="role-page">
        <div class="role-wrap class-dashboard-modern">
            <section class="class-dashboard-hero"><div><span class="class-dashboard-kicker"><i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($labelKelas ?? $officer['nama_kelas']) ?></span><h2>Kas kelas lebih teratur.</h2><p>Pantau pembayaran siswa, progres kas, dan setoran kelas dalam satu tempat.</p></div><div class="class-dashboard-hero-side"><span><i class="bi bi-calendar3"></i> <?= $monthNames[$month] ?> <?= $year ?></span><div><a href="uang_kas.php" class="btn btn-light"><i class="bi bi-table"></i> Uang Kas</a><a href="konfirmasi.php" class="btn btn-warning"><i class="bi bi-send"></i> Setoran OSIS</a></div></div></section>

            <?php if (!$setting): ?><div class="alert alert-warning class-setting-alert"><i class="bi bi-exclamation-triangle-fill"></i><div><strong>Pengaturan kas belum tersedia.</strong><br><span>Bendahara OSIS belum mengatur kas kelas untuk tahun <?= $year ?>.</span></div></div><?php endif; ?>

            <section class="class-stat-grid-modern">
                <article class="class-stat-modern class-stat-cash"><div class="class-stat-icon"><i class="bi bi-wallet2"></i></div><span>Kas terkumpul</span><strong>Rp <?= number_format($totalKasTerkumpul, 0, ',', '.') ?></strong><small><?= $collectionPercent ?>% dari target kas tahun ini</small></article>
                <article class="class-stat-modern"><div class="class-stat-icon icon-blue"><i class="bi bi-people"></i></div><span>Total siswa</span><strong><?= $totalSiswa ?> siswa</strong><small>Anggota kelas <?= htmlspecialchars($officer['nama_kelas']) ?></small></article>
                <article class="class-stat-modern"><div class="class-stat-icon icon-green"><i class="bi bi-check2-circle"></i></div><span>Sudah bayar periode ini</span><strong><?= $paidCurrent ?> siswa</strong><small><?= $paymentPercent ?>% pembayaran tercatat</small></article>
                <article class="class-stat-modern"><div class="class-stat-icon icon-orange"><i class="bi bi-hourglass-split"></i></div><span>Belum bayar periode ini</span><strong><?= $unpaidCurrent ?> siswa</strong><small>Periode <?= strtolower($currentPeriodLabel) ?></small></article>
            </section>

            <section class="class-dashboard-insights">
                <article class="class-payment-progress-card"><div class="class-card-head"><div><span>Status pembayaran</span><h4>Periode kas berjalan</h4></div><span class="class-period-badge"><?= $currentPeriodLabel ?></span></div><div class="class-progress-body"><div class="class-progress-circle" style="--progress: <?= $paymentPercent ?>"><div><strong><?= $paymentPercent ?>%</strong><span>Lunas</span></div></div><div class="class-progress-copy"><strong><?= $paidCurrent ?> dari <?= $totalSiswa ?> siswa</strong><p>telah membayar kas pada periode berjalan.</p><div class="class-modern-progress"><i style="width: <?= $paymentPercent ?>%"></i></div><a href="uang_kas.php" class="class-dashboard-link">Catat pembayaran <i class="bi bi-arrow-right"></i></a></div></div></article>
                <article class="class-chart-card"><div class="class-card-head"><div><span>Perkembangan kas</span><h4>Pembayaran 6 bulan terakhir</h4></div><i class="bi bi-bar-chart-line"></i></div><div class="class-chart-wrap"><canvas id="classFinanceChart"></canvas></div></article>
            </section>

            <section class="class-dashboard-bottom-grid">
                <article class="class-recent-card"><div class="class-card-head"><div><span>Pembayaran terbaru</span><h4>Kas yang baru tercatat</h4></div><a href="uang_kas.php" class="class-dashboard-link">Lihat semua <i class="bi bi-arrow-right"></i></a></div><div class="class-recent-list"><?php foreach ($recentPayments as $payment): ?><div><span class="class-initial"><?= htmlspecialchars(strtoupper(substr($payment['nama_lengkap'], 0, 1))) ?></span><p><strong><?= htmlspecialchars($payment['nama_lengkap']) ?></strong><small><?= date('d M Y', strtotime($payment['tanggal_bayar'])) ?></small></p><b>+ Rp <?= number_format((float) $payment['nominal'], 0, ',', '.') ?></b></div><?php endforeach; ?><?php if (!$recentPayments): ?><p class="class-empty-state"><i class="bi bi-receipt"></i>Belum ada pembayaran kas yang tercatat.</p><?php endif; ?></div></article>
                <article class="class-status-card"><div class="class-card-head"><div><span>Status setoran</span><h4>Setoran ke OSIS</h4></div><i class="bi bi-send-check"></i></div><?php $status = $lastConfirmation['status'] ?? 'belum_ada'; $statusLabel = $status === 'diterima' ? 'Sudah diterima' : ($status === 'menunggu' ? 'Menunggu konfirmasi' : ($status === 'ditolak' ? 'Perlu diperbaiki' : 'Belum ada setoran')); ?><div class="class-confirmation-status <?= htmlspecialchars($status) ?>"><i class="bi <?= $status === 'diterima' ? 'bi-check-circle-fill' : ($status === 'menunggu' ? 'bi-clock-fill' : 'bi-info-circle-fill') ?>"></i><strong><?= $statusLabel ?></strong><small><?= $lastConfirmation ? 'Pembaruan terakhir: ' . date('d M Y', strtotime($lastConfirmation['submitted_at'])) : 'Kirim setoran setelah pembayaran kas dicatat.' ?></small></div><a href="konfirmasi.php" class="btn btn-primary w-100"><i class="bi bi-send"></i> Kelola setoran OSIS</a></article>
            </section>
        </div>
    </section>
</main>
<script src="/simosis/assets/vendor/chartjs/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('classFinanceChart'), {type: 'bar', data: {labels: <?= json_encode(array_column($chartRows, 'label')) ?>, datasets: [{label: 'Kas terkumpul', data: <?= json_encode(array_map('floatval', array_column($chartRows, 'nominal'))) ?>, backgroundColor: '#1976a8', borderRadius: 7, borderSkipped: false, maxBarThickness: 42}]}, options: {responsive: true, maintainAspectRatio: false, plugins: {legend: {display: false}, tooltip: {callbacks: {label: item => `Rp ${new Intl.NumberFormat('id-ID').format(item.raw)}`}}}, scales: {y: {beginAtZero: true, ticks: {callback: value => new Intl.NumberFormat('id-ID', {notation: 'compact', maximumFractionDigits: 1}).format(value)}, border: {display: false}, grid: {color: '#edf2f5'}}, x: {grid: {display: false}, border: {display: false}}}}});
</script>
<?php require_once '../includes/footer.php'; ?>
