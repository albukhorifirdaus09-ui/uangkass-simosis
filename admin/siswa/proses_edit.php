<?php

require_once "../../config/auth.php";
require_once "../../config/database.php";

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = $_POST['id'] ?? '';

$nama = trim($_POST['nama_lengkap'] ?? '');
$kelas_id = $_POST['kelas_id'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$status = $_POST['status'] ?? 'aktif';


if (
    !is_numeric($id) ||
    $nama === '' ||
    $kelas_id === '' ||
    $email === ''
) {

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Ambil user_id
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT user_id, kelas_id
    FROM students
    WHERE id = ?
");

$stmt->execute([$id]);

$student = $stmt->fetch();

if (!$student) {
    header("Location: index.php");
    exit;
}

$user_id = $student['user_id'];
$kelasLamaId = (int) $student['kelas_id'];


/*
|--------------------------------------------------------------------------
| Cek Email milik user lain
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE email = ?
      AND id != ?
");

$stmt->execute([$email, $user_id]);

if ($stmt->fetch()) {

    header("Location: edit.php?id=$id&error=Email sudah digunakan");
    exit;
}


try {

    $pdo->beginTransaction();


    /*
    | Update STUDENTS
    */

    $stmt = $pdo->prepare("
        UPDATE students

        SET
            nama_lengkap = ?,
            kelas_id = ?,
            jenis_kelamin = ?

        WHERE id = ?
    ");

    $stmt->execute([
        $nama,
        $kelas_id,
        $jenis_kelamin !== '' ? $jenis_kelamin : null,
        $id
    ]);

    /* Kelas saat ini tetap diperbarui untuk modul kas, sedangkan kelas lama
       dicatat per tahun ajaran agar tidak hilang dari arsip. */
    if ($kelasLamaId !== (int) $kelas_id) {
        $year = $pdo->query("SELECT id, tanggal_mulai FROM academic_years WHERE status = 'aktif' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if (!$year) {
            throw new RuntimeException('Tahun ajaran aktif tidak ditemukan.');
        }
        $close = $pdo->prepare("UPDATE student_class_history SET status = 'pindah', tanggal_selesai = CURDATE() WHERE student_id = ? AND academic_year_id = ? AND status = 'aktif'");
        $close->execute([$id, $year['id']]);
        $insert = $pdo->prepare("INSERT INTO student_class_history (student_id, class_id, academic_year_id, tanggal_mulai, status, catatan) VALUES (?, ?, ?, CURDATE(), 'aktif', 'Perpindahan kelas oleh admin')");
        $insert->execute([$id, $kelas_id, $year['id']]);
    }


    /*
    | Update USERS
    */

    if ($password !== '') {

        if (strlen($password) < 6) {

            $pdo->rollBack();

            header("Location: edit.php?id=$id&error=Password minimal 6 karakter");
            exit;
        }

        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            UPDATE users

            SET
                email = ?,
                password = ?,
                status = ?

            WHERE id = ?
        ");

        $stmt->execute([
            $email,
            $password_hash,
            $status,
            $user_id
        ]);

    } else {

        $stmt = $pdo->prepare("
            UPDATE users

            SET
                email = ?,
                status = ?

            WHERE id = ?
        ");

        $stmt->execute([
            $email,
            $status,
            $user_id
        ]);
    }


    $pdo->commit();


    header("Location: index.php?success=Data siswa berhasil diperbarui");
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: edit.php?id=$id&error=Gagal memperbarui data");
    exit;
}
