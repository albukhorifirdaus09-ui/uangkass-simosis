<?php
require_once "../../config/auth.php";
require_once "../../config/database.php";

requireLogin();

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

if (!$id) {

    header("Location: index.php");

    exit;
}


/* =========================
   CEK DATA
========================= */

$stmt = $pdo->prepare("
    SELECT id
    FROM class_officers
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {

    header(
        "Location: index.php?error=Data pengurus tidak ditemukan"
    );

    exit;
}


/* Arsipkan, jangan hapus: penugasan periode lama harus tetap menjadi riwayat. */

try {

    $stmt = $pdo->prepare("
        UPDATE class_officers
        SET status = 'nonaktif', tanggal_selesai = COALESCE(tanggal_selesai, CURDATE())
        WHERE id = ?
    ");

    $stmt->execute([$id]);


    header(
        "Location: index.php?success=arsip"
    );

    exit;

} catch (PDOException $e) {

    header(
        "Location: index.php?error=Gagal mengarsipkan data pengurus"
    );

    exit;
}
