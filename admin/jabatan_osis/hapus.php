<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = $_POST['id'] ?? null;

if ($id) {
    // Check if there are active osis members using this position
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM osis_members WHERE position_id = ? AND status = 'aktif'");
    $stmt->execute([$id]);
    $activeMembers = $stmt->fetchColumn();

    if ($activeMembers > 0) {
        header("Location: index.php?error=" . urlencode("Tidak dapat menghapus jabatan karena masih ada anggota OSIS aktif dengan jabatan ini."));
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM osis_positions WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?success=" . urlencode("Jabatan berhasil dihapus."));
        exit;
    } catch (PDOException $e) {
        header("Location: index.php?error=" . urlencode("Gagal menghapus jabatan: " . $e->getMessage()));
        exit;
    }
}

header("Location: index.php");
exit;
