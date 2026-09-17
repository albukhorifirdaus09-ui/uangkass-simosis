<?php
require_once '../../config/auth.php';
requireRole('admin');
header('Location: index.php?error=' . urlencode('Kenaikan kelas dilakukan otomatis saat tahun ajaran baru diaktifkan.'));
exit;

$sourceClassId = (int) ($_POST['source_class_id'] ?? 0);
$targetClassId = (int) ($_POST['target_class_id'] ?? 0);
$studentIds = array_values(array_unique(array_filter(array_map('intval', $_POST['student_ids'] ?? []))));
if (!$sourceClassId || !$targetClassId || $sourceClassId === $targetClassId || !$studentIds) { header('Location: index.php?source_class_id=' . $sourceClassId . '&error=' . urlencode('Pilih minimal satu siswa dan kelas tujuan yang berbeda.')); exit; }

$year = $pdo->query("SELECT id, tanggal_mulai FROM academic_years WHERE status = 'aktif' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$year) { header('Location: index.php?error=' . urlencode('Tahun ajaran aktif tidak ditemukan.')); exit; }

try {
    $pdo->beginTransaction();
    $targetExists = $pdo->prepare('SELECT id FROM classes WHERE id = ?'); $targetExists->execute([$targetClassId]);
    if (!$targetExists->fetchColumn()) { throw new RuntimeException('Kelas tujuan tidak ditemukan.'); }
    $marks = implode(',', array_fill(0, count($studentIds), '?'));
    $check = $pdo->prepare("SELECT id FROM students WHERE kelas_id = ? AND id IN ($marks)"); $check->execute(array_merge([$sourceClassId], $studentIds));
    $validIds = array_map('intval', $check->fetchAll(PDO::FETCH_COLUMN));
    if (!$validIds) { throw new RuntimeException('Siswa yang dipilih tidak berada di kelas asal.'); }
    $close = $pdo->prepare("UPDATE student_class_history SET status = 'nonaktif', tanggal_selesai = CURDATE() WHERE academic_year_id = ? AND status = 'aktif' AND student_id IN (" . implode(',', array_fill(0, count($validIds), '?')) . ")"); $close->execute(array_merge([$year['id']], $validIds));
    $insert = $pdo->prepare("INSERT INTO student_class_history (student_id, class_id, academic_year_id, tanggal_mulai, status, catatan) VALUES (?, ?, ?, ?, 'aktif', 'Kenaikan/perpindahan kelas oleh admin')");
    $update = $pdo->prepare('UPDATE students SET kelas_id = ? WHERE id = ?');
    foreach ($validIds as $studentId) { $insert->execute([$studentId, $targetClassId, $year['id'], $year['tanggal_mulai']]); $update->execute([$targetClassId, $studentId]); }
    $pdo->commit(); header('Location: index.php?source_class_id=' . $sourceClassId . '&success=' . count($validIds)); exit;
} catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); header('Location: index.php?source_class_id=' . $sourceClassId . '&error=' . urlencode($e->getMessage())); exit; }
