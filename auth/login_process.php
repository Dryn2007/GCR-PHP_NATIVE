<?php
// Pastikan session selalu dimulai di paling atas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Ambil data user berdasarkan email
    $stmt = $conn->prepare("SELECT id, nama, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // 2. Cek apakah email ditemukan
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nama, $hashed_password, $role);
        $stmt->fetch();

        // 3. Verifikasi password yang diinput dengan hash di database
        if (password_verify($password, $hashed_password)) {
            // LOGIN BERHASIL
            $_SESSION['user_id'] = $id;
            $_SESSION['nama'] = $nama;
            $_SESSION['role'] = $role;

            // 4. Arahkan ke dashboard yang sesuai
            if ($role == 'Admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($role == 'Guru') {
                header("Location: ../guru/dashboard.php");
            } else { // Siswa
                header("Location: ../siswa/dashboard.php");
            }
            exit(); // Penting!
        }
    }

    // Jika langkah 2 atau 3 gagal (email tidak ada atau password salah)
    header("Location: ../login.php?status=gagal");
    exit();

    $stmt->close();
    $conn->close();
}
