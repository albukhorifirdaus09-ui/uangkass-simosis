<?php

require_once "../config/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| CEK BENDAHARA KELAS AKTIF
|--------------------------------------------------------------------------
*/

$officer = requireActiveClassOfficer($pdo);

if (($officer['position'] ?? '') !== 'bendahara_kelas') {
    header('Location: dashboard.php?error=akses');
    exit;
}


$classId = (int) $officer['class_id'];


/*
|--------------------------------------------------------------------------
| PERIODE
|--------------------------------------------------------------------------
*/

$selectedMonth = (int) (
    $_GET['bulan'] ?? date('n')
);

$selectedYear = (int) (
    $_GET['tahun'] ?? date('Y')
);


/*
|--------------------------------------------------------------------------
| VALIDASI BULAN
|--------------------------------------------------------------------------
*/

if ($selectedMonth < 1 || $selectedMonth > 12) {
    $selectedMonth = (int) date('n');
}


/*
|--------------------------------------------------------------------------
| VALIDASI TAHUN
|--------------------------------------------------------------------------
*/

if ($selectedYear < 2020 || $selectedYear > 2100) {
    $selectedYear = (int) date('Y');
}


/*
|--------------------------------------------------------------------------
| NAMA BULAN
|--------------------------------------------------------------------------
*/

$monthNames = [
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

$monthName = $monthNames[$selectedMonth] ?? '';


/*
|--------------------------------------------------------------------------
| AMBIL PENGATURAN DAN TAGIHAN KAS KELAS
|--------------------------------------------------------------------------
*/

$settingStmt = $pdo->prepare("
    SELECT id
    FROM cash_settings
    WHERE jenis_kas = 'kelas'
      AND class_id = ?
      AND tahun = ?
      AND status = 'aktif'
    ORDER BY id DESC
    LIMIT 1
");

$settingStmt->execute([$classId, $selectedYear]);
$classSettingId = (int) $settingStmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT
        id,
        bulan,
        tahun,
        nominal,
        tanggal_jatuh_tempo,
        status
    FROM student_dues
    WHERE setting_id = ?
      AND jenis_kas = 'kelas'
      AND class_id = ?
      AND bulan = ?
      AND tahun = ?
      AND status = 'aktif'
    ORDER BY minggu_ke ASC, id ASC
    LIMIT 1
");

$stmt->execute([
    $classSettingId,
    $classId,
    $selectedMonth,
    $selectedYear
]);

$due = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| JIKA TAGIHAN BELUM ADA
|--------------------------------------------------------------------------
*/

if (!$due) {

    header(
        "Location: uang_kas.php?error=tagihan"
    );

    exit;
}


$dueId = (int) $due['id'];
$nominalKas = (float) ($due['nominal'] ?? 0);


/*
|--------------------------------------------------------------------------
| AMBIL DATA SISWA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.nama_lengkap,

        CASE
            WHEN COUNT(d.id) > 0
             AND SUM(CASE WHEN sdp.status = 'lunas' THEN 1 ELSE 0 END) = COUNT(d.id)
            THEN 'lunas'
            ELSE 'belum_bayar'
        END AS payment_status,

        COALESCE(
            SUM(CASE WHEN sdp.status = 'lunas' THEN sdp.nominal ELSE 0 END),
            0
        ) AS payment_nominal,

        MAX(
            CASE WHEN sdp.status = 'lunas' THEN sdp.tanggal_bayar END
        ) AS tanggal_bayar

    FROM students s

    LEFT JOIN student_dues d
        ON d.setting_id = ?
        AND d.jenis_kas = 'kelas'
        AND d.class_id = ?
        AND d.bulan = ?
        AND d.tahun = ?
        AND d.status = 'aktif'

    LEFT JOIN student_due_payments sdp
        ON sdp.student_id = s.id
        AND sdp.student_due_id = d.id

    WHERE s.kelas_id = ?

    GROUP BY
        s.id,
        s.nama_lengkap

    ORDER BY s.nama_lengkap ASC
");

$stmt->execute([
    $classSettingId,
    $classId,
    $selectedMonth,
    $selectedYear,
    $classId
]);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HITUNG PEMBAYARAN
|--------------------------------------------------------------------------
*/

$totalStudents = count($students);

$totalPaid = 0;

$totalAmount = 0;


foreach ($students as $student) {

    if (($student['payment_status'] ?? '') === 'lunas') {

        $totalPaid++;

        /*
        |--------------------------------------------------------------------------
        | Gunakan nominal pembayaran yang tercatat.
        |--------------------------------------------------------------------------
        */

        $totalAmount += (float) (
            $student['payment_nominal']
            ?? $nominalKas
        );
    }
}


$totalUnpaid = max(
    0,
    $totalStudents - $totalPaid
);


$paymentPercent = $totalStudents > 0
    ? round(
        ($totalPaid / $totalStudents) * 100
    )
    : 0;


$paymentPercent = max(
    0,
    min(
        100,
        $paymentPercent
    )
);


/*
|--------------------------------------------------------------------------
| CEK KONFIRMASI SETORAN
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        *
    FROM class_due_confirmations
    WHERE student_due_id = ?
      AND class_id = ?
    LIMIT 1
");

$stmt->execute([
    $dueId,
    $classId
]);

$confirmation = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PESAN ERROR
|--------------------------------------------------------------------------
*/

$error = null;


/*
|--------------------------------------------------------------------------
| PROSES KONFIRMASI
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $catatan = trim(
        $_POST['catatan'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | SEMUA SISWA HARUS LUNAS
    |--------------------------------------------------------------------------
    */

    if ($totalStudents === 0) {

        $error =
            'Belum ada siswa di kelas ini. Tambahkan siswa terlebih dahulu.';

    } elseif ($totalPaid !== $totalStudents) {

        $error =
            'Semua siswa harus ditandai lunas sebelum setoran dikonfirmasi.';

    } elseif (
        $confirmation
        &&
        $confirmation['status'] === 'diterima'
    ) {

        $error =
            'Setoran periode ini sudah diterima bendahara OSIS.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | SIMPAN / UPDATE KONFIRMASI
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            INSERT INTO class_due_confirmations
            (
                student_due_id,
                class_id,
                submitted_by,
                status,
                nominal,
                catatan
            )
            VALUES
            (
                ?,
                ?,
                ?,
                'menunggu',
                ?,
                ?
            )

            ON DUPLICATE KEY UPDATE

                submitted_by = VALUES(submitted_by),

                status = 'menunggu',

                nominal = VALUES(nominal),

                catatan = VALUES(catatan),

                confirmed_by = NULL,

                confirmed_at = NULL
        ");

        $stmt->execute([
            $dueId,
            $classId,
            (int) $_SESSION['user_id'],
            $totalAmount,
            $catatan !== '' ? $catatan : null
        ]);


        /*
        |--------------------------------------------------------------------------
        | KEMBALI
        |--------------------------------------------------------------------------
        */

        header(
            "Location: konfirmasi.php?bulan={$selectedMonth}&tahun={$selectedYear}&success=1"
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| JUDUL HALAMAN
|--------------------------------------------------------------------------
*/

$pageTitle = 'Konfirmasi Setoran Kelas';


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once '../includes/header.php';


/*
|--------------------------------------------------------------------------
| SIDEBAR
|--------------------------------------------------------------------------
*/

require_once '../includes/class_sidebar.php';

?>


<!-- ============================================================
     MAIN
============================================================ -->

<main class="class-main">


    <!-- ========================================================
         TOPBAR
    ========================================================= -->

    <header class="class-topbar">


        <div class="class-topbar-left">


            <!-- MENU MOBILE -->

            <button
                type="button"
                class="class-menu-toggle"
                id="classMenuToggle"
                aria-label="Buka menu"
            >

                <i class="bi bi-list"></i>

            </button>


            <!-- JUDUL -->

            <div>

                <h5 class="mb-0">
                    Setoran OSIS
                </h5>


                <small>

                    Konfirmasi setoran kas kelas

                </small>

            </div>


        </div>


        <!-- ====================================================
             USER
        ===================================================== -->

        <div class="class-user">


            <div class="class-user-avatar">


                <?php

                $namaBendahara =
                    trim(
                        (string) (
                            $officer['nama_lengkap']
                            ?? ''
                        )
                    );


                $avatar =
                    $namaBendahara !== ''
                    ? strtoupper(
                        mb_substr(
                            $namaBendahara,
                            0,
                            1,
                            'UTF-8'
                        )
                    )
                    : 'B';

                ?>


                <?= htmlspecialchars(
                    $avatar,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>


            </div>


            <div>


                <strong>

                    <?= htmlspecialchars(
                        $namaBendahara !== ''
                            ? $namaBendahara
                            : 'Bendahara',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                </strong>


                <small>
                    Bendahara Kelas
                </small>


            </div>


        </div>


    </header>


    <!-- ========================================================
         CONTENT
    ========================================================= -->

    <section class="class-content class-deposit-page">


        <!-- ====================================================
             KEMBALI
        ===================================================== -->

        <a
            href="pembayaran.php?bulan=<?= $selectedMonth; ?>&tahun=<?= $selectedYear; ?>"
            class="btn btn-link px-0 mb-2"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Kembali ke pembayaran

        </a>


        <!-- ====================================================
             JUDUL
        ===================================================== -->

        <div class="page-intro">


            <div>


                <span class="eyebrow">
                    Keuangan Kelas
                </span>


                <h2>
                    Konfirmasi Setoran Kelas
                </h2>


                <p>

                    <?= htmlspecialchars(
                        $officer['nama_kelas'] ?? '-',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>


                    ·


                    <?= htmlspecialchars(
                        $monthName,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>


                    <?= $selectedYear; ?>

                </p>


            </div>


        </div>


        <!-- ====================================================
             SUCCESS
        ===================================================== -->

        <?php if (isset($_GET['success'])): ?>


            <div
                class="alert alert-success"
                role="alert"
            >

                <i class="bi bi-check-circle-fill me-2"></i>

                Konfirmasi setoran berhasil dikirim
                ke bendahara OSIS.

            </div>


        <?php endif; ?>


        <!-- ====================================================
             ERROR
        ===================================================== -->

        <?php if (!empty($error)): ?>


            <div
                class="alert alert-danger"
                role="alert"
            >

                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>

            </div>


        <?php endif; ?>


        <!-- ====================================================
             RINGKASAN
        ===================================================== -->

        <div class="row g-3 mb-4">


            <!-- SUDAH BAYAR -->

            <div class="col-md-4">


                <div class="stat-card">


                    <div class="stat-card-icon">

                        <i class="bi bi-check-circle"></i>

                    </div>


                    <div>


                        <span class="text-muted">
                            Sudah Bayar
                        </span>


                        <h4 class="mb-0">

                            <?= $totalPaid; ?>

                            <small class="text-muted">

                                /

                                <?= $totalStudents; ?>

                            </small>

                        </h4>


                    </div>


                </div>


            </div>


            <!-- BELUM BAYAR -->

            <div class="col-md-4">


                <div class="stat-card">


                    <div class="stat-card-icon">

                        <i class="bi bi-clock"></i>

                    </div>


                    <div>


                        <span class="text-muted">
                            Belum Bayar
                        </span>


                        <h4 class="mb-0">

                            <?= $totalUnpaid; ?>

                        </h4>


                    </div>


                </div>


            </div>


            <!-- TOTAL SETORAN -->

            <div class="col-md-4">


                <div class="stat-card">


                    <div class="stat-card-icon">

                        <i class="bi bi-wallet2"></i>

                    </div>


                    <div>


                        <span class="text-muted">
                            Total Setoran
                        </span>


                        <h4 class="mb-0">

                            Rp

                            <?= number_format(
                                $totalAmount,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </h4>


                    </div>


                </div>


            </div>


        </div>


        <!-- ====================================================
             STATUS SETORAN
        ===================================================== -->

        <?php if ($confirmation): ?>


            <?php

            $confirmationStatus =
                $confirmation['status'] ?? 'menunggu';


            if (
                $confirmationStatus === 'diterima'
            ) {

                $statusClass = 'alert-success';

                $statusIcon = 'bi-check-circle-fill';

                $statusText = 'Diterima';

            } elseif (
                $confirmationStatus === 'ditolak'
            ) {

                $statusClass = 'alert-danger';

                $statusIcon = 'bi-x-circle-fill';

                $statusText = 'Ditolak';

            } else {

                $statusClass = 'alert-warning';

                $statusIcon = 'bi-clock-fill';

                $statusText = 'Menunggu';

            }

            ?>


            <div
                class="alert <?= $statusClass; ?>"
                role="alert"
            >


                <i
                    class="bi <?= $statusIcon; ?> me-2"
                ></i>


                <strong>
                    Status Setoran:
                </strong>


                <?= htmlspecialchars(
                    $statusText,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>


                <?php if (
                    !empty($confirmation['catatan'])
                ): ?>


                    <div class="mt-2">


                        <small>

                            <strong>
                                Catatan:
                            </strong>


                            <?= htmlspecialchars(
                                $confirmation['catatan'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>


                        </small>


                    </div>


                <?php endif; ?>


            </div>


        <?php endif; ?>


        <!-- ====================================================
             PROGRESS
        ===================================================== -->

        <div class="table-card mb-4">


            <div
                class="d-flex justify-content-between align-items-center mb-2"
            >

                <strong>
                    Progress Pembayaran
                </strong>


                <span class="fw-bold">

                    <?= $paymentPercent; ?>%

                </span>

            </div>


            <div class="cash-progress">


                <div
                    style="width: <?= $paymentPercent; ?>%;"
                ></div>


            </div>


            <div
                class="d-flex justify-content-between mt-2"
            >

                <small class="text-muted">

                    <?= $totalPaid; ?>

                    siswa sudah bayar

                </small>


                <small class="text-muted">

                    <?= $totalUnpaid; ?>

                    belum bayar

                </small>


            </div>


        </div>


        <!-- ====================================================
             DAFTAR SISWA
        ===================================================== -->

        <div class="table-card mb-4">


            <div class="section-heading">


                <div>


                    <span class="eyebrow">
                        Daftar Pembayaran
                    </span>


                    <h4>

                        <?= htmlspecialchars(
                            $officer['nama_kelas'] ?? '-',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>

                    </h4>


                </div>


            </div>


            <div class="table-responsive">


                <table
                    class="table table-hover align-middle"
                >


                    <thead>


                        <tr>

                            <th width="70">
                                No.
                            </th>

                            <th>
                                Nama
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>


                    </thead>


                    <tbody>


                    <?php if (empty($students)): ?>


                        <tr>


                            <td
                                colspan="4"
                                class="text-center text-muted py-5"
                            >


                                <i
                                    class="bi bi-people"
                                    style="font-size: 40px;"
                                ></i>


                                <div class="mt-2">


                                    <strong>
                                        Belum ada data siswa
                                    </strong>


                                </div>


                                <small>

                                    Tambahkan siswa terlebih dahulu
                                    melalui menu Data Siswa.

                                </small>


                            </td>


                        </tr>


                    <?php else: ?>


                        <?php foreach (
                            $students as $index => $student
                        ): ?>


                            <?php

                            /*
                            |--------------------------------------------------------------------------
                            | NAMA
                            |--------------------------------------------------------------------------
                            */

                            $nama = trim(
                                (string) (
                                    $student['nama_lengkap']
                                    ?? ''
                                )
                            );


                            if ($nama === '') {
                                $nama = '-';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | STATUS
                            |--------------------------------------------------------------------------
                            */

                            $paymentStatus =
                                $student['payment_status']
                                ?? 'belum_bayar';

                            ?>


                            <tr>


                                <!-- NO -->

                                <td>

                                    <?= $index + 1; ?>

                                </td>


                                <!-- NAMA -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $nama,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </strong>

                                </td>


                                <!-- STATUS -->

                                <td>


                                    <?php if (
                                        $paymentStatus === 'lunas'
                                    ): ?>


                                        <span
                                            class="text-success"
                                        >

                                            <i
                                                class="bi bi-check-circle-fill me-1"
                                            ></i>

                                            Sudah bayar

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="text-danger"
                                        >

                                            <i
                                                class="bi bi-x-circle-fill me-1"
                                            ></i>

                                            Belum bayar

                                        </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </div>


        <!-- ====================================================
             FORM KONFIRMASI
        ===================================================== -->

        <?php

        /*
        |--------------------------------------------------------------------------
        | FORM BOLEH DIKIRIM JIKA:
        |
        | 1. Semua siswa sudah lunas
        | 2. Belum diterima
        |
        | Kalau ditolak -> boleh kirim ulang.
        |--------------------------------------------------------------------------
        */

        $canSubmit =
            $totalStudents > 0
            &&
            $totalPaid === $totalStudents
            &&
            !(
                $confirmation
                &&
                ($confirmation['status'] ?? '') === 'diterima'
            );

        ?>


        <div class="table-card">


            <div class="section-heading">


                <div>


                    <span class="eyebrow">
                        Setoran OSIS
                    </span>


                    <h4>
                        Kirim Konfirmasi Setoran
                    </h4>


                </div>


            </div>


            <form method="post">


                <!-- CATATAN -->

                <div class="mb-3">


                    <label
                        for="catatan"
                        class="form-label fw-semibold"
                    >

                        Catatan Penyerahan

                    </label>


                    <textarea
                        id="catatan"
                        name="catatan"
                        class="form-control"
                        rows="4"
                        placeholder="Contoh: Uang diserahkan melalui bendahara kelas"
                    ><?= htmlspecialchars(
                        $confirmation['catatan'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?></textarea>


                </div>


                <!-- TOMBOL -->

                <button
                    type="submit"
                    class="btn btn-primary"
                    <?= !$canSubmit ? 'disabled' : ''; ?>
                >

                    <i
                        class="bi bi-send-check-fill me-1"
                    ></i>

                    Kirim Konfirmasi Setoran

                </button>


                <?php if (
                    $totalStudents === 0
                ): ?>


                    <small class="text-danger d-block mt-2">

                        Belum ada siswa di kelas ini.

                    </small>


                <?php elseif (
                    $totalPaid !== $totalStudents
                ): ?>


                    <small class="text-danger d-block mt-2">

                        Semua siswa harus lunas terlebih dahulu.

                    </small>


                <?php elseif (
                    $confirmation
                    &&
                    ($confirmation['status'] ?? '') === 'diterima'
                ): ?>


                    <small class="text-success d-block mt-2">

                        Setoran ini sudah diterima oleh bendahara OSIS.

                    </small>


                <?php elseif (
                    $confirmation
                    &&
                    ($confirmation['status'] ?? '') === 'ditolak'
                ): ?>


                    <small class="text-warning d-block mt-2">

                        Setoran sebelumnya ditolak.
                        Kamu dapat mengirim konfirmasi kembali.

                    </small>


                <?php endif; ?>


            </form>


        </div>


    </section>


</main>


<?php

/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
|
| Tidak menambahkan JavaScript sidebar di sini.
| footer.php yang menangani sidebar.
|
|--------------------------------------------------------------------------
*/

require_once '../includes/footer.php';

?>
