<?php
session_start();
require_once '../config/db.php';

// Pastikan user adalah siswa yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Siswa') {
    header("Location: ../login.php");
    exit();
}

$siswa_id = $_SESSION['user_id'];

// Update status_guru menjadi 'pending'
$stmt = $conn->prepare("UPDATE users SET status_guru = 'pending' WHERE id = ?");
$stmt->bind_param("i", $siswa_id);

if ($stmt->execute()) {
    // Berhasil, kembalikan ke dashboard siswa
    header("Location: ../siswa/dashboard.php?status=pengajuan_sukses");
} else {
    // Gagal
    header("Location: ../siswa/dashboard.php?status=pengajuan_gagal");
}

$stmt->close();
$conn->close();
