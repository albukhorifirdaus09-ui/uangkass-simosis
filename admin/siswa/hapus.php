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


/*
|--------------------------------------------------------------------------
| Cari user_id
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT user_id
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


try {
    $pdo->beginTransaction();

    /*
    | Relasi students.user_id menggunakan ON DELETE SET NULL.
    | Karena itu siswa harus dihapus terlebih dahulu, baru akun login.
    */
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);

    if (!empty($user_id)) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    }

    $pdo->commit();


    header("Location: index.php?success=Siswa berhasil dihapus");
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: index.php?error=Siswa tidak dapat dihapus");
    exit;
}
