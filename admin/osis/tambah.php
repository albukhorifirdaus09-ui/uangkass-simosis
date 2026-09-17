<?php

require_once "../../config/auth.php";
require_once "../../config/database.php";

requireLogin();

header("Location: index.php?info=tambah_dinonaktifkan");
exit;

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Ambil daftar siswa
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        s.id,
        s.nama_lengkap,
        c.nama_kelas

    FROM students s

    LEFT JOIN classes c
        ON c.id = s.kelas_id

    INNER JOIN users u
        ON u.id = s.user_id

    WHERE u.status = 'aktif'

    ORDER BY s.nama_lengkap ASC
");

$students = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Ambil tahun ajaran
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        tahun_ajaran
    FROM academic_years
    ORDER BY id DESC
");

$academicYears = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Ambil jabatan
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        nama_jabatan,
        deskripsi
    FROM osis_positions
    WHERE nama_jabatan IN ('Ketua', 'Wakil Ketua')
    ORDER BY id ASC
");

$positions = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Anggota OSIS</title>

    <link
        href="/simosis/assets/vendor/bootstrap/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card border-0 shadow">

                <div class="card-body p-4">

                    <h3 class="fw-bold">
                        Tambah Anggota OSIS
                    </h3>

                    <p class="text-muted">
                        Admin/Pembina menentukan siswa dan jabatan OSIS.
                    </p>


                    <?php if (isset($_GET['error'])): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($_GET['error']); ?>

                        </div>

                    <?php endif; ?>


                    <form
                        action="proses_tambah.php"
                        method="POST"
                    >

                        <!-- SISWA -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Siswa
                            </label>

                            <select
                                name="student_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Siswa --
                                </option>

                                <?php foreach ($students as $student): ?>

                                    <option
                                        value="<?= $student['id']; ?>"
                                    >

                                        <?= htmlspecialchars($student['nama_lengkap']); ?>

                                        (<?= htmlspecialchars($student['nama_kelas'] ?? '-'); ?>)

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- JABATAN -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Jabatan OSIS
                            </label>

                            <select
                                name="position_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Jabatan --
                                </option>

                                <?php foreach ($positions as $position): ?>

                                    <option
                                        value="<?= $position['id']; ?>"
                                    >

                                        <?= htmlspecialchars($position['nama_jabatan']); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- TAHUN AJARAN -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Tahun Ajaran
                            </label>

                            <select
                                name="academic_year_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Tahun Ajaran --
                                </option>

                                <?php foreach ($academicYears as $year): ?>

                                    <option
                                        value="<?= $year['id']; ?>"
                                    >

                                        <?= htmlspecialchars($year['tahun_ajaran']); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- TANGGAL MULAI -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Tanggal Mulai
                            </label>

                            <input
                                type="date"
                                name="tanggal_mulai"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- TANGGAL SELESAI -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Tanggal Selesai
                            </label>

                            <input
                                type="date"
                                name="tanggal_selesai"
                                class="form-control"
                            >

                            <small class="text-muted">
                                Boleh dikosongkan jika belum ditentukan.
                            </small>

                        </div>


                        <div class="d-flex gap-2">

                            <a
                                href="index.php"
                                class="btn btn-secondary"
                            >
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Simpan Anggota OSIS
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
