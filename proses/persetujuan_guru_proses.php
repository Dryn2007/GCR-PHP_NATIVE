<?php
session_start();
require_once '../config/db.php';

// Pastikan user adalah admin yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $user_id_to_action = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        // Ubah peran menjadi 'Guru' dan status menjadi 'approved'
        $stmt = $conn->prepare("UPDATE users SET role = 'Guru', status_guru = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $user_id_to_action);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'delete') {
        // Hapus pengguna dari database
        // Pastikan admin tidak bisa menghapus dirinya sendiri
        if ($user_id_to_action != $_SESSION['user_id']) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id_to_action);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Kembalikan ke dashboard admin setelah aksi selesai
header("Location: ../admin/dashboard.php");
exit();
