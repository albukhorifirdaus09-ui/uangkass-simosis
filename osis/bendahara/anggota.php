<?php

require_once '../../config/auth.php';
require_once '../../config/database.php';

requireOsisTreasurer();


$stmt = $pdo->query("
    SELECT
        s.nama_lengkap,
        c.nama_kelas,
        c.jurusan

    FROM osis_members om

    INNER JOIN students s
        ON s.id = om.student_id

    INNER JOIN classes c
        ON c.id = s.kelas_id

    INNER JOIN academic_years ay
        ON ay.id = om.academic_year_id

    WHERE om.status = 'aktif'

      AND ay.status = 'aktif'

    ORDER BY s.nama_lengkap ASC
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
$memberClasses = array_unique(array_filter(array_column($members, 'nama_kelas')));
$memberDepartments = array_unique(array_filter(array_column($members, 'jurusan')));


$pageTitle = 'Anggota OSIS';


require_once '../../includes/header.php';

require_once '../../includes/osis_sidebar.php';

?>


<div class="osis-main-content">


    <header class="osis-topbar">

        <button
            class="btn btn-light d-lg-none"
            id="osisMenuToggle"
        >

            <i class="bi bi-list"></i>

        </button>


        <div>

            <strong>
                Anggota OSIS
            </strong>

            <small class="d-block text-muted">
                Data kepengurusan aktif
            </small>

        </div>


        <span class="text-muted small">

            <?= htmlspecialchars(
                $_SESSION['username'] ?? ''
            ) ?>

        </span>

    </header>


    <main class="role-page osis-member-page">

        <div class="role-wrap">


            <div class="role-hero osis-member-hero"><div>

                <span class="text-uppercase small">
                    Organisasi
                </span>

                <h2>
                    Anggota OSIS
                </h2>

                <p class="mb-0">
                    Daftar anggota OSIS aktif.
                </p>

            </div><div class="osis-member-hero-icon"><i class="bi bi-people-fill"></i><small><?= count($members) ?> anggota</small></div></div>

            <section class="osis-member-stat-grid"><article class="primary"><span><i class="bi bi-people-fill"></i></span><small>Anggota aktif</small><strong><?= count($members) ?></strong><p>Kepengurusan tahun aktif</p></article><article><span class="blue"><i class="bi bi-mortarboard-fill"></i></span><small>Kelas terlibat</small><strong><?= count($memberClasses) ?></strong><p>kelas mengirim anggota</p></article><article><span class="teal"><i class="bi bi-diagram-3-fill"></i></span><small>Jurusan terlibat</small><strong><?= count($memberDepartments) ?></strong><p>jurusan dalam OSIS</p></article></section>


            <section class="table-card osis-member-table-card">


                <div class="section-heading">

                    <div>

                        <span class="eyebrow">
                            Daftar anggota
                        </span>

                        <h4>
                            <?= count($members) ?>
                            anggota aktif
                        </h4>

                    </div>


                    <div class="osis-member-search"><i class="bi bi-search"></i><input type="search" id="osisMemberSearch" placeholder="Cari nama, kelas, jurusan..."></div>

                </div>


                <div class="table-responsive">


                    <table class="table align-middle osis-member-table">


                        <thead>

                            <tr>

                                <th>No.</th>

                                <th>Nama</th>

                                <th>Kelas</th>

                                <th>Jurusan</th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach (
                                $members
                                as $i => $member
                            ): ?>

                                <tr>

                                    <td class="osis-member-number">
                                        <?= $i + 1 ?>
                                    </td>


                                    <td>

                                        <span class="osis-member-avatar"><?= htmlspecialchars(strtoupper(mb_substr($member['nama_lengkap'], 0, 1, 'UTF-8'))) ?></span><strong>

                                            <?= htmlspecialchars(
                                                $member['nama_lengkap']
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <span class="osis-member-class"><i class="bi bi-mortarboard"></i><?= htmlspecialchars(
                                            $member['nama_kelas']
                                        ) ?></span>

                                    </td>

                                    <td>

                                        <span class="osis-member-department"><?= htmlspecialchars(
                                            $member['jurusan'] ?: '-'
                                        ) ?></span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                            <?php if (!$members): ?>

                                <tr>

                                    <td
                                        colspan="4"
                                        class="text-center text-muted py-4"
                                    >

                                        Belum ada anggota OSIS aktif.

                                    </td>

                                </tr>

                            <?php endif; ?>


                        </tbody>

                    </table>


                </div>


            </section>


        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('osisMemberSearch');
            if (!search) return;
            search.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                document.querySelectorAll('.osis-member-table tbody tr').forEach(function (row) {
                    if (row.querySelector('[colspan]')) return;
                    row.style.display = row.textContent.toLowerCase().includes(keyword) ? '' : 'none';
                });
            });
        });
        </script>

    </main>

</div>


<?php require_once '../../includes/footer.php'; ?>
