<?php
// Pastikan session selalu dimulai di awal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// Cek jika user sudah login, arahkan ke dashboard yang sesuai
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {

    // Gunakan if-elseif-else untuk menangani semua kemungkinan peran
    if ($_SESSION['role'] == 'Admin') {
        header("Location: admin/dashboard.php");
        exit(); // Selalu exit() setelah header location
    } elseif ($_SESSION['role'] == 'Guru') {
        header("Location: guru/dashboard.php");
        exit();
    } else { // Anggap selain itu adalah Siswa
        header("Location: siswa/dashboard.php");
        exit();
    }
}

// Jika belum login sama sekali, arahkan ke halaman login
header("Location: login.php");
exit();
