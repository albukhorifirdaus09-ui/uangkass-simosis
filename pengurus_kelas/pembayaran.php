<?php

require_once '../config/auth.php';
require_once '../config/database.php';


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

$month = (int) ($_GET['bulan'] ?? date('n'));
$year  = (int) ($_GET['tahun'] ?? date('Y'));


/*
|--------------------------------------------------------------------------
| VALIDASI BULAN
|--------------------------------------------------------------------------
*/

if ($month < 1 || $month > 12) {
    $month = (int) date('n');
}


/*
|--------------------------------------------------------------------------
| VALIDASI TAHUN
|--------------------------------------------------------------------------
*/

if ($year < 2020 || $year > 2100) {
    $year = (int) date('Y');
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

$monthName = $monthNames[$month] ?? '';


/*
|--------------------------------------------------------------------------
| AMBIL TAGIHAN KAS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        bulan,
        tahun,
        nominal,
        tanggal_jatuh_tempo,
        status
    FROM student_dues
    WHERE bulan = ?
      AND tahun = ?
      AND status = 'aktif'
    LIMIT 1
");

$stmt->execute([
    $month,
    $year
]);

$due = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| JIKA TAGIHAN BELUM DIBUAT
|--------------------------------------------------------------------------
*/

if (!$due) {

    header(
        "Location: uang_kas.php?error=tagihan"
    );

    exit;
}


$dueId = (int) $due['id'];
$nominalKas = (float) $due['nominal'];


/*
|--------------------------------------------------------------------------
| PROSES PEMBAYARAN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $studentId = (int) ($_POST['student_id'] ?? 0);

    $action = $_POST['action'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | VALIDASI AKSI
    |--------------------------------------------------------------------------
    */

    if (!in_array($action, ['bayar', 'batal'], true)) {

        header(
            "Location: pembayaran.php?bulan={$month}&tahun={$year}&error=aksi"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI SISWA
    |--------------------------------------------------------------------------
    |
    | Pastikan siswa benar-benar berada di kelas
    | Bendahara yang sedang login.
    |
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama_lengkap
        FROM students
        WHERE id = ?
          AND kelas_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $studentId,
        $classId
    ]);

    $student = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$student) {

        header(
            "Location: pembayaran.php?bulan={$month}&tahun={$year}&error=siswa"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | TANDAI SUDAH BAYAR
    |--------------------------------------------------------------------------
    */

    if ($action === 'bayar') {

        $stmt = $pdo->prepare("
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
                CURDATE(),
                ?,
                'lunas',
                'Dicatat oleh Bendahara Kelas'
            )

            ON DUPLICATE KEY UPDATE
                recorded_by = VALUES(recorded_by),
                tanggal_bayar = CURDATE(),
                nominal = VALUES(nominal),
                status = 'lunas',
                keterangan = VALUES(keterangan)
        ");

        $stmt->execute([
            $dueId,
            $studentId,
            (int) $_SESSION['user_id'],
            $nominalKas
        ]);


        /*
        |--------------------------------------------------------------------------
        | PESAN BERHASIL
        |--------------------------------------------------------------------------
        */

        header(
            "Location: pembayaran.php?bulan={$month}&tahun={$year}&success=bayar"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | BATALKAN PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    if ($action === 'batal') {

        $stmt = $pdo->prepare("
            UPDATE student_due_payments

            SET
                status = 'belum_bayar',
                tanggal_bayar = NULL,
                nominal = 0,
                keterangan = 'Pembayaran dibatalkan oleh Bendahara Kelas'

            WHERE student_due_id = ?
              AND student_id = ?
        ");

        $stmt->execute([
            $dueId,
            $studentId
        ]);


        /*
        |--------------------------------------------------------------------------
        | PESAN BERHASIL
        |--------------------------------------------------------------------------
        */

        header(
            "Location: pembayaran.php?bulan={$month}&tahun={$year}&success=batal"
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA SISWA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.nama_lengkap,
        sdp.status AS payment_status,
        sdp.tanggal_bayar,
        sdp.nominal AS payment_nominal

    FROM students s

    LEFT JOIN student_due_payments sdp
        ON sdp.student_id = s.id
        AND sdp.student_due_id = ?

    WHERE s.kelas_id = ?

    ORDER BY s.nama_lengkap ASC
");

$stmt->execute([
    $dueId,
    $classId
]);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HITUNG JUMLAH SISWA
|--------------------------------------------------------------------------
*/

$total = count($students);


/*
|--------------------------------------------------------------------------
| HITUNG SUDAH BAYAR
|--------------------------------------------------------------------------
*/

$paid = 0;

foreach ($students as $student) {

    if (($student['payment_status'] ?? '') === 'lunas') {
        $paid++;
    }
}


/*
|--------------------------------------------------------------------------
| HITUNG BELUM BAYAR
|--------------------------------------------------------------------------
*/

$unpaid = max(
    0,
    $total - $paid
);


/*
|--------------------------------------------------------------------------
| PERSENTASE
|--------------------------------------------------------------------------
*/

$percent = $total > 0
    ? round(($paid / $total) * 100)
    : 0;


/*
|--------------------------------------------------------------------------
| TOTAL UANG MASUK
|--------------------------------------------------------------------------
*/

$totalAmount = 0;

foreach ($students as $student) {

    if (($student['payment_status'] ?? '') === 'lunas') {

        $totalAmount += (float) (
            $student['payment_nominal']
            ?? $nominalKas
        );
    }
}


/*
|--------------------------------------------------------------------------
| JUDUL HALAMAN
|--------------------------------------------------------------------------
*/

$pageTitle = 'Detail Uang Kas';


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


            <!-- TOMBOL MENU MOBILE -->

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
                    Detail Uang Kas
                </h5>

                <small>

                    <?= htmlspecialchars(
                        $officer['nama_kelas'] ?? '-',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                </small>

            </div>


        </div>


        <!-- ====================================================
             USER
        ===================================================== -->

        <div class="class-user">


            <div class="class-user-avatar">

                <i class="bi bi-person-fill"></i>

            </div>


            <div>

                <strong>

                    <?= htmlspecialchars(
                        $officer['nama_lengkap'] ?? '-',
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

    <section class="class-content">


        <!-- ====================================================
             JUDUL HALAMAN
        ===================================================== -->

        <div class="page-intro">


            <div>


                <!-- KEMBALI -->

                <a
                    href="uang_kas.php"
                    class="text-decoration-none"
                >

                    <i class="bi bi-arrow-left"></i>

                    Kembali ke Uang Kas

                </a>


                <!-- JUDUL -->

                <h2>

                    Detail Bulan Pembayaran:

                    <?= htmlspecialchars(
                        $monthName,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                    <?= $year; ?>

                </h2>


                <!-- INFO -->

                <p>

                    Rp

                    <?= number_format(
                        $nominalKas,
                        0,
                        ',',
                        '.'
                    ); ?>

                    per siswa

                    ·

                    <?= $paid; ?>/<?= $total; ?>

                    sudah bayar

                </p>


            </div>


        </div>


        <!-- ====================================================
             ALERT SUCCESS
        ===================================================== -->

        <?php if (isset($_GET['success'])): ?>


            <?php if ($_GET['success'] === 'bayar'): ?>


                <div
                    class="alert alert-success"
                    role="alert"
                >

                    <i class="bi bi-check-circle-fill me-2"></i>

                    Pembayaran siswa berhasil dicatat.

                </div>


            <?php elseif ($_GET['success'] === 'batal'): ?>


                <div
                    class="alert alert-success"
                    role="alert"
                >

                    <i class="bi bi-check-circle-fill me-2"></i>

                    Pembayaran siswa berhasil dibatalkan.

                </div>


            <?php endif; ?>


        <?php endif; ?>


        <!-- ====================================================
             ALERT ERROR
        ===================================================== -->

        <?php if (isset($_GET['error'])): ?>


            <div
                class="alert alert-danger"
                role="alert"
            >

                <i class="bi bi-exclamation-triangle-fill me-2"></i>


                <?php

                $errorMessages = [

                    'tagihan' =>
                        'Tagihan kas untuk periode ini belum dibuat oleh Admin.',

                    'aksi' =>
                        'Aksi pembayaran tidak valid.',

                    'siswa' =>
                        'Siswa tidak ditemukan atau bukan bagian dari kelas Anda.',

                    'data' =>
                        'Data pembayaran tidak valid.'

                ];

                $errorCode = $_GET['error'] ?? 'data';

                echo htmlspecialchars(
                    $errorMessages[$errorCode] ?? 'Terjadi kesalahan.',
                    ENT_QUOTES,
                    'UTF-8'
                );

                ?>

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

                            <?= $paid; ?>

                            <small class="text-muted">
                                / <?= $total; ?>
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

                            <?= $unpaid; ?>

                        </h4>


                    </div>


                </div>


            </div>


            <!-- UANG MASUK -->

            <div class="col-md-4">


                <div class="stat-card">


                    <div class="stat-card-icon">

                        <i class="bi bi-wallet2"></i>

                    </div>


                    <div>

                        <span class="text-muted">
                            Uang Masuk
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
             CARD DAFTAR SISWA
        ===================================================== -->

        <section class="table-card">


            <!-- HEADER -->

            <div class="section-heading">


                <div>


                    <span class="eyebrow">
                        Daftar Siswa
                    </span>


                    <h4>

                        <?= htmlspecialchars(
                            $officer['nama_kelas'] ?? '-',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>

                    </h4>


                </div>


                <!-- PERSENTASE -->

                <span class="status-pill">

                    <?= $percent; ?>% lunas

                </span>


            </div>


            <!-- =================================================
                 PROGRESS
            ================================================== -->

            <div class="mb-4">


                <div class="d-flex justify-content-between mb-2">

                    <small class="text-muted">
                        Progress pembayaran
                    </small>


                    <small class="fw-bold">

                        <?= $paid; ?>/<?= $total; ?>

                    </small>

                </div>


                <div class="cash-progress">

                    <div
                        style="width: <?= $percent; ?>%;"
                    ></div>

                </div>


            </div>


            <!-- =================================================
                 TABEL
            ================================================== -->

            <div class="table-responsive">


                <table class="table align-middle">


                    <thead>

                        <tr>

                            <th>
                                No.
                            </th>

                            <th>
                                Nama Siswa
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($students)): ?>


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

                            $status =
                                $student['payment_status']
                                ?? 'belum_bayar';


                            /*
                            |--------------------------------------------------------------------------
                            | TANGGAL
                            |--------------------------------------------------------------------------
                            */

                            $tanggalBayar =
                                $student['tanggal_bayar']
                                ?? null;

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
                                        $status === 'lunas'
                                    ): ?>


                                        <span
                                            class="badge bg-success-subtle text-success"
                                        >

                                            <i
                                                class="bi bi-check-circle me-1"
                                            ></i>

                                            Sudah bayar

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="badge bg-danger-subtle text-danger"
                                        >

                                            <i
                                                class="bi bi-x-circle me-1"
                                            ></i>

                                            Belum bayar

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- TANGGAL -->

                                <td>


                                    <?php if (
                                        !empty($tanggalBayar)
                                    ): ?>


                                        <?= date(
                                            'd/m/Y',
                                            strtotime(
                                                $tanggalBayar
                                            )
                                        ); ?>


                                    <?php else: ?>


                                        <span class="text-muted">
                                            -
                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- AKSI -->

                                <td>


                                    <form
                                        method="post"
                                        class="d-inline"
                                    >


                                        <input
                                            type="hidden"
                                            name="student_id"
                                            value="<?= (int) $student['id']; ?>"
                                        >


                                        <?php if (
                                            $status === 'lunas'
                                        ): ?>


                                            <button
                                                type="submit"
                                                name="action"
                                                value="batal"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Batalkan pembayaran siswa ini?')"
                                            >

                                                <i class="bi bi-x-circle me-1"></i>

                                                Batalkan

                                            </button>


                                        <?php else: ?>


                                            <button
                                                type="submit"
                                                name="action"
                                                value="bayar"
                                                class="btn btn-sm btn-primary"
                                            >

                                                <i class="bi bi-check-circle me-1"></i>

                                                Tandai Bayar

                                            </button>


                                        <?php endif; ?>


                                    </form>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>


                            <td
                                colspan="6"
                                class="text-center text-muted py-5"
                            >


                                <i
                                    class="bi bi-people"
                                    style="font-size: 40px;"
                                ></i>


                                <div class="mt-2">

                                    <strong>
                                        Belum ada siswa
                                    </strong>

                                </div>


                                <small>

                                    Belum ada siswa yang terdaftar
                                    di kelas ini.

                                </small>


                            </td>


                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


    </section>


</main>


<?php

/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
|
| Jangan tambahkan JavaScript sidebar lagi di sini.
| footer.php sudah menangani classMenuToggle.
|
|--------------------------------------------------------------------------
*/

require_once '../includes/footer.php';

?>
