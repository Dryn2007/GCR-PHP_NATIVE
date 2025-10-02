<?php
require_once '../includes/header.php';

// Pastikan hanya guru yang bisa mengakses
if ($_SESSION['role'] !== 'Guru') {
    header("Location: ../index.php");
    exit();
}

// Validasi ID kelas dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID Kelas tidak valid.");
}

$kelas_id = $_GET['id'];
$guru_id = $_SESSION['user_id'];

// Verifikasi apakah guru ini adalah pemilik kelas
$stmt = $conn->prepare("SELECT nama_kelas FROM kelas WHERE id = ? AND guru_id = ?");
$stmt->bind_param("ii", $kelas_id, $guru_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Anda tidak memiliki akses ke kelas ini.");
}
$kelas = $result->fetch_assoc();
$stmt->close();

// --- LOGIKA UPLOAD MATERI ---
if (isset($_POST['upload_materi'])) {
    $judul = $_POST['judul'];
    $file = $_FILES['file_materi'];
    $fileName = time() . '_' . $file['name'];
    $filePath = '../uploads/materi/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $stmt_materi = $conn->prepare("INSERT INTO materi (kelas_id, judul, file) VALUES (?, ?, ?)");
        $stmt_materi->bind_param("iss", $kelas_id, $judul, $fileName);
        $stmt_materi->execute();
        $stmt_materi->close();
        echo "<div class='alert alert-success'>Materi berhasil diunggah.</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal mengunggah materi.</div>";
    }
}

// --- LOGIKA BUAT TUGAS ---
if (isset($_POST['buat_tugas'])) {
    $judul_tugas = $_POST['judul_tugas'];
    $deskripsi = $_POST['deskripsi'];
    $deadline = $_POST['deadline'];

    $stmt_tugas = $conn->prepare("INSERT INTO tugas (kelas_id, judul, deskripsi, deadline) VALUES (?, ?, ?, ?)");
    $stmt_tugas->bind_param("isss", $kelas_id, $judul_tugas, $deskripsi, $deadline);
    $stmt_tugas->execute();
    $stmt_tugas->close();
    echo "<div class='alert alert-success'>Tugas berhasil dibuat.</div>";
}

// Ambil data materi dan tugas untuk ditampilkan
$materi_list = $conn->query("SELECT id, judul, file, created_at FROM materi WHERE kelas_id = $kelas_id ORDER BY created_at DESC");
$tugas_list = $conn->query("SELECT id, judul, deskripsi, deadline FROM tugas WHERE kelas_id = $kelas_id ORDER BY deadline DESC");
?>

<a href="dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>
<h3>Detail Kelas: <?php echo htmlspecialchars($kelas['nama_kelas']); ?></h3>
<hr>

<div class="row">
    <div class="col-md-6">
        <h4>Materi Pembelajaran</h4>
        <div class="card mb-4">
            <div class="card-header">Unggah Materi Baru</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Materi</label>
                        <input type="text" class="form-control" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="file_materi" class="form-label">Pilih File (PDF, PPT, dll)</label>
                        <input type="file" class="form-control" name="file_materi" required>
                    </div>
                    <button type="submit" name="upload_materi" class="btn btn-primary">Unggah</button>
                </form>
            </div>
        </div>

        <h5>Daftar Materi</h5>
        <ul class="list-group">
            <?php while ($materi = $materi_list->fetch_assoc()): ?>
                <li class="list-group-item">
                    <?php echo htmlspecialchars($materi['judul']); ?>
                    <a href="../uploads/materi/<?php echo $materi['file']; ?>" target="_blank" class="btn btn-sm btn-info float-end">Lihat</a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="col-md-6">
        <h4>Tugas</h4>
        <div class="card mb-4">
            <div class="card-header">Buat Tugas Baru</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="judul_tugas" class="form-label">Judul Tugas</label>
                        <input type="text" class="form-control" name="judul_tugas" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="datetime-local" class="form-control" name="deadline" required>
                    </div>
                    <button type="submit" name="buat_tugas" class="btn btn-primary">Buat Tugas</button>
                </form>
            </div>
        </div>

        <h5>Daftar Tugas</h5>
        <div class="list-group">
            <?php while ($tugas = $tugas_list->fetch_assoc()): ?>
                <a href="penilaian.php?tugas_id=<?php echo $tugas['id']; ?>" class="list-group-item list-group-item-action">
                    <h6 class="mb-1"><?php echo htmlspecialchars($tugas['judul']); ?></h6>
                    <small>Deadline: <?php echo date('d M Y, H:i', strtotime($tugas['deadline'])); ?></small>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>