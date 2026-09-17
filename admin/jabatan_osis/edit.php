<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

requireRole(['admin']);

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM osis_positions WHERE id = ?");
$stmt->execute([$id]);
$position = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$position) {
    header("Location: index.php?error=" . urlencode("Jabatan tidak ditemukan."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_jabatan = trim($_POST['nama_jabatan'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($nama_jabatan)) {
        $error = "Nama jabatan wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE osis_positions SET nama_jabatan = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$nama_jabatan, $deskripsi, $id]);
            header("Location: index.php?success=" . urlencode("Jabatan berhasil diperbarui."));
            exit;
        } catch (PDOException $e) {
            $error = "Gagal memperbarui jabatan: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Jabatan OSIS';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Jabatan OSIS</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="nama_jabatan" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" required value="<?= htmlspecialchars($_POST['nama_jabatan'] ?? $position['nama_jabatan']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($_POST['deskripsi'] ?? $position['deskripsi']) ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php require_once '../../includes/footer.php'; ?>
