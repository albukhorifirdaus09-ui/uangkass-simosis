<?php

require_once "../../config/auth.php";
require_once "../../config/database.php";

requireLogin();

header("Location: index.php?info=tambah_dinonaktifkan");
exit;

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah.php");
    exit;
}


$student_id = $_POST['student_id'] ?? '';
$position_id = $_POST['position_id'] ?? '';
$academic_year_id = $_POST['academic_year_id'] ?? '';
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';


/*
|--------------------------------------------------------------------------
| Validasi
|--------------------------------------------------------------------------
*/

if (
    $student_id === '' ||
    $position_id === '' ||
    $academic_year_id === '' ||
    $tanggal_mulai === ''
) {

    header("Location: tambah.php?error=Semua data wajib diisi.");

    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Cek siswa
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            s.id,
            s.user_id
        FROM students s

        WHERE s.id = ?

        LIMIT 1
    ");

    $stmt->execute([
        $student_id
    ]);

    $student = $stmt->fetch();


    if (!$student) {

        $pdo->rollBack();

        header("Location: tambah.php?error=Siswa tidak ditemukan.");

        exit;
    }

    $stmt = $pdo->prepare("SELECT nama_jabatan FROM osis_positions WHERE id = ? LIMIT 1");
    $stmt->execute([$position_id]);
    $position = $stmt->fetchColumn();

    if (!$position) {
        throw new RuntimeException('Jabatan tidak ditemukan.');
    }

    if (!in_array($position, ['Ketua', 'Wakil Ketua'], true)) {
        throw new RuntimeException('Jabatan tersebut diajukan oleh Ketua atau Wakil Ketua OSIS.');
    }

    $stmt = $pdo->prepare("SELECT id FROM osis_members WHERE position_id = ? AND academic_year_id = ? AND status = 'aktif' LIMIT 1");
    $stmt->execute([$position_id, $academic_year_id]);

    if ($stmt->fetch()) {
        throw new RuntimeException('Jabatan tersebut sudah memiliki pemegang aktif.');
    }


    /*
    |--------------------------------------------------------------------------
    | Cek apakah sudah menjadi OSIS pada tahun ajaran tersebut
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM osis_members

        WHERE student_id = ?
        AND academic_year_id = ?

        LIMIT 1
    ");

    $stmt->execute([
        $student_id,
        $academic_year_id
    ]);


    if ($stmt->fetch()) {

        $pdo->rollBack();

        header(
            "Location: tambah.php?error=Siswa tersebut sudah terdaftar sebagai anggota OSIS pada tahun ajaran ini."
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Tambahkan anggota OSIS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO osis_members
        (
            student_id,
            position_id,
            academic_year_id,
            tanggal_mulai,
            tanggal_selesai,
            status
        )

        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            'aktif'
        )
    ");

    $stmt->execute([
        $student_id,
        $position_id,
        $academic_year_id,
        $tanggal_mulai,
        $tanggal_selesai !== '' ? $tanggal_selesai : null
    ]);


    /*
    |--------------------------------------------------------------------------
    | Ubah role user menjadi OSIS
    |--------------------------------------------------------------------------
    */

    if (!empty($student['user_id'])) {

        $stmt = $pdo->prepare("
            UPDATE users
            SET role = 'osis'
            WHERE id = ?
        ");

        $stmt->execute([
            $student['user_id']
        ]);

    }


    $pdo->commit();


    header(
        "Location: index.php?success=Anggota OSIS berhasil ditambahkan."
    );

    exit;


} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        "Location: tambah.php?error=Terjadi kesalahan saat menyimpan data."
    );

    exit;
}
