<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

$positions = $pdo->query("SELECT op.id, op.nama_jabatan, op.deskripsi, COUNT(om.id) AS total_anggota FROM osis_positions op LEFT JOIN osis_members om ON om.position_id = op.id AND om.status = 'aktif' GROUP BY op.id, op.nama_jabatan, op.deskripsi ORDER BY op.nama_jabatan")->fetchAll(PDO::FETCH_ASSOC);
$totalJabatan = count($positions);
$totalAnggota = array_sum(array_map(static fn($position) => (int) $position['total_anggota'], $positions));
$jabatanTerisi = count(array_filter($positions, static fn($position) => (int) $position['total_anggota'] > 0));
$jabatanKosong = max(0, $totalJabatan - $jabatanTerisi);
$pageTitle = 'Jabatan OSIS';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<main class="main-content">
    <?php require_once '../../includes/navbar.php'; ?>
    <section class="dashboard-container position-page-modern">
        <section class="position-page-hero"><div><span class="position-page-kicker"><i class="bi bi-award-fill"></i> Organisasi OSIS</span><h2>Jabatan OSIS</h2><p>Pantau susunan jabatan dan jumlah anggota aktif pada setiap peran organisasi.</p></div><div class="d-flex gap-2"><a href="../osis/index.php" class="btn btn-warning"><i class="bi bi-people-fill"></i> Kelola Anggota</a><a href="tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Jabatan</a></div></section>

        <section class="position-stat-grid"><article><span class="position-stat-icon blue"><i class="bi bi-award"></i></span><div><small>Total jabatan</small><strong><?= $totalJabatan ?></strong><p>Jabatan organisasi tersedia</p></div></article><article><span class="position-stat-icon teal"><i class="bi bi-person-check"></i></span><div><small>Jabatan terisi</small><strong><?= $jabatanTerisi ?></strong><p>Memiliki anggota aktif</p></div></article><article><span class="position-stat-icon amber"><i class="bi bi-person-dash"></i></span><div><small>Jabatan kosong</small><strong><?= $jabatanKosong ?></strong><p>Belum memiliki anggota</p></div></article><article><span class="position-stat-icon violet"><i class="bi bi-people"></i></span><div><small>Total anggota</small><strong><?= $totalAnggota ?></strong><p>Anggota aktif bertugas</p></div></article></section>

        <?php if (isset($_GET['success'])): ?><div class="alert alert-success position-alert"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_GET['success']) ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger position-alert"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>

        <section class="position-insight"><i class="bi bi-info-circle-fill"></i><div><strong>Pengaturan jabatan dilakukan dari Anggota OSIS.</strong><span>Tentukan atau ubah jabatan anggota melalui menu Anggota OSIS agar data kepengurusan selalu sesuai.</span></div><a href="../osis/index.php">Buka Anggota OSIS <i class="bi bi-arrow-right"></i></a></section>

        <section class="position-list-card"><div class="position-list-head"><div><span class="eyebrow">Struktur organisasi</span><h4>Daftar jabatan OSIS</h4><p>Setiap kartu menunjukkan jumlah anggota yang aktif pada jabatan tersebut.</p></div><div class="position-search"><i class="bi bi-search"></i><input type="search" id="positionSearch" placeholder="Cari jabatan..."></div></div><div class="position-card-grid">
            <?php foreach ($positions as $position): $name = strtolower($position['nama_jabatan']); $isTreasurer = str_contains($name, 'bendahara'); $isLeader = str_contains($name, 'ketua'); $icon = $isTreasurer ? 'bi-wallet2' : ($isLeader ? 'bi-person-badge-fill' : 'bi-award-fill'); ?><article class="position-item-card <?= $isTreasurer ? 'treasurer-position' : '' ?>" data-search="<?= htmlspecialchars(strtolower($position['nama_jabatan'] . ' ' . ($position['deskripsi'] ?? ''))) ?>"><div class="position-item-top"><span class="position-icon"><i class="bi <?= $icon ?>"></i></span><span class="position-member-count"><i class="bi bi-people-fill"></i> <?= (int) $position['total_anggota'] ?></span></div><h5><?= htmlspecialchars($position['nama_jabatan']) ?></h5><p><?= htmlspecialchars($position['deskripsi'] ?: 'Belum ada deskripsi untuk jabatan ini.') ?></p><div class="position-item-footer"><span class="<?= (int) $position['total_anggota'] > 0 ? 'filled' : 'empty' ?>"><i class="bi bi-circle-fill"></i><?= (int) $position['total_anggota'] > 0 ? 'Terisi' : 'Belum terisi' ?></span><div class="d-flex gap-2"><a href="edit.php?id=<?= $position['id'] ?>" class="btn btn-sm btn-light text-primary" title="Edit jabatan"><i class="bi bi-pencil-square"></i></a><form action="hapus.php" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jabatan ini?');"><input type="hidden" name="id" value="<?= $position['id'] ?>"><button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus jabatan"><i class="bi bi-trash"></i></button></form><a href="../osis/index.php" class="btn btn-sm btn-light" title="Kelola anggota"><i class="bi bi-arrow-up-right"></i></a></div></div></article><?php endforeach; ?>
            <?php if (!$positions): ?><div class="position-empty"><i class="bi bi-award"></i><strong>Belum ada jabatan OSIS</strong><span>Data jabatan akan ditampilkan di sini.</span></div><?php endif; ?><div id="positionNotFound" class="position-empty d-none"><i class="bi bi-search"></i><strong>Jabatan tidak ditemukan</strong><span>Coba kata kunci lain.</span></div>
        </div></section>
    </section>
</main>
<script>document.getElementById('positionSearch')?.addEventListener('input', function () { const keyword = this.value.trim().toLowerCase(); const cards = Array.from(document.querySelectorAll('.position-item-card')); let found = 0; cards.forEach(card => { const visible = card.dataset.search.includes(keyword); card.classList.toggle('d-none', !visible); if (visible) found++; }); document.getElementById('positionNotFound')?.classList.toggle('d-none', found !== 0 || keyword === ''); });</script>
<?php require_once '../../includes/footer.php'; ?>
