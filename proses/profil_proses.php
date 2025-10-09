<?php
session_start();
require_once '../config/db.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newName = htmlspecialchars(trim($_POST['nama']));
    $userId = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Update nama di database
    $stmt = $conn->prepare("UPDATE users SET nama = ? WHERE id = ?");
    $stmt->bind_param("si", $newName, $userId);

    if ($stmt->execute()) {
        // Jika berhasil, update juga session agar nama di header langsung berubah
        $_SESSION['nama'] = $newName;

        // Arahkan kembali ke halaman profil yang sesuai dengan peran
        if ($role == 'Siswa') {
            header("Location: ../siswa/profil.php?status=sukses");
        } elseif ($role == 'Guru') {
            header("Location: ../guru/profil.php?status=sukses");
        } else {
            header("Location: ../index.php");
        }
    }
    $stmt->close();
    exit();
}
