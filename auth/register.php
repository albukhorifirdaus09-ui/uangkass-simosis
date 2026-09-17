<?php
require_once '../config/database.php';
$departments = $pdo->query("SELECT id, nama_jurusan FROM departments WHERE status = 'aktif' ORDER BY nama_jurusan ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SIMOSIS</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-modern-page auth-register-page">
<main class="auth-layout">
    <section class="auth-showcase">
        <div class="auth-showcase-brand"><img src="../img/logo simosi.jpeg" alt="Logo SIMOSIS"><span>SIMOSIS</span></div>
        <div class="auth-showcase-copy"><span class="auth-kicker"><i class="bi bi-person-plus-fill"></i> Pendaftaran akun</span><h1>Bergabung dan mulai kelola peran Anda.</h1><p>Daftarkan akun sebagai Bendahara Kelas atau Anggota OSIS dengan data yang sesuai.</p></div>
        <div class="auth-showcase-points"><div><i class="bi bi-person-badge"></i><span>Untuk Bendahara Kelas</span></div><div><i class="bi bi-people"></i><span>Untuk Anggota OSIS</span></div></div>
    </section>
    <section class="auth-form-panel auth-register-panel">
        <div class="auth-form-wrap auth-register-wrap">
            <a class="auth-mobile-brand" href="login.php"><img src="../img/logo simosi.jpeg" alt="Logo SIMOSIS"><span>SIMOSIS</span></a>
            <div class="auth-form-heading"><span>Buat akun baru</span><h2>Registrasi SIMOSIS</h2><p>Lengkapi data di bawah ini dengan benar.</p></div>
            <?php if (isset($_GET['error'])): ?><div class="alert alert-danger auth-alert"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>
            <form method="post" action="proses_register.php" class="auth-modern-form auth-register-form">
                <div class="auth-field-full"><label for="jenis_register">Daftar sebagai</label><div class="auth-input"><i class="bi bi-person-badge"></i><select id="jenis_register" name="jenis_register" required><option value="">Pilih jenis pendaftaran</option><option value="bendahara_kelas">Bendahara Kelas</option><option value="anggota_osis">Anggota OSIS</option></select></div><small class="auth-help">Bendahara kelas langsung aktif. Anggota OSIS menunggu jabatan dari Admin.</small></div>
                <div class="auth-field-full"><label for="nama_lengkap">Nama lengkap</label><div class="auth-input"><i class="bi bi-person"></i><input id="nama_lengkap" type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" required></div></div>
                <div><label for="nama_kelas">Kelas</label><div class="auth-input"><i class="bi bi-mortarboard"></i><input id="nama_kelas" type="text" name="nama_kelas" placeholder="Contoh: X RPL 1" maxlength="50" required></div></div>
                <div><label for="jurusan_id">Jurusan</label><div class="auth-input"><i class="bi bi-building"></i><select id="jurusan_id" name="jurusan_id" required><option value="">Pilih jurusan</option><?php foreach ($departments as $department): ?><option value="<?= (int) $department['id'] ?>"><?= htmlspecialchars($department['nama_jurusan']) ?></option><?php endforeach; ?></select></div></div>
                <?php if (!$departments): ?><p class="auth-help text-danger auth-field-full mb-0">Belum ada jurusan. Hubungi Admin untuk menambahkannya.</p><?php endif; ?>
                <div><label for="jenis_kelamin">Jenis kelamin</label><div class="auth-input"><i class="bi bi-gender-ambiguous"></i><select id="jenis_kelamin" name="jenis_kelamin" required><option value="">Pilih jenis kelamin</option><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div></div>
                <div><label for="email">Email</label><div class="auth-input"><i class="bi bi-envelope"></i><input id="email" type="email" name="email" placeholder="nama@email.com" required></div></div>
                <div><label for="password">Password</label><div class="auth-input"><i class="bi bi-lock"></i><input id="password" type="password" name="password" placeholder="Minimal 6 karakter" minlength="6" required><button class="auth-password-toggle" type="button" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div></div>
                <div><label for="password_confirm">Konfirmasi password</label><div class="auth-input"><i class="bi bi-shield-lock"></i><input id="password_confirm" type="password" name="password_confirm" placeholder="Ulangi password" minlength="6" required><button class="auth-password-toggle" type="button" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div></div>
                <button type="submit" class="btn auth-submit-btn auth-field-full">Buat akun <i class="bi bi-arrow-right"></i></button>
            </form>
            <p class="auth-switch">Sudah memiliki akun? <a href="login.php">Masuk sekarang</a></p>
        </div>
    </section>
</main>
<script>document.querySelectorAll('.auth-password-toggle').forEach(button => button.addEventListener('click', () => { const input = button.parentElement.querySelector('input'); const icon = button.querySelector('i'); input.type = input.type === 'password' ? 'text' : 'password'; icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash'; }));</script>
</body></html>
