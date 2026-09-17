<?php

session_start();

require_once "../config/database.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| DATA LOGIN
|--------------------------------------------------------------------------
*/

$login = trim($_POST['login'] ?? '');

$password = $_POST['password'] ?? '';


if ($login === '' || $password === '') {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Username/email dan password wajib diisi."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CARI USER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *

    FROM users

    WHERE username = ? OR email = ?

    LIMIT 1
");

$stmt->execute([$login, $login]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Username/email atau password salah."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CEK PASSWORD
|--------------------------------------------------------------------------
*/

if (
    !password_verify(
        $password,
        $user['password']
    )
) {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Username/email atau password salah."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| STATUS AKUN
|--------------------------------------------------------------------------
*/

if ($user['status'] !== 'aktif') {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Akun sedang tidak aktif."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ANGGOTA OSIS BELUM DISETUJUI
|--------------------------------------------------------------------------
*/

if ($user['role'] === 'osis_pending') {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Pendaftaran OSIS masih menunggu admin menentukan jabatan."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);


$_SESSION['user_id'] = $user['id'];

$_SESSION['username'] = $user['username'];

$_SESSION['email'] = $user['email'];

$_SESSION['role'] = $user['role'];


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

if ($user['role'] === 'admin') {

    header(
        "Location: ../admin/dashboard.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| SISWA / BENDAHARA KELAS
|--------------------------------------------------------------------------
*/

if ($user['role'] === 'siswa') {

    /*
    | Cek apakah user merupakan Bendahara Kelas aktif.
    */

    $stmt = $pdo->prepare("
        SELECT
            co.id,
            co.class_id,
            co.position

        FROM class_officers co

        INNER JOIN students s
            ON s.id = co.student_id

        INNER JOIN academic_years ay
            ON ay.id = co.academic_year_id

        WHERE s.user_id = ?

          AND co.position = 'bendahara_kelas'

          AND co.status = 'aktif'

          AND ay.status = 'aktif'

        ORDER BY co.id DESC

        LIMIT 1
    ");

    $stmt->execute([
        $user['id']
    ]);


    $bendaharaKelas = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    if ($bendaharaKelas) {

        $_SESSION['class_officer_id'] =
            $bendaharaKelas['id'];

        $_SESSION['class_id'] =
            $bendaharaKelas['class_id'];

        $_SESSION['jabatan'] =
            'bendahara_kelas';


        header(
            "Location: ../pengurus_kelas/dashboard.php"
        );

        exit;
    }


    /*
    | Siswa biasa.
    */

    header(
        "Location: ../siswa/dashboard.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| OSIS
|--------------------------------------------------------------------------
*/

if ($user['role'] === 'osis') {

    $stmt = $pdo->prepare("
        SELECT
            om.id,
            om.student_id,
            op.nama_jabatan

        FROM osis_members om

        INNER JOIN students s
            ON s.id = om.student_id

        INNER JOIN osis_positions op
            ON op.id = om.position_id

        INNER JOIN academic_years ay
            ON ay.id = om.academic_year_id

        WHERE s.user_id = ?

          AND om.status = 'aktif'

          AND ay.status = 'aktif'

        ORDER BY om.id DESC

        LIMIT 1
    ");

    $stmt->execute([
        $user['id']
    ]);

    $osis = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    if (!$osis) {
        /*
        |----------------------------------------------------------------------
        | Tidak ada keanggotaan OSIS aktif di tahun ajaran aktif.
        | Kembalikan role ke 'siswa' agar tidak terkunci, lalu arahkan ke dashboard siswa.
        |----------------------------------------------------------------------
        */
        $pdo->prepare("UPDATE users SET role = 'siswa' WHERE id = ?")->execute([$user['id']]);
        $_SESSION['role'] = 'siswa';

        /* Cek apakah siswa juga bendahara kelas aktif */
        $stmtBK = $pdo->prepare("
            SELECT co.id, co.class_id, co.position
            FROM class_officers co
            INNER JOIN students s ON s.id = co.student_id
            INNER JOIN academic_years ay ON ay.id = co.academic_year_id
            WHERE s.user_id = ?
              AND co.position = 'bendahara_kelas'
              AND co.status = 'aktif'
              AND ay.status = 'aktif'
            ORDER BY co.id DESC
            LIMIT 1
        ");
        $stmtBK->execute([$user['id']]);
        $bendaharaKelas = $stmtBK->fetch(PDO::FETCH_ASSOC);

        if ($bendaharaKelas) {
            $_SESSION['class_officer_id'] = $bendaharaKelas['id'];
            $_SESSION['class_id'] = $bendaharaKelas['class_id'];
            $_SESSION['jabatan'] = 'bendahara_kelas';
            header("Location: ../pengurus_kelas/dashboard.php");
            exit;
        }

        header("Location: ../siswa/dashboard.php");
        exit;
    }


    $_SESSION['jabatan'] =
        $osis['nama_jabatan'];


    /*
    |--------------------------------------------------------------------------
    | BENDAHARA OSIS
    |--------------------------------------------------------------------------
    */

    if (
        strtolower(
            $osis['nama_jabatan']
        ) === 'bendahara'
    ) {

        header(
            "Location: ../osis/bendahara/dashboard.php"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | KETUA / WAKIL / SEKRETARIS / ANGGOTA
    |--------------------------------------------------------------------------
    |
    | Semuanya masuk dashboard anggota biasa.
    |
    */

    header(
        "Location: ../osis/anggota/dashboard.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ROLE TIDAK DIKENALI
|--------------------------------------------------------------------------
*/

session_unset();

session_destroy();


header(
    "Location: login.php?error=" .
    urlencode(
        "Role akun tidak dikenali."
    )
);

exit;
