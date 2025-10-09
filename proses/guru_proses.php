<?php
session_start();
require_once '../config/db.php';

// Auth Guard: Pastikan hanya Guru yang bisa mengakses file proses ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guru') {
    die("Akses ditolak. Anda bukan Guru.");
}

// Mengambil aksi dari POST atau GET
$action = $_REQUEST['action'] ?? '';
$guru_id = $_SESSION['user_id'];

//=========================================================
// AKSI MANAJEMEN TUGAS OLEH GURU
//=========================================================

if ($action === 'join_kelas') {
    $kode_kelas = $_POST['kode_kelas'];
    $guru_id = $_SESSION['user_id'];

    // 1. Cari kelas berdasarkan kode
    $stmt = $conn->prepare("SELECT id FROM kelas WHERE kode_kelas = ?");
    $stmt->bind_param("s", $kode_kelas);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $kelas = $result->fetch_assoc();
        $kelas_id = $kelas['id'];

        // 2. CEK APAKAH GURU INI SUDAH MENGAJAR SESUATU DI KELAS INI
        $cek_pengajar_q = $conn->prepare("SELECT p.id FROM pengajar p JOIN mapel m ON p.mapel_id = m.id WHERE p.guru_id = ? AND m.kelas_id = ?");
        $cek_pengajar_q->bind_param("ii", $guru_id, $kelas_id);
        $cek_pengajar_q->execute();

        if ($cek_pengajar_q->get_result()->num_rows > 0) {
            // 3a. JIKA SUDAH, langsung ke halaman kelas
            header("Location: ../guru/kelas.php?id=" . $kelas_id);
        } else {
            // 3b. JIKA BELUM (GURU BARU), alihkan ke halaman pilih mapel
            header("Location: ../guru/pilih_mapel.php?id=" . $kelas_id);
        }
        exit();
    } else {
        // 4. Jika kelas tidak ditemukan
        header("Location: ../guru/dashboard.php?status=kode_salah");
        exit();
    }
}

if ($action === 'pilih_mapel' && isset($_GET['mapel_id'])) {
    $mapel_id = $_GET['mapel_id'];
    $kelas_id = $_GET['kelas_id']; // Untuk redirect

    // Sebenarnya perlu validasi lagi apakah mapel itu kosong, tapi untuk sekarang kita buat simpel

    // Masukkan data ke tabel penghubung 'pengajar'
    $stmt = $conn->prepare("INSERT INTO pengajar (guru_id, mapel_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $guru_id, $mapel_id);

    if ($stmt->execute()) {
        // Berhasil
        header("Location: ../guru/kelas.php?id=" . $kelas_id . "&status=sukses_pilih");
    } else {
        // Gagal, mungkin karena mapel sudah diambil guru lain
        header("Location: ../guru/kelas.php?id=" . $kelas_id . "&status=gagal_pilih");
    }
    exit();
}

// [BARU] LOGIKA UNTUK TAMBAH MATERI
if ($action === 'tambah_materi') {
    $kelas_id = $_POST['kelas_id'];
    $mapel_id = $_POST['mapel_id'];
    $judul = htmlspecialchars(trim($_POST['judul']));
    $file = $_FILES['file_materi'];

    if (!empty($judul) && $file['error'] == UPLOAD_ERR_OK) {
        $fileName = time() . '_' . basename($file['name']);
        $filePath = '../uploads/materi/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $stmt = $conn->prepare("INSERT INTO materi (kelas_id, mapel_id, judul, file) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $kelas_id, $mapel_id, $judul, $fileName);
            $stmt->execute();
        }
    }
    header("Location: ../guru/kelas.php?id=" . $kelas_id);
    exit();
}

// [BARU] LOGIKA UNTUK TAMBAH TUGAS
if ($action === 'tambah_tugas') {
    $kelas_id = $_POST['kelas_id'];
    $mapel_id = $_POST['mapel_id'];
    $judul_tugas = htmlspecialchars(trim($_POST['judul_tugas']));
    $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));
    $deadline = $_POST['deadline'];

    if (!empty($judul_tugas) && !empty($deadline)) {
        $stmt = $conn->prepare("INSERT INTO tugas (kelas_id, mapel_id, judul, deskripsi, deadline) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $kelas_id, $mapel_id, $judul_tugas, $deskripsi, $deadline);
        $stmt->execute();
        // Logika notifikasi ke siswa bisa ditambahkan di sini jika diperlukan
    }
    header("Location: ../guru/kelas.php?id=" . $kelas_id);
    exit();
}

if ($action === 'add_tugas') {
    // Ambil data dari form yang dikirim guru
    $kelas_id = $_POST['kelas_id'];
    $judul_tugas = $_POST['judul'];
    $deadline = $_POST['deadline'];

    // 1. Simpan tugas baru ke database
    $stmt_tugas = $conn->prepare("INSERT INTO tugas (kelas_id, judul, deadline) VALUES (?, ?, ?)");
    $stmt_tugas->bind_param("iss", $kelas_id, $judul_tugas, $deadline);
    $stmt_tugas->execute();

    // Dapatkan ID dari tugas yang baru saja dibuat
    $tugas_id_baru = $stmt_tugas->insert_id;


    // --- [LOGIKA PEMBUATAN NOTIFIKASI YANG DIPERBAIKI] ---

    // 2. Ambil semua siswa_id yang terdaftar di kelas ini
    $stmt_siswa = $conn->prepare("SELECT siswa_id FROM kelas_siswa WHERE kelas_id = ?");
    $stmt_siswa->bind_param("i", $kelas_id);
    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();

    if ($result_siswa->num_rows > 0) {
        // 3. Siapkan pesan dan link notifikasi
        $pesan_notif = "Tugas baru di kelas Anda: " . htmlspecialchars($judul_tugas);
        $link_notif = "/gcr/siswa/kelas.php?id=" . $kelas_id;

        // 4. [DIUBAH] Siapkan statement untuk memasukkan notifikasi (dengan kelas_id)
        $stmt_notif = $conn->prepare("INSERT INTO notifikasi (siswa_id, kelas_id, pesan, link) VALUES (?, ?, ?, ?)");

        // 5. [DIUBAH] Loop dan buat notifikasi untuk SETIAP siswa di kelas tersebut
        while ($siswa = $result_siswa->fetch_assoc()) {
            $id_siswa_untuk_notif = $siswa['siswa_id'];
            $stmt_notif->bind_param("iiss", $id_siswa_untuk_notif, $kelas_id, $pesan_notif, $link_notif);
            $stmt_notif->execute();
        }
    }

    // 6. Redirect guru kembali ke halaman kelas setelah berhasil
    header("Location: ../guru/kelas.php?id=" . $kelas_id . "&status=tugas_added");
    exit();
}

if ($action === 'keluar_kelas' && isset($_GET['id'])) {
    $kelas_id = $_GET['id'];
    $guru_id = $_SESSION['user_id'];

    // Hapus semua entri 'pengajar' untuk guru ini di semua mapel dalam kelas ini
    $stmt = $conn->prepare("
        DELETE FROM pengajar 
        WHERE guru_id = ? AND mapel_id IN (
            SELECT id FROM mapel WHERE kelas_id = ?
        )
    ");
    $stmt->bind_param("ii", $guru_id, $kelas_id);
    $stmt->execute();

    // Arahkan kembali ke dashboard
    header("Location: ../guru/dashboard.php?status=keluar_sukses");
    exit();
}


// Anda bisa menambahkan logika lain untuk Guru di sini (misal: add_materi, edit_tugas, dll)


// Jika tidak ada aksi yang cocok, kembalikan ke dashboard guru
header("Location: ../guru/dashboard.php");
exit();
