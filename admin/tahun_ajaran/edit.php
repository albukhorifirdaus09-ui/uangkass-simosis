<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM academic_years WHERE id = ?");
$stmt->execute([$id]);
$tahunAjaran = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tahunAjaran) {
    header('Location: index.php?error=Data tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahunAjaranStr = trim($_POST['tahun_ajaran'] ?? '');
    $tanggalMulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggalSelesai = trim($_POST['tanggal_selesai'] ?? '');

    if ($tahunAjaranStr && $tanggalMulai && $tanggalSelesai) {
        $stmt = $pdo->prepare("UPDATE academic_years SET tahun_ajaran = ?, tanggal_mulai = ?, tanggal_selesai = ? WHERE id = ?");
        $stmt->execute([$tahunAjaranStr, $tanggalMulai, $tanggalSelesai, $id]);
        header('Location: index.php?success=edit');
        exit;
    }
}

$pageTitle = 'Edit Tahun Ajaran';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Tahun Ajaran</h5>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-control" value="<?= htmlspecialchars($tahunAjaran['tahun_ajaran']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($tahunAjaran['tanggal_mulai']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="<?= htmlspecialchars($tahunAjaran['tanggal_selesai']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    <a href="index.php" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </section>
</main>
<?php require_once '../../includes/footer.php'; ?>
