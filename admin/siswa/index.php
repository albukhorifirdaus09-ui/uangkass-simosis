<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireLogin();

if (!in_array($_SESSION['role'] ?? '', ['admin', 'pembina'], true)) { header('Location: ../dashboard.php'); exit; }

$stmt = $pdo->query("SELECT s.id, s.nama_lengkap, s.jenis_kelamin, s.no_hp, c.nama_kelas, COALESCE(NULLIF(d.singkatan, ''), c.jurusan, '') AS jurusan_singkat, u.status FROM students s INNER JOIN classes c ON c.id = s.kelas_id INNER JOIN users u ON u.id = s.user_id LEFT JOIN departments d ON d.nama_jurusan = c.jurusan ORDER BY s.nama_lengkap ASC");
$siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalSiswa = count($siswa);
$totalLaki = count(array_filter($siswa, static fn($student) => $student['jenis_kelamin'] === 'L'));
$totalPerempuan = count(array_filter($siswa, static fn($student) => $student['jenis_kelamin'] === 'P'));
$totalKelas = count(array_unique(array_column($siswa, 'nama_kelas')));
$pageTitle = 'Data Siswa';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container student-page-modern">
        <section class="student-page-hero">
            <div><span class="student-page-kicker"><i class="bi bi-people-fill"></i> Data Sekolah</span><h2>Data Siswa</h2><p>Admin dapat memperbarui atau menghapus data siswa yang sudah terdaftar.</p></div>
            <span class="student-page-readonly"><i class="bi bi-shield-lock-fill"></i> Tambah siswa melalui registrasi</span>
        </section>

        <section class="student-stat-grid">
            <article><span class="student-stat-icon blue"><i class="bi bi-people"></i></span><div><small>Total siswa</small><strong><?= number_format($totalSiswa) ?></strong><p>Data siswa terdaftar</p></div></article>
            <article><span class="student-stat-icon teal"><i class="bi bi-building"></i></span><div><small>Kelas terisi</small><strong><?= number_format($totalKelas) ?></strong><p>Kelas dengan siswa aktif</p></div></article>
            <article><span class="student-stat-icon violet"><i class="bi bi-gender-male"></i></span><div><small>Laki-laki</small><strong><?= number_format($totalLaki) ?></strong><p>Siswa laki-laki</p></div></article>
            <article><span class="student-stat-icon pink"><i class="bi bi-gender-female"></i></span><div><small>Perempuan</small><strong><?= number_format($totalPerempuan) ?></strong><p>Siswa perempuan</p></div></article>
        </section>

        <section class="student-data-card">
            <div class="student-data-head"><div><span class="eyebrow">Daftar siswa</span><h4>Semua siswa terdaftar</h4><p><?= $totalSiswa ?> data dapat dikelola dari halaman ini.</p></div><div class="student-search"><i class="bi bi-search"></i><input type="search" id="studentSearch" placeholder="Cari nama, kelas, atau jurusan..."></div></div>
            <div class="table-responsive"><table class="table student-modern-table align-middle mb-0"><thead><tr><th>No.</th><th>Siswa</th><th>Kelas</th><th>Jurusan</th><th>Jenis kelamin</th><th>No. HP</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody id="studentTableBody">
                <?php foreach ($siswa as $index => $row): ?><tr data-search="<?= htmlspecialchars(strtolower($row['nama_lengkap'] . ' ' . $row['nama_kelas'] . ' ' . $row['jurusan_singkat'])) ?>"><td class="student-row-number"><?= $index + 1 ?></td><td><div class="student-profile-cell"><span class="student-list-avatar"><?= htmlspecialchars(strtoupper(substr($row['nama_lengkap'], 0, 1))) ?></span><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></div></td><td><span class="student-class-pill"><?= htmlspecialchars($row['nama_kelas']) ?></span></td><td><?= $row['jurusan_singkat'] !== '' ? '<span class="student-department-pill">' . htmlspecialchars($row['jurusan_singkat']) . '</span>' : '<span class="text-muted">—</span>' ?></td><td><?= $row['jenis_kelamin'] === 'L' ? '<span class="student-gender male"><i class="bi bi-gender-male"></i> Laki-laki</span>' : ($row['jenis_kelamin'] === 'P' ? '<span class="student-gender female"><i class="bi bi-gender-female"></i> Perempuan</span>' : '—') ?></td><td><?= htmlspecialchars($row['no_hp'] ?: '—') ?></td><td><span class="student-status <?= $row['status'] === 'aktif' ? 'active' : 'inactive' ?>"><i class="bi bi-circle-fill"></i><?= htmlspecialchars(ucfirst($row['status'])) ?></span></td><td class="text-end"><div class="student-action-group"><a href="edit.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-light text-primary" title="Edit siswa"><i class="bi bi-pencil-square"></i></a><a href="hapus.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-light text-danger" title="Hapus siswa" onclick="return confirm('Yakin ingin menghapus siswa ini?');"><i class="bi bi-trash"></i></a></div></td></tr><?php endforeach; ?>
                <?php if (!$siswa): ?><tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-people d-block fs-2 mb-2"></i>Belum ada data siswa.</td></tr><?php endif; ?>
                <tr id="studentNotFound" class="d-none"><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-search d-block fs-3 mb-2"></i>Siswa tidak ditemukan.</td></tr>
            </tbody></table></div>
        </section>
    </section>
</main>
<script>
document.getElementById('studentSearch')?.addEventListener('input', function () {
    const keyword = this.value.trim().toLowerCase();
    const rows = Array.from(document.querySelectorAll('#studentTableBody tr[data-search]'));
    let found = 0;
    rows.forEach(row => { const visible = row.dataset.search.includes(keyword); row.classList.toggle('d-none', !visible); if (visible) found++; });
    document.getElementById('studentNotFound')?.classList.toggle('d-none', found !== 0 || keyword === '');
});
</script>
<?php require_once '../../includes/footer.php'; ?>
