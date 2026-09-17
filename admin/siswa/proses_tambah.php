<?php

require_once "../../config/auth.php";
require_once "../../config/database.php";

requireLogin();

header("Location: index.php?info=tambah_dinonaktifkan");
exit;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah.php");
    exit;
}

$nama = trim($_POST['nama_lengkap'] ?? '');
$kelas_id = $_POST['kelas_id'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


if (
    $nama === '' ||
    $kelas_id === '' ||
    $email === '' ||
    $password === ''
) {

    header("Location: tambah.php?error=Semua data wajib diisi");
    exit;
}


if (strlen($password) < 6) {

    header("Location: tambah.php?error=Password minimal 6 karakter");
    exit;
}


/*
|--------------------------------------------------------------------------
| Cek Email
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE email = ?
");

$stmt->execute([$email]);

if ($stmt->fetch()) {

    header("Location: tambah.php?error=Email sudah digunakan");
    exit;
}


try {

    $pdo->beginTransaction();


    /*
    | Username internal dibuat otomatis; login menggunakan email.
    */

    $username = 'user_' . bin2hex(random_bytes(8));

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    /*
    | USERS
    */

    $stmt = $pdo->prepare("
        INSERT INTO users
        (
            username,
            email,
            password,
            role,
            status
        )
        VALUES
        (?, ?, ?, 'siswa', 'aktif')
    ");

    $stmt->execute([
        $username,
        $email,
        $password_hash
    ]);

    $user_id = $pdo->lastInsertId();


    /*
    | STUDENTS
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
        (?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $nama,
        $kelas_id,
        $jenis_kelamin !== '' ? $jenis_kelamin : null
    ]);


    $pdo->commit();


    header("Location: index.php?success=Siswa berhasil ditambahkan");
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: tambah.php?error=Gagal menambahkan siswa");
    exit;
}
