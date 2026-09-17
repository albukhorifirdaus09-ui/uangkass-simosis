<?php
require_once '../config/auth.php';
require_once '../config/database.php';
$officer = requireActiveClassOfficer($pdo);
$classId = (int) $officer['class_id'];
$stmt = $pdo->prepare('SELECT id, nama_lengkap, jenis_kelamin, no_hp, alamat FROM students WHERE kelas_id = ? ORDER BY nama_lengkap ASC');
$stmt->execute([$classId]);
$siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalSiswa = count($siswa);
$totalLaki = count(array_filter($siswa, static fn($student) => $student['jenis_kelamin'] === 'L'));
$totalPerempuan = count(array_filter($siswa, static fn($student) => $student['jenis_kelamin'] === 'P'));
$totalKontak = count(array_filter($siswa, static fn($student) => trim((string) $student['no_hp']) !== ''));
$success = $_SESSION['success'] ?? null; $error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
$pageTitle = 'Data Siswa';
require_once '../includes/header.php';
require_once '../includes/class_sidebar.php';
?>
<main class="class-main">
    <header class="class-topbar"><div class="class-topbar-left"><button type="button" class="class-menu-toggle" id="classMenuToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button><div><h5 class="mb-0">Data Siswa</h5><small><?= htmlspecialchars($labelKelas ?? $officer['nama_kelas']) ?></small></div></div><div class="class-user"><div class="class-user-avatar"><i class="bi bi-person-fill"></i></div><div><strong><?= htmlspecialchars($officer['nama_lengkap']) ?></strong><small>Bendahara Kelas</small></div></div></header>
    <section class="class-content class-student-page">
        <section class="class-student-hero"><div><span class="class-student-kicker"><i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($labelKelas ?? $officer['nama_kelas']) ?></span><h2>Data siswa kelas</h2><p>Kelola daftar siswa yang menjadi tanggung jawab Bendahara Kelas.</p></div><a href="siswa_tambah.php" class="btn btn-warning"><i class="bi bi-person-plus-fill"></i> Tambah Siswa</a></section>

        <section class="class-student-stat-grid"><article><span class="class-student-stat-icon blue"><i class="bi bi-people"></i></span><div><small>Total siswa</small><strong><?= $totalSiswa ?></strong><p>Siswa dalam kelas</p></div></article><article><span class="class-student-stat-icon violet"><i class="bi bi-gender-male"></i></span><div><small>Laki-laki</small><strong><?= $totalLaki ?></strong><p>Siswa laki-laki</p></div></article><article><span class="class-student-stat-icon pink"><i class="bi bi-gender-female"></i></span><div><small>Perempuan</small><strong><?= $totalPerempuan ?></strong><p>Siswa perempuan</p></div></article><article><span class="class-student-stat-icon teal"><i class="bi bi-telephone"></i></span><div><small>Kontak tersedia</small><strong><?= $totalKontak ?></strong><p>Nomor HP tercatat</p></div></article></section>

        <?php if ($success): ?><div class="alert alert-success class-student-alert"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger class-student-alert"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <section class="class-student-list-card"><div class="class-student-list-head"><div><span>Daftar siswa</span><h4>Anggota kelas <?= htmlspecialchars($officer['nama_kelas']) ?></h4><p><?= $totalSiswa ?> siswa terdaftar pada kelas ini.</p></div><div class="class-student-search"><i class="bi bi-search"></i><input type="search" id="classStudentSearch" placeholder="Cari nama atau nomor HP..."></div></div><div class="table-responsive"><table class="table class-student-modern-table align-middle mb-0"><thead><tr><th>No.</th><th>Siswa</th><th>Jenis kelamin</th><th>No. HP</th><th>Alamat</th></tr></thead><tbody id="classStudentTableBody">
            <?php foreach ($siswa as $index => $student): $name = trim((string) $student['nama_lengkap']); $initial = $name !== '' ? strtoupper(mb_substr($name, 0, 1, 'UTF-8')) : '—'; ?><tr data-search="<?= htmlspecialchars(strtolower($name . ' ' . ($student['no_hp'] ?? ''))) ?>"><td class="class-student-row-number"><?= $index + 1 ?></td><td><div class="class-student-profile"><span class="class-student-avatar"><?= htmlspecialchars($initial) ?></span><strong><?= htmlspecialchars($name ?: '—') ?></strong></div></td><td><?= $student['jenis_kelamin'] === 'L' ? '<span class="class-student-gender male"><i class="bi bi-gender-male"></i> Laki-laki</span>' : ($student['jenis_kelamin'] === 'P' ? '<span class="class-student-gender female"><i class="bi bi-gender-female"></i> Perempuan</span>' : '<span class="text-muted">—</span>') ?></td><td><?= trim((string) $student['no_hp']) !== '' ? '<span class="class-student-phone"><i class="bi bi-telephone"></i> ' . htmlspecialchars($student['no_hp']) . '</span>' : '<span class="text-muted">—</span>' ?></td><td class="class-student-address" title="<?= htmlspecialchars((string) ($student['alamat'] ?? '')) ?>"><?= htmlspecialchars(trim((string) $student['alamat']) ?: '—') ?></td></tr><?php endforeach; ?>
            <?php if (!$siswa): ?><tr><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-people d-block fs-2 mb-2"></i>Belum ada data siswa. Tambahkan siswa melalui tombol di atas.</td></tr><?php endif; ?><tr id="classStudentNotFound" class="d-none"><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-search d-block fs-3 mb-2"></i>Siswa tidak ditemukan.</td></tr>
        </tbody></table></div></section>
    </section>
</main>
<script>document.getElementById('classStudentSearch')?.addEventListener('input', function () { const keyword = this.value.trim().toLowerCase(); const rows = Array.from(document.querySelectorAll('#classStudentTableBody tr[data-search]')); let found = 0; rows.forEach(row => { const visible = row.dataset.search.includes(keyword); row.classList.toggle('d-none', !visible); if (visible) found++; }); document.getElementById('classStudentNotFound')?.classList.toggle('d-none', found !== 0 || keyword === ''); });</script>
<?php require_once '../includes/footer.php'; ?>
