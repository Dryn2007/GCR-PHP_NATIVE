<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nama, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nama, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Login sukses, simpan data ke session
            $_SESSION['user_id'] = $id;
            $_SESSION['nama'] = $nama;
            $_SESSION['role'] = $role;

            // Arahkan ke dashboard sesuai role
            if ($role == 'Guru') {
                header("Location: ../guru/dashboard.php");
            } else {
                header("Location: ../siswa/dashboard.php");
            }
        } else {
            // Password salah
            header("Location: ../login.php?status=gagal");
        }
    } else {
        // Email tidak ditemukan
        header("Location: ../login.php?status=gagal");
    }
    $stmt->close();
    $conn->close();
}
