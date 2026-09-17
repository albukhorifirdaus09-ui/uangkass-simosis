<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

requireRole(['admin']);

$totalTransaksiMasuk = (float) $pdo->query("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pemasukan'")->fetchColumn();
$totalPengeluaran = (float) $pdo->query("SELECT COALESCE(SUM(ot.nominal), 0) FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id WHERE c.jenis = 'pengeluaran'")->fetchColumn();
$totalKasAnggota = (float) $pdo->query("SELECT COALESCE(SUM(p.nominal), 0) FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id WHERE d.jenis_kas = 'osis' AND p.status = 'lunas'")->fetchColumn();
$totalPemasukan = $totalTransaksiMasuk + $totalKasAnggota;
$saldoKas = $totalPemasukan - $totalPengeluaran;

/* Gabungkan transaksi manual dan pembayaran kas anggota agar laporan sama dengan dashboard. */
$transactions = $pdo->query("SELECT * FROM (SELECT ot.tanggal, ot.nominal, ot.keterangan, c.nama_kategori, c.jenis, COALESCE(u.username, 'Sistem') AS pencatat FROM osis_transactions ot INNER JOIN osis_transaction_categories c ON c.id = ot.category_id LEFT JOIN users u ON u.id = ot.user_id UNION ALL SELECT p.tanggal_bayar AS tanggal, p.nominal, 'Pembayaran kas OSIS anggota' AS keterangan, 'Kas OSIS anggota' AS nama_kategori, 'pemasukan' AS jenis, 'Sistem pembayaran' AS pencatat FROM student_due_payments p INNER JOIN student_dues d ON d.id = p.student_due_id WHERE d.jenis_kas = 'osis' AND p.status = 'lunas') laporan ORDER BY tanggal DESC")->fetchAll(PDO::FETCH_ASSOC);

$confirmations = $pdo->query("SELECT c.nama_kelas, COALESCE(NULLIF(TRIM(c.jurusan), ''), 'Belum diatur') AS jurusan, sd.bulan, sd.tahun, cdc.nominal, cdc.status, cdc.submitted_at FROM class_due_confirmations cdc INNER JOIN classes c ON c.id = cdc.class_id INNER JOIN student_dues sd ON sd.id = cdc.student_due_id ORDER BY cdc.submitted_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Laporan Kas OSIS';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <div class="container-fluid py-4">
        <div class="admin-report-hero">
            <div><span class="eyebrow">Keuangan OSIS</span><h2>Laporan Kas OSIS</h2><p>Seluruh pemasukan, pembayaran kas anggota, pengeluaran, dan setoran kelas.</p></div>
            <a href="../dashboard.php" class="btn btn-light"><i class="bi bi-grid"></i> Dashboard</a>
        </div>

        <section class="admin-report-summary">
            <article><span><i class="bi bi-arrow-down-left"></i> Total pemasukan</span><strong class="text-success">Rp <?= number_format($totalPemasukan, 0, ',', '.') ?></strong><small>Termasuk kas anggota OSIS</small></article>
            <article><span><i class="bi bi-people"></i> Kas anggota OSIS</span><strong>Rp <?= number_format($totalKasAnggota, 0, ',', '.') ?></strong><small>Pembayaran dengan status lunas</small></article>
            <article><span><i class="bi bi-arrow-up-right"></i> Total pengeluaran</span><strong class="text-danger">Rp <?= number_format($totalPengeluaran, 0, ',', '.') ?></strong><small>Transaksi pengeluaran tercatat</small></article>
            <article class="report-saldo"><span><i class="bi bi-wallet2"></i> Saldo kas OSIS</span><strong>Rp <?= number_format($saldoKas, 0, ',', '.') ?></strong><small>Pemasukan dikurangi pengeluaran</small></article>
        </section>

        <section class="admin-report-card">
            <div class="admin-report-head"><div><span class="eyebrow">Riwayat keuangan</span><h4>Transaksi dan pembayaran kas OSIS</h4></div><span class="report-count"><?= count($transactions) ?> catatan</span></div>
            <div class="table-responsive"><table class="table admin-report-table align-middle mb-0"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Pencatat</th><th class="text-end">Nominal</th></tr></thead><tbody>
                <?php foreach ($transactions as $transaction): ?><tr><td><?= date('d M Y', strtotime($transaction['tanggal'])) ?></td><td><span class="admin-transaction-type <?= $transaction['jenis'] === 'pemasukan' ? 'income' : 'expense' ?>"><i class="bi <?= $transaction['jenis'] === 'pemasukan' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' ?>"></i><?= htmlspecialchars(ucfirst($transaction['jenis'])) ?></span></td><td><?= htmlspecialchars($transaction['nama_kategori']) ?></td><td><?= htmlspecialchars($transaction['keterangan'] ?: '—') ?></td><td><?= htmlspecialchars($transaction['pencatat']) ?></td><td class="text-end fw-semibold <?= $transaction['jenis'] === 'pemasukan' ? 'text-success' : 'text-danger' ?>"><?= $transaction['jenis'] === 'pemasukan' ? '+' : '-' ?> Rp <?= number_format((float) $transaction['nominal'], 0, ',', '.') ?></td></tr><?php endforeach; ?>
                <?php if (!$transactions): ?><tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-receipt d-block fs-3 mb-2"></i>Belum ada transaksi atau pembayaran kas OSIS.</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>

        <section class="admin-report-card">
            <div class="admin-report-head"><div><span class="eyebrow">Setoran kelas</span><h4>Konfirmasi setoran dari bendahara kelas</h4></div><a href="../kas_siswa/index.php" class="btn btn-outline-primary btn-sm">Kas per jurusan <i class="bi bi-arrow-right"></i></a></div>
            <div class="table-responsive"><table class="table admin-report-table align-middle mb-0"><thead><tr><th>Kelas</th><th>Jurusan</th><th>Periode</th><th class="text-end">Nominal</th><th>Status</th></tr></thead><tbody>
                <?php foreach ($confirmations as $confirmation): ?><tr><td><?= htmlspecialchars($confirmation['nama_kelas']) ?></td><td><?= htmlspecialchars($confirmation['jurusan']) ?></td><td><?= htmlspecialchars($confirmation['bulan'] . '/' . $confirmation['tahun']) ?></td><td class="text-end">Rp <?= number_format((float) $confirmation['nominal'], 0, ',', '.') ?></td><td><span class="admin-transaction-type <?= $confirmation['status'] === 'diterima' ? 'income' : 'expense' ?>"><?= htmlspecialchars(ucfirst($confirmation['status'])) ?></span></td></tr><?php endforeach; ?>
                <?php if (!$confirmations): ?><tr><td colspan="5" class="text-center text-muted py-5">Belum ada setoran kelas.</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>
    </div>
</main>
<?php require_once '../../includes/footer.php'; ?>
