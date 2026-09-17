<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

/*
|--------------------------------------------------------------------------
| KEAMANAN HALAMAN
|--------------------------------------------------------------------------
| Tetap gunakan fungsi auth lama agar sistem login tetap berjalan.
*/
requireOsisTreasurer();

/*
|--------------------------------------------------------------------------
| Pastikan yang masuk benar-benar BENDAHARA OSIS
|--------------------------------------------------------------------------
| Bukan:
| - bendahara kelas
| - anggota OSIS
| - siswa biasa
|
| Di database:
| users.role = 'osis'
| osis_members.position_id = 4 = Bendahara
|--------------------------------------------------------------------------
*/

$currentUserId = (int) (
    $_SESSION['user_id']
    ?? $_SESSION['id']
    ?? 0
);

$currentRole = $_SESSION['role'] ?? '';

if ($currentUserId <= 0) {
    header('Location: ../../login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Admin boleh melihat halaman ini.
| Selain admin, hanya user yang tercatat sebagai Bendahara OSIS.
|--------------------------------------------------------------------------
*/

$isAdmin = ($currentRole === 'admin');

$isOsisTreasurer = false;

if (!$isAdmin) {

    $checkTreasurer = $pdo->prepare("
        SELECT om.id
        FROM osis_members om
        INNER JOIN osis_positions op
            ON op.id = om.position_id
        INNER JOIN students s
            ON s.id = om.student_id
        WHERE s.user_id = ?
          AND om.status = 'aktif'
          AND op.nama_jabatan = 'Bendahara'
        LIMIT 1
    ");

    $checkTreasurer->execute([$currentUserId]);

    $isOsisTreasurer = (bool) $checkTreasurer->fetchColumn();

    if (!$isOsisTreasurer) {

        http_response_code(403);

        echo '
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Akses Ditolak</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f5f7fb;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                }

                .box {
                    background: white;
                    padding: 35px;
                    border-radius: 15px;
                    text-align: center;
                    box-shadow: 0 10px 30px rgba(0,0,0,.08);
                    max-width: 450px;
                }

                h2 {
                    color: #dc3545;
                    margin-bottom: 10px;
                }

                p {
                    color: #666;
                    line-height: 1.6;
                }

                a {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 10px 18px;
                    background: #0d6efd;
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                }
            </style>
        </head>

        <body>

            <div class="box">

                <h2>Akses Ditolak</h2>

                <p>
                    Halaman ini hanya dapat diakses oleh
                    <strong>Bendahara OSIS</strong>.
                </p>

                <a href="../../index.php">
                    Kembali
                </a>

            </div>

        </body>
        </html>
        ';

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Tahun
|--------------------------------------------------------------------------
*/

$year = max(
    2020,
    (int) ($_GET['tahun'] ?? date('Y'))
);

$showHistory = ($_GET['riwayat'] ?? '') === '1';


/*
|--------------------------------------------------------------------------
| Nama Bulan
|--------------------------------------------------------------------------
*/

$months = [
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];


/*
|--------------------------------------------------------------------------
| TOKEN SEDERHANA
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_uang_kas'])) {
    $_SESSION['csrf_uang_kas'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_uang_kas'];


/*
|--------------------------------------------------------------------------
| PROSES KLIK PEMBAYARAN
|--------------------------------------------------------------------------
|
| Ketika tombol merah "-" diklik, pembayaran dicatat lunas.
| Ketika centang hijau diklik, pembayaran dapat dibatalkan kembali.
|
| 1. Cek student_due_id benar-benar kas OSIS.
| 2. Cek siswa ada.
| 3. Jika pembayaran sudah ada -> UPDATE menjadi lunas.
| 4. Jika belum ada -> INSERT pembayaran baru.
|
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if (in_array($action, ['bayar', 'batal'], true)) {

        /*
        |------------------------------------------------------------------
        | Cek CSRF
        |------------------------------------------------------------------
        */

        if (
            empty($_POST['csrf_token']) ||
            !hash_equals(
                $_SESSION['csrf_uang_kas'],
                $_POST['csrf_token']
            )
        ) {

            $_SESSION['kas_error'] = 'Permintaan tidak valid.';

            header(
                'Location: uang_kas.php?tahun=' . $year
            );

            exit;
        }


        $studentDueId = (int) ($_POST['student_due_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);


        if ($studentDueId <= 0 || $studentId <= 0) {

            $_SESSION['kas_error'] = 'Data pembayaran tidak valid.';

            header(
                'Location: uang_kas.php?tahun=' . $year
            );

            exit;
        }


        /*
        |------------------------------------------------------------------
        | Pastikan periode adalah KAS OSIS
        |------------------------------------------------------------------
        */

        $dueStmt = $pdo->prepare("
            SELECT
                id,
                nominal,
                bulan,
                tahun,
                frekuensi,
                minggu_ke
            FROM student_dues
            WHERE id = ?
              AND tahun = ?
              AND jenis_kas = 'osis'
              AND status = 'aktif'
            LIMIT 1
        ");

        $dueStmt->execute([
            $studentDueId,
            $year
        ]);

        $due = $dueStmt->fetch(PDO::FETCH_ASSOC);


        if (!$due) {

            $_SESSION['kas_error'] =
                'Periode kas OSIS tidak ditemukan.';

            header(
                'Location: uang_kas.php?tahun=' . $year
            );

            exit;
        }


        /*
        |------------------------------------------------------------------
        | Pastikan siswa memang ada
        |------------------------------------------------------------------
        */

        $studentCheck = $pdo->prepare("
            SELECT id
            FROM students
            WHERE id = ?
            LIMIT 1
        ");

        $studentCheck->execute([$studentId]);

        if (!$studentCheck->fetchColumn()) {

            $_SESSION['kas_error'] =
                'Siswa tidak ditemukan.';

            header(
                'Location: uang_kas.php?tahun=' . $year
            );

            exit;
        }


        /*
        |------------------------------------------------------------------
        | Cek apakah pembayaran sudah mempunyai baris
        |------------------------------------------------------------------
        */

        $paymentCheck = $pdo->prepare("
            SELECT id
            FROM student_due_payments
            WHERE student_due_id = ?
              AND student_id = ?
            LIMIT 1
        ");

        $paymentCheck->execute([
            $studentDueId,
            $studentId
        ]);

        $paymentId = $paymentCheck->fetchColumn();

        /* Batalkan hanya pembayaran yang memang sudah tercatat. */
        if ($action === 'batal') {

            if (!$paymentId) {
                $_SESSION['kas_error'] = 'Pembayaran tidak ditemukan.';
            } else {
                $cancelPayment = $pdo->prepare("
                    UPDATE student_due_payments
                    SET
                        status = 'belum_bayar',
                        tanggal_bayar = NULL,
                        nominal = 0,
                        keterangan = 'Pembayaran dibatalkan oleh Bendahara OSIS'
                    WHERE id = ?
                ");
                $cancelPayment->execute([$paymentId]);
                $_SESSION['kas_success'] = 'Pembayaran berhasil dibatalkan.';
            }

            header('Location: uang_kas.php?tahun=' . $year . ($showHistory ? '&riwayat=1' : ''));
            exit;
        }


        $today = date('Y-m-d');

        $nominal = (float) $due['nominal'];


        /*
        |------------------------------------------------------------------
        | UPDATE jika sudah ada
        |------------------------------------------------------------------
        */

        if ($paymentId) {

            $updatePayment = $pdo->prepare("
                UPDATE student_due_payments
                SET
                    recorded_by = ?,
                    tanggal_bayar = ?,
                    nominal = ?,
                    status = 'lunas',
                    keterangan = 'Dicatat oleh Bendahara OSIS'
                WHERE id = ?
            ");

            $updatePayment->execute([
                $currentUserId,
                $today,
                $nominal,
                $paymentId
            ]);

        } else {

            /*
            |------------------------------------------------------------------
            | INSERT jika belum ada
            |------------------------------------------------------------------
            */

            $insertPayment = $pdo->prepare("
                INSERT INTO student_due_payments
                (
                    student_due_id,
                    student_id,
                    recorded_by,
                    tanggal_bayar,
                    nominal,
                    status,
                    keterangan
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'lunas',
                    'Dicatat oleh Bendahara OSIS'
                )
            ");

            $insertPayment->execute([
                $studentDueId,
                $studentId,
                $currentUserId,
                $today,
                $nominal
            ]);
        }


        $_SESSION['kas_success'] =
            'Pembayaran berhasil dicatat.';


        /*
        |------------------------------------------------------------------
        | Kembali ke halaman kas
        |------------------------------------------------------------------
        */

        header(
            'Location: uang_kas.php?tahun=' . $year . ($showHistory ? '&riwayat=1' : '')
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Pesan
|--------------------------------------------------------------------------
*/

$successMessage = $_SESSION['kas_success'] ?? '';
$errorMessage = $_SESSION['kas_error'] ?? '';

unset($_SESSION['kas_success']);
unset($_SESSION['kas_error']);


/*
|--------------------------------------------------------------------------
| Ambil pengaturan kas OSIS
|--------------------------------------------------------------------------
*/

$settingStmt = $pdo->prepare("
    SELECT *
    FROM cash_settings
    WHERE tahun = ?
      AND jenis_kas = 'osis'
      AND status = 'aktif'
    ORDER BY id DESC
    LIMIT 1
");

$settingStmt->execute([$year]);

$setting = $settingStmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Frekuensi kas
|--------------------------------------------------------------------------
*/

$frekuensi = $setting['frekuensi'] ?? 'bulanan';

$nominalSetting = (float) (
    $setting['nominal'] ?? 0
);

/* Tabel kas dimulai dari bulan pengaturan dibuat pada tahun berjalan. */
$settingStartMonth = 1;
if ($setting && substr((string) $setting['created_at'], 0, 4) === (string) $year) {
    $settingStartMonth = max(1, min(12, (int) substr((string) $setting['created_at'], 5, 2)));
}


/*
|--------------------------------------------------------------------------
| Ambil periode kas OSIS
|--------------------------------------------------------------------------
*/

$dues = [];


/*
|--------------------------------------------------------------------------
| Hanya ambil student_dues JENIS KAS OSIS
|--------------------------------------------------------------------------
*/

$dueStmt = $pdo->prepare("
    SELECT
        id,
        setting_id,
        jenis_kas,
        class_id,
        frekuensi,
        periode,
        bulan,
        tahun,
        minggu_ke,
        nominal,
        tanggal_mulai,
        tanggal_selesai,
        tanggal_jatuh_tempo,
        status
    FROM student_dues
    WHERE setting_id = ?
      AND tahun = ?
      AND jenis_kas = 'osis'
      AND status = 'aktif'
      AND bulan >= ?
    ORDER BY
        bulan ASC,
        minggu_ke ASC,
        periode ASC,
        id ASC
");

$dueStmt->execute([
    (int) ($setting['id'] ?? 0),
    $year,
    $settingStartMonth
]);


while ($row = $dueStmt->fetch(PDO::FETCH_ASSOC)) {

    $bulan = (int) $row['bulan'];

    $minggu = (int) $row['minggu_ke'];


    if ($row['frekuensi'] === 'mingguan') {

        if (!isset($dues[$bulan])) {
            $dues[$bulan] = [];
        }

        $dues[$bulan][$minggu] = $row;

    } else {

        $dues[$bulan] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| Ambil semua pembayaran
|--------------------------------------------------------------------------
*/

$payments = [];


$paymentStmt = $pdo->prepare("
    SELECT
        p.id,
        p.student_due_id,
        p.student_id,
        p.status,
        p.tanggal_bayar,
        p.nominal
    FROM student_due_payments p
    INNER JOIN student_dues d
        ON d.id = p.student_due_id
    WHERE d.tahun = ?
      AND d.setting_id = ?
      AND d.jenis_kas = 'osis'
      AND d.status = 'aktif'
");

$paymentStmt->execute([
    $year,
    (int) ($setting['id'] ?? 0)
]);


while ($row = $paymentStmt->fetch(PDO::FETCH_ASSOC)) {

    $studentId = (int) $row['student_id'];

    $dueId = (int) $row['student_due_id'];

    $payments[$studentId][$dueId] = $row;
}

/* Riwayat pembayaran dari pengaturan kas lama tetap harus dapat dibatalkan. */
$legacyPayments = [];
if ($showHistory && !empty($setting['id'])) {
    $legacyStmt = $pdo->prepare("
        SELECT p.student_due_id, p.student_id, p.tanggal_bayar, p.nominal,
               s.nama_lengkap, d.bulan, d.tahun, d.minggu_ke, d.frekuensi
        FROM student_due_payments p
        INNER JOIN student_dues d ON d.id = p.student_due_id
        INNER JOIN students s ON s.id = p.student_id
        WHERE d.jenis_kas = 'osis'
          AND d.tahun = ?
          AND d.setting_id <> ?
          AND p.status = 'lunas'
        ORDER BY d.bulan ASC, d.minggu_ke ASC, s.nama_lengkap ASC
    ");
    $legacyStmt->execute([$year, (int) $setting['id']]);
    $legacyPayments = $legacyStmt->fetchAll(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| Ambil siswa
|--------------------------------------------------------------------------
*/

$studentStmt = $pdo->query("
    SELECT
        id,
        nama_lengkap,
        kelas_id
    FROM students
    WHERE EXISTS (
        SELECT 1
        FROM osis_members om
        INNER JOIN academic_years ay
            ON ay.id = om.academic_year_id
        WHERE om.student_id = students.id
          AND om.status = 'aktif'
          AND ay.status = 'aktif'
    )
    ORDER BY nama_lengkap ASC
");

$students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Hanya tampilkan periode yang masih memiliki tunggakan.
|--------------------------------------------------------------------------
*/

$visibleMonths = [];

foreach ($months as $monthNumber => $monthName) {

    if ($frekuensi === 'mingguan') {

        $visibleWeeks = [];

        for ($minggu = 1; $minggu <= 5; $minggu++) {

            $due = $dues[$monthNumber][$minggu] ?? null;

            if (!$due) {
                continue;
            }

            if ($showHistory) {
                $visibleWeeks[] = $minggu;
                continue;
            }

            foreach ($students as $student) {

                $studentId = (int) $student['id'];
                $dueId = (int) $due['id'];
                $isPaid = isset($payments[$studentId][$dueId])
                    && $payments[$studentId][$dueId]['status'] === 'lunas';

                if (!$isPaid) {
                    $visibleWeeks[] = $minggu;
                    break;
                }
            }
        }

        if ($visibleWeeks) {
            $visibleMonths[$monthNumber] = $visibleWeeks;
        }

    } else {

        $due = $dues[$monthNumber] ?? null;

        if (!$due) {
            continue;
        }

        if ($showHistory) {
            $visibleMonths[$monthNumber] = true;
            continue;
        }

        $dueId = (int) $due['id'];

        foreach ($students as $student) {

            $studentId = (int) $student['id'];
            $isPaid = isset($payments[$studentId][$dueId])
                && $payments[$studentId][$dueId]['status'] === 'lunas';

            if (!$isPaid) {
                $visibleMonths[$monthNumber] = true;
                break;
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Judul halaman
|--------------------------------------------------------------------------
*/

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

$activeDues = [];
foreach ($dues as $dueGroup) {
    if ($frekuensi === 'mingguan') {
        foreach ($dueGroup as $due) {
            $activeDues[] = $due;
        }
    } else {
        $activeDues[] = $dueGroup;
    }
}
$totalTargetKas = count($students) * array_sum(array_map(static fn($due) => (float) $due['nominal'], $activeDues));
$totalKasTerkumpul = 0.0;
$totalPembayaranLunas = 0;
foreach ($payments as $studentPayments) {
    foreach ($studentPayments as $payment) {
        if (($payment['status'] ?? '') === 'lunas') {
            $totalPembayaranLunas++;
            foreach ($activeDues as $due) {
                if ((int) $due['id'] === (int) $payment['student_due_id']) {
                    $totalKasTerkumpul += (float) $due['nominal'];
                    break;
                }
            }
        }
    }
}
$totalSlotPembayaran = count($students) * count($activeDues);
$persentaseKas = $totalTargetKas > 0 ? min(100, round(($totalKasTerkumpul / $totalTargetKas) * 100)) : 0;

$pageTitle = 'Uang Kas OSIS';


require_once '../../includes/header.php';
require_once '../../includes/osis_sidebar.php';

?>


<div class="osis-main-content">

    <!-- TOPBAR -->

    <header class="osis-topbar">

        <button
            class="btn btn-light d-lg-none"
            id="osisMenuToggle"
            type="button"
        >
            <i class="bi bi-list"></i>
        </button>


        <div>

            <strong>
                Uang Kas OSIS
            </strong>

            <small class="d-block text-muted">
                Rekap pembayaran kas siswa
            </small>

        </div>


        <span class="text-muted small">

            <?= htmlspecialchars(
                $_SESSION['username'] ?? ''
            ) ?>

        </span>

    </header>


    <main class="role-page osis-cash-page">

        <div class="role-wrap">


            <!-- JUDUL -->

            <div class="role-hero">

                <span class="text-uppercase small">
                    Kas Siswa
                </span>

                <h2>
                    Tabel Uang Kas OSIS
                </h2>

                <p class="mb-0">
                    Rekap pembayaran kas siswa tahun <?= $year ?>.
                </p>

            </div>


            <!-- FILTER TAHUN -->

            <form
                class="payment-filter role-card mb-4"
                method="get"
            >

                <div class="row g-3 align-items-end">

                    <div class="col-sm-5 col-md-4">

                        <label class="form-label">
                            Tahun
                        </label>

                        <input
                            type="number"
                            name="tahun"
                            class="form-control"
                            min="2020"
                            value="<?= $year ?>"
                        >

                    </div>


                    <div class="col-sm-3 col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Tampilkan
                        </button>

                    </div>


                    <div class="col-sm-4 col-md-3">

                        <?php if ($showHistory): ?>

                            <a
                                href="uang_kas.php?tahun=<?= $year ?>"
                                class="btn btn-outline-secondary w-100"
                            >
                                <i class="bi bi-eye-slash me-1"></i>
                                Sembunyikan yang lunas
                            </a>

                        <?php else: ?>

                            <a
                                href="uang_kas.php?tahun=<?= $year ?>&riwayat=1"
                                class="btn btn-outline-primary w-100"
                            >
                                <i class="bi bi-clock-history me-1"></i>
                                Lihat riwayat
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </form>


            <!-- PESAN BERHASIL -->

            <?php if ($successMessage): ?>

                <div class="alert alert-success">

                    <i class="bi bi-check-circle"></i>

                    <?= htmlspecialchars($successMessage) ?>

                </div>

            <?php endif; ?>


            <!-- PESAN ERROR -->

            <?php if ($errorMessage): ?>

                <div class="alert alert-danger">

                    <i class="bi bi-exclamation-triangle"></i>

                    <?= htmlspecialchars($errorMessage) ?>

                </div>

            <?php endif; ?>


            <?php if (!$setting): ?>


                <div class="alert alert-warning">

                    <i class="bi bi-exclamation-triangle"></i>

                    Belum ada pengaturan kas OSIS untuk tahun
                    <?= $year ?>.

                    <a
                        href="pengaturan_kas.php"
                        class="alert-link"
                    >
                        Buka Pengaturan Kas
                    </a>

                </div>


            <?php else: ?>

                <section class="osis-cash-stat-grid">
                    <article class="osis-cash-stat primary"><span><i class="bi bi-wallet2"></i></span><small>Kas terkumpul</small><strong>Rp <?= number_format($totalKasTerkumpul, 0, ',', '.') ?></strong><p><?= $persentaseKas ?>% dari target</p></article>
                    <article class="osis-cash-stat"><span class="blue"><i class="bi bi-bullseye"></i></span><small>Target kas</small><strong>Rp <?= number_format($totalTargetKas, 0, ',', '.') ?></strong><p><?= count($activeDues) ?> periode kas</p></article>
                    <article class="osis-cash-stat"><span class="green"><i class="bi bi-people-fill"></i></span><small>Anggota OSIS</small><strong><?= count($students) ?></strong><p>siswa aktif terdaftar</p></article>
                    <article class="osis-cash-stat"><span class="violet"><i class="bi bi-check2-circle"></i></span><small>Pembayaran lunas</small><strong><?= $totalPembayaranLunas ?></strong><p>dari <?= $totalSlotPembayaran ?> pembayaran</p></article>
                </section>


                <!-- INFORMASI KAS -->

                <div class="role-card mb-4">

                    <div class="row g-4 align-items-center">


                        <div class="col-md-4">

                            <small class="text-muted d-block">
                                Jenis Kas
                            </small>

                            <h4 class="mb-0">
                                Kas OSIS
                            </h4>

                        </div>


                        <div class="col-md-4">

                            <small class="text-muted d-block">
                                Frekuensi
                            </small>

                            <h4 class="mb-0">

                                <?php if ($frekuensi === 'mingguan'): ?>

                                    Per Minggu

                                <?php else: ?>

                                    Per Bulan

                                <?php endif; ?>

                            </h4>

                        </div>


                        <div class="col-md-4">

                            <small class="text-muted d-block">
                                Nominal
                            </small>

                            <h4 class="mb-0">

                                Rp <?= number_format(
                                    $nominalSetting,
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                                <small class="text-muted">

                                    /

                                    <?= $frekuensi === 'mingguan'
                                        ? 'minggu'
                                        : 'bulan'
                                    ?>

                                </small>

                            </h4>

                        </div>

                    </div>

                </div>


                <?php if ($showHistory && !empty($legacyPayments)): ?>

                    <div class="role-card mb-4">

                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h4 class="mb-1">Riwayat pengaturan kas sebelumnya</h4>
                                <small class="text-muted">Pembayaran ini berasal dari pengaturan lama dan masih dihitung dalam saldo kas OSIS.</small>
                            </div>
                            <span class="badge text-bg-warning"><?= count($legacyPayments) ?> pembayaran</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead><tr><th>Siswa</th><th>Periode</th><th>Tanggal bayar</th><th>Nominal</th><th class="text-center">Aksi</th></tr></thead>
                                <tbody>
                                <?php foreach ($legacyPayments as $legacy): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($legacy['nama_lengkap']) ?></strong></td>
                                        <td><?= htmlspecialchars($months[(int) $legacy['bulan']] . ' ' . $legacy['tahun']) ?><?= $legacy['frekuensi'] === 'mingguan' ? ' - M' . (int) $legacy['minggu_ke'] : '' ?></td>
                                        <td><?= $legacy['tanggal_bayar'] ? htmlspecialchars(date('d M Y', strtotime($legacy['tanggal_bayar']))) : '-' ?></td>
                                        <td>Rp <?= number_format((float) $legacy['nominal'], 0, ',', '.') ?></td>
                                        <td class="text-center"><form method="post" class="m-0" onsubmit="return confirm('Batalkan pembayaran kas ini?');"><input type="hidden" name="action" value="batal"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="student_id" value="<?= (int) $legacy['student_id'] ?>"><input type="hidden" name="student_due_id" value="<?= (int) $legacy['student_due_id'] ?>"><button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Batalkan</button></form></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                <?php endif; ?>


                <?php if (empty($students)): ?>


                    <div class="alert alert-warning">

                        <i class="bi bi-exclamation-triangle"></i>

                        Data siswa belum ditemukan pada tabel
                        <strong>students</strong>.

                    </div>


                <?php else: ?>


                    <!--
                    =========================================================
                    TABEL UANG KAS OSIS
                    =========================================================
                    -->

                    <div class="role-card class-cash-table-card">

                        <div class="mb-3 class-cash-table-head">

                            <h4 class="mb-1">
                                Tabel Uang Kas OSIS
                            </h4>

                            <small class="text-muted">
                                Tahun <?= $year ?>
                            </small>

                        </div>


                        <div class="table-responsive payment-table-scroll class-cash-table-scroll">

                            <table
                                class="table table-bordered table-hover align-middle text-center payment-grid class-cash-payment-grid"
                                style="min-width: <?= $tableMinimumWidth ?>px;"
                            >


                                <!-- HEADER -->

                                <thead>


                                <?php if ($frekuensi === 'mingguan'): ?>


                                    <!-- BARIS BULAN -->

                                    <tr>

                                        <th
                                            rowspan="2"
                                            class="sticky-no"
                                        >
                                            No
                                        </th>


                                        <th
                                            rowspan="2"
                                            class="sticky-name"
                                        >
                                            Nama Siswa
                                        </th>


                                        <?php foreach ($visibleMonths as $monthNumber => $visibleWeeks): ?>

                                            <th colspan="<?= count($visibleWeeks) ?>">
                                                <?= strtoupper($months[$monthNumber]) ?>
                                            </th>

                                        <?php endforeach; ?>


                                    </tr>


                                    <!-- BARIS MINGGU -->

                                    <tr>

                                        <?php foreach ($visibleMonths as $monthNumber => $visibleWeeks): ?>

                                            <?php foreach ($visibleWeeks as $minggu): ?>
                                                <?php $period = $dues[$monthNumber][$minggu] ?? null; ?>
                                                <th title="<?= $period ? htmlspecialchars(date('d M Y', strtotime($period['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($period['tanggal_selesai']))) : '' ?>">
                                                    M<?= $minggu ?>
                                                    <?php if ($period): ?>
                                                        <small class="d-block fw-normal"><?= date('d', strtotime($period['tanggal_mulai'])) ?>–<?= date('d', strtotime($period['tanggal_selesai'])) ?></small>
                                                    <?php endif; ?>
                                                </th>
                                            <?php endforeach; ?>

                                        <?php endforeach; ?>

                                    </tr>


                                <?php else: ?>


                                    <!-- BULANAN -->

                                    <tr>

                                        <th
                                            class="sticky-no"
                                        >
                                            No
                                        </th>


                                        <th
                                            class="sticky-name"
                                        >
                                            Nama Siswa
                                        </th>


                                        <?php foreach ($visibleMonths as $monthNumber => $isVisible): ?>

                                            <th>
                                                <?= strtoupper($months[$monthNumber]) ?>
                                            </th>

                                        <?php endforeach; ?>


                                    </tr>


                                <?php endif; ?>


                                </thead>


                                <tbody>


                                <?php foreach ($students as $index => $student): ?>

                                    <?php

                                    $studentId =
                                        (int) $student['id'];

                                    $totalBayar = 0;

                                    ?>


                                    <tr>


                                        <!-- NO -->

                                        <td class="sticky-no">

                                            <?= $index + 1 ?>

                                        </td>


                                        <!-- NAMA -->

                                        <td class="sticky-name">

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $student['nama_lengkap']
                                                ) ?>

                                            </strong>

                                        </td>


                                        <?php if ($frekuensi === 'mingguan'): ?>


                                            <!--
                                            =================================================
                                            MINGGUAN
                                            =================================================
                                            -->

                                            <?php foreach ($visibleMonths as $monthNumber => $visibleWeeks): ?>


                                                <?php foreach ($visibleWeeks as $minggu): ?>


                                                    <?php

                                                    $due = $dues[$monthNumber][$minggu] ?? null;

                                                    ?>


                                                    <td>


                                                        <?php if (!$due): ?>


                                                            <!--
                                                            Minggu belum dibuat
                                                            -->

                                                            <span
                                                                class="text-muted"
                                                            >
                                                                -
                                                            </span>


                                                        <?php else: ?>


                                                            <?php

                                                            $dueId =
                                                                (int) $due['id'];

                                                            $isPaid =
                                                                isset(
                                                                    $payments[$studentId][$dueId]
                                                                )
                                                                &&
                                                                $payments[$studentId][$dueId]['status']
                                                                === 'lunas';

                                                            ?>


                                                            <?php if ($isPaid): ?>


                                                                <!-- SUDAH BAYAR -->

                                                                <form method="post" style="margin:0;" onsubmit="return confirm('Batalkan pembayaran siswa ini?');">
                                                                    <input type="hidden" name="action" value="batal">
                                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                                    <input type="hidden" name="student_id" value="<?= $studentId ?>">
                                                                    <input type="hidden" name="student_due_id" value="<?= $dueId ?>">
                                                                    <button type="submit" class="btn btn-success btn-sm" title="Klik untuk membatalkan pembayaran" style="min-width:34px; height:34px; padding:4px 10px; font-weight:bold;">
                                                                    ✓
                                                                    </button>
                                                                </form>


                                                                <?php

                                                                $totalBayar++;

                                                                ?>


                                                            <?php else: ?>


                                                                <!--
                                                                BELUM BAYAR
                                                                MERAH DAN BISA DIKLIK
                                                                -->

                                                                <form
                                                                    method="post"
                                                                    style="margin:0;"
                                                                    onsubmit="return confirm('Catat pembayaran siswa ini sebagai sudah bayar?');"
                                                                >

                                                                    <input
                                                                        type="hidden"
                                                                        name="action"
                                                                        value="bayar"
                                                                    >

                                                                    <input
                                                                        type="hidden"
                                                                        name="csrf_token"
                                                                        value="<?= htmlspecialchars($csrfToken) ?>"
                                                                    >

                                                                    <input
                                                                        type="hidden"
                                                                        name="student_id"
                                                                        value="<?= $studentId ?>"
                                                                    >

                                                                    <input
                                                                        type="hidden"
                                                                        name="student_due_id"
                                                                        value="<?= $dueId ?>"
                                                                    >


                                                                    <button
                                                                        type="submit"
                                                                        class="btn btn-danger btn-sm"
                                                                        title="Klik untuk mencatat pembayaran"
                                                                        style="
                                                                            min-width:34px;
                                                                            height:34px;
                                                                            padding:4px 10px;
                                                                            font-weight:bold;
                                                                        "
                                                                    >
                                                                        -
                                                                    </button>

                                                                </form>


                                                            <?php endif; ?>


                                                        <?php endif; ?>


                                                    </td>


                                                <?php endforeach; ?>


                                            <?php endforeach; ?>


                                        <?php else: ?>


                                            <!--
                                            =================================================
                                            BULANAN
                                            =================================================
                                            -->

                                            <?php foreach ($visibleMonths as $monthNumber => $isVisible): ?>


                                                <?php

                                                $due =
                                                    $dues[$monthNumber] ?? null;

                                                ?>


                                                <td>


                                                    <?php if (!$due): ?>


                                                        <span
                                                            class="text-muted"
                                                        >
                                                            -
                                                        </span>


                                                    <?php else: ?>


                                                        <?php

                                                        $dueId =
                                                            (int) $due['id'];

                                                        $isPaid =
                                                            isset(
                                                                $payments[$studentId][$dueId]
                                                            )
                                                            &&
                                                            $payments[$studentId][$dueId]['status']
                                                            === 'lunas';

                                                        ?>


                                                        <?php if ($isPaid): ?>


                                                            <form method="post" style="margin:0;" onsubmit="return confirm('Batalkan pembayaran siswa ini?');">
                                                                <input type="hidden" name="action" value="batal">
                                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                                <input type="hidden" name="student_id" value="<?= $studentId ?>">
                                                                <input type="hidden" name="student_due_id" value="<?= $dueId ?>">
                                                                <button type="submit" class="btn btn-success btn-sm" title="Klik untuk membatalkan pembayaran" style="min-width:34px; height:34px; padding:4px 10px; font-weight:bold;">
                                                                ✓
                                                                </button>
                                                            </form>


                                                            <?php

                                                            $totalBayar++;

                                                            ?>


                                                        <?php else: ?>


                                                            <form
                                                                method="post"
                                                                style="margin:0;"
                                                                onsubmit="return confirm('Catat pembayaran siswa ini sebagai sudah bayar?');"
                                                            >

                                                                <input
                                                                    type="hidden"
                                                                    name="action"
                                                                    value="bayar"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="csrf_token"
                                                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="student_id"
                                                                    value="<?= $studentId ?>"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="student_due_id"
                                                                    value="<?= $dueId ?>"
                                                                >


                                                                <button
                                                                    type="submit"
                                                                    class="btn btn-danger btn-sm"
                                                                    title="Klik untuk mencatat pembayaran"
                                                                    style="
                                                                        min-width:34px;
                                                                        height:34px;
                                                                        padding:4px 10px;
                                                                        font-weight:bold;
                                                                    "
                                                                >
                                                                    -
                                                                </button>

                                                            </form>


                                                        <?php endif; ?>


                                                    <?php endif; ?>


                                                </td>


                                            <?php endforeach; ?>


                                        <?php endif; ?>


                                    </tr>


                                <?php endforeach; ?>


                                </tbody>


                            </table>

                        </div>


                        <!-- KETERANGAN -->

                        <div class="mt-3">

                            <span
                                class="badge bg-success"
                                style="padding:8px 10px;"
                            >
                                ✓
                            </span>

                            Sudah bayar


                            &nbsp;&nbsp;


                            <span
                                class="badge bg-danger"
                                style="padding:8px 10px;"
                            >
                                -
                            </span>

                            Belum bayar


                            &nbsp;&nbsp;


                            <span class="text-muted">
                                - Periode belum dibuat
                            </span>


                        </div>


                    </div>


                <?php endif; ?>


            <?php endif; ?>


        </div>

    </main>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const scrollKey = 'simosis.uangKas.scrollPosition';
    const tableScroll = document.querySelector('.payment-table-scroll');
    const savedPosition = sessionStorage.getItem(scrollKey);

    if (savedPosition) {
        const position = JSON.parse(savedPosition);
        sessionStorage.removeItem(scrollKey);

        requestAnimationFrame(function () {
            window.scrollTo(0, position.top);

            if (tableScroll) {
                tableScroll.scrollLeft = position.left;
            }
        });
    }

    document.querySelectorAll('form[method="post"]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (event.defaultPrevented) {
                return;
            }

            sessionStorage.setItem(scrollKey, JSON.stringify({
                top: window.scrollY,
                left: tableScroll ? tableScroll.scrollLeft : 0
            }));
        });
    });
});
</script>


<?php
require_once '../../includes/footer.php';
?>
