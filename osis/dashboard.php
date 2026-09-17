<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireOsisMember();

$stmt = $pdo->prepare("SELECT s.nama_lengkap, c.nama_kelas, op.nama_jabatan, ay.tahun_ajaran FROM osis_members om INNER JOIN students s ON s.id = om.student_id INNER JOIN classes c ON c.id = s.kelas_id INNER JOIN osis_positions op ON op.id = om.position_id INNER JOIN academic_years ay ON ay.id = om.academic_year_id WHERE s.user_id = ? AND om.status = 'aktif' AND ay.status = 'aktif' ORDER BY om.id DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);
$announcements = $pdo->query("SELECT judul, isi, tanggal_publish FROM announcements WHERE status = 'publish' AND target IN ('semua', 'osis') ORDER BY tanggal_publish DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$activities = $pdo->query("SELECT nama_kegiatan, tanggal_mulai, lokasi, status FROM activities ORDER BY tanggal_mulai DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = 'Dashboard OSIS';
require_once '../includes/header.php';
?>
<div class="role-page"><div class="role-wrap"><div class="role-hero"><span class="text-uppercase small">Pusat Organisasi</span><h2 class="fw-bold">Dashboard OSIS</h2>
<?php if ($member): ?><p>Selamat datang, <strong><?= htmlspecialchars($member['nama_lengkap']) ?></strong> · <?= htmlspecialchars($member['nama_jabatan']) ?> · <?= htmlspecialchars($member['nama_kelas']) ?></p><?php endif; ?></div>
<div class="row g-3 mb-4"><div class="col-lg-6"><div class="card card-body"><h5>Pengumuman</h5><?php foreach ($announcements as $item): ?><article class="border-bottom py-2"><strong><?= htmlspecialchars($item['judul']) ?></strong><p class="mb-0"><?= nl2br(htmlspecialchars($item['isi'])) ?></p></article><?php endforeach; ?><?php if (!$announcements): ?><p class="text-muted mb-0">Belum ada pengumuman.</p><?php endif; ?></div></div><div class="col-lg-6"><div class="card card-body"><h5>Kegiatan OSIS</h5><?php foreach ($activities as $item): ?><p class="border-bottom py-2 mb-0"><strong><?= htmlspecialchars($item['nama_kegiatan']) ?></strong><br><?= htmlspecialchars($item['tanggal_mulai']) ?> · <?= htmlspecialchars($item['lokasi'] ?? '-') ?> · <?= htmlspecialchars($item['status']) ?></p><?php endforeach; ?><?php if (!$activities): ?><p class="text-muted mb-0">Belum ada kegiatan.</p><?php endif; ?></div></div></div>
<a href="../auth/logout.php" class="btn btn-danger">Logout</a></div></div>
<?php require_once '../includes/footer.php'; ?>
