<?php
// WAJIB ADA DI BARIS PALING ATAS!
session_start();

require_once '../config/db.php';

// Auth Guard: Pastikan user adalah siswa yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Siswa') {
    header("Location: ../login.php");
    exit();
}

$action = $_POST['action'] ?? ''; // Ambil dari POST karena ini adalah form submission
$siswa_id = $_SESSION['user_id'];

// Logika untuk Mengumpulkan Tugas
if ($action === 'kumpul_tugas') {
    $tugas_id = $_POST['tugas_id'];
    $kelas_id_redirect = $_POST['kelas_id']; // untuk redirect
    $jawaban_teks = trim($_POST['jawaban_teks']) ?? null;
    $file = $_FILES['file_jawaban'];
    $page_message = '';

    // Validasi: Pastikan ada salah satu yang diisi (file atau teks)
    if ($file['error'] == UPLOAD_ERR_NO_FILE && empty($jawaban_teks)) {
        // Gagal: tidak ada yang diisi
        header("Location: ../siswa/kelas.php?id=" . $kelas_id_redirect . "&status=gagal_kosong");
        exit();
    }

    $fileName = null;
    // Jika ada file yang diupload, proses file
    if ($file['error'] == UPLOAD_ERR_OK) {
        $fileName = time() . '_' . $siswa_id . '_' . basename($file['name']);
        $filePath = '../uploads/pengumpulan/' . $fileName;

        // Pindahkan file yang diupload
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            // Gagal upload
            header("Location: ../siswa/kelas.php?id=" . $kelas_id_redirect . "&status=gagal_upload");
            exit();
        }
    }

    // Simpan ke database
    $stmt_kumpul = $conn->prepare("INSERT INTO pengumpulan (tugas_id, siswa_id, file, jawaban_teks) VALUES (?, ?, ?, ?)");
    $stmt_kumpul->bind_param("iiss", $tugas_id, $siswa_id, $fileName, $jawaban_teks);

    if ($stmt_kumpul->execute()) {
        // Berhasil
        header("Location: ../siswa/kelas.php?id=" . $kelas_id_redirect . "&status=sukses_kumpul");
    } else {
        // Gagal menyimpan
        header("Location: ../siswa/kelas.php?id=" . $kelas_id_redirect . "&status=gagal_simpan");
    }
    $stmt_kumpul->close();
    exit();
}

// Logika untuk keluar kelas (jika Anda memindahkannya ke sini)
if ($_GET['action'] === 'leave_class' && isset($_GET['id'])) {
    // ... (logika keluar kelas Anda) ...
}

// Jika tidak ada aksi yang cocok
header("Location: ../siswa/dashboard.php");
exit();
