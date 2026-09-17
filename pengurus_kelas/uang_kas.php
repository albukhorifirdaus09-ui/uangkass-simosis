<?php
require_once '../config/auth.php';
require_once '../config/database.php';

$officer = requireActiveClassOfficer($pdo);
if (($officer['position'] ?? '') !== 'bendahara_kelas') { header('Location: dashboard.php?error=akses'); exit; }

$classId = (int) $officer['class_id'];
$year = max(2020, (int) ($_GET['tahun'] ?? date('Y')));
$showHistory = ($_GET['riwayat'] ?? '') === '1';
$months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

if (empty($_SESSION['csrf_uang_kas_kelas'])) $_SESSION['csrf_uang_kas_kelas'] = bin2hex(random_bytes(32));
$csrfToken = $_SESSION['csrf_uang_kas_kelas'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $dueId = (int) ($_POST['student_due_id'] ?? 0);
    $studentId = (int) ($_POST['student_id'] ?? 0);
    $valid = in_array($action, ['bayar', 'batal'], true) && hash_equals($csrfToken, $_POST['csrf_token'] ?? '') && $dueId > 0 && $studentId > 0;
    if (!$valid) {
        $_SESSION['kas_kelas_error'] = 'Permintaan pembayaran tidak valid.';
    } else {
        $dueStmt = $pdo->prepare("SELECT id, nominal FROM student_dues WHERE id=? AND tahun=? AND jenis_kas='kelas' AND class_id=? AND status='aktif' LIMIT 1");
        $dueStmt->execute([$dueId, $year, $classId]); $due = $dueStmt->fetch(PDO::FETCH_ASSOC);
        $studentStmt = $pdo->prepare('SELECT id FROM students WHERE id=? AND kelas_id=? LIMIT 1');
        $studentStmt->execute([$studentId, $classId]);
        if (!$due || !$studentStmt->fetchColumn()) {
            $_SESSION['kas_kelas_error'] = 'Data pembayaran tidak ditemukan.';
        } elseif ($action === 'batal') {
            $stmt = $pdo->prepare("UPDATE student_due_payments SET status='belum_bayar', tanggal_bayar=NULL, nominal=0, keterangan='Pembayaran dibatalkan oleh Bendahara Kelas' WHERE student_due_id=? AND student_id=?");
            $stmt->execute([$dueId, $studentId]); $_SESSION['kas_kelas_success'] = 'Pembayaran berhasil dibatalkan.';
        } else {
            $check = $pdo->prepare('SELECT id FROM student_due_payments WHERE student_due_id=? AND student_id=? LIMIT 1');
            $check->execute([$dueId, $studentId]); $paymentId = $check->fetchColumn();
            if ($paymentId) {
                $stmt = $pdo->prepare("UPDATE student_due_payments SET recorded_by=?, tanggal_bayar=CURDATE(), nominal=?, status='lunas', keterangan='Dicatat oleh Bendahara Kelas' WHERE id=?");
                $stmt->execute([(int) $_SESSION['user_id'], $due['nominal'], $paymentId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO student_due_payments (student_due_id,student_id,recorded_by,tanggal_bayar,nominal,status,keterangan) VALUES (?,?,?,CURDATE(),?,'lunas','Dicatat oleh Bendahara Kelas')");
                $stmt->execute([$dueId, $studentId, (int) $_SESSION['user_id'], $due['nominal']]);
            }
            $_SESSION['kas_kelas_success'] = 'Pembayaran berhasil dicatat.';
        }
    }
    header('Location: uang_kas.php?tahun='.$year.($showHistory ? '&riwayat=1' : '')); exit;
}

$successMessage = $_SESSION['kas_kelas_success'] ?? '';
$errorMessage = $_SESSION['kas_kelas_error'] ?? '';
unset($_SESSION['kas_kelas_success'], $_SESSION['kas_kelas_error']);

$settingStmt = $pdo->prepare("SELECT * FROM cash_settings WHERE jenis_kas='kelas' AND class_id=? AND tahun=? AND status='aktif' ORDER BY id DESC LIMIT 1");
$settingStmt->execute([$classId, $year]); $setting = $settingStmt->fetch(PDO::FETCH_ASSOC);

/* Endpoint ringan untuk pembaruan otomatis tampilan kas kelas. */
if (($_GET['ajax'] ?? '') === 'pengaturan_kas') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'setting_id' => (int) ($setting['id'] ?? 0),
        'frekuensi' => $setting['frekuensi'] ?? null,
        'nominal' => (float) ($setting['nominal'] ?? 0),
    ]);
    exit;
}

$activeSettingId = (int) ($setting['id'] ?? 0);
$frekuensi = $setting['frekuensi'] ?? 'bulanan'; $nominalSetting = (float) ($setting['nominal'] ?? 0);

$dueStmt = $pdo->prepare("SELECT id,frekuensi,bulan,minggu_ke,nominal,tanggal_mulai,tanggal_selesai FROM student_dues WHERE setting_id=? AND tahun=? AND jenis_kas='kelas' AND class_id=? AND status='aktif' ORDER BY bulan,minggu_ke,periode,id");
$dueStmt->execute([$activeSettingId, $year, $classId]); $dues = [];
while ($due = $dueStmt->fetch(PDO::FETCH_ASSOC)) {
    if ($due['frekuensi'] === 'mingguan') $dues[(int)$due['bulan']][(int)$due['minggu_ke']] = $due;
    else $dues[(int)$due['bulan']] = $due;
}
$studentStmt = $pdo->prepare('SELECT id,nama_lengkap FROM students WHERE kelas_id=? ORDER BY nama_lengkap');
$studentStmt->execute([$classId]); $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);
$paymentStmt = $pdo->prepare("SELECT p.student_due_id,p.student_id,p.status FROM student_due_payments p INNER JOIN student_dues d ON d.id=p.student_due_id WHERE d.setting_id=? AND d.tahun=? AND d.jenis_kas='kelas' AND d.class_id=? AND d.status='aktif'");
$paymentStmt->execute([$activeSettingId, $year, $classId]); $payments = [];
while ($payment = $paymentStmt->fetch(PDO::FETCH_ASSOC)) $payments[(int)$payment['student_id']][(int)$payment['student_due_id']] = $payment;

$visibleMonths = [];
foreach ($months as $monthNumber => $monthName) {
    if ($frekuensi === 'mingguan') {
        $weeks = [];
        foreach ($dues[$monthNumber] ?? [] as $week => $due) {
            $unpaid = false; foreach ($students as $student) if (($payments[$student['id']][$due['id']]['status'] ?? '') !== 'lunas') { $unpaid = true; break; }
            if ($showHistory || $unpaid) $weeks[] = $week;
        }
        if ($weeks) $visibleMonths[$monthNumber] = $weeks;
    } elseif (isset($dues[$monthNumber])) {
        $due = $dues[$monthNumber]; $unpaid = false;
        foreach ($students as $student) if (($payments[$student['id']][$due['id']]['status'] ?? '') !== 'lunas') { $unpaid = true; break; }
        if ($showHistory || $unpaid) $visibleMonths[$monthNumber] = true;
    }
}

$visiblePeriodCount = 0;
if ($frekuensi === 'mingguan') {
    foreach ($visibleMonths as $weeks) {
        $visiblePeriodCount += count($weeks);
    }
} else {
    $visiblePeriodCount = count($visibleMonths);
}
$periodColumnWidth = $frekuensi === 'mingguan' ? 84 : 100;
$tableMinimumWidth = 70 + 220 + ($visiblePeriodCount * $periodColumnWidth);

/* Ringkasan visual kas kelas, dihitung dari periode dan pembayaran yang sama dengan tabel. */
$allActiveDues = [];
foreach ($dues as $dueGroup) {
    if ($frekuensi === 'mingguan') {
        foreach ($dueGroup as $due) $allActiveDues[] = $due;
    } else {
        $allActiveDues[] = $dueGroup;
    }
}
$totalTargetKas = count($students) * array_sum(array_map(static fn($due) => (float) $due['nominal'], $allActiveDues));
$totalKasTerkumpul = 0.0;
$totalPembayaranLunas = 0;
foreach ($payments as $studentPayments) {
    foreach ($studentPayments as $payment) {
        if (($payment['status'] ?? '') === 'lunas') {
            $totalPembayaranLunas++;
            $dueNominal = 0.0;
            foreach ($allActiveDues as $due) if ((int) $due['id'] === (int) $payment['student_due_id']) { $dueNominal = (float) $due['nominal']; break; }
            $totalKasTerkumpul += $dueNominal;
        }
    }
}
$totalSlotPembayaran = count($students) * count($allActiveDues);
$persentaseKas = $totalTargetKas > 0 ? min(100, round(($totalKasTerkumpul / $totalTargetKas) * 100)) : 0;

function paymentButton($due, $student, $payments, $csrfToken) {
    if (!$due) return '<span class="text-muted">-</span>';
    $dueId = (int)$due['id']; $studentId = (int)$student['id'];
    $paid = (($payments[$studentId][$dueId]['status'] ?? '') === 'lunas');
    $action = $paid ? 'batal' : 'bayar'; $class = $paid ? 'btn-success' : 'btn-danger';
    $label = $paid ? 'Batalkan pembayaran siswa ini?' : 'Catat pembayaran siswa ini sebagai sudah bayar?';
    $title = $paid ? 'Klik untuk membatalkan pembayaran' : 'Klik untuk mencatat pembayaran';
    return '<form method="post" style="margin:0;" onsubmit="return confirm(\''.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'\');"><input type="hidden" name="action" value="'.$action.'"><input type="hidden" name="csrf_token" value="'.htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8').'"><input type="hidden" name="student_id" value="'.$studentId.'"><input type="hidden" name="student_due_id" value="'.$dueId.'"><button type="submit" class="btn '.$class.' btn-sm" title="'.$title.'" style="min-width:34px;height:34px;padding:4px 10px;font-weight:bold;">'.($paid ? '✓' : '-').'</button></form>';
}

$pageTitle = 'Uang Kas Kelas'; require_once '../includes/header.php'; require_once '../includes/class_sidebar.php';
?>
<main class="class-main">
    <header class="class-topbar"><div class="class-topbar-left"><button type="button" class="class-menu-toggle" id="classMenuToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button><div><h5 class="mb-0">Uang Kas</h5><small><?= htmlspecialchars($officer['nama_kelas'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small></div></div><div class="class-user"><div class="class-user-avatar"><i class="bi bi-person-fill"></i></div><div><strong><?= htmlspecialchars($officer['nama_lengkap'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong><small>Bendahara Kelas</small></div></div></header>
    <section class="role-page"><div class="role-wrap class-cash-page-modern">
        <div class="class-cash-hero"><div><span class="class-cash-kicker"><i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($labelKelas ?? ($officer['nama_kelas'] ?? 'Kelas'), ENT_QUOTES, 'UTF-8') ?></span><h2>Uang kas kelas</h2><p>Catat pembayaran siswa dan pantau progres kas kelas dengan lebih mudah.</p></div><span class="class-cash-hero-period"><i class="bi bi-calendar3"></i> Tahun <?= $year ?></span></div>
        <form class="class-cash-filter" method="get"><div class="class-cash-filter-label"><i class="bi bi-funnel"></i><span>Periode kas</span></div><input type="number" name="tahun" class="form-control" min="2020" value="<?= $year ?>"><button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Tampilkan</button><?php if ($showHistory): ?><a href="uang_kas.php?tahun=<?= $year ?>" class="btn btn-outline-secondary"><i class="bi bi-eye-slash me-1"></i>Sembunyikan riwayat</a><?php else: ?><a href="uang_kas.php?tahun=<?= $year ?>&riwayat=1" class="btn btn-outline-primary"><i class="bi bi-clock-history me-1"></i>Lihat riwayat</a><?php endif; ?></form>
        <?php if ($successMessage): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!$setting): ?><div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Bendahara OSIS belum membuat pengaturan kas kelas untuk tahun <?= $year ?>.</div>
        <?php elseif (!$students): ?><div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Data siswa untuk kelas ini belum ditemukan.</div>
        <?php else: ?>
            <section class="class-cash-stat-grid"><article class="class-cash-stat primary"><span class="class-cash-stat-icon"><i class="bi bi-wallet2"></i></span><small>Kas terkumpul</small><strong>Rp <?= number_format($totalKasTerkumpul, 0, ',', '.') ?></strong><p><?= $persentaseKas ?>% dari target kas</p></article><article class="class-cash-stat"><span class="class-cash-stat-icon blue"><i class="bi bi-bullseye"></i></span><small>Target kas</small><strong>Rp <?= number_format($totalTargetKas, 0, ',', '.') ?></strong><p><?= count($allActiveDues) ?> periode tersedia</p></article><article class="class-cash-stat"><span class="class-cash-stat-icon green"><i class="bi bi-check2-circle"></i></span><small>Pembayaran lunas</small><strong><?= $totalPembayaranLunas ?></strong><p>dari <?= $totalSlotPembayaran ?> pembayaran</p></article><article class="class-cash-stat"><span class="class-cash-stat-icon violet"><i class="bi bi-cash-coin"></i></span><small>Aturan kas</small><strong>Rp <?= number_format($nominalSetting, 0, ',', '.') ?></strong><p>/ <?= $frekuensi === 'mingguan' ? 'minggu' : 'bulan' ?></p></article></section>
            <div class="class-cash-info-card"><div><span><i class="bi bi-gear-fill"></i> Pengaturan kas</span><strong><?= $frekuensi === 'mingguan' ? 'Kas per minggu' : 'Kas per bulan' ?></strong><small>Pengaturan ini ditentukan oleh Bendahara OSIS.</small></div><div class="class-cash-progress-group"><span>Progres pengumpulan <strong><?= $persentaseKas ?>%</strong></span><div class="class-cash-progress"><i style="width: <?= $persentaseKas ?>%"></i></div></div></div>
            <div class="class-cash-table-card"><div class="class-cash-table-head"><div><span>Catat pembayaran</span><h4>Tabel uang kas kelas</h4><p>Klik tombol merah untuk mencatat pembayaran, atau centang hijau untuk membatalkannya.</p></div><div class="class-cash-table-legend"><span><i class="legend-paid"></i> Sudah bayar</span><span><i class="legend-unpaid"></i> Belum bayar</span></div></div>
            <?php if (!$visibleMonths): ?><div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Semua periode yang tersedia sudah lunas. Pilih <strong>Lihat riwayat</strong> untuk menampilkannya.</div>
            <?php else: ?><div class="table-responsive payment-table-scroll class-cash-table-scroll"><table class="table table-bordered table-hover align-middle text-center payment-grid class-cash-payment-grid" style="min-width:<?= $tableMinimumWidth ?>px;"><thead>
                <?php if ($frekuensi === 'mingguan'): ?><tr><th rowspan="2" class="sticky-no">No</th><th rowspan="2" class="sticky-name">Nama Siswa</th><?php foreach ($visibleMonths as $month=>$weeks): ?><th colspan="<?= count($weeks) ?>"><?= strtoupper($months[$month]) ?></th><?php endforeach; ?></tr><tr><?php foreach ($visibleMonths as $month=>$weeks): foreach ($weeks as $week): $period=$dues[$month][$week]??null; ?><th title="<?= $period ? htmlspecialchars(date('d M Y',strtotime($period['tanggal_mulai'])).' - '.date('d M Y',strtotime($period['tanggal_selesai']))) : '' ?>">M<?= $week ?><?php if($period): ?><small class="d-block fw-normal"><?= date('d',strtotime($period['tanggal_mulai'])) ?>–<?= date('d',strtotime($period['tanggal_selesai'])) ?></small><?php endif; ?></th><?php endforeach; endforeach; ?></tr>
                <?php else: ?><tr><th class="sticky-no">No</th><th class="sticky-name">Nama Siswa</th><?php foreach ($visibleMonths as $month=>$_): ?><th><?= strtoupper($months[$month]) ?></th><?php endforeach; ?></tr><?php endif; ?></thead><tbody>
                <?php foreach ($students as $index=>$student): ?><tr><td class="sticky-no"><?= $index+1 ?></td><td class="sticky-name"><span class="class-cash-student-initial"><?= htmlspecialchars(strtoupper(mb_substr($student['nama_lengkap'], 0, 1, 'UTF-8'))) ?></span><strong><?= htmlspecialchars($student['nama_lengkap']) ?></strong></td><?php if ($frekuensi === 'mingguan'): ?><?php foreach ($visibleMonths as $month=>$weeks): foreach ($weeks as $week): ?><td><?= paymentButton($dues[$month][$week] ?? null, $student, $payments, $csrfToken) ?></td><?php endforeach; endforeach; ?><?php else: ?><?php foreach ($visibleMonths as $month=>$_): ?><td><?= paymentButton($dues[$month] ?? null, $student, $payments, $csrfToken) ?></td><?php endforeach; ?><?php endif; ?></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
        <?php endif; ?>
    </div></section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const key = 'simosis.kasKelas.scrollPosition';
    const table = document.querySelector('.payment-table-scroll');
    const saved = sessionStorage.getItem(key);
    const setting = <?= json_encode([
        'setting_id' => (int) ($setting['id'] ?? 0),
        'frekuensi' => $setting['frekuensi'] ?? null,
        'nominal' => (float) ($setting['nominal'] ?? 0),
    ]) ?>;

    if (saved) {
        const position = JSON.parse(saved);
        sessionStorage.removeItem(key);
        requestAnimationFrame(() => {
            window.scrollTo(0, position.top);
            if (table) table.scrollLeft = position.left;
        });
    }

    document.querySelectorAll('form[method="post"]').forEach((form) => {
        form.addEventListener('submit', () => {
            sessionStorage.setItem(key, JSON.stringify({
                top: window.scrollY,
                left: table ? table.scrollLeft : 0
            }));
        });
    });

    /* Cek perubahan pengaturan dari Bendahara OSIS setiap 10 detik. */
    window.setInterval(() => {
        fetch('uang_kas.php?tahun=<?= $year ?>&ajax=pengaturan_kas', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
        .then((response) => response.ok ? response.json() : null)
        .then((latest) => {
            if (!latest) return;
            if (latest.setting_id !== setting.setting_id
                || latest.frekuensi !== setting.frekuensi
                || Number(latest.nominal) !== Number(setting.nominal)) {
                sessionStorage.setItem(key, JSON.stringify({
                    top: window.scrollY,
                    left: table ? table.scrollLeft : 0
                }));
                window.location.reload();
            }
        })
        .catch(() => {});
    }, 10000);
});
</script>
<?php require_once '../includes/footer.php'; ?>
