<?php
session_start();
// Pastikan file koneksi.php sudah benar path-nya
require_once '../config/db.php';

// Auth Guard & Role Guard: Pastikan user sudah login dan adalah Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak. Anda tidak memiliki izin untuk melakukan aksi ini.");
}

// Mengambil aksi dari POST atau GET
$action = $_REQUEST['action'] ?? '';

//=========================================================
// AKSI MANAJEMEN KELAS
//=========================================================

// --- AKSI EDIT NAMA KELAS ---
if ($action === 'edit_kelas' && isset($_POST['kelas_id'])) {
    $kelas_id = $_POST['kelas_id'];
    $nama_kelas = $_POST['nama_kelas'];

    $stmt = $conn->prepare("UPDATE kelas SET nama_kelas = ? WHERE id = ?");
    $stmt->bind_param("si", $nama_kelas, $kelas_id);
    $stmt->execute();

    header("Location: ../admin/view_class.php?id=" . $kelas_id . "&status=kelas_updated");
    exit();
}

// --- AKSI HAPUS KELAS ---
if ($action === 'delete_class' && isset($_GET['id'])) {
    $kelas_id = $_GET['id'];

    // 1. Hapus semua materi terkait & filenya
    $materi_q = $conn->query("SELECT file FROM materi WHERE kelas_id = $kelas_id");
    while ($materi = $materi_q->fetch_assoc()) {
        if (file_exists('../uploads/materi/' . $materi['file'])) {
            unlink('../uploads/materi/' . $materi['file']);
        }
    }
    $conn->query("DELETE FROM materi WHERE kelas_id = $kelas_id");

    // 2. Hapus semua tugas terkait
    $conn->query("DELETE FROM tugas WHERE kelas_id = $kelas_id");

    // 3. Hapus semua pendaftaran siswa di kelas itu
    $conn->query("DELETE FROM kelas_siswa WHERE kelas_id = $kelas_id");

    // 4. Hapus kelas itu sendiri
    $stmt = $conn->prepare("DELETE FROM kelas WHERE id = ?");
    $stmt->bind_param("i", $kelas_id);
    $stmt->execute();

    header("Location: ../admin/manage_classes.php?status=kelas_deleted");
    exit();
}


//=========================================================
// AKSI MANAJEMEN SISWA DI KELAS
//=========================================================

// --- AKSI KELUARKAN SISWA ---
if ($action === 'remove_student' && isset($_GET['kelas_id']) && isset($_GET['siswa_id'])) {
    $kelas_id = $_GET['kelas_id'];
    $siswa_id = $_GET['siswa_id'];

    $stmt = $conn->prepare("DELETE FROM kelas_siswa WHERE kelas_id = ? AND siswa_id = ?");
    $stmt->bind_param("ii", $kelas_id, $siswa_id);
    $stmt->execute();

    header("Location: ../admin/view_class.php?id=" . $kelas_id . "&status=student_removed");
    exit();
}


//=========================================================
// AKSI MANAJEMEN MATERI
//=========================================================

// --- AKSI TAMBAH MATERI ---
if ($action === 'add_materi' && isset($_POST['kelas_id'])) {
    $kelas_id = $_POST['kelas_id'];
    $judul = $_POST['judul'];

    // Proses upload file
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $target_dir = "../uploads/materi/";
    $new_file_name = time() . '_' . basename($file_name);
    $target_file = $target_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        $stmt = $conn->prepare("INSERT INTO materi (kelas_id, judul, file) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $kelas_id, $judul, $new_file_name);
        $stmt->execute();
    }

    header("Location: ../admin/view_class.php?id=" . $kelas_id . "&status=materi_added");
    exit();
}

// --- AKSI EDIT MATERI ---
if ($action === 'edit_materi' && isset($_POST['materi_id'])) {
    $materi_id = $_POST['materi_id'];
    $kelas_id = $_POST['kelas_id'];
    $judul = $_POST['judul'];
    $file_lama = $_POST['file_lama'];

    // Cek apakah ada file baru yang diupload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        // Hapus file lama
        if (file_exists('../uploads/materi/' . $file_lama)) {
            unlink('../uploads/materi/' . $file_lama);
        }

        // Upload file baru
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $target_dir = "../uploads/materi/";
        $new_file_name = time() . '_' . basename($file_name);
        $target_file = $target_dir . $new_file_name;

        move_uploaded_file($file_tmp, $target_file);

        // Update database dengan file baru
        $stmt = $conn->prepare("UPDATE materi SET judul = ?, file = ? WHERE id = ?");
        $stmt->bind_param("ssi", $judul, $new_file_name, $materi_id);
    } else {
        // Update database tanpa mengubah file
        $stmt = $conn->prepare("UPDATE materi SET judul = ? WHERE id = ?");
        $stmt->bind_param("si", $judul, $materi_id);
    }
    $stmt->execute();

    header("Location: ../admin/view_class.php?id=" . $kelas_id . "&status=materi_updated");
    exit();
}

// --- AKSI HAPUS MATERI ---
if ($action === 'delete_materi' && isset($_GET['id'])) {
    $materi_id = $_GET['id'];
    $kelas_id = $_GET['kelas_id']; // Ambil kelas_id untuk redirect

    // 1. Ambil nama file dari database
    $stmt = $conn->prepare("SELECT file FROM materi WHERE id = ?");
    $stmt->bind_param("i", $materi_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $file_to_delete = $result['file'];

    // 2. Hapus file dari server
    if (file_exists('../uploads/materi/' . $file_to_delete)) {
        unlink('../uploads/materi/' . $file_to_delete);
    }

    // 3. Hapus record dari database
    $stmt = $conn->prepare("DELETE FROM materi WHERE id = ?");
    $stmt->bind_param("i", $materi_id);
    $stmt->execute();

    header("Location: ../admin/view_class.php?id=" . $kelas_id . "&status=materi_deleted");
    exit();
}


//=========================================================
// [BARU] AKSI MANAJEMEN TUGAS
//=========================================================

// --- AKSI TAMBAH TUGAS ---
if ($action === 'add_tugas' && isset($_POST['kelas_id'])) {
    $kelas_id = $_POST['kelas_id'];
    $judul = $_POST['judul'];
    $deadline = $_POST['deadline'];

    // Validasi sederhana untuk memastikan data tidak kosong
    if (!empty($judul) && !empty($deadline)) {
        $stmt = $conn->prepare("INSERT INTO tugas (kelas_id, judul, deadline) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $kelas_id, $judul, $deadline);
        $stmt->execute();

        // Redirect kembali ke halaman detail kelas dengan pesan sukses
        header("Location: ../admin/view_class.php?id=" . $kelas_id . "&status=tugas_added");
        exit();
    }
}


// --- [BARU] AKSI EDIT TUGAS ---
if ($action === 'edit_tugas' && isset($_POST['tugas_id'])) {
    $tugas_id = $_POST['tugas_id'];
    $kelas_id = $_POST['kelas_id'];
    $judul = $_POST['judul'];
    $deadline = $_POST['deadline'];

    $stmt = $conn->prepare("UPDATE tugas SET judul = ?, deadline = ? WHERE id = ?");
    $stmt->bind_param("ssi", $judul, $deadline, $tugas_id);
    $stmt->execute();

    header("Location: ../admin/view_class.php?id=" . $kelas_id . "&status=tugas_updated");
    exit();
}

// --- [BARU] AKSI HAPUS TUGAS ---
if ($action === 'delete_tugas' && isset($_GET['id'])) {
    $tugas_id = $_GET['id'];
    $kelas_id = $_GET['kelas_id']; // Diperlukan untuk redirect kembali

    $stmt = $conn->prepare("DELETE FROM tugas WHERE id = ?");
    $stmt->bind_param("i", $tugas_id);
    $stmt->execute();

    header("Location: ../admin/view_class.php?id=" . $kelas_id . "&status=tugas_deleted");
    exit();
}

// [BARU] AKSI TAMBAH KELAS
if ($action === 'tambah_kelas') {
    $nama_kelas = $_POST['nama_kelas'];
    // Generate kode kelas unik
    $kode_kelas = strtoupper(substr(md5(time() . $nama_kelas), 0, 6));

    $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, kode_kelas) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama_kelas, $kode_kelas);
    $stmt->execute();
    header("Location: ../admin/manage_classes.php?status=kelas_added");
    exit();
}

// [BARU] AKSI TAMBAH MAPEL
if ($action === 'tambah_mapel') {
    $kelas_id = $_POST['kelas_id'];
    $nama_mapel = $_POST['nama_mapel'];
    $stmt = $conn->prepare("INSERT INTO mapel (kelas_id, nama_mapel) VALUES (?, ?)");
    $stmt->bind_param("is", $kelas_id, $nama_mapel);
    $stmt->execute();
    header("Location: ../admin/view_class.php?id=" . $kelas_id);
    exit();
}

// [BARU] AKSI HAPUS MAPEL
if ($action === 'hapus_mapel') {
    $mapel_id = $_GET['id'];
    $kelas_id = $_GET['kelas_id']; // untuk redirect
    $stmt = $conn->prepare("DELETE FROM mapel WHERE id = ?");
    $stmt->bind_param("i", $mapel_id);
    $stmt->execute();
    header("Location: ../admin/view_class.php?id=" . $kelas_id);
    exit();
}

// [BARU] AKSI TUGASKAN GURU
if ($action === 'tugaskan_guru') {
    $kelas_id = $_POST['kelas_id']; // untuk redirect
    $guru_id = $_POST['guru_id'];
    $mapel_id = $_POST['mapel_id'];
    $stmt = $conn->prepare("INSERT INTO pengajar (guru_id, mapel_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $guru_id, $mapel_id);
    $stmt->execute();
    header("Location: ../admin/view_class.php?id=" . $kelas_id);
    exit();
}

// Jika tidak ada aksi yang cocok
header("Location: ../admin/dashboard.php");
exit();
