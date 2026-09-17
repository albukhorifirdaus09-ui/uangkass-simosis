<?php

require_once "../../config/auth.php";
require_once "../../config/database.php";


requireRole('admin');


$id = (int) (
    $_GET['id']
    ??
    $_POST['id']
    ??
    0
);


if ($id <= 0) {

    header(
        "Location: index.php?error=" .
        urlencode(
            "Data anggota tidak ditemukan."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| PROSES SIMPAN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $position_id = (int) (
        $_POST['position_id'] ?? 0
    );


    if ($position_id <= 0) {

        header(
            "Location: tentukan_jabatan.php?id=" .
            $id .
            "&error=" .
            urlencode(
                "Silakan pilih jabatan."
            )
        );

        exit;
    }


    try {

        $pdo->beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA PENDAFTAR
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT
                om.id,
                om.student_id,
                om.academic_year_id,
                om.status,

                s.user_id

            FROM osis_members om

            INNER JOIN students s
                ON s.id = om.student_id

            WHERE om.id = ?

              AND om.status = 'menunggu'

            LIMIT 1
        ");

        $stmt->execute([$id]);

        $member = $stmt->fetch(
            PDO::FETCH_ASSOC
        );


        if (!$member) {

            throw new Exception(
                "Pendaftaran OSIS tidak ditemukan atau sudah diproses."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CEK JABATAN
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT
                id,
                nama_jabatan

            FROM osis_positions

            WHERE id = ?

            LIMIT 1
        ");

        $stmt->execute([
            $position_id
        ]);

        $position = $stmt->fetch(
            PDO::FETCH_ASSOC
        );


        if (!$position) {

            throw new Exception(
                "Jabatan tidak ditemukan."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CEK KETUA / WAKIL / BENDAHARA
        |--------------------------------------------------------------------------
        |
        | Jabatan utama hanya boleh satu orang aktif
        | pada tahun ajaran yang sama.
        |
        */

        $jabatanUtama = [
            'ketua',
            'wakil ketua',
            'bendahara'
        ];


        if (
            in_array(
                strtolower(
                    $position['nama_jabatan']
                ),
                $jabatanUtama,
                true
            )
        ) {

            $stmt = $pdo->prepare("
                SELECT
                    om.id,
                    s.nama_lengkap

                FROM osis_members om

                INNER JOIN students s
                    ON s.id = om.student_id

                WHERE om.position_id = ?

                  AND om.academic_year_id = ?

                  AND om.status = 'aktif'

                  AND om.id != ?

                LIMIT 1
            ");

            $stmt->execute([
                $position_id,
                $member['academic_year_id'],
                $id
            ]);


            $sudahAda = $stmt->fetch(
                PDO::FETCH_ASSOC
            );


            if ($sudahAda) {

                throw new Exception(
                    "Jabatan " .
                    $position['nama_jabatan'] .
                    " sudah diisi oleh " .
                    $sudahAda['nama_lengkap'] .
                    "."
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE OSIS MEMBERS
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE osis_members

            SET
                position_id = ?,
                status = 'aktif',
                tanggal_mulai = CURDATE(),
                tanggal_selesai = NULL

            WHERE id = ?
        ");

        $stmt->execute([
            $position_id,
            $id
        ]);


        /*
        |--------------------------------------------------------------------------
        | UPDATE USER ROLE
        |--------------------------------------------------------------------------
        */

        if (!empty($member['user_id'])) {

            $stmt = $pdo->prepare("
                UPDATE users

                SET
                    role = 'osis',
                    status = 'aktif'

                WHERE id = ?
            ");

            $stmt->execute([
                $member['user_id']
            ]);
        }


        $pdo->commit();


        header(
            "Location: index.php?success=jabatan"
        );

        exit;


    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();
        }


        header(
            "Location: tentukan_jabatan.php?id=" .
            $id .
            "&error=" .
            urlencode(
                $e->getMessage()
            )
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| AMBIL PENDAFTAR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        om.id,
        s.nama_lengkap,

        c.nama_kelas,
        c.jurusan,

        ay.tahun_ajaran

    FROM osis_members om

    INNER JOIN students s
        ON s.id = om.student_id

    INNER JOIN classes c
        ON c.id = s.kelas_id

    INNER JOIN academic_years ay
        ON ay.id = om.academic_year_id

    WHERE om.id = ?

      AND om.status = 'menunggu'

    LIMIT 1
");

$stmt->execute([$id]);

$member = $stmt->fetch(
    PDO::FETCH_ASSOC
);


if (!$member) {

    header(
        "Location: index.php?error=" .
        urlencode(
            "Pendaftaran OSIS tidak ditemukan atau sudah diproses."
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL JABATAN
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        nama_jabatan,
        deskripsi

    FROM osis_positions

    ORDER BY
        CASE LOWER(nama_jabatan)

            WHEN 'ketua'
            THEN 1

            WHEN 'wakil ketua'
            THEN 2

            WHEN 'sekretaris'
            THEN 3

            WHEN 'bendahara'
            THEN 4

            WHEN 'anggota'
            THEN 5

            ELSE 6

        END,

        nama_jabatan ASC
");

$positions = $stmt->fetchAll(
    PDO::FETCH_ASSOC
);


$pageTitle = "Tentukan Jabatan";

require_once "../../includes/header.php";
require_once "../../includes/sidebar.php";

?>


<main class="main-content">


<?php
require_once "../../includes/navbar.php";
?>


<div class="container-fluid py-4">


    <div class="row justify-content-center">


        <div class="col-lg-7">


            <div
                class="card border-0 shadow-sm"
            >


                <div class="card-body p-4">


                    <h4 class="fw-bold">

                        Tentukan Jabatan OSIS

                    </h4>


                    <p class="text-muted">

                        Pilih jabatan untuk anggota yang baru mendaftar.

                    </p>


                    <?php if (
                        isset($_GET['error'])
                    ): ?>


                        <div class="alert alert-danger">

                            <?= htmlspecialchars(
                                $_GET['error']
                            ); ?>

                        </div>


                    <?php endif; ?>


                    <div
                        class="bg-light rounded p-3 mb-4"
                    >


                        <div class="mb-2">

                            <strong>
                                Nama:
                            </strong>

                            <?= htmlspecialchars(
                                $member['nama_lengkap']
                            ); ?>

                        </div>


                        <div class="mb-2">

                            <strong>
                                Kelas:
                            </strong>

                            <?= htmlspecialchars(
                                $member['nama_kelas']
                            ); ?>

                            -

                            <?= htmlspecialchars(
                                $member['jurusan']
                            ); ?>

                        </div>


                        <div>

                            <strong>
                                Tahun Ajaran:
                            </strong>

                            <?= htmlspecialchars(
                                $member['tahun_ajaran']
                            ); ?>

                        </div>


                    </div>


                    <form method="POST">


                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $member['id']; ?>"
                        >


                        <div class="mb-4">


                            <label
                                class="form-label fw-semibold"
                            >

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


                                <?php foreach (
                                    $positions as $position
                                ): ?>


                                    <option
                                        value="<?= (int) $position['id']; ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $position['nama_jabatan']
                                        ); ?>

                                    </option>


                                <?php endforeach; ?>


                            </select>


                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-check-circle"></i>

                            Simpan dan Aktifkan

                        </button>


                        <a
                            href="index.php"
                            class="btn btn-secondary"
                        >

                            Batal

                        </a>


                    </form>


                </div>


            </div>


        </div>


    </div>


</div>


</main>


<?php
require_once "../../includes/footer.php";
?>
