<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

$tahunAjaran = $pdo->query("SELECT * FROM academic_years ORDER BY tahun_ajaran DESC")->fetchAll(PDO::FETCH_ASSOC);
$totalTahunAjaran = count($tahunAjaran);
$aktif = count(array_filter($tahunAjaran, static fn($row) => $row['status'] === 'aktif'));

$pageTitle = 'Tahun Ajaran';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container member-page-modern">
        <section class="member-page-hero">
            <div>
                <span class="member-page-kicker"><i class="bi bi-calendar-event"></i> Data Sekolah</span>
                <h2>Tahun Ajaran</h2>
                <p>Kelola daftar tahun ajaran dan atur tahun ajaran yang aktif.</p>
            </div>
            <a href="tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Tahun Ajaran</a>
        </section>

        <section class="member-stat-grid">
            <article>
                <span class="member-stat-icon blue"><i class="bi bi-calendar-event"></i></span>
                <div>
                    <small>Total tahun ajaran</small>
                    <strong><?= $totalTahunAjaran ?></strong>
                    <p>Semua tahun ajaran terdaftar</p>
                </div>
            </article>
            <article>
                <span class="member-stat-icon teal"><i class="bi bi-check-circle"></i></span>
                <div>
                    <small>Tahun ajaran aktif</small>
                    <strong><?= $aktif ?></strong>
                    <p>Tahun ajaran saat ini</p>
                </div>
            </article>
        </section>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success member-alert"><i class="bi bi-check-circle-fill"></i> Data berhasil diproses.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger member-alert"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <section class="member-list-card">
            <div class="member-list-head">
                <div>
                    <span class="eyebrow">Daftar tahun ajaran</span>
                    <h4>Semua Tahun Ajaran</h4>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table member-modern-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tahunAjaran as $index => $row): ?>
                            <tr>
                                <td class="member-row-number"><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($row['tahun_ajaran']) ?></strong></td>
                                <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_selesai']) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="member-action-group">
                                        <?php if ($row['status'] === 'nonaktif'): ?>
                                            <a href="aktifkan.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-success" title="Aktifkan"><i class="bi bi-check-circle"></i> Aktifkan</a>
                                        <?php endif; ?>
                                        <a href="edit.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-light text-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$tahunAjaran): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5"><i class="bi bi-calendar-event d-block fs-2 mb-2"></i>Belum ada data tahun ajaran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</main>
<?php require_once '../../includes/footer.php'; ?>
