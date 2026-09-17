<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

$academicYears = $pdo->query("SELECT * FROM academic_years ORDER BY tahun_ajaran DESC")->fetchAll(PDO::FETCH_ASSOC);

$selectedYearId = (int) ($_GET['year_id'] ?? 0);
$osisMembers = [];

if ($selectedYearId) {
    // Get members from the selected year, excluding class XII
    $stmt = $pdo->prepare("
        SELECT om.id, om.student_id, om.position_id, s.nama_lengkap, c.nama_kelas, op.nama_jabatan, om.status
        FROM osis_members om 
        INNER JOIN students s ON s.id = om.student_id 
        INNER JOIN classes c ON c.id = s.kelas_id 
        INNER JOIN osis_positions op ON op.id = om.position_id 
        WHERE om.academic_year_id = ? AND c.tingkat != 'XII'
        ORDER BY s.nama_lengkap ASC
    ");
    $stmt->execute([$selectedYearId]);
    $osisMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetYearId = (int) ($_POST['target_year_id'] ?? 0);
    $extendOsis = $_POST['extend_osis'] ?? [];
    
    if ($targetYearId && !empty($extendOsis)) {
        try {
            $pdo->beginTransaction();
            
            // Perpanjang member yang dipilih ke target_year_id
            foreach ($extendOsis as $memberId) {
                // Get old member data
                $stmt = $pdo->prepare("SELECT student_id, position_id FROM osis_members WHERE id = ?");
                $stmt->execute([$memberId]);
                $member = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($member) {
                    // Check if already exists in target year
                    $stmt = $pdo->prepare("SELECT id FROM osis_members WHERE student_id = ? AND academic_year_id = ?");
                    $stmt->execute([$member['student_id'], $targetYearId]);
                    if (!$stmt->fetch()) {
                        // Insert new record
                        $stmt = $pdo->prepare("INSERT INTO osis_members (student_id, position_id, academic_year_id, status, tanggal_mulai) VALUES (?, ?, ?, 'menunggu', CURDATE())");
                        $stmt->execute([$member['student_id'], $member['position_id'], $targetYearId]);
                    }
                }
            }
            
            $pdo->commit();
            header("Location: index.php?success=perpanjang");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Gagal perpanjang: " . $e->getMessage();
        }
    } else {
        $error = "Pilih tahun ajaran tujuan dan minimal satu anggota.";
    }
}

$pageTitle = 'Perpanjang Kepengurusan OSIS';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Perpanjang Kepengurusan OSIS</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form action="" method="get" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Pilih Tahun Ajaran Asal</label>
                            <select name="year_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                <?php foreach ($academicYears as $year): ?>
                                    <option value="<?= $year['id'] ?>" <?= $year['id'] === $selectedYearId ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($year['tahun_ajaran']) ?> <?= $year['status'] === 'aktif' ? '(Aktif)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>

                <?php if ($selectedYearId && empty($osisMembers)): ?>
                    <div class="alert alert-info">Tidak ada anggota OSIS yang bisa diperpanjang pada tahun ajaran ini (Siswa kelas XII tidak ditampilkan).</div>
                <?php elseif ($selectedYearId && !empty($osisMembers)): ?>
                <form action="" method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tahun Ajaran Tujuan</label>
                        <select name="target_year_id" class="form-select" required>
                            <option value="">-- Pilih Tahun Ajaran Tujuan --</option>
                            <?php foreach ($academicYears as $year): ?>
                                <?php if ($year['id'] !== $selectedYearId): ?>
                                    <option value="<?= $year['id'] ?>">
                                        <?= htmlspecialchars($year['tahun_ajaran']) ?> <?= $year['status'] === 'aktif' ? '(Aktif)' : '' ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h6 class="mb-3">Pilih Anggota OSIS (Selain Kelas XII)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll">
                                        </div>
                                    </th>
                                    <th>Nama Anggota</th>
                                    <th>Kelas</th>
                                    <th>Jabatan Sebelumnya</th>
                                    <th>Status Sebelumnya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($osisMembers as $member): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input member-checkbox" type="checkbox" name="extend_osis[]" value="<?= htmlspecialchars($member['id']) ?>" id="extend_<?= $member['id'] ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <label class="form-check-label" for="extend_<?= $member['id'] ?>">
                                            <strong><?= htmlspecialchars($member['nama_lengkap']) ?></strong>
                                        </label>
                                    </td>
                                    <td><?= htmlspecialchars($member['nama_kelas']) ?></td>
                                    <td><?= htmlspecialchars($member['nama_jabatan']) ?></td>
                                    <td>
                                        <span class="badge <?= $member['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($member['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Proses perpanjangan anggota OSIS terpilih?');">
                        <i class="bi bi-person-plus-fill"></i> Perpanjang Anggota Terpilih
                    </button>
                    <a href="index.php" class="btn btn-light">Kembali</a>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<script>
document.getElementById('checkAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>
<?php require_once '../../includes/footer.php'; ?>
