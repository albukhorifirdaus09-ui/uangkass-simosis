<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireLogin();
if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: ../../index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'tambah';
    if ($action === 'hapus') {
        $departmentId = (int) ($_POST['id'] ?? 0);
        if ($departmentId <= 0) { header('Location: index.php?error=' . urlencode('Jurusan tidak valid.')); exit; }
        $stmt = $pdo->prepare('DELETE FROM departments WHERE id = ?');
        $stmt->execute([$departmentId]);
        header('Location: index.php?success=' . urlencode('Jurusan berhasil dihapus dari daftar pilihan.')); exit;
    }
    $namaJurusan = trim($_POST['nama_jurusan'] ?? '');
    $singkatan = strtoupper(trim($_POST['singkatan'] ?? ''));
    if ($namaJurusan === '' || mb_strlen($namaJurusan) > 100 || $singkatan === '' || mb_strlen($singkatan) > 20) { header('Location: index.php?error=' . urlencode('Nama jurusan dan singkatan wajib diisi. Singkatan maksimal 20 karakter.')); exit; }
    try { $stmt = $pdo->prepare("INSERT INTO departments (nama_jurusan, singkatan, status) VALUES (?, ?, 'aktif')"); $stmt->execute([$namaJurusan, $singkatan]); header('Location: index.php?success=' . urlencode('Jurusan berhasil ditambahkan.')); } catch (PDOException $e) { header('Location: index.php?error=' . urlencode('Jurusan sudah ada atau gagal ditambahkan.')); }
    exit;
}

$departments = $pdo->query("SELECT d.id, d.nama_jurusan, d.singkatan, d.status, COUNT(DISTINCT c.id) AS total_kelas, COUNT(s.id) AS total_siswa FROM departments d LEFT JOIN classes c ON c.jurusan = d.nama_jurusan LEFT JOIN students s ON s.kelas_id = c.id GROUP BY d.id, d.nama_jurusan, d.singkatan, d.status ORDER BY d.nama_jurusan ASC")->fetchAll(PDO::FETCH_ASSOC);
$totalJurusan = count($departments);
$totalKelas = array_sum(array_map(static fn($department) => (int) $department['total_kelas'], $departments));
$totalSiswa = array_sum(array_map(static fn($department) => (int) $department['total_siswa'], $departments));
$pageTitle = 'Data Jurusan';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container department-page-modern">
        <section class="department-page-hero"><div><span class="department-page-kicker"><i class="bi bi-building-fill"></i> Data Sekolah</span><h2>Data Jurusan</h2><p>Atur daftar jurusan dan singkatan yang digunakan pada kelas serta registrasi siswa.</p></div><div class="department-hero-icon"><i class="bi bi-diagram-3-fill"></i></div></section>

        <section class="department-stat-grid"><article><span class="department-stat-icon blue"><i class="bi bi-building"></i></span><div><small>Total jurusan</small><strong><?= $totalJurusan ?></strong><p>Jurusan tersedia</p></div></article><article><span class="department-stat-icon teal"><i class="bi bi-door-open"></i></span><div><small>Total kelas</small><strong><?= $totalKelas ?></strong><p>Kelas yang tertaut</p></div></article><article><span class="department-stat-icon violet"><i class="bi bi-people"></i></span><div><small>Total siswa</small><strong><?= $totalSiswa ?></strong><p>Siswa di semua jurusan</p></div></article></section>

        <?php if (isset($_GET['success'])): ?><div class="alert alert-success department-alert"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($_GET['success']) ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger department-alert"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>

        <section class="department-workspace">
            <article class="department-add-card"><div class="department-card-head"><div><span class="eyebrow">Tambah jurusan</span><h4>Buat pilihan jurusan baru</h4></div><i class="bi bi-plus-circle"></i></div><p>Singkatan akan digunakan pada tampilan kas per jurusan dan kelas.</p><form method="post" class="department-add-form"><div><label for="nama_jurusan">Nama jurusan</label><div class="department-input"><i class="bi bi-building"></i><input id="nama_jurusan" name="nama_jurusan" maxlength="100" placeholder="Contoh: Rekayasa Perangkat Lunak" required></div></div><div><label for="singkatan">Singkatan</label><div class="department-input"><i class="bi bi-type"></i><input id="singkatan" name="singkatan" class="text-uppercase" maxlength="20" placeholder="Contoh: RPL" required></div></div><button class="btn department-add-button"><i class="bi bi-plus-lg"></i> Tambah Jurusan</button></form></article>
            <article class="department-tip-card"><div class="department-tip-icon"><i class="bi bi-lightbulb-fill"></i></div><div><h5>Tips penamaan</h5><p>Gunakan nama jurusan lengkap dan singkatan yang mudah dikenali, misalnya <strong>Teknik Komputer dan Jaringan — TKJ</strong>.</p></div></article>
        </section>

        <section class="department-list-card"><div class="department-list-head"><div><span class="eyebrow">Daftar jurusan</span><h4>Jurusan yang tersedia</h4><p><?= $totalJurusan ?> jurusan dapat dipilih saat siswa mendaftar.</p></div><div class="department-search"><i class="bi bi-search"></i><input type="search" id="departmentSearch" placeholder="Cari jurusan atau singkatan..."></div></div><div class="department-card-grid" id="departmentGrid">
            <?php foreach ($departments as $department): ?><article class="department-item-card" data-search="<?= htmlspecialchars(strtolower($department['nama_jurusan'] . ' ' . ($department['singkatan'] ?? ''))) ?>"><div class="department-item-top"><span class="department-abbreviation"><?= htmlspecialchars($department['singkatan'] ?: '—') ?></span><form method="post" onsubmit="return confirm('Hapus jurusan ini dari daftar pilihan? Data kelas yang sudah ada tidak akan dihapus.');"><input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?= (int) $department['id'] ?>"><button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus jurusan"><i class="bi bi-trash"></i></button></form></div><h5><?= htmlspecialchars($department['nama_jurusan']) ?></h5><span class="department-active-status"><i class="bi bi-circle-fill"></i><?= htmlspecialchars(ucfirst($department['status'])) ?></span><div class="department-item-stats"><span><i class="bi bi-door-open"></i><?= (int) $department['total_kelas'] ?> kelas</span><span><i class="bi bi-people"></i><?= (int) $department['total_siswa'] ?> siswa</span></div></article><?php endforeach; ?>
            <?php if (!$departments): ?><div class="department-empty"><i class="bi bi-building-add"></i><strong>Belum ada jurusan</strong><span>Tambahkan jurusan pertama melalui formulir di atas.</span></div><?php endif; ?>
            <div id="departmentNotFound" class="department-empty d-none"><i class="bi bi-search"></i><strong>Jurusan tidak ditemukan</strong><span>Coba kata kunci lain.</span></div>
        </div></section>
    </section>
</main>
<script>document.getElementById('departmentSearch')?.addEventListener('input', function () { const keyword = this.value.trim().toLowerCase(); const cards = Array.from(document.querySelectorAll('.department-item-card')); let found = 0; cards.forEach(card => { const visible = card.dataset.search.includes(keyword); card.classList.toggle('d-none', !visible); if (visible) found++; }); document.getElementById('departmentNotFound')?.classList.toggle('d-none', found !== 0 || keyword === ''); });</script>
<?php require_once '../../includes/footer.php'; ?>
