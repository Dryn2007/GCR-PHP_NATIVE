<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}

$action = $_GET['action'] ?? '';

if ($action === 'reset_password' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $new_password = 'ganti' . rand(100, 999); // Buat password sementara, contoh: ganti123
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_request = 0 WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    $stmt->execute();

    // Siapkan pesan untuk ditampilkan ke admin
    $_SESSION['flash_message'] = "Password untuk user ID #{$user_id} telah direset menjadi: <strong>{$new_password}</strong>. Harap berikan password ini kepada pengguna.";

    header("Location: ../admin/kelola_reset.php");
    exit();
}
