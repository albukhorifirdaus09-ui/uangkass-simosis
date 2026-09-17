<?php

require_once "../config/database.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: register.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA FORM
|--------------------------------------------------------------------------
*/

$jenis_register = trim(
    $_POST['jenis_register'] ?? ''
);

$nama_lengkap = trim(
    $_POST['nama_lengkap'] ?? ''
);

$nama_kelas = trim($_POST['nama_kelas'] ?? '');

$jurusan_id = (int) ($_POST['jurusan_id'] ?? 0);

$jenis_kelamin = trim(
    $_POST['jenis_kelamin'] ?? ''
);

$email = trim(
    $_POST['email'] ?? ''
);

$password = $_POST['password'] ?? '';

$password_confirm = $_POST['password_confirm'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDASI JENIS REGISTER
|--------------------------------------------------------------------------
*/

$jenisRegisterValid = [
    'bendahara_kelas',
    'anggota_osis'
];


if (
    !in_array(
        $jenis_register,
        $jenisRegisterValid,
        true
    )
) {

    header(
        "Location: register.php?error=" .
        urlencode(
            "Silakan pilih jenis pendaftaran."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDASI DATA
|--------------------------------------------------------------------------
*/

if (
    $nama_lengkap === '' ||
    $nama_kelas === '' ||
    $jurusan_id <= 0 ||
    $jenis_kelamin === '' ||
    $email === '' ||
    $password === ''
) {

    header(
        "Location: register.php?error=" .
        urlencode(
            "Semua data wajib diisi."
        )
    );

    exit;
}

if (mb_strlen($nama_kelas) > 50) {

    header(
        "Location: register.php?error=" .
        urlencode("Nama kelas maksimal 50 karakter.")
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
        "Location: register.php?error=" .
        urlencode(
            "Jenis kelamin tidak valid."
        )
    );

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header(
        "Location: register.php?error=" .
        urlencode(
            "Format email tidak valid."
        )
    );

    exit;
}


if ($password !== $password_confirm) {

    header(
        "Location: register.php?error=" .
        urlencode(
            "Konfirmasi password tidak sesuai."
        )
    );

    exit;
}


if (strlen($password) < 6) {

    header(
        "Location: register.php?error=" .
        urlencode(
            "Password minimal 6 karakter."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CEK EMAIL
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id

    FROM users

    WHERE email = ?

    LIMIT 1
");

$stmt->execute([$email]);


if ($stmt->fetch()) {

    header(
        "Location: register.php?error=" .
        urlencode(
            "Email sudah digunakan."
        )
    );

    exit;
}


/* Jurusan harus berasal dari daftar yang dibuat admin. */
$stmt = $pdo->prepare("
    SELECT id, nama_jurusan
    FROM departments
    WHERE id = ? AND status = 'aktif'
    LIMIT 1
");

$stmt->execute([$jurusan_id]);
$jurusan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jurusan) {

    header(
        "Location: register.php?error=" .
        urlencode(
            "Jurusan tidak tersedia. Pilih jurusan yang ada pada daftar."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL TAHUN AJARAN AKTIF
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        tahun_ajaran,
        tanggal_mulai,
        tanggal_selesai

    FROM academic_years

    WHERE status = 'aktif'

    ORDER BY id DESC

    LIMIT 1
");

$academicYear = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$academicYear) {

    header(
        "Location: register.php?error=" .
        urlencode(
            "Tahun ajaran aktif belum tersedia. Hubungi admin."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CARI / BUAT KELAS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM classes
    WHERE nama_kelas = ? AND jurusan = ?
    LIMIT 1
");
$stmt->execute([$nama_kelas, $jurusan['nama_jurusan']]);
$kelas_id = (int) ($stmt->fetchColumn() ?: 0);

if ($kelas_id <= 0) {
    $tingkat = '-';
    if (preg_match('/^(XII|XI|X)\\b/i', $nama_kelas, $matches)) {
        $tingkat = strtoupper($matches[1]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO classes (nama_kelas, tingkat, jurusan)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$nama_kelas, $tingkat, $jurusan['nama_jurusan']]);
    $kelas_id = (int) $pdo->lastInsertId();
}


/*
|--------------------------------------------------------------------------
| JIKA BENDAHARA KELAS
| CEK APAKAH KELAS SUDAH MEMILIKI BENDAHARA
|--------------------------------------------------------------------------
*/

if ($jenis_register === 'bendahara_kelas') {

    $stmt = $pdo->prepare("
        SELECT
            co.id,
            s.nama_lengkap
        FROM class_officers co
        INNER JOIN students s
            ON s.id = co.student_id
        WHERE co.class_id = ?
          AND co.academic_year_id = ?
          AND co.position = 'bendahara_kelas'
          AND co.status = 'aktif'
        LIMIT 1
    ");

    $stmt->execute([
        $kelas_id,
        $academicYear['id']
    ]);

    $bendaharaAda = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($bendaharaAda) {

        header(
            "Location: register.php?error=" .
            urlencode(
                "Kelas tersebut sudah memiliki Bendahara Kelas aktif (" . $bendaharaAda['nama_lengkap'] . ")."
            )
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| USERNAME OTOMATIS & HASH PASSWORD & ROLE
|--------------------------------------------------------------------------
*/

$username = 'user_' . bin2hex(random_bytes(8));
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = ($jenis_register === 'anggota_osis') ? 'osis_pending' : 'siswa';


/*
|--------------------------------------------------------------------------
| TRANSACTION
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | INSERT USERS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, role, status)
        VALUES (?, ?, ?, ?, 'aktif')
    ");

    $stmt->execute([
        $username,
        $email,
        $hashedPassword,
        $role
    ]);

    $user_id = (int) $pdo->lastInsertId();

    /*
    |--------------------------------------------------------------------------
    | INSERT STUDENTS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO students
        (
            user_id,
            nama_lengkap,
            kelas_id,
            jenis_kelamin
        )

        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $user_id,
        $nama_lengkap,
        $kelas_id,
        $jenis_kelamin
    ]);


    $student_id = (int) $pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | REGISTER BENDAHARA KELAS
    |--------------------------------------------------------------------------
    */

    if ($jenis_register === 'bendahara_kelas') {

        $stmt = $pdo->prepare("
            INSERT INTO class_officers
            (
                student_id,
                class_id,
                position,
                academic_year_id,
                tanggal_mulai,
                tanggal_selesai,
                status
            )

            VALUES
            (
                ?,
                ?,
                'bendahara_kelas',
                ?,
                CURDATE(),
                NULL,
                'aktif'
            )
        ");

        $stmt->execute([
            $student_id,
            $kelas_id,
            $academicYear['id']
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | REGISTER ANGGOTA OSIS
    |--------------------------------------------------------------------------
    */

    if ($jenis_register === 'anggota_osis') {

        /*
        | Ambil jabatan Anggota sebagai jabatan sementara.
        */

        $stmt = $pdo->query("
            SELECT id

            FROM osis_positions

            WHERE LOWER(nama_jabatan) = 'anggota'

            LIMIT 1
        ");

        $anggotaPosition = $stmt->fetch(
            PDO::FETCH_ASSOC
        );


        if (!$anggotaPosition) {

            throw new Exception(
                "Jabatan Anggota OSIS belum tersedia."
            );
        }


        $stmt = $pdo->prepare("
            INSERT INTO osis_members
            (
                student_id,
                position_id,
                bagian,
                academic_year_id,
                tanggal_mulai,
                tanggal_selesai,
                status
            )

            VALUES
            (
                ?,
                ?,
                NULL,
                ?,
                CURDATE(),
                NULL,
                'menunggu'
            )
        ");

        $stmt->execute([
            $student_id,
            $anggotaPosition['id'],
            $academicYear['id']
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    if ($jenis_register === 'anggota_osis') {

        header(
            "Location: login.php?success=" .
            urlencode(
                "Registrasi Anggota OSIS berhasil. Gunakan email untuk login dan tunggu admin menentukan jabatan terlebih dahulu."
            )
        );

    } else {

        header(
            "Location: login.php?success=" .
            urlencode(
                "Registrasi Bendahara Kelas berhasil. Silakan login menggunakan email."
            )
        );
    }


    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }


    header(
        "Location: register.php?error=" .
        urlencode(
            "Registrasi gagal: " .
            $e->getMessage()
        )
    );

    exit;
}
