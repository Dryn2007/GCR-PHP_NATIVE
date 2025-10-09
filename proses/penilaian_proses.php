<?php
session_start();
require_once '../config/db.php';

// Auth Guard: Pastikan guru yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guru') {
    die("Akses ditolak.");
}

// Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pengumpulan_id'])) {
    header("Location: ../guru/dashboard.php");
    exit();
}

// Ambil dan bersihkan data dari form
$pengumpulan_id = filter_input(INPUT_POST, 'pengumpulan_id', FILTER_VALIDATE_INT);
$tugas_id_redirect = filter_input(INPUT_POST, 'tugas_id', FILTER_VALIDATE_INT);
$komentar = htmlspecialchars(trim($_POST['komentar']));

// Logika baru untuk nilai: jika kosong, jadikan NULL. Jika tidak, validasi sebagai angka.
$nilai_input = trim($_POST['nilai']);
$nilai = null; // Default value is NULL

if ($nilai_input !== '') {
    $nilai_validated = filter_var($nilai_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 100]]);
    if ($nilai_validated !== false) {
        $nilai = $nilai_validated;
    } else {
        // Jika nilai diisi tapi tidak valid (misal: "abc"), kirim pesan error
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Gagal menyimpan! Nilai harus berupa angka antara 0-100.'
        ];
        header("Location: ../guru/penilaian.php?tugas_id=" . $tugas_id_redirect);
        exit();
    }
}

// Lakukan update ke database
if ($pengumpulan_id) {
    $stmt = $conn->prepare("UPDATE pengumpulan SET nilai = ?, komentar = ? WHERE id = ?");
    // Gunakan "s" untuk komentar dan "i" untuk id. Untuk nilai, kita bind manual karena bisa NULL.
    $stmt->bind_param("ssi", $nilai_str, $komentar, $pengumpulan_id);

    // Konversi nilai ke string untuk bind_param
    $nilai_str = is_null($nilai) ? null : strval($nilai);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Penilaian berhasil disimpan.'
        ];
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Terjadi kesalahan saat menyimpan ke database.'
        ];
    }
    $stmt->close();
}

// Redirect kembali ke halaman penilaian
header("Location: ../guru/penilaian.php?tugas_id=" . $tugas_id_redirect);
exit();
