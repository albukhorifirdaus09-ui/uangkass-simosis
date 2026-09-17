<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

requireOsisTreasurer();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmationId = (int) ($_POST['confirmation_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $status = $action === 'terima' ? 'diterima' : ($action === 'tolak' ? 'ditolak' : '');

    if ($confirmationId && $action === 'hapus') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM class_due_confirmations WHERE id = ? FOR UPDATE");
            $stmt->execute([$confirmationId]);
            $confirmation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$confirmation) {
                throw new RuntimeException('Setoran tidak ditemukan.');
            }

            $stmt = $pdo->prepare("DELETE FROM class_due_confirmations WHERE id = ?");
            $stmt->execute([$confirmationId]);

            $pdo->commit();
            header('Location: setoran.php?success=hapus');
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $exception->getMessage();
        }
    }

    if ($confirmationId && $status) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT * FROM class_due_confirmations WHERE id = ? AND status = 'menunggu' FOR UPDATE");
            $stmt->execute([$confirmationId]);
            $confirmation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$confirmation) {
                throw new RuntimeException('Setoran sudah diproses atau tidak ditemukan.');
            }

            $note = trim($_POST['catatan'] ?? '') ?: null;
            $stmt = $pdo->prepare("UPDATE class_due_confirmations SET status = ?, confirmed_by = ?, confirmed_at = NOW(), catatan = COALESCE(?, catatan) WHERE id = ?");
            $stmt->execute([$status, $_SESSION['user_id'], $note, $confirmationId]);

            if ($status === 'diterima') {
                $stmt = $pdo->prepare("INSERT INTO osis_transactions (category_id, user_id, tanggal, nominal, keterangan) VALUES (1, ?, CURDATE(), ?, ?)");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $confirmation['nominal'],
                    'Setoran kas OSIS dari kelas ID ' . $confirmation['class_id'],
                ]);
            }

            $pdo->commit();
            header('Location: setoran.php?success=' . $status);
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Setoran gagal diproses.';
        }
    }
}

$stmt = $pdo->query("
    SELECT
    cdc.*,
    c.nama_kelas,
    c.jurusan,
    sd.bulan,
    sd.tahun,
    sd.frekuensi,
    sd.minggu_ke,
    u.username AS pengaju
    FROM class_due_confirmations cdc
    INNER JOIN classes c ON c.id = cdc.class_id
    INNER JOIN student_dues sd ON sd.id = cdc.student_due_id
    INNER JOIN users u ON u.id = cdc.submitted_by
    ORDER BY cdc.status = 'menunggu' DESC, cdc.submitted_at DESC
");
$confirmations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$setoranMenunggu = 0;
$setoranDiterima = 0;
$setoranDitolak = 0;
$nominalDiterima = 0.0;
foreach ($confirmations as $summaryConfirmation) {
    if ($summaryConfirmation['status'] === 'menunggu') {
        $setoranMenunggu++;
    } elseif ($summaryConfirmation['status'] === 'diterima') {
        $setoranDiterima++;
        $nominalDiterima += (float) $summaryConfirmation['nominal'];
    } else {
        $setoranDitolak++;
    }
}

$pageTitle = 'Penerimaan Setoran Kas Kelas';
require_once '../../includes/header.php';
require_once '../../includes/osis_sidebar.php';
?>
<div class="osis-main-content"><header class="osis-topbar"><button class="btn btn-light d-lg-none" id="osisMenuToggle"><i class="bi bi-list"></i></button><div><strong>Setoran Kelas</strong><small class="d-block text-muted">Penerimaan kas dari kelas</small></div><span class="text-muted small"><?= htmlspecialchars($_SESSION['username']) ?></span></header><main class="role-page osis-deposit-page"><div class="role-wrap">
    <div class="role-hero osis-deposit-hero"><div><span class="text-uppercase small">Kas per kelas</span><h2>Setoran Kelas</h2><p class="mb-0">Terima dan verifikasi konfirmasi setoran dari Bendahara Kelas.</p></div><div class="osis-deposit-hero-icon"><i class="bi bi-send-check-fill"></i><small><?= count($confirmations) ?> setoran</small></div></div>

    <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?= $_GET['success'] === 'hapus' ? 'Setoran berhasil dihapus.' : 'Setoran berhasil diproses.' ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="osis-deposit-stat-grid"><article class="primary"><span><i class="bi bi-cash-stack"></i></span><small>Total diterima</small><strong>Rp <?= number_format($nominalDiterima, 0, ',', '.') ?></strong><p><?= $setoranDiterima ?> setoran telah diterima</p></article><article><span class="waiting"><i class="bi bi-hourglass-split"></i></span><small>Menunggu verifikasi</small><strong><?= $setoranMenunggu ?></strong><p>Setoran perlu diproses</p></article><article><span class="received"><i class="bi bi-check2-circle"></i></span><small>Sudah diterima</small><strong><?= $setoranDiterima ?></strong><p>Masuk ke kas OSIS</p></article><article><span class="rejected"><i class="bi bi-x-circle"></i></span><small>Ditolak</small><strong><?= $setoranDitolak ?></strong><p>Perlu konfirmasi ulang</p></article></section>

    <div class="table-card osis-deposit-table-card"><div class="osis-deposit-table-head"><div><span><i class="bi bi-inboxes"></i> Verifikasi setoran</span><h4>Daftar setoran kelas</h4><p>Periksa periode dan nominal sebelum menerima setoran.</p></div><small><?= count($confirmations) ?> data setoran</small></div><div class="table-responsive">
        <table class="table align-middle osis-deposit-table">
            <thead><tr><th>Kelas</th><th>Jurusan</th><th>Periode</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($confirmations as $confirmation): ?>
                <tr>
                    <td><strong class="osis-deposit-class-name"><?= htmlspecialchars($confirmation['nama_kelas']) ?></strong></td>
                    <td><span class="osis-deposit-department"><?= htmlspecialchars($confirmation['jurusan'] ?: '-') ?></span></td>
                    <td>

    <?php

    $bulanNama = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $namaBulan =
        $bulanNama[(int) $confirmation['bulan']]
        ?? $confirmation['bulan'];

    $labelFrekuensi = $confirmation['frekuensi'] === 'mingguan'
        ? 'Perminggu'
        : 'Perbulan';

    echo htmlspecialchars(
        $namaBulan .
        ' - ' .
        $labelFrekuensi .
        ' ' .
        $confirmation['tahun']
    );

    ?>

</td>
                    <td><strong class="osis-deposit-amount">Rp <?= number_format((float) $confirmation['nominal'], 0, ',', '.') ?></strong></td>
                    <td><span class="osis-deposit-status <?= htmlspecialchars($confirmation['status']) ?>"><i class="bi <?= $confirmation['status'] === 'diterima' ? 'bi-check-circle-fill' : ($confirmation['status'] === 'menunggu' ? 'bi-clock-fill' : 'bi-x-circle-fill') ?>"></i><?= htmlspecialchars(ucfirst($confirmation['status'])) ?></span></td>
                    <td>
                        <?php if ($confirmation['status'] === 'menunggu'): ?>
                            <form method="post" class="d-flex gap-2 osis-deposit-action-form">
                                <input type="hidden" name="confirmation_id" value="<?= (int) $confirmation['id'] ?>">
                                <input type="text" name="catatan" class="form-control" placeholder="Catatan penerimaan">
                                <button name="action" value="terima" class="btn btn-success"><i class="bi bi-check-lg"></i> Terima</button>
                                <button name="action" value="tolak" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i> Tolak</button>
                                <button name="action" value="hapus" class="btn btn-danger" onclick="return confirm('Hapus konfirmasi setoran ini?');" title="Hapus setoran"><i class="bi bi-trash3"></i></button>
                            </form>
                        <?php elseif ($confirmation['status'] === 'ditolak'): ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('Hapus konfirmasi setoran yang ditolak ini?');">
                                <input type="hidden" name="confirmation_id" value="<?= (int) $confirmation['id'] ?>">
                                <button name="action" value="hapus" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        <?php else: ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('Hapus konfirmasi setoran yang sudah diterima? Transaksi pemasukan Kas OSIS tetap tersimpan.');">
                                <input type="hidden" name="confirmation_id" value="<?= (int) $confirmation['id'] ?>">
                                <button name="action" value="hapus" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$confirmations): ?><tr><td colspan="6" class="text-center text-muted">Belum ada konfirmasi setoran.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div></div>
</div></main></div>
<?php require_once '../../includes/footer.php'; ?>
