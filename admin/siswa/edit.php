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
| Ambil Data Siswa
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        s.*,
        u.email,
        u.status,
        c.nama_kelas
    FROM students s

    INNER JOIN users u
        ON u.id = s.user_id

    LEFT JOIN classes c
        ON c.id = s.kelas_id

    WHERE s.id = ?
");

$stmt->execute([$id]);

$student = $stmt->fetch();

if (!$student) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Data Kelas
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT id, nama_kelas
    FROM classes
    ORDER BY nama_kelas ASC
");

$classes = $stmt->fetchAll();

$pageTitle = 'Edit Data Siswa';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container admin-student-edit-page">
        <a href="index.php" class="admin-edit-back"><i class="bi bi-arrow-left"></i> Kembali ke Data Siswa</a>
        <section class="admin-edit-hero"><div><span><i class="bi bi-person-gear"></i> Kelola data siswa</span><h2>Edit Data Siswa</h2><p>Perbarui informasi siswa dan pengaturan akun dengan data yang akurat.</p></div><i class="bi bi-person-vcard"></i></section>
        <section class="admin-edit-grid">
            <aside class="admin-edit-profile admin-student-edit-profile"><span class="admin-edit-avatar"><?= htmlspecialchars(strtoupper(mb_substr($student['nama_lengkap'], 0, 1, 'UTF-8'))) ?></span><span class="admin-edit-profile-label">Profil siswa</span><h4><?= htmlspecialchars($student['nama_lengkap']); ?></h4><p><i class="bi bi-envelope"></i> <?= htmlspecialchars($student['email']); ?></p><div><span><i class="bi bi-mortarboard"></i> <?= htmlspecialchars($student['nama_kelas'] ?? '-'); ?></span><span><i class="bi <?= $student['status'] === 'aktif' ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' ?>"></i> Akun <?= htmlspecialchars(ucfirst($student['status'])); ?></span></div></aside>
            <section class="admin-edit-form-card"><div class="admin-edit-form-head"><div><span><i class="bi bi-pencil-square"></i> Form pembaruan</span><h4>Informasi siswa dan akun</h4><p>Perubahan akan disimpan pada data siswa dan akun login terkait.</p></div></div><form action="proses_edit.php" method="POST"><input type="hidden" name="id" value="<?= (int) $student['id']; ?>"><div class="admin-student-form-section"><span>Data siswa</span></div><div class="row g-3"><div class="col-md-7"><label class="form-label">Nama Lengkap</label><input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($student['nama_lengkap']); ?>" required></div><div class="col-md-5"><label class="form-label">Jenis Kelamin</label><select name="jenis_kelamin" class="form-select"><option value="" <?= $student['jenis_kelamin'] === null ? 'selected' : ''; ?>>-- Pilih --</option><option value="L" <?= $student['jenis_kelamin'] === 'L' ? 'selected' : ''; ?>>Laki-laki</option><option value="P" <?= $student['jenis_kelamin'] === 'P' ? 'selected' : ''; ?>>Perempuan</option></select></div><div class="col-12"><label class="form-label">Kelas</label><select name="kelas_id" class="form-select" required><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id']; ?>" <?= $student['kelas_id'] == $class['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($class['nama_kelas']); ?></option><?php endforeach; ?></select></div></div><div class="admin-student-form-section"><span>Data akun</span></div><div class="row g-3"><div class="col-md-7"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']); ?>" required></div><div class="col-md-5"><label class="form-label">Status Akun</label><select name="status" class="form-select"><option value="aktif" <?= $student['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option><option value="nonaktif" <?= $student['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option></select></div><div class="col-12"><label class="form-label">Password Baru <small>(opsional)</small></label><input type="password" name="password" class="form-control" minlength="6" placeholder="Kosongkan jika password tidak diubah"><small class="admin-edit-help"><i class="bi bi-info-circle"></i> Password baru minimal 6 karakter.</small></div></div><div class="admin-edit-actions"><a href="index.php" class="btn btn-light">Batal</a><button type="submit" class="btn btn-primary"><i class="bi bi-save2"></i> Simpan Perubahan</button></div></form></section>
        </section>
    </section>
</main>
<?php require_once '../../includes/footer.php'; ?>
