<?php

require_once "../../config/auth.php";
require_once "../../config/database.php";

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

$id = $_GET['id'] ?? '';

if (!is_numeric($id)) {
    header("Location: index.php");
    exit;
}

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Cari user_id dari anggota OSIS
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
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $member = $stmt->fetch();

    if (!$member) {
        $pdo->rollBack();

        header("Location: index.php?error=Anggota OSIS tidak ditemukan");
        exit;
    }

    $user_id = $member['user_id'];


    /*
    |--------------------------------------------------------------------------
    | Arsipkan anggota; riwayat dan transaksi tidak boleh dihapus.
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE osis_members
        SET status = 'keluar',
            tanggal_selesai = COALESCE(tanggal_selesai, CURDATE()),
            alasan_keluar = COALESCE(NULLIF(alasan_keluar, ''), 'Diarsipkan oleh admin')
        WHERE id = ?
    ");

    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | Cek apakah user masih menjadi anggota OSIS lain
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT om.id
        FROM osis_members om
        INNER JOIN students s
            ON s.id = om.student_id
        WHERE s.user_id = ?
          AND om.status = 'aktif'
        LIMIT 1
    ");

    $stmt->execute([$user_id]);

    $masihOsis = $stmt->fetch();


    /*
    |--------------------------------------------------------------------------
    | Jika sudah tidak punya keanggotaan OSIS,
    | kembalikan role menjadi siswa
    |--------------------------------------------------------------------------
    */

    if (!$masihOsis) {

        $stmt = $pdo->prepare("
            UPDATE users
            SET role = 'siswa'
            WHERE id = ?
        ");

        $stmt->execute([$user_id]);
    }


    $pdo->commit();

    header("Location: index.php?success=Anggota OSIS berhasil diarsipkan");
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: index.php?error=Gagal mengarsipkan anggota OSIS");
    exit;
}
