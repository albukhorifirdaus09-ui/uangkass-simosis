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
    header("Location: index.php?error=ID tidak valid");
    exit;
}


/*
|--------------------------------------------------------------------------
| Ambil data anggota OSIS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        om.id,
        om.student_id,
        om.position_id,
        om.academic_year_id,
        om.tanggal_mulai,
        om.tanggal_selesai,
        om.status,
        om.alasan_keluar,

        s.nama_lengkap,

        c.nama_kelas,

        op.nama_jabatan,

        ay.tahun_ajaran

    FROM osis_members om

    INNER JOIN students s
        ON s.id = om.student_id

    LEFT JOIN classes c
        ON c.id = s.kelas_id

    INNER JOIN osis_positions op
        ON op.id = om.position_id

    INNER JOIN academic_years ay
        ON ay.id = om.academic_year_id

    WHERE om.id = ?

    LIMIT 1
");

$stmt->execute([$id]);

$member = $stmt->fetch();

if (!$member) {
    header("Location: index.php?error=Data anggota OSIS tidak ditemukan");
    exit;
}


/*
|--------------------------------------------------------------------------
| Ambil jabatan
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        nama_jabatan
    FROM osis_positions
    ORDER BY id ASC
");

$positions = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Ambil tahun ajaran
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        tahun_ajaran
    FROM academic_years
    ORDER BY id DESC
");

$academicYears = $stmt->fetchAll();

$pageTitle = 'Edit Anggota OSIS';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container admin-edit-page">
        <a href="index.php" class="admin-edit-back"><i class="bi bi-arrow-left"></i> Kembali ke Anggota OSIS</a>
        <section class="admin-edit-hero"><div><span><i class="bi bi-pencil-square"></i> Kelola anggota</span><h2>Edit Anggota OSIS</h2><p>Perbarui jabatan, masa kepengurusan, dan status anggota dengan data yang tepat.</p></div><i class="bi bi-person-gear"></i></section>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger admin-edit-alert"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
        <section class="admin-edit-grid">
            <aside class="admin-edit-profile"><span class="admin-edit-avatar"><?= htmlspecialchars(strtoupper(mb_substr($member['nama_lengkap'], 0, 1, 'UTF-8'))) ?></span><span class="admin-edit-profile-label">Anggota yang diedit</span><h4><?= htmlspecialchars($member['nama_lengkap']); ?></h4><p><i class="bi bi-mortarboard"></i> <?= htmlspecialchars($member['nama_kelas'] ?? '-'); ?></p><div><span><i class="bi bi-award"></i> <?= htmlspecialchars($member['nama_jabatan']); ?></span><span><i class="bi bi-calendar3"></i> <?= htmlspecialchars($member['tahun_ajaran']); ?></span></div></aside>
            <section class="admin-edit-form-card"><div class="admin-edit-form-head"><div><span><i class="bi bi-sliders"></i> Informasi kepengurusan</span><h4>Perbarui data anggota</h4><p>Atur tanggal selesai periode, lalu tentukan status sesuai kondisi anggota.</p></div></div><form action="proses_edit.php" method="POST"><input type="hidden" name="id" value="<?= (int) $member['id']; ?>"><div class="row g-3"><div class="col-md-6"><label class="form-label">Jabatan OSIS</label><select name="position_id" class="form-select" required><?php foreach ($positions as $position): ?><option value="<?= (int) $position['id']; ?>" <?= $position['id'] == $member['position_id'] ? 'selected' : ''; ?>><?= htmlspecialchars($position['nama_jabatan']); ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Tahun Ajaran</label><select name="academic_year_id" class="form-select" required><?php foreach ($academicYears as $year): ?><option value="<?= (int) $year['id']; ?>" <?= $year['id'] == $member['academic_year_id'] ? 'selected' : ''; ?>><?= htmlspecialchars($year['tahun_ajaran']); ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Tanggal Mulai</label><input type="date" name="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($member['tanggal_mulai']); ?>" required></div><div class="col-md-6"><label class="form-label">Tanggal Selesai Periode <small>(opsional)</small></label><input type="date" name="tanggal_selesai" class="form-control" value="<?= htmlspecialchars($member['tanggal_selesai'] ?? ''); ?>"></div><div class="col-md-6"><label class="form-label">Status Keanggotaan</label><select name="status" class="form-select" required><option value="aktif" <?= $member['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif — lanjut sebagai OSIS</option><option value="nonaktif" <?= $member['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif — sementara / PKL</option><option value="keluar" <?= $member['status'] === 'keluar' ? 'selected' : ''; ?>>Keluar — berhenti dari OSIS</option><option value="lulus" <?= $member['status'] === 'lulus' ? 'selected' : ''; ?>>Lulus — menyelesaikan sekolah</option></select><small class="admin-edit-help"><i class="bi bi-info-circle"></i> Status nonaktif dapat diubah kembali menjadi aktif.</small></div><div class="col-md-6"><label class="form-label">Alasan keluar / keterangan</label><input type="text" name="alasan_keluar" class="form-control" value="<?= htmlspecialchars($member['alasan_keluar'] ?? ''); ?>" maxlength="255" placeholder="Contoh: PKL, pindah sekolah, atau keterangan lain"></div></div><div class="admin-edit-actions"><a href="index.php" class="btn btn-light">Batal</a><button type="submit" class="btn btn-primary"><i class="bi bi-save2"></i> Simpan Perubahan</button></div></form></section>
        </section>
    </section>
</main>
<?php require_once '../../includes/footer.php'; ?>
