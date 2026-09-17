<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireOsisTreasurer();

$month = max(1, min(12, (int) ($_GET['bulan'] ?? date('n'))));
$year = max(2020, (int) ($_GET['tahun'] ?? date('Y')));
$monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_transaksi') {
    $transactionId = (int) ($_POST['transaction_id'] ?? 0);
    if ($transactionId > 0) {
        $stmt = $pdo->prepare('DELETE FROM osis_transactions WHERE id = ?');
        $stmt->execute([$transactionId]);
    }
    header("Location: dashboard.php?bulan={$month}&tahun={$year}&success=hapus_transaksi");
    exit;
}

$income = (float) $pdo->query("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pemasukan'")->fetchColumn();
$expense = (float) $pdo->query("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pengeluaran'")->fetchColumn();
$memberCash = (float) $pdo->query("SELECT COALESCE(SUM(p.nominal), 0) FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id INNER JOIN osis_members om ON om.student_id = p.student_id AND om.status = 'aktif' WHERE d.jenis_kas = 'osis' AND p.status = 'lunas'")->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pemasukan' AND MONTH(ot.tanggal) = ? AND YEAR(ot.tanggal) = ?");
$stmt->execute([$month, $year]);
$monthIncome = (float) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pengeluaran' AND MONTH(ot.tanggal) = ? AND YEAR(ot.tanggal) = ?");
$stmt->execute([$month, $year]);
$monthExpense = (float) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COALESCE(SUM(p.nominal), 0) FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id INNER JOIN osis_members om ON om.student_id = p.student_id AND om.status = 'aktif' WHERE d.jenis_kas = 'osis' AND p.status = 'lunas' AND MONTH(p.tanggal_bayar) = ? AND YEAR(p.tanggal_bayar) = ?");
$stmt->execute([$month, $year]);
$monthMemberCash = (float) $stmt->fetchColumn();

$totalStudents = (int) $pdo->query("SELECT COUNT(DISTINCT om.student_id) FROM osis_members om WHERE om.status = 'aktif'")->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT p.student_id) FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id INNER JOIN osis_members om ON om.student_id = p.student_id AND om.status = 'aktif' WHERE d.bulan = ? AND d.tahun = ? AND d.jenis_kas = 'osis' AND p.status = 'lunas'");
$stmt->execute([$month, $year]);
$paidStudents = (int) $stmt->fetchColumn();
$unpaidStudents = max(0, $totalStudents - $paidStudents);
$paidPercent = $totalStudents ? round(($paidStudents / $totalStudents) * 100) : 0;
$currentBalance = $income + $memberCash - $expense;
$monthRevenue = $monthIncome + $monthMemberCash;
$monthNet = $monthRevenue - $monthExpense;

$history = $pdo->query("SELECT ot.id, ot.tanggal, ot.nominal, ot.keterangan, c.nama_kategori, c.jenis FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id ORDER BY ot.tanggal DESC, ot.id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
$chartRows = $pdo->query("SELECT DATE_FORMAT(ot.tanggal, '%b') label, SUM(CASE WHEN c.jenis = 'pemasukan' THEN ot.nominal ELSE 0 END) income, SUM(CASE WHEN c.jenis = 'pengeluaran' THEN ot.nominal ELSE 0 END) expense FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE ot.tanggal >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY YEAR(ot.tanggal), MONTH(ot.tanggal), DATE_FORMAT(ot.tanggal, '%b') ORDER BY YEAR(ot.tanggal), MONTH(ot.tanggal)")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Dashboard Bendahara OSIS';
require_once '../../includes/header.php';
require_once '../../includes/osis_sidebar.php';
?>
<div class="osis-main-content">
    <header class="osis-topbar">
        <button class="btn btn-light d-lg-none" id="osisMenuToggle"><i class="bi bi-list"></i></button>
        <div><strong>Dashboard Bendahara</strong><small class="d-block text-muted">Ringkasan kas OSIS</small></div>
        <span class="text-muted small"><?= htmlspecialchars($_SESSION['username']) ?></span>
    </header>

    <main class="role-page">
        <div class="role-wrap treasurer-dashboard">
            <section class="treasurer-hero">
                <div>
                    <span class="treasurer-kicker"><i class="bi bi-wallet2"></i> Keuangan OSIS</span>
                    <h2>Keuangan dalam satu pandangan.</h2>
                    <p>Pantau saldo, setoran kas anggota, serta arus pemasukan dan pengeluaran dengan cepat.</p>
                </div>
                <div class="treasurer-hero-actions">
                    <span class="treasurer-period"><i class="bi bi-calendar3"></i> <?= $monthNames[$month] ?> <?= $year ?></span>
                    <div><a href="uang_kas.php" class="btn btn-light"><i class="bi bi-grid-3x3-gap"></i> Uang Kas</a><a href="transaksi.php" class="btn btn-warning"><i class="bi bi-plus-lg"></i> Transaksi</a></div>
                </div>
            </section>

            <form class="treasurer-filter" method="get">
                <div class="treasurer-filter-label"><i class="bi bi-funnel"></i><span>Periode laporan</span></div>
                <select class="form-select" name="bulan" aria-label="Bulan laporan"><?php foreach ($monthNames as $number => $name): ?><option value="<?= $number ?>" <?= $month === $number ? 'selected' : '' ?>><?= $name ?></option><?php endforeach; ?></select>
                <input class="form-control" type="number" name="tahun" min="2020" value="<?= $year ?>" aria-label="Tahun laporan">
                <button class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Perbarui</button>
            </form>

            <section class="treasurer-stat-grid">
                <article class="treasurer-stat treasurer-stat-balance"><div class="treasurer-stat-icon"><i class="bi bi-wallet2"></i></div><span>Saldo kas OSIS</span><strong>Rp <?= number_format($currentBalance, 0, ',', '.') ?></strong><small>Akumulasi kas yang tercatat</small></article>
                <article class="treasurer-stat"><div class="treasurer-stat-icon icon-green"><i class="bi bi-arrow-down-left-circle"></i></div><span>Pemasukan <?= $monthNames[$month] ?></span><strong>Rp <?= number_format($monthRevenue, 0, ',', '.') ?></strong><small>Kas anggota: Rp <?= number_format($monthMemberCash, 0, ',', '.') ?></small></article>
                <article class="treasurer-stat"><div class="treasurer-stat-icon icon-orange"><i class="bi bi-arrow-up-right-circle"></i></div><span>Pengeluaran <?= $monthNames[$month] ?></span><strong>Rp <?= number_format($monthExpense, 0, ',', '.') ?></strong><small>Selisih bulan ini: <?= $monthNet >= 0 ? '+' : '-' ?>Rp <?= number_format(abs($monthNet), 0, ',', '.') ?></small></article>
                <article class="treasurer-stat"><div class="treasurer-stat-icon icon-violet"><i class="bi bi-people"></i></div><span>Anggota OSIS aktif</span><strong><?= $totalStudents ?> siswa</strong><small><?= $paidStudents ?> sudah membayar kas</small></article>
            </section>

            <section class="treasurer-insight-grid">
                <article class="treasurer-chart-card"><div class="treasurer-card-head"><div><span>Arus kas</span><h4>Pemasukan &amp; pengeluaran 6 bulan terakhir</h4></div><div class="treasurer-card-icon"><i class="bi bi-bar-chart-line"></i></div></div><div class="treasurer-chart-wrap"><canvas id="treasurerFinanceChart"></canvas></div></article>
                <article class="treasurer-payment-card"><div class="treasurer-card-head"><div><span>Setoran kas</span><h4>Pembayaran anggota</h4></div><a href="uang_kas.php" class="treasurer-text-link">Lihat kas <i class="bi bi-arrow-right"></i></a></div><div class="treasurer-donut-wrap"><canvas id="treasurerPaymentChart"></canvas><div class="treasurer-donut-value"><strong><?= $paidPercent ?>%</strong><span>Lunas</span></div></div><div class="treasurer-payment-legend"><div><span class="legend-dot legend-paid"></span><span>Sudah bayar</span><strong><?= $paidStudents ?></strong></div><div><span class="legend-dot legend-unpaid"></span><span>Belum bayar</span><strong><?= $unpaidStudents ?></strong></div></div><div class="treasurer-progress"><div style="width: <?= $paidPercent ?>%"></div></div><small class="treasurer-muted">Data pembayaran kas untuk <?= $monthNames[$month] ?> <?= $year ?>.</small></article>
            </section>

            <section class="treasurer-activity-card"><div class="treasurer-card-head"><div><span>Aktivitas terakhir</span><h4>Transaksi pemasukan dan pengeluaran</h4></div><a href="transaksi.php" class="btn btn-outline-primary btn-sm">Kelola transaksi <i class="bi bi-arrow-right"></i></a></div><div class="table-responsive"><table class="table treasurer-table align-middle mb-0"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th class="text-end">Nominal</th><th></th></tr></thead><tbody><?php foreach ($history as $row): ?><tr><td class="text-nowrap"><?= date('d M Y', strtotime($row['tanggal'])) ?></td><td><span class="treasurer-type <?= $row['jenis'] === 'pemasukan' ? 'type-income' : 'type-expense' ?>"><i class="bi <?= $row['jenis'] === 'pemasukan' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' ?>"></i><?= ucfirst($row['jenis']) ?></span></td><td><?= htmlspecialchars($row['nama_kategori']) ?></td><td class="treasurer-note"><?= htmlspecialchars($row['keterangan'] ?: '—') ?></td><td class="text-end fw-semibold <?= $row['jenis'] === 'pemasukan' ? 'text-success' : 'text-danger' ?>"><?= $row['jenis'] === 'pemasukan' ? '+' : '-' ?> Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td><td><form method="post" onsubmit="return confirm('Hapus transaksi ini?');"><input type="hidden" name="action" value="hapus_transaksi"><input type="hidden" name="transaction_id" value="<?= (int) $row['id'] ?>"><button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus transaksi"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?><?php if (!$history): ?><tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-receipt d-block fs-3 mb-2"></i>Belum ada transaksi yang dicatat.</td></tr><?php endif; ?></tbody></table></div></section>
        </div>
    </main>
</div>
<script src="/simosis/assets/vendor/chartjs/chart.umd.min.js"></script>
<script>
const moneyFormat = value => new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
new Chart(document.getElementById('treasurerFinanceChart'), {type: 'bar', data: {labels: <?= json_encode(array_column($chartRows, 'label')) ?>, datasets: [{label: 'Pemasukan', data: <?= json_encode(array_map('floatval', array_column($chartRows, 'income'))) ?>, backgroundColor: '#129c8c', borderRadius: 7, borderSkipped: false, maxBarThickness: 38}, {label: 'Pengeluaran', data: <?= json_encode(array_map('floatval', array_column($chartRows, 'expense'))) ?>, backgroundColor: '#f0a944', borderRadius: 7, borderSkipped: false, maxBarThickness: 38}]}, options: {responsive: true, maintainAspectRatio: false, interaction: {intersect: false, mode: 'index'}, plugins: {legend: {position: 'bottom', labels: {usePointStyle: true, pointStyle: 'circle', padding: 20}}, tooltip: {callbacks: {label: item => `${item.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(item.raw)}`}}}, scales: {y: {beginAtZero: true, ticks: {callback: moneyFormat}, border: {display: false}, grid: {color: '#edf2f5'}}, x: {grid: {display: false}, border: {display: false}}}}});
new Chart(document.getElementById('treasurerPaymentChart'), {type: 'doughnut', data: {labels: ['Sudah bayar', 'Belum bayar'], datasets: [{data: [<?= $paidStudents ?>, <?= $unpaidStudents ?>], backgroundColor: ['#129c8c', '#e8eef2'], hoverBackgroundColor: ['#0c8174', '#dce5eb'], borderWidth: 0}]}, options: {responsive: true, maintainAspectRatio: false, cutout: '76%', plugins: {legend: {display: false}, tooltip: {callbacks: {label: item => `${item.label}: ${item.raw} siswa`}}}}});
</script>
<?php require_once '../../includes/footer.php'; ?>
