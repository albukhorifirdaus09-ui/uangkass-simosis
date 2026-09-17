<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahunAjaran = trim($_POST['tahun_ajaran'] ?? '');
    $tanggalMulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggalSelesai = trim($_POST['tanggal_selesai'] ?? '');

    if ($tahunAjaran && $tanggalMulai && $tanggalSelesai) {
        $stmt = $pdo->prepare("INSERT INTO academic_years (tahun_ajaran, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, 'nonaktif')");
        $stmt->execute([$tahunAjaran, $tanggalMulai, $tanggalSelesai]);
        header('Location: index.php?success=tambah');
        exit;
    }
}

$pageTitle = 'Tambah Tahun Ajaran';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tambah Tahun Ajaran</h5>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-control" placeholder="Contoh: 2027/2028" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    <a href="index.php" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </section>
</main>
<?php require_once '../../includes/footer.php'; ?>
