<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireOsisMember();

$statement = $pdo->prepare("SELECT s.nama_lengkap, s.no_hp, c.nama_kelas, op.nama_jabatan, om.bagian, om.tanggal_mulai, ay.tahun_ajaran FROM osis_members om INNER JOIN students s ON s.id = om.student_id LEFT JOIN classes c ON c.id = s.kelas_id INNER JOIN osis_positions op ON op.id = om.position_id INNER JOIN academic_years ay ON ay.id = om.academic_year_id WHERE s.user_id = ? AND om.status = 'aktif' AND ay.status = 'aktif' ORDER BY om.id DESC LIMIT 1");
$statement->execute([$_SESSION['user_id']]);
$profile = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
$pageTitle = 'Profil Keanggotaan OSIS';
require_once '../../includes/header.php';
require_once '../../includes/osis_sidebar.php';
?>
<div class="osis-main-content">
    <header class="osis-topbar"><button class="btn btn-light d-lg-none" id="osisMenuToggle"><i class="bi bi-list"></i></button><div><strong>Profil Keanggotaan</strong><small class="d-block text-muted">Data anggota OSIS aktif</small></div></header>
    <main class="role-page"><div class="role-wrap">
        <section class="role-hero"><span>Anggota terverifikasi</span><h2><?= htmlspecialchars($profile['nama_lengkap'] ?? 'Anggota OSIS') ?></h2><p><?= htmlspecialchars($profile['nama_jabatan'] ?? 'Anggota OSIS') ?><?= !empty($profile['bagian']) ? ' · ' . htmlspecialchars($profile['bagian']) : '' ?></p></section>
        <section class="card card-body"><h4>Data keanggotaan</h4><dl class="row mb-0">
            <dt class="col-sm-4">Nama</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['nama_lengkap'] ?? '-') ?></dd>
            <dt class="col-sm-4">Kelas</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['nama_kelas'] ?? '-') ?></dd>
            <dt class="col-sm-4">Jabatan</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['nama_jabatan'] ?? '-') ?></dd>
            <dt class="col-sm-4">Masa bakti</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['tahun_ajaran'] ?? '-') ?></dd>
            <dt class="col-sm-4">Mulai bertugas</dt><dd class="col-sm-8"><?= !empty($profile['tanggal_mulai']) ? date('d M Y', strtotime($profile['tanggal_mulai'])) : '-' ?></dd>
            <dt class="col-sm-4">No. HP</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['no_hp'] ?? '-') ?></dd>
        </dl></section>
    </div></main>
</div>
<?php require_once '../../includes/footer.php'; ?>
