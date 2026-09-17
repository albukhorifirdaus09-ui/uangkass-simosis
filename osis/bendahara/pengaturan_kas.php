<?php

require_once '../../config/auth.php';
require_once '../../config/database.php';

requireOsisTreasurer();

$pageTitle = 'Pengaturan Kas';

$success = '';
$error = '';

$classes = $pdo->query("
    SELECT id, nama_kelas
    FROM classes
    ORDER BY nama_kelas ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* Riwayat aturan nonaktif tidak disimpan di halaman pengaturan. */
$pdo->exec("DELETE FROM cash_settings WHERE status = 'nonaktif'");


/*
|--------------------------------------------------------------------------
| SIMPAN PENGATURAN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_aturan') {

    $jenisKas = $_POST['jenis_kas'] ?? '';
    $frekuensi = $_POST['frekuensi'] ?? '';
    $nominal = (float) ($_POST['nominal'] ?? 0);
    $tahun = (int) ($_POST['tahun'] ?? 0);

    if (!in_array($jenisKas, ['osis', 'kelas'], true) || !in_array($frekuensi, ['bulanan', 'mingguan'], true) || $nominal <= 0 || $tahun < 2020) {
        $error = 'Aturan kas yang akan dihapus tidak valid.';
    } else {
        try {
            $pdo->beginTransaction();

            /* Nonaktifkan tagihan agar tidak lagi tampil pada kas berjalan. */
            $deactivateDues = $pdo->prepare("UPDATE student_dues d INNER JOIN cash_settings cs ON cs.id = d.setting_id SET d.status = 'nonaktif' WHERE cs.jenis_kas = ? AND cs.frekuensi = ? AND cs.nominal = ? AND cs.tahun = ? AND cs.status = 'aktif'");
            $deactivateDues->execute([$jenisKas, $frekuensi, $nominal, $tahun]);

            /* Record aturan dihapus; tagihan/pembayaran tetap menjadi riwayat kas. */
            $deactivateSettings = $pdo->prepare("DELETE FROM cash_settings WHERE jenis_kas = ? AND frekuensi = ? AND nominal = ? AND tahun = ? AND status = 'aktif'");
            $deactivateSettings->execute([$jenisKas, $frekuensi, $nominal, $tahun]);

            if ($deactivateSettings->rowCount() === 0) {
                throw new RuntimeException('Aturan kas sudah tidak aktif atau tidak ditemukan.');
            }

            $pdo->commit();
            $success = 'Aturan kas berhasil dihapus. Riwayat pembayaran tetap tersimpan.';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e instanceof RuntimeException ? $e->getMessage() : 'Gagal menghapus aturan kas. Silakan coba lagi.';
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jenisKas  = $_POST['jenis_kas'] ?? '';
    $frekuensi = $_POST['frekuensi'] ?? '';

    $nominal = (float) ($_POST['nominal'] ?? 0);

    $tahun = (int) ($_POST['tahun'] ?? date('Y'));
    $startMonth = $tahun === (int) date('Y')
        ? (int) date('n')
        : 1;


    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if (!in_array($jenisKas, ['osis', 'kelas'], true)) {

        $error = 'Jenis kas tidak valid.';

    } elseif (!in_array($frekuensi, ['bulanan', 'mingguan'], true)) {

        $error = 'Frekuensi tidak valid.';

    } elseif ($nominal <= 0) {

        $error = 'Nominal harus lebih dari 0.';

    } else {

        try {

            $pdo->beginTransaction();

            /*
             * Satu jenis kas hanya memakai satu aturan aktif pada satu tahun.
             * Aturan sebelumnya serta tagihannya dinonaktifkan, bukan dihapus,
             * agar riwayat pembayaran tetap aman.
             */
            $deactivatePreviousDues = $pdo->prepare("UPDATE student_dues d INNER JOIN cash_settings cs ON cs.id = d.setting_id SET d.status = 'nonaktif' WHERE cs.jenis_kas = ? AND cs.tahun = ? AND cs.status = 'aktif'");
            $deactivatePreviousDues->execute([$jenisKas, $tahun]);

            $deactivatePreviousSettings = $pdo->prepare("UPDATE cash_settings SET status = 'nonaktif' WHERE jenis_kas = ? AND tahun = ? AND status = 'aktif'");
            $deactivatePreviousSettings->execute([$jenisKas, $tahun]);

            $targetClassIds = $jenisKas === 'kelas'
                ? array_column($classes, 'id')
                : [null];

            if (!$targetClassIds) {
                throw new RuntimeException('Data kelas belum tersedia.');
            }

            foreach ($targetClassIds as $classId) {


            /*
            |--------------------------------------------------------------------------
            | SIMPAN SETTING
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO cash_settings
                (
                    jenis_kas,
                    class_id,
                    frekuensi,
                    nominal,
                    tahun,
                    status,
                    dibuat_oleh
                )
                VALUES (?, ?, ?, ?, ?, 'aktif', ?)
            ");

            $stmt->execute([
                $jenisKas,
                $classId,
                $frekuensi,
                $nominal,
                $tahun,
                $_SESSION['user_id']
            ]);

            $settingId = (int) $pdo->lastInsertId();


            /*
            |--------------------------------------------------------------------------
            | JIKA BULANAN
            |--------------------------------------------------------------------------
            */

            if ($frekuensi === 'bulanan') {

                for ($bulan = $startMonth; $bulan <= 12; $bulan++) {

                    $tanggalMulai = sprintf(
                        '%04d-%02d-01',
                        $tahun,
                        $bulan
                    );

                    $tanggalSelesai = date(
                        'Y-m-t',
                        strtotime($tanggalMulai)
                    );


                    $stmt = $pdo->prepare("
                        INSERT INTO student_dues
                        (
                            setting_id,
                            jenis_kas,
                            class_id,
                            frekuensi,
                            bulan,
                            tahun,
                            minggu_ke,
                            nominal,
                            tanggal_mulai,
                            tanggal_selesai,
                            dibuat_oleh,
                            status
                        )
                        VALUES
                        (
                            ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 'aktif'
                        )
                    ");

                    $stmt->execute([
                        $settingId,
                        $jenisKas,
                        $classId,
                        $frekuensi,
                        $bulan,
                        $tahun,
                        $nominal,
                        $tanggalMulai,
                        $tanggalSelesai,
                        $_SESSION['user_id']
                    ]);
                }


            /*
            |--------------------------------------------------------------------------
            | JIKA MINGGUAN
            |--------------------------------------------------------------------------
            */

            } else {

                for ($bulan = $startMonth; $bulan <= 12; $bulan++) {

                    /*
                     * Setiap periode kas sekolah berjalan Senin sampai Jumat.
                     * Jika awal bulan jatuh Selasa–Jumat, M1 dimulai pada
                     * tanggal tersebut sampai Jumat; M2 dimulai Senin berikutnya.
                     */
                    $awalBulan = new DateTimeImmutable(sprintf('%04d-%02d-01', $tahun, $bulan));
                    $akhirBulan = $awalBulan->modify('last day of this month');
                    $hariAwal = (int) $awalBulan->format('N');
                    $senin = $hariAwal <= 5
                        ? $awalBulan
                        : $awalBulan->modify('next monday');

                    $minggu = 1;

                    while ($senin <= $akhirBulan) {

                        $jumat = $senin->modify(
                            '+' . (5 - (int) $senin->format('N')) . ' days'
                        );

                        /*
                         * Jangan buat periode sisa di akhir bulan.
                         * Contoh: 28–30 September tidak menjadi M5;
                         * periode berikutnya dimulai sebagai M1 Oktober.
                         */
                        if ($jumat > $akhirBulan) {
                            break;
                        }

                        $tanggalMulai = $senin->format('Y-m-d');
                        $tanggalSelesai = $jumat->format('Y-m-d');


                        $stmt = $pdo->prepare("
                            INSERT INTO student_dues
                            (
                                setting_id,
                                jenis_kas,
                                class_id,
                                frekuensi,
                                bulan,
                                tahun,
                                minggu_ke,
                                nominal,
                                tanggal_mulai,
                                tanggal_selesai,
                                dibuat_oleh,
                                status
                            )
                            VALUES
                            (
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif'
                            )
                        ");

                        $stmt->execute([
                            $settingId,
                            $jenisKas,
                            $classId,
                            $frekuensi,
                            $bulan,
                            $tahun,
                            $minggu,
                            $nominal,
                            $tanggalMulai,
                            $tanggalSelesai,
                            $_SESSION['user_id']
                        ]);

                        /* Periode berikutnya selalu dimulai pada Senin setelah Jumat. */
                        $senin = $jumat->modify('+3 days');
                        $minggu++;
                    }
                }
            }


            }

            $pdo->commit();

            $success = $jenisKas === 'kelas'
                ? 'Pengaturan kas berhasil dibuat untuk semua kelas.'
                : 'Pengaturan kas berhasil dibuat.';

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if (
                $e instanceof PDOException
                && (($e->errorInfo[1] ?? null) === 1062)
            ) {
                $error = 'Pengaturan kas untuk periode tersebut sudah ada. Silakan gunakan tahun, frekuensi, atau pengaturan yang berbeda.';
            } else {
                $error = 'Gagal membuat pengaturan kas. Silakan coba lagi.';
            }
        }
    }
}


$activeSettingsStmt = $pdo->prepare("SELECT jenis_kas, frekuensi, nominal, tahun, COUNT(*) AS total_pengaturan, COUNT(DISTINCT class_id) AS total_kelas FROM cash_settings WHERE status = 'aktif' AND tahun >= ? GROUP BY jenis_kas, frekuensi, nominal, tahun ORDER BY tahun DESC, jenis_kas ASC");
$activeSettingsStmt->execute([(int) date('Y')]);
$activeSettings = $activeSettingsStmt->fetchAll(PDO::FETCH_ASSOC);

$inactiveSettingsStmt = $pdo->prepare("SELECT jenis_kas, frekuensi, nominal, tahun, COUNT(*) AS total_pengaturan, COUNT(DISTINCT class_id) AS total_kelas, MAX(updated_at) AS terbaru FROM cash_settings WHERE status = 'nonaktif' GROUP BY jenis_kas, frekuensi, nominal, tahun ORDER BY tahun DESC, jenis_kas ASC, terbaru DESC");
$inactiveSettingsStmt->execute();
$inactiveSettings = $inactiveSettingsStmt->fetchAll(PDO::FETCH_ASSOC);


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
            <strong>Pengaturan Kas</strong>

            <small class="d-block text-muted">
                Pengaturan kas oleh Bendahara OSIS
            </small>
        </div>

        <span class="text-muted small">
            <?= htmlspecialchars($_SESSION['username']) ?>
        </span>

    </header>


    <main class="role-page osis-settings-page">

        <div class="role-wrap">

            <div class="role-hero osis-setting-hero">

                <span class="text-uppercase small">
                    Bendahara OSIS
                </span>

                <h2>
                    Pengaturan Kas
                </h2>

                <p class="mb-0">
                    Tentukan jenis pembayaran dan nominal kas.
                </p>

            </div>


            <?php if ($success): ?>

                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <section class="osis-setting-active-card">
                <div class="osis-setting-active-head"><div><span><i class="bi bi-shield-check"></i> Pengaturan aktif</span><h4>Aturan kas yang sedang berjalan</h4></div><small><?= count($activeSettings) ?> aturan tersedia</small></div>
                <?php if ($activeSettings): ?><div class="osis-setting-active-grid"><?php foreach ($activeSettings as $activeSetting): ?><article><span class="osis-setting-type <?= $activeSetting['jenis_kas'] === 'osis' ? 'osis' : 'kelas' ?>"><i class="bi <?= $activeSetting['jenis_kas'] === 'osis' ? 'bi-buildings-fill' : 'bi-mortarboard-fill' ?>"></i> Kas <?= htmlspecialchars(strtoupper($activeSetting['jenis_kas'])) ?></span><strong>Rp <?= number_format((float) $activeSetting['nominal'], 0, ',', '.') ?></strong><p><?= $activeSetting['frekuensi'] === 'mingguan' ? 'Per minggu' : 'Per bulan' ?> · Tahun <?= (int) $activeSetting['tahun'] ?><?= $activeSetting['jenis_kas'] === 'kelas' ? ' · ' . (int) $activeSetting['total_kelas'] . ' kelas' : '' ?></p></article><?php endforeach; ?></div><?php else: ?><div class="osis-setting-empty"><i class="bi bi-info-circle"></i><span>Belum ada pengaturan kas aktif untuk tahun berjalan.</span></div><?php endif; ?>
            </section>

            <?php if ($inactiveSettings): ?>
                <section class="osis-setting-inactive-card">
                    <div class="osis-setting-inactive-head">
                        <div><span><i class="bi bi-archive"></i> Riwayat aturan</span><h4>Aturan kas tidak aktif</h4><p>Aturan lama tersimpan sebagai riwayat dan tidak digunakan untuk tagihan baru.</p></div>
                        <small><?= count($inactiveSettings) ?> aturan tidak aktif</small>
                    </div>
                    <div class="osis-setting-inactive-grid">
                        <?php foreach ($inactiveSettings as $inactiveSetting): ?>
                            <article>
                                <span class="osis-setting-inactive-type"><i class="bi <?= $inactiveSetting['jenis_kas'] === 'osis' ? 'bi-buildings-fill' : 'bi-mortarboard-fill' ?>"></i> Kas <?= htmlspecialchars(strtoupper($inactiveSetting['jenis_kas'])) ?></span>
                                <strong>Rp <?= number_format((float) $inactiveSetting['nominal'], 0, ',', '.') ?></strong>
                                <p><?= $inactiveSetting['frekuensi'] === 'mingguan' ? 'Per minggu' : 'Per bulan' ?> · Tahun <?= (int) $inactiveSetting['tahun'] ?><?= $inactiveSetting['jenis_kas'] === 'kelas' ? ' · ' . (int) $inactiveSetting['total_kelas'] . ' kelas' : '' ?></p>
                                <em><i class="bi bi-slash-circle"></i> Tidak aktif</em>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($activeSettings): ?>
                <section class="osis-setting-remove-card">
                    <div><span><i class="bi bi-trash3"></i> Kelola aturan</span><h4>Hapus aturan yang tidak digunakan</h4><p>Aturan akan dihilangkan dari daftar aktif. Data dan riwayat pembayaran tetap tersimpan.</p></div>
                    <div class="osis-setting-remove-list">
                        <?php foreach ($activeSettings as $activeSetting): ?>
                            <form method="post" class="osis-setting-delete-form" onsubmit="return confirm('Hapus aturan kas ini dari pengaturan aktif? Riwayat pembayaran tidak akan dihapus.');">
                                <input type="hidden" name="action" value="hapus_aturan">
                                <input type="hidden" name="jenis_kas" value="<?= htmlspecialchars($activeSetting['jenis_kas'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="frekuensi" value="<?= htmlspecialchars($activeSetting['frekuensi'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="nominal" value="<?= htmlspecialchars((string) $activeSetting['nominal'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="tahun" value="<?= (int) $activeSetting['tahun'] ?>">
                                <span><strong>Kas <?= htmlspecialchars(strtoupper($activeSetting['jenis_kas'])) ?></strong><small><?= $activeSetting['frekuensi'] === 'mingguan' ? 'Per minggu' : 'Per bulan' ?> · Rp <?= number_format((float) $activeSetting['nominal'], 0, ',', '.') ?> · <?= (int) $activeSetting['tahun'] ?></small></span>
                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i> Hapus</button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const cards = document.querySelectorAll('.osis-setting-active-grid article');
                const removeCard = document.querySelector('.osis-setting-remove-card');
                const deleteForms = document.querySelectorAll('.osis-setting-remove-list .osis-setting-delete-form');

                deleteForms.forEach(function (form, index) {
                    if (cards[index]) {
                        cards[index].appendChild(form);
                    }
                });

                if (removeCard) {
                    removeCard.remove();
                }
            });
            </script>

            <div class="role-card osis-setting-form-card">

                <div class="osis-setting-form-head"><div><span><i class="bi bi-sliders"></i> Buat pengaturan</span><h4>Atur kas baru</h4><p>Pengaturan kas kelas akan otomatis diterapkan pada seluruh kelas.</p></div><i class="bi bi-cash-stack"></i></div>

                <form method="POST">


                    <!-- JENIS KAS -->

                    <div class="mb-3">

                        <label class="form-label">
                            Jenis Kas
                        </label>

                        <select
                            name="jenis_kas"
                            id="jenis_kas"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Pilih Jenis Kas --
                            </option>

                            <option value="osis">
                                Kas OSIS
                            </option>

                            <option value="kelas">
                                Kas Kelas
                            </option>

                        </select>

                        <small class="text-muted d-block mt-2">
                            Jika memilih Kas Kelas, pengaturan akan diterapkan otomatis ke semua kelas.
                        </small>

                    </div>

                    <!-- FREKUENSI -->

                    <div class="mb-3">

                        <label class="form-label">
                            Frekuensi Kas
                        </label>

                        <select
                            name="frekuensi"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Pilih Frekuensi --
                            </option>

                            <option value="bulanan">
                                Per Bulan
                            </option>

                            <option value="mingguan">
                                Per Minggu
                            </option>

                        </select>

                    </div>


                    <!-- NOMINAL -->

                    <div class="mb-3">

                        <label class="form-label">
                            Nominal Kas
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                Rp
                            </span>

                            <input
                                type="number"
                                name="nominal"
                                class="form-control"
                                min="1"
                                step="1"
                                placeholder="Contoh: 5000"
                                required
                            >

                        </div>

                    </div>


                    <!-- TAHUN -->

                    <div class="mb-4">

                        <label class="form-label">
                            Tahun
                        </label>

                        <input
                            type="number"
                            name="tahun"
                            class="form-control"
                            value="<?= date('Y') ?>"
                            min="2020"
                            max="2100"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-save"></i>

                        Simpan Pengaturan

                    </button>

                </form>

            </div>

        </div>

    </main>

</div>


<?php require_once '../../includes/footer.php'; ?>
