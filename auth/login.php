<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMOSIS</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-modern-page">
<main class="auth-layout">
    <section class="auth-showcase">
        <div class="auth-showcase-brand"><img src="../img/logo simosi.jpeg" alt="Logo SIMOSIS"><span>SIMOSIS</span></div>
        <div class="auth-showcase-copy"><span class="auth-kicker"><i class="bi bi-stars"></i> Sistem Informasi OSIS</span><h1>Kelola organisasi dan kas sekolah dengan lebih teratur.</h1><p>Satu tempat untuk administrasi siswa, keuangan OSIS, dan pengelolaan kas kelas.</p></div>
        <div class="auth-showcase-points"><div><i class="bi bi-shield-check"></i><span>Akses akun yang aman</span></div><div><i class="bi bi-wallet2"></i><span>Rekap kas lebih mudah</span></div></div>
    </section>
    <section class="auth-form-panel">
        <div class="auth-form-wrap">
            <a class="auth-mobile-brand" href="login.php"><img src="../img/logo simosi.jpeg" alt="Logo SIMOSIS"><span>SIMOSIS</span></a>
            <div class="auth-form-heading"><span>Selamat datang kembali</span><h2>Masuk ke akun Anda</h2><p>Masukkan email dan password untuk melanjutkan.</p></div>
            <?php if (isset($_GET['success'])): ?><div class="alert alert-success auth-alert"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($_GET['success']) ?></div><?php endif; ?>
            <?php if (isset($_GET['error'])): ?><div class="alert alert-danger auth-alert"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>
            <form action="proses_login.php" method="post" class="auth-modern-form">
                <div><label for="login">Email</label><div class="auth-input"><i class="bi bi-envelope"></i><input id="login" type="email" name="login" autocomplete="username" placeholder="nama@email.com" required></div></div>
                <div><label for="password">Password</label><div class="auth-input"><i class="bi bi-lock"></i><input id="password" type="password" name="password" autocomplete="current-password" placeholder="Masukkan password" required><button class="auth-password-toggle" type="button" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div></div>
                <button type="submit" class="btn auth-submit-btn">Masuk ke SIMOSIS <i class="bi bi-arrow-right"></i></button>
            </form>
            <p class="auth-switch">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
        </div>
    </section>
</main>
<script>document.querySelectorAll('.auth-password-toggle').forEach(button => button.addEventListener('click', () => { const input = button.parentElement.querySelector('input'); const icon = button.querySelector('i'); input.type = input.type === 'password' ? 'text' : 'password'; icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash'; }));</script>
</body></html>
