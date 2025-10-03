<?php
session_start();
require_once '../config/db.php';

// 1. Validasi Awal: Pastikan pengguna adalah Guru yang login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Guru') {
    header("Location: ../login.php");
    exit();
}

// 2. Validasi Input: Pastikan parameter yang dibutuhkan ada
if (!isset($_GET['type']) || !isset($_GET['id']) || !isset($_GET['kelas_id'])) {
    die("Parameter tidak lengkap.");
}

$item_type = $_GET['type'];
$item_id = (int)$_GET['id'];
$kelas_id = (int)$_GET['kelas_id'];
$guru_id = $_SESSION['user_id'];
$redirect_url = "../guru/kelas.php?id=" . $kelas_id;

// 3. Proses Penghapusan berdasarkan Tipe Item
if ($item_type == 'materi') {
    // Keamanan: Cek apakah guru ini adalah pemilik materi tersebut
    $stmt = $conn->prepare("SELECT m.file FROM materi m JOIN kelas k ON m.kelas_id = k.id WHERE m.id = ? AND k.guru_id = ?");
    $stmt->bind_param("ii", $item_id, $guru_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $materi = $result->fetch_assoc();
        $file_to_delete = '../uploads/materi/' . $materi['file'];

        // Hapus file dari server jika ada
        if (file_exists($file_to_delete) && !empty($materi['file'])) {
            unlink($file_to_delete);
        }

        // Hapus record dari database
        $stmt_delete = $conn->prepare("DELETE FROM materi WHERE id = ?");
        $stmt_delete->bind_param("i", $item_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        header("Location: " . $redirect_url . "&status=delete_sukses");
        exit();
    }
} elseif ($item_type == 'tugas') {
    // Keamanan: Cek apakah guru ini adalah pemilik tugas tersebut
    $stmt = $conn->prepare("SELECT t.id FROM tugas t JOIN kelas k ON t.kelas_id = k.id WHERE t.id = ? AND k.guru_id = ?");
    $stmt->bind_param("ii", $item_id, $guru_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Sebelum hapus tugas, hapus dulu semua file pengumpulan terkait
        $stmt_pengumpulan = $conn->prepare("SELECT file FROM pengumpulan WHERE tugas_id = ?");
        $stmt_pengumpulan->bind_param("i", $item_id);
        $stmt_pengumpulan->execute();
        $result_pengumpulan = $stmt_pengumpulan->get_result();

        while ($row = $result_pengumpulan->fetch_assoc()) {
            if (!empty($row['file'])) {
                $file_to_delete = '../uploads/pengumpulan/' . $row['file'];
                if (file_exists($file_to_delete)) {
                    unlink($file_to_delete);
                }
            }
        }
        $stmt_pengumpulan->close();

        // Hapus semua record pengumpulan terkait
        $stmt_delete_pengumpulan = $conn->prepare("DELETE FROM pengumpulan WHERE tugas_id = ?");
        $stmt_delete_pengumpulan->bind_param("i", $item_id);
        $stmt_delete_pengumpulan->execute();
        $stmt_delete_pengumpulan->close();

        // Terakhir, hapus record tugas itu sendiri
        $stmt_delete_tugas = $conn->prepare("DELETE FROM tugas WHERE id = ?");
        $stmt_delete_tugas->bind_param("i", $item_id);
        $stmt_delete_tugas->execute();
        $stmt_delete_tugas->close();

        header("Location: " . $redirect_url . "&status=delete_sukses");
        exit();
    }
}

// Jika ada masalah (misal: ID tidak cocok dengan guru), kembalikan dengan status gagal
header("Location: " . $redirect_url . "&status=delete_gagal");
exit();
