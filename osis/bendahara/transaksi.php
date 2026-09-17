<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

requireOsisTreasurer();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $amount = (float) ($_POST['nominal'] ?? 0);
    $date = $_POST['tanggal'] ?? date('Y-m-d');
    $description = trim($_POST['keterangan'] ?? '');

    $stmt = $pdo->prepare("SELECT id FROM osis_transaction_categories WHERE id = ? LIMIT 1");
    $stmt->execute([$categoryId]);

    if (!$stmt->fetch() || $amount <= 0 || $description === '') {
        $error = 'Kategori, nominal yang valid, dan keterangan wajib diisi.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO osis_transactions (category_id, user_id, tanggal, nominal, keterangan) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$categoryId, $_SESSION['user_id'], $date, $amount, $description]);
        header('Location: dashboard.php?success=transaksi');
        exit;
    }
}

$categories = $pdo->query("SELECT id, nama_kategori, jenis FROM osis_transaction_categories ORDER BY jenis, nama_kategori")->fetchAll(PDO::FETCH_ASSOC);
$cashSummary = $pdo->query("SELECT COALESCE(SUM(CASE WHEN c.jenis = 'pemasukan' THEN t.nominal ELSE 0 END), 0) AS total_pemasukan, COALESCE(SUM(CASE WHEN c.jenis = 'pengeluaran' THEN t.nominal ELSE 0 END), 0) AS total_pengeluaran, COUNT(t.id) AS total_transaksi FROM osis_transactions t INNER JOIN osis_transaction_categories c ON c.id = t.category_id")->fetch(PDO::FETCH_ASSOC);
$recentTransactions = $pdo->query("SELECT t.tanggal, t.nominal, t.keterangan, c.nama_kategori, c.jenis FROM osis_transactions t INNER JOIN osis_transaction_categories c ON c.id = t.category_id ORDER BY t.tanggal DESC, t.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$saldoKas = (float) $cashSummary['total_pemasukan'] - (float) $cashSummary['total_pengeluaran'];
$pageTitle = 'Catat Transaksi OSIS';
require_once '../../includes/header.php';
require_once '../../includes/osis_sidebar.php';
?>

<div class="osis-main-content">

    <header class="osis-topbar">
        <button class="btn btn-light d-lg-none" id="osisMenuToggle" type="button">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <strong>Transaksi Kas</strong>
            <small class="d-block text-muted">Catat pemasukan dan pengeluaran OSIS</small>
        </div>
        <span class="text-muted small"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
    </header>

    <main class="role-page osis-transaction-page"><div class="role-wrap">
    <a href="dashboard.php" class="osis-transaction-back"><i class="bi bi-arrow-left"></i> Dashboard bendahara</a>
    <section class="osis-transaction-hero"><div><span><i class="bi bi-journal-plus"></i> Pembukuan OSIS</span><h2>Catat transaksi kas</h2><p>Semua pemasukan dan pengeluaran tersimpan dalam laporan kas OSIS.</p></div><div><i class="bi bi-cash-stack"></i><small><?= date('d M Y') ?></small></div></section>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <section class="osis-transaction-stat-grid"><article class="primary"><span><i class="bi bi-wallet2"></i></span><small>Saldo transaksi</small><strong>Rp <?= number_format($saldoKas, 0, ',', '.') ?></strong><p>Pemasukan dikurangi pengeluaran</p></article><article><span class="income"><i class="bi bi-arrow-down-left-circle"></i></span><small>Total pemasukan</small><strong>Rp <?= number_format((float) $cashSummary['total_pemasukan'], 0, ',', '.') ?></strong><p>Seluruh transaksi tercatat</p></article><article><span class="expense"><i class="bi bi-arrow-up-right-circle"></i></span><small>Total pengeluaran</small><strong>Rp <?= number_format((float) $cashSummary['total_pengeluaran'], 0, ',', '.') ?></strong><p>Seluruh transaksi tercatat</p></article><article><span class="violet"><i class="bi bi-receipt"></i></span><small>Transaksi</small><strong><?= (int) $cashSummary['total_transaksi'] ?></strong><p>Catatan kas tersedia</p></article></section>
    <section class="osis-transaction-grid"><div class="role-card osis-transaction-form-card"><div class="osis-transaction-card-head"><div><span><i class="bi bi-plus-circle"></i> Tambah catatan</span><h4>Transaksi baru</h4><p>Pastikan kategori dan nominal sudah sesuai.</p></div><i class="bi bi-pencil-square"></i></div><form method="post">
        <label class="form-label" for="category_id">Kategori</label>
        <select class="form-select mb-3" id="category_id" name="category_id" required>
            <option value="">Pilih kategori</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars(ucfirst($category['jenis']) . ' - ' . $category['nama_kategori']) ?></option>
            <?php endforeach; ?>
        </select>
        <label class="form-label" for="nominal">Nominal</label>
        <input class="form-control mb-3" id="nominal" name="nominal" type="number" min="1" step="0.01" required>
        <label class="form-label" for="tanggal">Tanggal</label>
        <input class="form-control mb-3" id="tanggal" name="tanggal" type="date" value="<?= date('Y-m-d') ?>" required>
        <label class="form-label" for="keterangan">Keterangan</label>
        <textarea class="form-control mb-3" id="keterangan" name="keterangan" rows="3" required placeholder="Contoh: Membeli sapu untuk ruang OSIS"></textarea>
        <button class="btn btn-primary" type="submit"><i class="bi bi-save2"></i> Simpan Transaksi</button>
    </form></div>
    <aside class="role-card osis-transaction-recent-card"><div class="osis-transaction-card-head"><div><span><i class="bi bi-clock-history"></i> Aktivitas terbaru</span><h4>Transaksi terakhir</h4></div><a href="../../admin/keuangan/index.php">Lihat laporan</a></div><?php if ($recentTransactions): ?><div class="osis-transaction-recent-list"><?php foreach ($recentTransactions as $transaction): ?><article><span class="osis-transaction-type <?= $transaction['jenis'] === 'pemasukan' ? 'income' : 'expense' ?>"><i class="bi <?= $transaction['jenis'] === 'pemasukan' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' ?>"></i></span><div><strong><?= htmlspecialchars($transaction['nama_kategori']) ?></strong><small><?= htmlspecialchars($transaction['keterangan']) ?> · <?= date('d M Y', strtotime($transaction['tanggal'])) ?></small></div><b class="<?= $transaction['jenis'] === 'pemasukan' ? 'income' : 'expense' ?>"><?= $transaction['jenis'] === 'pemasukan' ? '+' : '-' ?>Rp <?= number_format((float) $transaction['nominal'], 0, ',', '.') ?></b></article><?php endforeach; ?></div><?php else: ?><div class="osis-transaction-empty"><i class="bi bi-receipt"></i><strong>Belum ada transaksi</strong><span>Catatan yang disimpan akan tampil di sini.</span></div><?php endif; ?></aside></section>
    </div></main>
</div>
<?php require_once '../../includes/footer.php'; ?>
