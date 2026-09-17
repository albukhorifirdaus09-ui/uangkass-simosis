<?php

session_start();

require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION['user_id'])) {

    header("Location: auth/login.php");
    exit;

}

switch ($_SESSION['role']) {

    case 'admin':

        header("Location: admin/dashboard.php");
        exit;

    case 'siswa':

        $stmt = $pdo->prepare("
            SELECT co.id
            FROM class_officers co
            INNER JOIN students s ON s.id = co.student_id
            INNER JOIN academic_years ay ON ay.id = co.academic_year_id
            WHERE s.user_id = ?
              AND co.status = 'aktif'
              AND ay.status = 'aktif'
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);

        if ($stmt->fetch()) {
            header("Location: pengurus_kelas/dashboard.php");
            exit;
        }

        header("Location: siswa/dashboard.php");
        exit;

    case 'osis':

        switch (strtolower($_SESSION['jabatan'] ?? '')) {

            case 'bendahara':
                header("Location: osis/bendahara/dashboard.php");
                exit;

            case 'ketua':
            case 'anggota':
            case 'wakil ketua':
            case 'sekretaris':
                header("Location: osis/anggota/dashboard.php");
                exit;

            default:
                header("Location: osis/dashboard.php");
                exit;
        }

    default:

        session_destroy();

        header("Location: auth/login.php");
        exit;
}