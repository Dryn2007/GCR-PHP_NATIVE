<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Tandai user di database untuk direset
    $stmt = $conn->prepare("UPDATE users SET reset_request = 1 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    // Arahkan kembali dengan pesan sukses
    header("Location: ../lupa_password.php?status=terkirim");
    exit();
}
