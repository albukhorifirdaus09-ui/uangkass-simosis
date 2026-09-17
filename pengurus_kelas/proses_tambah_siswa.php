<?php

require_once "../config/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| CEK BENDAHARA KELAS
|--------------------------------------------------------------------------
*/

$officer = requireActiveClassOfficer(
    $pdo
);


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        "Location: siswa.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/

$nama_lengkap = trim(
    $_POST['nama_lengkap'] ?? ''
);

$jenis_kelamin = trim(
    $_POST['jenis_kelamin'] ?? ''
);

$no_hp = trim(
    $_POST['no_hp'] ?? ''
);

$alamat = trim(
    $_POST['alamat'] ?? ''
);


/*
|--------------------------------------------------------------------------
| VALIDASI
|--------------------------------------------------------------------------
*/

if (
    $nama_lengkap === '' ||
    $jenis_kelamin === ''
) {

    header(
        "Location: siswa.php?error=" .
        urlencode(
            "Nama dan jenis kelamin wajib diisi."
        )
    );

    exit;
}


if (
    !in_array(
        $jenis_kelamin,
        ['L', 'P'],
        true
    )
) {

    header(
        "Location: siswa.php?error=" .
        urlencode(
            "Jenis kelamin tidak valid."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| INSERT
|--------------------------------------------------------------------------
|
| user_id NULL karena siswa anggota kelas tidak perlu akun login.
|
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO students
        (
            user_id,
            nama_lengkap,
            kelas_id,
            jenis_kelamin,
            no_hp,
            alamat
        )

        VALUES
        (
            NULL,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");


    $stmt->execute([
        $nama_lengkap,

        /*
        | PENTING
        | Class ID diambil dari Bendahara Kelas login,
        | bukan dari form.
        */

        $officer['class_id'],

        $jenis_kelamin,

        $no_hp !== ''
            ? $no_hp
            : null,

        $alamat !== ''
            ? $alamat
            : null
    ]);


    header(
        "Location: siswa.php?success=tambah"
    );

    exit;


} catch (Throwable $e) {

    header(
        "Location: siswa.php?error=" .
        urlencode(
            "Gagal menambahkan siswa: " .
            $e->getMessage()
        )
    );

    exit;
}
