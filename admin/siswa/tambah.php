<?php

require_once "../../config/auth.php";
require_once "../../config/database.php";

requireLogin();

header("Location: index.php?info=tambah_dinonaktifkan");
exit;

$stmt = $pdo->query("
    SELECT id, nama_kelas
    FROM classes
    ORDER BY nama_kelas ASC
");

$classes = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Siswa - SIMOSIS</title>

    <link
        href="/simosis/assets/vendor/bootstrap/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-7">

            <div class="card shadow">

                <div class="card-body p-4">

                    <h3 class="fw-bold mb-4">
                        Tambah Siswa
                    </h3>

                    <form
                        action="proses_tambah.php"
                        method="POST"
                    >

                        <div class="mb-3">

                            <label class="form-label">
                                Nama Lengkap
                            </label>

                            <input
                                type="text"
                                name="nama_lengkap"
                                class="form-control"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Kelas
                            </label>

                            <select
                                name="kelas_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Kelas --
                                </option>

                                <?php foreach ($classes as $class): ?>

                                    <option value="<?= $class['id']; ?>">

                                        <?= htmlspecialchars($class['nama_kelas']); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Jenis Kelamin
                            </label>

                            <select
                                name="jenis_kelamin"
                                class="form-select"
                            >

                                <option value="">
                                    -- Pilih --
                                </option>

                                <option value="L">
                                    Laki-laki
                                </option>

                                <option value="P">
                                    Perempuan
                                </option>

                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                required
                            >

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                            <small class="text-muted">
                                Minimal 6 karakter.
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
                                Simpan Siswa
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
