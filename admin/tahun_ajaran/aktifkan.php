<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

$newId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM academic_years WHERE id = ?");
$stmt->execute([$newId]);
$newYear = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$newYear) {
    header("Location: index.php?error=Tahun ajaran tidak ditemukan");
    exit;
}

$stmt = $pdo->query("SELECT * FROM academic_years WHERE status = 'aktif' LIMIT 1");
$oldYear = $stmt->fetch(PDO::FETCH_ASSOC);

$osisMembers = [];
if ($oldYear) {
    $stmt = $pdo->prepare("
        SELECT om.id, om.student_id, om.position_id, s.nama_lengkap, c.nama_kelas, op.nama_jabatan, c.tingkat
        FROM osis_members om 
        INNER JOIN students s ON s.id = om.student_id 
        INNER JOIN classes c ON c.id = s.kelas_id 
        INNER JOIN osis_positions op ON op.id = om.position_id 
        WHERE om.academic_year_id = ? AND om.status = 'aktif'
    ");
    $stmt->execute([$oldYear['id']]);
    $osisMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $extendOsis = $_POST['extend_osis'] ?? [];
    
    try {
        $pdo->beginTransaction();

        /* Kenaikan otomatis: X -> XI, XI -> XII, XII -> lulus.
           Jurusan dan nomor rombel dipertahankan, misalnya X RPL 1 -> XI RPL 1. */
        if ($oldYear) {
            $classRows = $pdo->query("SELECT id, nama_kelas, tingkat, jurusan FROM classes")->fetchAll(PDO::FETCH_ASSOC);
            $classLookup = [];
            foreach ($classRows as $classRow) {
                $suffix = preg_replace('/^(XII|XI|X)\\s+/i', '', trim($classRow['nama_kelas']));
                $classLookup[strtoupper($classRow['tingkat']) . '|' . (string) $classRow['jurusan'] . '|' . mb_strtoupper($suffix, 'UTF-8')] = (int) $classRow['id'];
            }
            
            // Get old treasurers to carry them over
            $stmt = $pdo->prepare("SELECT student_id FROM class_officers WHERE academic_year_id = ? AND position = 'bendahara_kelas' AND status = 'aktif'");
            $stmt->execute([$oldYear['id']]);
            $oldTreasurers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $treasurerMap = array_flip($oldTreasurers); // For quick O(1) lookup
            $promotedTreasurers = [];

            $students = $pdo->query("SELECT s.id, s.user_id, c.nama_kelas, c.tingkat, c.jurusan FROM students s INNER JOIN classes c ON c.id = s.kelas_id")->fetchAll(PDO::FETCH_ASSOC);
            $closeHistory = $pdo->prepare("UPDATE student_class_history SET status = ?, tanggal_selesai = ? WHERE student_id = ? AND academic_year_id = ? AND status = 'aktif'");
            $newHistory = $pdo->prepare("INSERT INTO student_class_history (student_id, class_id, academic_year_id, tanggal_mulai, status, catatan) VALUES (?, ?, ?, ?, 'aktif', 'Kenaikan kelas otomatis saat tahun ajaran diaktifkan')");
            $updateStudent = $pdo->prepare("UPDATE students SET kelas_id = ? WHERE id = ?");
            $deactivateUser = $pdo->prepare("UPDATE users SET status = 'nonaktif' WHERE id = ?");

            foreach ($students as $student) {
                $level = strtoupper($student['tingkat']);
                if ($level === 'XII') {
                    $closeHistory->execute(['lulus', $oldYear['tanggal_selesai'], $student['id'], $oldYear['id']]);
                    if ($student['user_id']) {
                        $deactivateUser->execute([$student['user_id']]);
                    }
                    continue;
                }
                $nextLevel = $level === 'X' ? 'XI' : ($level === 'XI' ? 'XII' : null);
                if (!$nextLevel) continue;
                $suffix = preg_replace('/^(XII|XI|X)\\s+/i', '', trim($student['nama_kelas']));
                $targetKey = $nextLevel . '|' . (string) $student['jurusan'] . '|' . mb_strtoupper($suffix, 'UTF-8');
                if (!isset($classLookup[$targetKey])) continue;
                
                $closeHistory->execute(['nonaktif', $oldYear['tanggal_selesai'], $student['id'], $oldYear['id']]);
                $newHistory->execute([$student['id'], $classLookup[$targetKey], $newId, $newYear['tanggal_mulai']]);
                $updateStudent->execute([$classLookup[$targetKey], $student['id']]);
                
                // Track promoted treasurers
                if (isset($treasurerMap[$student['id']])) {
                    $promotedTreasurers[] = [
                        'student_id' => $student['id'],
                        'new_class_id' => $classLookup[$targetKey]
                    ];
                }
            }
        }
        
        $pdo->exec("UPDATE academic_years SET status = 'nonaktif'");
        
        $stmt = $pdo->prepare("UPDATE academic_years SET status = 'aktif' WHERE id = ?");
        $stmt->execute([$newId]);
        
        if ($oldYear) {
            /* Tutup periode lama sebagai arsip. Data anggota, jabatan, dan transaksi tidak dihapus. */
            $stmt = $pdo->prepare("UPDATE osis_members SET status = 'nonaktif', tanggal_selesai = COALESCE(tanggal_selesai, CURDATE()) WHERE academic_year_id = ? AND status = 'aktif'");
            $stmt->execute([$oldYear['id']]);
            
            $stmt = $pdo->prepare("UPDATE class_officers SET status = 'nonaktif', tanggal_selesai = CURDATE() WHERE academic_year_id = ? AND status = 'aktif'");
            $stmt->execute([$oldYear['id']]);
            
            // Carry over the class treasurers to the new academic year
            if (!empty($promotedTreasurers)) {
                $insertTreasurer = $pdo->prepare("INSERT INTO class_officers (student_id, class_id, position, academic_year_id, status, tanggal_mulai) VALUES (?, ?, 'bendahara_kelas', ?, 'aktif', CURDATE())");
                foreach ($promotedTreasurers as $pt) {
                    $insertTreasurer->execute([$pt['student_id'], $pt['new_class_id'], $newId]);
                }
            }
            
            foreach ($osisMembers as $member) {
                if (in_array((string)$member['id'], $extendOsis, true)) {
                    $stmt = $pdo->prepare("INSERT INTO osis_members (student_id, position_id, academic_year_id, status, tanggal_mulai) VALUES (?, ?, ?, 'aktif', CURDATE())");
                    $stmt->execute([$member['student_id'], $member['position_id'], $newId]);
                }
            }
        }
        
        $pdo->commit();
        header("Location: index.php?success=aktivasi");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: index.php?error=" . urlencode("Gagal aktivasi: " . $e->getMessage()));
        exit;
    }
}

$pageTitle = 'Aktifkan Tahun Ajaran';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Aktifkan Tahun Ajaran Baru</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i> 
                    Mengaktifkan tahun ajaran baru akan menonaktifkan tahun ajaran lama dan semua kepengurusan OSIS/kelas periode lama.
                </div>
                
                <h6 class="mb-3">Informasi Tahun Ajaran</h6>
                <table class="table table-bordered mb-4">
                    <tr>
                        <th style="width: 200px;">Tahun Ajaran Baru</th>
                        <td><strong><?= htmlspecialchars($newYear['tahun_ajaran']) ?></strong></td>
                    </tr>
                    <?php if ($oldYear): ?>
                    <tr>
                        <th>Tahun Ajaran Lama</th>
                        <td><?= htmlspecialchars($oldYear['tahun_ajaran']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
                
                <form action="" method="post">
                    <?php if ($oldYear && !empty($osisMembers)): ?>
                    <h6 class="mb-3">Perpanjang Kepengurusan OSIS</h6>
                    <p class="text-muted small">Pilih anggota OSIS yang ingin diperpanjang masa jabatannya ke tahun ajaran baru. Anggota yang tidak dipilih akan diubah perannya menjadi siswa biasa.</p>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Pilih</th>
                                    <th>Nama Anggota</th>
                                    <th>Kelas</th>
                                    <th>Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($osisMembers as $member): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="extend_osis[]" value="<?= htmlspecialchars($member['id']) ?>" id="extend_<?= $member['id'] ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <label class="form-check-label" for="extend_<?= $member['id'] ?>">
                                            <strong><?= htmlspecialchars($member['nama_lengkap']) ?></strong>
                                        </label>
                                    </td>
                                    <td><?= htmlspecialchars($member['nama_kelas']) ?></td>
                                    <td><?= htmlspecialchars($member['nama_jabatan']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin mengaktifkan tahun ajaran ini?');">
                        <i class="bi bi-check-circle"></i> Konfirmasi Aktivasi
                    </button>
                    <a href="index.php" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </section>
</main>
<?php require_once '../../includes/footer.php'; ?>
