<?php
require_once "../config/auth.php";
require_once "../config/database.php";

requireLogin();
if (($_SESSION['role'] ?? '') !== 'siswa') {
    header("Location: /simosis/index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT s.id, s.nama_lengkap, c.nama_kelas FROM students s INNER JOIN classes c ON c.id = s.kelas_id WHERE s.user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$payment = null;
if ($student) {
    $stmt = $pdo->prepare("
        SELECT sd.bulan, sd.tahun, sd.nominal, COALESCE(sdp.status, 'belum_bayar') AS payment_status
        FROM student_dues sd
        LEFT JOIN student_due_payments sdp ON sdp.student_due_id = sd.id AND sdp.student_id = ?
        WHERE sd.bulan = ? AND sd.tahun = ? AND sd.jenis_kas = 'osis' AND sd.status = 'aktif'
        LIMIT 1
    ");
    $stmt->execute([$student['id'], date('n'), date('Y')]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
}

$announcements = $pdo->query("SELECT judul, isi, tanggal_publish FROM announcements WHERE status = 'publish' AND target IN ('semua', 'siswa') ORDER BY tanggal_publish DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Dashboard Siswa';
require_once '../includes/header.php';
require_once '../includes/siswa_sidebar.php';
?>
<div class="osis-main-content">
    <header class="osis-topbar">
        <button class="btn btn-light d-lg-none" id="osisMenuToggle"><i class="bi bi-list"></i></button>
        <div><strong>Dashboard Siswa</strong><small class="d-block text-muted">Portal Informasi Siswa</small></div>
        <span class="text-muted small"><?= htmlspecialchars($_SESSION['username']) ?></span>
    </header>

    <main class="role-page">
        <div class="role-wrap">
            <section class="role-hero mb-4">
                <span class="text-uppercase small">Ruang Siswa</span>
                <h2 class="fw-bold">Dashboard Siswa</h2>
                <p class="mb-0">Selamat datang, <?= htmlspecialchars($_SESSION['username']); ?></p>
            </section>

            <div class="row g-3">
                <div class="col-lg-6">
                    <?php if ($student): ?>
                        <div class="role-card h-100">
                            <h5>Informasi Siswa</h5>
                            <p class="mb-2">Nama: <strong><?= htmlspecialchars($student['nama_lengkap']) ?></strong></p>
                            <p class="mb-3">Kelas: <strong><?= htmlspecialchars($student['nama_kelas']) ?></strong></p>
                            
                            <h6 class="mt-4">Kas OSIS bulan <?= date('F Y') ?></h6>
                            <?php if ($payment): ?>
                                <p class="mb-1">Nominal: Rp <?= number_format((float) $payment['nominal'], 0, ',', '.') ?></p>
                                <p class="mb-0">Status: <strong class="<?= $payment['payment_status'] === 'lunas' ? 'text-success' : 'text-danger' ?>"><?= $payment['payment_status'] === 'lunas' ? 'Sudah bayar' : 'Belum bayar' ?></strong></p>
                            <?php else: ?>
                                <p class="mb-0 text-muted">Tagihan bulan ini belum dibuat.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-6">
                    <div class="role-card h-100">
                        <h5>Pengumuman OSIS</h5>
                        <?php foreach ($announcements as $announcement): ?>
                            <article class="border-bottom py-2">
                                <strong><?= htmlspecialchars($announcement['judul']) ?></strong>
                                <p class="mb-0 small text-muted"><?= date('d M Y', strtotime($announcement['tanggal_publish'])) ?></p>
                                <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($announcement['isi'])) ?></p>
                            </article>
                        <?php endforeach; ?>
                        <?php if (!$announcements): ?><p class="text-muted mt-2">Belum ada pengumuman.</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once '../includes/footer.php'; ?>