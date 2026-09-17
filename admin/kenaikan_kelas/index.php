<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

$years = $pdo->query('SELECT id, tahun_ajaran, status FROM academic_years ORDER BY tanggal_mulai DESC')->fetchAll(PDO::FETCH_ASSOC);
$yearId = (int)($_GET['academic_year_id'] ?? 0);
$grade = $_GET['tingkat'] ?? '';

if (!in_array($grade, ['X', 'XI', 'XII'], true)) {
    $grade = '';
}

if (!$yearId) {
    foreach ($years as $year) {
        if ($year['status'] === 'aktif') {
            $yearId = (int)$year['id'];
            break;
        }
    }
}

$sql = "
    SELECT sch.*, s.nama_lengkap, c.nama_kelas, c.jurusan, ay.tahun_ajaran
    FROM student_class_history sch
    INNER JOIN students s ON s.id = sch.student_id
    INNER JOIN classes c ON c.id = sch.class_id
    INNER JOIN academic_years ay ON ay.id = sch.academic_year_id
    WHERE sch.academic_year_id = ?
";
$params = [$yearId];

if ($grade !== '') {
    $sql .= ' AND c.tingkat = ?';
    $params[] = $grade;
}

$sql .= " ORDER BY FIELD(c.tingkat, 'X', 'XI', 'XII'), c.jurusan, s.nama_lengkap";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Kenaikan Kelas Otomatis';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container">
        <!-- Header Section -->
        <div class="content-card mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: none; padding: 28px 32px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="text-white mb-1 fw-bold" style="letter-spacing: -0.02em;">Data Kenaikan Kelas</h2>
                    <p class="text-white-50 mb-0" style="font-size: 15px;">Melihat daftar siswa dan riwayat kenaikan kelas per tahun ajaran.</p>
                </div>
                <div>
                    <a class="btn btn-primary d-flex align-items-center gap-2" href="riwayat.php" style="box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); border-radius: 12px; font-weight: 600;">
                        <i class="bi bi-clock-history"></i> Riwayat Semua
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert mb-4 border-0 d-flex gap-3 align-items-center" style="background: #eff6ff; border-radius: 16px; padding: 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">
                <i class="bi bi-info-circle-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1" style="color: #1e3a8a;">Sistem Kenaikan Otomatis</h6>
                <p class="mb-0" style="color: #1e40af; font-size: 14px;">
                    Sistem menjalankan promosi kelas secara otomatis setiap kali Anda mengaktifkan Tahun Ajaran Baru. 
                    Aturan yang berlaku: <span class="badge bg-white text-primary rounded-pill border">X → XI</span>, 
                    <span class="badge bg-white text-primary rounded-pill border">XI → XII</span>, dan 
                    <span class="badge bg-white text-primary rounded-pill border">XII → Lulus</span>.
                </p>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="content-card mb-4" style="border-radius: 20px;">
            <h6 class="fw-bold mb-3" style="color: #64748b; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">Filter Pencarian</h6>
            <form method="get" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label text-muted fw-semibold">Pilih Tahun Ajaran</label>
                    <select name="academic_year_id" class="form-select border-0" onchange="this.form.submit()" style="border-radius: 12px; height: 48px; background-color: #f1f5f9;">
                        <?php foreach ($years as $year): ?>
                            <option value="<?= (int)$year['id'] ?>" <?= $yearId === (int)$year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['tahun_ajaran']) ?> <?= $year['status'] === 'aktif' ? ' (Tahun Berjalan)' : '' ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted fw-semibold">Pilih Tingkat Kelas</label>
                    <select name="tingkat" class="form-select border-0" onchange="this.form.submit()" style="border-radius: 12px; height: 48px; background-color: #f1f5f9;">
                        <option value="">Semua Tingkatan (X, XI, XII)</option>
                        <option value="X" <?= $grade === 'X' ? 'selected' : '' ?>>Tingkat X</option>
                        <option value="XI" <?= $grade === 'XI' ? 'selected' : '' ?>>Tingkat XI</option>
                        <option value="XII" <?= $grade === 'XII' ? 'selected' : '' ?>>Tingkat XII</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-light w-100 fw-bold border" style="height: 48px; border-radius: 12px; color: #475569;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Data -->
        <div class="content-card p-0 overflow-hidden" style="border-radius: 20px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th class="py-3 px-4 text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">Nama Siswa</th>
                            <th class="py-3 px-4 text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">Kelas</th>
                            <th class="py-3 px-4 text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">Jurusan</th>
                            <th class="py-3 px-4 text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">Status Akhir</th>
                            <th class="py-3 px-4 text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">Catatan Sistem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php 
                                $statusBadge = 'bg-success text-white';
                                $statusIcon = 'bi-check-circle-fill';
                                
                                if(strtolower($row['status']) === 'lulus') {
                                    $statusBadge = 'bg-primary text-white';
                                    $statusIcon = 'bi-mortarboard-fill';
                                }
                                if(strtolower($row['status']) === 'nonaktif') {
                                    $statusBadge = 'bg-secondary text-white';
                                    $statusIcon = 'bi-x-circle-fill';
                                }
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="px-4 py-3 fw-bold" style="color: #0f172a;">
                                    <?= htmlspecialchars($row['nama_lengkap']) ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-weight: 600;">
                                        <?= htmlspecialchars($row['nama_kelas']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted">
                                    <?= htmlspecialchars($row['jurusan'] ?: '-') ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge <?= $statusBadge ?> px-3 py-2 rounded-pill" style="font-weight: 600;">
                                        <i class="bi <?= $statusIcon ?> me-1"></i> <?= htmlspecialchars(ucfirst($row['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($row['catatan'] ?: '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (!$rows): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div style="width: 72px; height: 72px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                                            <i class="bi bi-inbox fs-1 text-muted"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">Data Kosong</h6>
                                        <p class="text-muted mb-0">Belum ada riwayat siswa pada filter ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
