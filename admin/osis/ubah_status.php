<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$id = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$allowed = ['aktif', 'nonaktif', 'keluar', 'lulus'];
if ($id < 1 || !in_array($status, $allowed, true)) { header('Location: index.php?error=' . urlencode('Status anggota tidak valid.')); exit; }

try {
    $stmt = $pdo->prepare('SELECT om.student_id, s.user_id FROM osis_members om INNER JOIN students s ON s.id = om.student_id WHERE om.id = ? LIMIT 1');
    $stmt->execute([$id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$member) throw new RuntimeException('Anggota OSIS tidak ditemukan.');

    $endDate = in_array($status, ['keluar', 'lulus'], true) ? date('Y-m-d') : null;
    $stmt = $pdo->prepare('UPDATE osis_members SET status = ?, tanggal_selesai = COALESCE(?, tanggal_selesai) WHERE id = ?');
    $stmt->execute([$status, $endDate, $id]);

    if ($member['user_id']) {
        if ($status === 'aktif') {
            $pdo->prepare("UPDATE users SET role = 'osis', status = 'aktif' WHERE id = ?")->execute([$member['user_id']]);
        } else {
            $stillActive = $pdo->prepare("SELECT 1 FROM osis_members WHERE student_id = ? AND status = 'aktif' AND id != ? LIMIT 1");
            $stillActive->execute([$member['student_id'], $id]);
            if (!$stillActive->fetchColumn()) $pdo->prepare("UPDATE users SET role = 'siswa' WHERE id = ?")->execute([$member['user_id']]);
        }
    }
    header('Location: index.php?success=status');
} catch (Throwable $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}
exit;
