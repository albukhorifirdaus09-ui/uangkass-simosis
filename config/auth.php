<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}


/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/

function isLogin()
{
    return isset(
        $_SESSION['user_id']
    );
}


/*
|--------------------------------------------------------------------------
| WAJIB LOGIN
|--------------------------------------------------------------------------
*/

function requireLogin()
{
    if (!isLogin()) {

        header(
            "Location: /simosis/auth/login.php"
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| WAJIB ROLE TERTENTU
|--------------------------------------------------------------------------
*/

function requireRole($roles)
{
    requireLogin();


    $roles = (array) $roles;


    if (
        !in_array(
            $_SESSION['role'] ?? '',
            $roles,
            true
        )
    ) {

        header(
            "Location: /simosis/index.php"
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| BENDAHARA KELAS AKTIF
|--------------------------------------------------------------------------
*/

function requireActiveClassOfficer(PDO $pdo)
{
    requireLogin();


    $stmt = $pdo->prepare("
        SELECT
            co.id AS officer_id,
            co.student_id,
            co.class_id,
            co.position,
            co.academic_year_id,

            s.nama_lengkap,

            c.nama_kelas,
            c.tingkat,
            c.jurusan,

            ay.tahun_ajaran

        FROM class_officers co

        INNER JOIN students s
            ON s.id = co.student_id

        INNER JOIN classes c
            ON c.id = co.class_id

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
        $_SESSION['user_id'] ?? 0
    ]);


    $officer = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    if (!$officer) {

        http_response_code(403);

        exit(
            "Anda bukan Bendahara Kelas aktif."
        );
    }


    return $officer;
}


/*
|--------------------------------------------------------------------------
| BENDAHARA OSIS
|--------------------------------------------------------------------------
*/

function requireOsisTreasurer()
{
    requireLogin();

    global $pdo;


    if (
        ($_SESSION['role'] ?? '') !== 'osis'
        ||
        strtolower(
            $_SESSION['jabatan'] ?? ''
        ) !== 'bendahara'
    ) {

        header(
            "Location: /simosis/index.php"
        );

        exit;
    }

    $stmt = $pdo->prepare("SELECT 1 FROM osis_members om INNER JOIN students s ON s.id = om.student_id INNER JOIN osis_positions op ON op.id = om.position_id INNER JOIN academic_years ay ON ay.id = om.academic_year_id WHERE s.user_id = ? AND om.status = 'aktif' AND ay.status = 'aktif' AND LOWER(op.nama_jabatan) = 'bendahara' LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetchColumn()) {
        header("Location: /simosis/index.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| ANGGOTA OSIS
|--------------------------------------------------------------------------
|
| Ketua, Wakil, Sekretaris dan Anggota menggunakan halaman anggota.
|
|--------------------------------------------------------------------------
*/

function requireOsisMember()
{
    requireLogin();

    global $pdo;


    if (
        ($_SESSION['role'] ?? '') !== 'osis'
    ) {

        header(
            "Location: /simosis/index.php"
        );

        exit;
    }

    $stmt = $pdo->prepare("SELECT 1 FROM osis_members om INNER JOIN students s ON s.id = om.student_id INNER JOIN academic_years ay ON ay.id = om.academic_year_id WHERE s.user_id = ? AND om.status = 'aktif' AND ay.status = 'aktif' LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetchColumn()) {
        header("Location: /simosis/index.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

function logout()
{
    session_unset();

    session_destroy();


    header(
        "Location: /simosis/auth/login.php"
    );

    exit;
}
