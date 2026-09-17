<?php

require_once "config/database.php";

$username = "admin";
$email = "admin@simosis.test";
$password = password_hash("admin", PASSWORD_DEFAULT);
$role = "admin";

$stmt = $pdo->prepare("
    INSERT INTO users
    (username, email, password, role)
    VALUES (?, ?, ?, ?)
");

$stmt->execute([
    $username,
    $email,
    $password,
    $role
]);

echo "Admin berhasil dibuat.";
