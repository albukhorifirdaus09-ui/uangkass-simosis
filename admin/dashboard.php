<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireLogin();

if (!in_array($_SESSION['role'] ?? '', ['admin', 'pembina'], true)) {
    header('Location: ../index.php');
    exit;
}

$pageTitle = 'Dashboard Admin';
$namaUser = $_SESSION['nama'] ?? $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin';
$month = (int) date('n');
$year = (int) date('Y');
$monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

$totalSiswa = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalOsis = (int) $pdo->query("SELECT COUNT(DISTINCT student_id) FROM osis_members WHERE status = 'aktif'")->fetchColumn();
$totalJabatan = (int) $pdo->query('SELECT COUNT(*) FROM osis_positions')->fetchColumn();
$totalKegiatan = (int) $pdo->query('SELECT COUNT(*) FROM activities')->fetchColumn();

$totalTransaksiMasuk = (float) $pdo->query("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pemasukan'")->fetchColumn();
$totalPengeluaran = (float) $pdo->query("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pengeluaran'")->fetchColumn();
/* Pembayaran kas OSIS tidak selalu tercatat sebagai transaksi manual, jadi dihitung langsung dari pembayaran siswa. */
$totalKasAnggota = (float) $pdo->query("SELECT COALESCE(SUM(p.nominal), 0) FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id WHERE d.jenis_kas = 'osis' AND p.status = 'lunas'")->fetchColumn();
$saldoKas = $totalTransaksiMasuk + $totalKasAnggota - $totalPengeluaran;

$stmt = $pdo->prepare("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pemasukan' AND MONTH(ot.tanggal) = ? AND YEAR(ot.tanggal) = ?");
$stmt->execute([$month, $year]);
$pemasukanTransaksiBulan = (float) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COALESCE(SUM(p.nominal), 0) FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id WHERE d.jenis_kas = 'osis' AND p.status = 'lunas' AND MONTH(p.tanggal_bayar) = ? AND YEAR(p.tanggal_bayar) = ?");
$stmt->execute([$month, $year]);
$kasAnggotaBulan = (float) $stmt->fetchColumn();
$pemasukanBulanIni = $pemasukanTransaksiBulan + $kasAnggotaBulan;
$stmt = $pdo->prepare("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pengeluaran' AND MONTH(ot.tanggal) = ? AND YEAR(ot.tanggal) = ?");
$stmt->execute([$month, $year]);
$pengeluaranBulanIni = (float) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT p.student_id) FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id INNER JOIN osis_members om ON om.student_id = p.student_id AND om.status = 'aktif' WHERE d.jenis_kas = 'osis' AND d.bulan = ? AND d.tahun = ? AND p.status = 'lunas'");
$stmt->execute([$month, $year]);
$anggotaSudahBayar = (int) $stmt->fetchColumn();
$anggotaBelumBayar = max(0, $totalOsis - $anggotaSudahBayar);
$persentaseBayar = $totalOsis > 0 ? round(($anggotaSudahBayar / $totalOsis) * 100) : 0;

$financeChart = $pdo->query("SELECT DATE_FORMAT(ledger.tanggal, '%b') AS label, SUM(CASE WHEN ledger.jenis = 'pemasukan' THEN ledger.nominal ELSE 0 END) AS pemasukan, SUM(CASE WHEN ledger.jenis = 'pengeluaran' THEN ledger.nominal ELSE 0 END) AS pengeluaran FROM (SELECT ot.tanggal, ot.nominal, c.jenis FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id UNION ALL SELECT p.tanggal_bayar AS tanggal, p.nominal, 'pemasukan' AS jenis FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id WHERE d.jenis_kas = 'osis' AND p.status = 'lunas') ledger WHERE ledger.tanggal >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY YEAR(ledger.tanggal), MONTH(ledger.tanggal), DATE_FORMAT(ledger.tanggal, '%b') ORDER BY YEAR(ledger.tanggal), MONTH(ledger.tanggal)")->fetchAll(PDO::FETCH_ASSOC);
$financeLabels = array_column($financeChart, 'label');
$financeIncome = array_map('floatval', array_column($financeChart, 'pemasukan'));
$financeExpense = array_map('floatval', array_column($financeChart, 'pengeluaran'));

$pendingSetoran = (int) $pdo->query("SELECT COUNT(*) FROM class_due_confirmations WHERE status = 'menunggu'")->fetchColumn();
$jurusanChart = $pdo->query("SELECT COALESCE(NULLIF(TRIM(c.jurusan), ''), 'Belum diatur') AS jurusan, COUNT(DISTINCT c.id) AS total_kelas, COUNT(DISTINCT CASE WHEN cdc.status = 'diterima' THEN c.id END) AS kelas_lunas FROM classes c INNER JOIN students s ON s.kelas_id = c.id LEFT JOIN class_due_confirmations cdc ON cdc.class_id = c.id GROUP BY COALESCE(NULLIF(TRIM(c.jurusan), ''), 'Belum diatur') ORDER BY jurusan")->fetchAll(PDO::FETCH_ASSOC);
$recentActivity = $pdo->query("SELECT * FROM (SELECT ot.tanggal, ot.nominal, c.jenis, c.nama_kategori AS kategori, COALESCE(ot.keterangan, '') AS keterangan FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id UNION ALL SELECT p.tanggal_bayar AS tanggal, p.nominal, 'pemasukan' AS jenis, 'Kas OSIS anggota' AS kategori, 'Pembayaran kas OSIS' AS keterangan FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id WHERE d.jenis_kas = 'osis' AND p.status = 'lunas') activity ORDER BY tanggal DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../includes/navbar.php'; ?>
    <section class="dashboard-container admin-dashboard-modern">
        <section class="admin-hero-modern">
            <div>
                <span class="admin-hero-kicker"><i class="bi bi-shield-check"></i> Panel Administrasi</span>
                <h2>Halo, <?= htmlspecialchars($namaUser) ?>.</h2>
                <p>Kelola data sekolah dan pantau kondisi keuangan OSIS dari satu dashboard.</p>
            </div>
            <div class="admin-hero-side"><span><i class="bi bi-calendar3"></i> <?= date('d') ?> <?= $monthNames[$month] ?> <?= $year ?></span><div><a href="kas_siswa/index.php" class="btn btn-light"><i class="bi bi-grid"></i> Kas Kelas</a><a href="keuangan/index.php" class="btn btn-warning"><i class="bi bi-wallet2"></i> Laporan</a></div></div>
        </section>

        <div class="admin-overview-head"><div><span class="eyebrow">Ringkasan utama</span><h3>Kondisi sekolah dan kas OSIS</h3></div><span class="overview-date">Data diperbarui otomatis</span></div>
        <section class="admin-stat-grid admin-stat-grid-modern">
            <article class="admin-stat-card"><div class="admin-stat-icon blue"><i class="bi bi-people-fill"></i></div><div><span>Total siswa</span><strong><?= number_format($totalSiswa) ?></strong><small>Data siswa terdaftar</small></div></article>
            <article class="admin-stat-card"><div class="admin-stat-icon teal"><i class="bi bi-person-badge-fill"></i></div><div><span>Anggota OSIS</span><strong><?= number_format($totalOsis) ?></strong><small>Anggota aktif</small></div></article>
            <article class="admin-stat-card admin-cash-stat"><div class="admin-stat-icon green"><i class="bi bi-wallet2"></i></div><div><span>Saldo Kas OSIS</span><strong>Rp <?= number_format($saldoKas, 0, ',', '.') ?></strong><small>Termasuk kas anggota OSIS</small></div></article>
            <article class="admin-stat-card"><div class="admin-stat-icon amber"><i class="bi bi-calendar-event-fill"></i></div><div><span>Kegiatan</span><strong><?= number_format($totalKegiatan) ?></strong><small><?= number_format($totalJabatan) ?> jabatan OSIS</small></div></article>
        </section>

        <section class="admin-finance-strip">
            <div><i class="bi bi-arrow-down-left"></i><span>Pemasukan <?= $monthNames[$month] ?></span><strong>Rp <?= number_format($pemasukanBulanIni, 0, ',', '.') ?></strong><small>Kas anggota: Rp <?= number_format($kasAnggotaBulan, 0, ',', '.') ?></small></div>
            <div><i class="bi bi-arrow-up-right"></i><span>Pengeluaran <?= $monthNames[$month] ?></span><strong>Rp <?= number_format($pengeluaranBulanIni, 0, ',', '.') ?></strong><small>Transaksi OSIS tercatat</small></div>
            <div><i class="bi bi-cash-stack"></i><span>Total kas anggota OSIS</span><strong>Rp <?= number_format($totalKasAnggota, 0, ',', '.') ?></strong><small>Akumulasi pembayaran lunas</small></div>
        </section>

        <section class="admin-chart-grid admin-chart-grid-modern">
            <article class="admin-chart-card"><div class="chart-card-head"><div><span class="eyebrow">Arus kas OSIS</span><h4>Pemasukan dan pengeluaran 6 bulan terakhir</h4></div><i class="bi bi-bar-chart-line"></i></div><div class="chart-box"><canvas id="financeChart"></canvas></div></article>
            <article class="admin-chart-card admin-payment-card"><div class="chart-card-head"><div><span class="eyebrow">Kas anggota OSIS</span><h4>Status pembayaran <?= $monthNames[$month] ?></h4></div><a href="keuangan/index.php" class="admin-inline-link">Detail <i class="bi bi-arrow-right"></i></a></div><div class="admin-payment-donut"><canvas id="paymentChart"></canvas><div><strong><?= $persentaseBayar ?>%</strong><span>Lunas</span></div></div><div class="admin-payment-list"><p><span class="admin-dot paid"></span>Sudah bayar <strong><?= $anggotaSudahBayar ?></strong></p><p><span class="admin-dot unpaid"></span>Belum bayar <strong><?= $anggotaBelumBayar ?></strong></p></div><small class="text-muted">Dihitung dari <?= $totalOsis ?> anggota OSIS aktif.</small></article>
        </section>

        <section class="admin-recap-grid">
            <article class="admin-chart-card"><div class="chart-card-head"><div><span class="eyebrow">Kas per jurusan</span><h4>Status setoran kelas</h4></div><i class="bi bi-building"></i></div><div class="admin-department-list"><?php foreach ($jurusanChart as $jurusan): $percent = (int) $jurusan['total_kelas'] > 0 ? round(((int) $jurusan['kelas_lunas'] / (int) $jurusan['total_kelas']) * 100) : 0; ?><div class="admin-department-row"><strong><?= htmlspecialchars($jurusan['jurusan']) ?></strong><span><?= (int) $jurusan['kelas_lunas'] ?>/<?= (int) $jurusan['total_kelas'] ?> kelas diterima</span><div class="mini-progress"><i style="width: <?= $percent ?>%"></i></div></div><?php endforeach; ?><?php if (!$jurusanChart): ?><p class="text-muted mb-0">Belum ada data kelas yang memiliki siswa.</p><?php endif; ?></div></article>
            <article class="admin-chart-card admin-pending-card"><div class="chart-card-head"><div><span class="eyebrow">Perlu ditinjau</span><h4>Konfirmasi setoran kelas</h4></div><i class="bi bi-inbox"></i></div><strong class="pending-number"><?= $pendingSetoran ?></strong><p class="text-muted">setoran kelas sedang menunggu penerimaan Bendahara OSIS.</p><a href="keuangan/index.php" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Lihat setoran</a></article>
        </section>

        <section class="admin-activity-card"><div class="chart-card-head"><div><span class="eyebrow">Aktivitas keuangan</span><h4>Transaksi dan kas OSIS terbaru</h4></div><a href="keuangan/index.php" class="btn btn-outline-primary btn-sm">Buka laporan <i class="bi bi-arrow-right"></i></a></div><div class="table-responsive"><table class="table admin-activity-table align-middle mb-0"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th class="text-end">Nominal</th></tr></thead><tbody><?php foreach ($recentActivity as $row): ?><tr><td><?= date('d M Y', strtotime($row['tanggal'])) ?></td><td><span class="admin-transaction-type <?= $row['jenis'] === 'pemasukan' ? 'income' : 'expense' ?>"><i class="bi <?= $row['jenis'] === 'pemasukan' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' ?>"></i><?= ucfirst($row['jenis']) ?></span></td><td><?= htmlspecialchars($row['kategori']) ?></td><td><?= htmlspecialchars($row['keterangan'] ?: '—') ?></td><td class="text-end fw-semibold <?= $row['jenis'] === 'pemasukan' ? 'text-success' : 'text-danger' ?>"><?= $row['jenis'] === 'pemasukan' ? '+' : '-' ?> Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td></tr><?php endforeach; ?><?php if (!$recentActivity): ?><tr><td colspan="5" class="text-center text-muted py-5">Belum ada aktivitas keuangan.</td></tr><?php endif; ?></tbody></table></div></section>
    </section>
</main>
<script src="/simosis/assets/vendor/chartjs/chart.umd.min.js"></script>
<script>
const compactRupiah = value => new Intl.NumberFormat('id-ID', {notation: 'compact', maximumFractionDigits: 1}).format(value);
new Chart(document.getElementById('financeChart'), {type: 'bar', data: {labels: <?= json_encode($financeLabels) ?>, datasets: [{label: 'Pemasukan', data: <?= json_encode($financeIncome) ?>, backgroundColor: '#129c8c', borderRadius: 7, borderSkipped: false, maxBarThickness: 38}, {label: 'Pengeluaran', data: <?= json_encode($financeExpense) ?>, backgroundColor: '#f0a944', borderRadius: 7, borderSkipped: false, maxBarThickness: 38}]}, options: {responsive: true, maintainAspectRatio: false, interaction: {intersect: false, mode: 'index'}, plugins: {legend: {position: 'bottom', labels: {usePointStyle: true, pointStyle: 'circle', padding: 20}}, tooltip: {callbacks: {label: item => `${item.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(item.raw)}`}}}, scales: {y: {beginAtZero: true, ticks: {callback: compactRupiah}, border: {display: false}, grid: {color: '#edf2f5'}}, x: {grid: {display: false}, border: {display: false}}}}});
new Chart(document.getElementById('paymentChart'), {type: 'doughnut', data: {labels: ['Sudah bayar', 'Belum bayar'], datasets: [{data: [<?= $anggotaSudahBayar ?>, <?= $anggotaBelumBayar ?>], backgroundColor: ['#129c8c', '#e8eef2'], borderWidth: 0}]}, options: {responsive: true, maintainAspectRatio: false, cutout: '76%', plugins: {legend: {display: false}, tooltip: {callbacks: {label: item => `${item.label}: ${item.raw} anggota`}}}}});
</script>
<?php require_once '../includes/footer.php'; ?>
