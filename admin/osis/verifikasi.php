<?php

require_once "../../config/auth.php";
require_once "../../config/database.php";


requireRole('admin');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        "Location: index.php"
    );

    exit;
}


$id = (int) (
    $_POST['id'] ?? 0
);

$action = $_POST['action'] ?? '';


if (
    $id <= 0 ||
    $action !== 'tolak'
) {

    header(
        "Location: index.php?error=" .
        urlencode(
            "Permintaan tidak valid."
        )
    );

    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            om.id,
            s.user_id

        FROM osis_members om

        INNER JOIN students s
            ON s.id = om.student_id

        WHERE om.id = ?

          AND om.status = 'menunggu'

        LIMIT 1
    ");

    $stmt->execute([$id]);

    $member = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    if (!$member) {

        throw new Exception(
            "Pendaftaran tidak ditemukan."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NONAKTIFKAN PENDAFTARAN
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE osis_members

        SET
            status = 'nonaktif',
            tanggal_selesai = CURDATE()

        WHERE id = ?
    ");

    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | KEMBALIKAN USER MENJADI SISWA
    |--------------------------------------------------------------------------
    */

    if (!empty($member['user_id'])) {

        $stmt = $pdo->prepare("
            UPDATE users

            SET role = 'siswa'

            WHERE id = ?
        ");

        $stmt->execute([
            $member['user_id']
        ]);
    }


    $pdo->commit();


    header(
        "Location: index.php?success=tolak"
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }


    header(
        "Location: index.php?error=" .
        urlencode(
            $e->getMessage()
        )
    );

    exit;
}