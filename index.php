<?php
require_once 'config/db.php';

// Cek jika user sudah login, arahkan ke dashboard yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'Guru') {
        header("Location: guru/dashboard.php");
    } else {
        header("Location: siswa/dashboard.php");
    }
    exit();
}

// Jika belum login, arahkan ke halaman login
header("Location: login.php");
exit();
