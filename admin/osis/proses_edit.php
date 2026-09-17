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
$position_id = $_POST['position_id'] ?? '';
$academic_year_id = $_POST['academic_year_id'] ?? '';
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status = $_POST['status'] ?? '';

if (
    !is_numeric($id) ||
    $position_id === '' ||
    $academic_year_id === '' ||
    $tanggal_mulai === '' ||
    !in_array($status, ['aktif', 'nonaktif', 'keluar', 'lulus'], true)
) {
    header("Location: index.php?error=Data tidak valid");
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT student_id
        FROM osis_members
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $member = $stmt->fetch();

    if (!$member) {
        header("Location: index.php?error=Anggota OSIS tidak ditemukan");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Cek duplikasi periode
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM osis_members

        WHERE student_id = ?
        AND academic_year_id = ?
        AND id != ?

        LIMIT 1
    ");

    $stmt->execute([
        $member['student_id'],
        $academic_year_id,
        $id
    ]);

    if ($stmt->fetch()) {

        header(
            "Location: edit.php?id=$id&error=Siswa sudah memiliki data OSIS pada tahun ajaran tersebut"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE osis_members

        SET
            position_id = ?,
            academic_year_id = ?,
            tanggal_mulai = ?,
            tanggal_selesai = ?,
            status = ?,
            alasan_keluar = ?

        WHERE id = ?
    ");

    $stmt->execute([
        $position_id,
        $academic_year_id,
        $tanggal_mulai,
        $tanggal_selesai !== '' ? $tanggal_selesai : null,
        $status,
        in_array($status, ['keluar', 'lulus'], true) ? trim($_POST['alasan_keluar'] ?? '') : null,
        $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | Sinkronisasi users.role
    |--------------------------------------------------------------------------
    |
    | Jika status OSIS diubah ke 'nonaktif', kembalikan role ke 'siswa'
    | agar user tidak terkunci saat login.
    | Jika status diubah ke 'aktif', pastikan role = 'osis'.
    |
    */

    $stmtUser = $pdo->prepare("
        SELECT user_id FROM students WHERE id = ? LIMIT 1
    ");
    $stmtUser->execute([$member['student_id']]);
    $userId = $stmtUser->fetchColumn();

    if ($userId) {
        if (in_array($status, ['nonaktif', 'keluar', 'lulus'], true)) {
            /* Cek apakah masih ada membership aktif lain */
            $stmtCheck = $pdo->prepare("
                SELECT id FROM osis_members
                WHERE student_id = ? AND status = 'aktif' AND id != ?
                LIMIT 1
            ");
            $stmtCheck->execute([$member['student_id'], $id]);
            if (!$stmtCheck->fetch()) {
                $pdo->prepare("UPDATE users SET role = 'siswa' WHERE id = ?")
                    ->execute([$userId]);
            }
        } elseif ($status === 'aktif') {
            $pdo->prepare("UPDATE users SET role = 'osis' WHERE id = ?")
                ->execute([$userId]);
        }
    }


    header(
        "Location: index.php?success=Data anggota OSIS berhasil diperbarui"
    );

    exit;

} catch (PDOException $e) {

    header(
        "Location: edit.php?id=$id&error=Gagal memperbarui data"
    );

    exit;
}
