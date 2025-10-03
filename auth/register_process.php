<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($nama) || empty($email) || empty($password)) {
        header("Location: ../register.php?status=gagal_kosong");
        exit();
    }

    // Enkripsi password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Peran default untuk semua pendaftar baru adalah 'Siswa'
    $default_role = 'Siswa';

    // Cek apakah email sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: ../register.php?status=gagal_email");
        exit();
    }
    $stmt->close();

    // Insert data pengguna baru dengan peran 'Siswa'
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $email, $hashed_password, $default_role);

    if ($stmt->execute()) {
        header("Location: ../login.php?status=sukses_register");
    } else {
        echo "Error saat registrasi: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
