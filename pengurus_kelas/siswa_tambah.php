<?php

require_once "../config/auth.php";
require_once "../config/database.php";

requireLogin();

/*
|--------------------------------------------------------------------------
| CEK BENDAHARA KELAS AKTIF
|--------------------------------------------------------------------------
*/

$officer = requireActiveClassOfficer($pdo);


/*
|--------------------------------------------------------------------------
| PROSES TAMBAH SISWA
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
    |--------------------------------------------------------------------------
    | AMBIL INPUT
    |--------------------------------------------------------------------------
    */

    $nama_lengkap = trim(
        $_POST['nama_lengkap'] ?? ''
    );

    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';

    $no_hp = trim(
        $_POST['no_hp'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDASI NAMA
    |--------------------------------------------------------------------------
    */

    if ($nama_lengkap === '') {

        $_SESSION['error'] =
            'Nama lengkap wajib diisi.';

        header("Location: siswa_tambah.php");

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI JENIS KELAMIN
    |--------------------------------------------------------------------------
    */

    if (
        !in_array(
            $jenis_kelamin,
            ['L', 'P'],
            true
        )
    ) {

        $_SESSION['error'] =
            'Jenis kelamin tidak valid.';

        header("Location: siswa_tambah.php");

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI NOMOR HP
    |--------------------------------------------------------------------------
    */

    if (
        $no_hp !== '' &&
        !preg_match(
            '/^[0-9+\-\s()]+$/',
            $no_hp
        )
    ) {

        $_SESSION['error'] =
            'Nomor HP tidak valid.';

        header("Location: siswa_tambah.php");

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    |
    | user_id = NULL
    | kelas_id diambil dari akun Bendahara Kelas.
    | Tidak pernah mengambil kelas dari input pengguna.
    |
    */

    try {


        $stmt = $pdo->prepare("
            INSERT INTO students
            (
                user_id,
                nama_lengkap,
                kelas_id,
                jenis_kelamin,
                no_hp
            )
            VALUES
            (
                NULL,
                ?,
                ?,
                ?,
                ?
            )
        ");


        $stmt->execute([
            $nama_lengkap,
            $officer['class_id'],
            $jenis_kelamin,
            $no_hp !== ''
                ? $no_hp
                : null
        ]);


        /*
        |--------------------------------------------------------------------------
        | BERHASIL
        |--------------------------------------------------------------------------
        */

        $_SESSION['success'] =
            'Siswa berhasil ditambahkan ke kelas ' .
            $officer['nama_kelas'] .
            '.';


        header("Location: siswa.php");

        exit;


    } catch (PDOException $e) {


        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        $_SESSION['error'] =
            'Gagal menambahkan siswa. Silakan coba lagi.';


        header("Location: siswa_tambah.php");

        exit;

    }

}


$pageTitle = "Tambah Siswa";

require_once "../includes/header.php";

?>


<!-- ============================================================
     SIDEBAR OVERLAY
============================================================ -->

<div
    class="class-sidebar-overlay"
    id="classSidebarOverlay">
</div>


<!-- ============================================================
     SIDEBAR
============================================================ -->

<aside
    class="class-sidebar"
    id="classSidebar">


    <!-- BRAND -->

    <div class="class-brand">

        <div class="brand-icon">

            <i class="bi bi-mortarboard-fill"></i>

        </div>


        <div>

            <strong>
                SIMOSIS
            </strong>

            <small>
                Pengurus Kelas
            </small>

        </div>

    </div>


    <!-- MENU -->

    <nav class="class-menu">


        <a
            href="dashboard.php"
            class="class-menu-item">

            <i class="bi bi-grid-1x2-fill"></i>

            <span>
                Dashboard
            </span>

        </a>


        <a
            href="siswa.php"
            class="class-menu-item active">

            <i class="bi bi-people-fill"></i>

            <span>
                Data Siswa
            </span>

        </a>


        <a
            href="uang_kas.php"
            class="class-menu-item">

            <i class="bi bi-wallet2"></i>

            <span>
                Uang Kas
            </span>

        </a>


        <a
            href="konfirmasi.php"
            class="class-menu-item">

            <i class="bi bi-send-check-fill"></i>

            <span>
                Konfirmasi Setoran
            </span>

        </a>


        <a
            href="../auth/logout.php"
            class="class-menu-item text-danger">

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>


    </nav>

</aside>


<!-- ============================================================
     MAIN
============================================================ -->

<main class="class-main">


    <!-- TOPBAR -->

    <header class="class-topbar">


        <div class="class-topbar-left">


            <button
                type="button"
                class="class-menu-toggle"
                id="classMenuToggle">

                <i class="bi bi-list"></i>

            </button>


            <div>

                <h5>
                    Tambah Siswa
                </h5>

                <small>

                    <?= htmlspecialchars(
                        $officer['nama_kelas']
                    ); ?>

                </small>

            </div>


        </div>



        <!-- USER -->

        <div class="class-user">


            <div class="class-user-avatar">

                <i class="bi bi-person-fill"></i>

            </div>


            <div>

                <strong>

                    <?= htmlspecialchars(
                        $officer['nama_lengkap']
                    ); ?>

                </strong>


                <small>
                    Bendahara Kelas
                </small>

            </div>


        </div>


    </header>



    <!-- ========================================================
         CONTENT
    ======================================================== -->

    <section class="class-content">


        <div class="mb-4">


            <h3 class="fw-bold mb-1">

                Tambah Siswa

            </h3>


            <p class="text-muted mb-0">

                Tambahkan siswa ke kelas

                <strong>

                    <?= htmlspecialchars(
                        $officer['nama_kelas']
                    ); ?>

                </strong>

            </p>


        </div>



        <!-- ALERT ERROR -->

        <?php if (
            !empty($_SESSION['error'])
        ): ?>


            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert">

                <i
                    class="bi bi-exclamation-triangle-fill me-2">
                </i>

                <?= htmlspecialchars(
                    $_SESSION['error']
                ); ?>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>

            </div>


            <?php

            unset($_SESSION['error']);

            ?>

        <?php endif; ?>



        <!-- ====================================================
             FORM
        ==================================================== -->

        <div class="table-card">


            <div class="mb-4">


                <h5 class="fw-bold">

                    Informasi Siswa

                </h5>


                <p class="text-muted mb-0">

                    Isi data siswa yang ingin ditambahkan.

                </p>


            </div>



            <form
                method="POST"
                action="siswa_tambah.php">


                <!-- =================================================
                     NAMA LENGKAP
                ================================================== -->

                <div class="mb-3">


                    <label
                        for="nama_lengkap"
                        class="form-label fw-semibold">

                        Nama Lengkap

                        <span class="text-danger">
                            *
                        </span>

                    </label>


                    <input
                        type="text"
                        name="nama_lengkap"
                        id="nama_lengkap"
                        class="form-control"
                        placeholder="Masukkan nama lengkap siswa"
                        maxlength="150"
                        required
                        autofocus>


                </div>



                <!-- =================================================
                     JENIS KELAMIN
                ================================================== -->

                <div class="mb-3">


                    <label
                        for="jenis_kelamin"
                        class="form-label fw-semibold">

                        Jenis Kelamin

                        <span class="text-danger">
                            *
                        </span>

                    </label>


                    <select
                        name="jenis_kelamin"
                        id="jenis_kelamin"
                        class="form-select"
                        required>


                        <option
                            value=""
                            selected
                            disabled>

                            -- Pilih Jenis Kelamin --

                        </option>


                        <option value="L">

                            Laki-laki

                        </option>


                        <option value="P">

                            Perempuan

                        </option>


                    </select>


                </div>



                <!-- =================================================
                     NO HP
                ================================================== -->

                <div class="mb-4">


                    <label
                        for="no_hp"
                        class="form-label fw-semibold">

                        No. HP

                        <span class="text-muted">
                            (Opsional)
                        </span>

                    </label>


                    <input
                        type="text"
                        name="no_hp"
                        id="no_hp"
                        class="form-control"
                        placeholder="Contoh: 081234567890"
                        maxlength="20">


                </div>



                <!-- =================================================
                     KELAS OTOMATIS
                ================================================== -->

                <div
                    class="alert alert-primary mb-4">


                    <div class="d-flex">


                        <i
                            class="bi bi-info-circle-fill me-2 mt-1">
                        </i>


                        <div>


                            <strong>
                                Kelas Otomatis
                            </strong>


                            <div>

                                Siswa akan otomatis dimasukkan ke kelas:

                                <strong>

                                    <?= htmlspecialchars(
                                        $officer['nama_kelas']
                                    ); ?>

                                </strong>

                            </div>


                            <small class="text-muted">

                                Kamu tidak perlu memilih kelas atau jurusan.

                            </small>


                        </div>


                    </div>


                </div>



                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div
                    class="d-flex justify-content-between gap-2">


                    <a
                        href="siswa.php"
                        class="btn btn-secondary">


                        <i
                            class="bi bi-arrow-left me-1">
                        </i>


                        Kembali


                    </a>



                    <button
                        type="submit"
                        class="btn btn-primary">


                        <i
                            class="bi bi-person-plus-fill me-1">
                        </i>


                        Tambah Siswa


                    </button>


                </div>


            </form>


        </div>


    </section>


</main>



<!-- ============================================================
     JAVASCRIPT SIDEBAR
============================================================ -->

<script>

const classSidebar =
    document.getElementById('classSidebar');

const classMenuToggle =
    document.getElementById('classMenuToggle');

const classSidebarOverlay =
    document.getElementById('classSidebarOverlay');


classMenuToggle?.addEventListener(
    'click',
    function () {

        classSidebar?.classList.toggle('show');

        classSidebarOverlay?.classList.toggle('show');

    }
);


classSidebarOverlay?.addEventListener(
    'click',
    function () {

        classSidebar?.classList.remove('show');

        classSidebarOverlay?.classList.remove('show');

    }
);

</script>


<?php

require_once "../includes/footer.php";

?>
