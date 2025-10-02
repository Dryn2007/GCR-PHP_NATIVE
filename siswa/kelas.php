<?php
require_once '../includes/header.php';

// Pastikan hanya siswa yang bisa mengakses
if ($_SESSION['role'] !== 'Siswa') {
    header("Location: ../index.php");
    exit();
}

// Validasi ID kelas
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID Kelas tidak valid.");
}

$kelas_id = $_GET['id'];
$siswa_id = $_SESSION['user_id'];

// Verifikasi apakah siswa terdaftar di kelas ini
$stmt = $conn->prepare("SELECT k.nama_kelas FROM kelas k JOIN kelas_siswa ks ON k.id = ks.kelas_id WHERE k.id = ? AND ks.siswa_id = ?");
$stmt->bind_param("ii", $kelas_id, $siswa_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Anda tidak terdaftar di kelas ini.");
}
$kelas = $result->fetch_assoc();
$stmt->close();

// --- LOGIKA KUMPUL TUGAS ---
if (isset($_POST['kumpul_tugas'])) {
    $tugas_id = $_POST['tugas_id'];
    $file = $_FILES['file_jawaban'];
    $fileName = time() . '_' . $siswa_id . '_' . $file['name'];
    $filePath = '../uploads/pengumpulan/' . $fileName;

    // Cek apakah sudah pernah mengumpulkan untuk tugas ini
    $stmt_cek = $conn->prepare("SELECT id FROM pengumpulan WHERE tugas_id = ? AND siswa_id = ?");
    $stmt_cek->bind_param("ii", $tugas_id, $siswa_id);
    $stmt_cek->execute();
    if ($stmt_cek->get_result()->num_rows > 0) {
        echo "<div class='alert alert-warning'>Anda sudah pernah mengumpulkan tugas ini.</div>";
    } else {
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $stmt_kumpul = $conn->prepare("INSERT INTO pengumpulan (tugas_id, siswa_id, file) VALUES (?, ?, ?)");
            $stmt_kumpul->bind_param("iis", $tugas_id, $siswa_id, $fileName);
            $stmt_kumpul->execute();
            $stmt_kumpul->close();
            echo "<div class='alert alert-success'>Tugas berhasil dikumpulkan.</div>";
        } else {
            echo "<div class='alert alert-danger'>Gagal mengumpulkan tugas.</div>";
        }
    }
    $stmt_cek->close();
}


// Ambil data materi dan tugas
$materi_list = $conn->query("SELECT id, judul, file FROM materi WHERE kelas_id = $kelas_id ORDER BY created_at DESC");
$tugas_list = $conn->query("SELECT id, judul, deskripsi, deadline FROM tugas WHERE kelas_id = $kelas_id ORDER BY deadline DESC");
?>

<a href="dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>
<h3>Kelas: <?php echo htmlspecialchars($kelas['nama_kelas']); ?></h3>
<hr>

<div class="row">
    <div class="col-md-5">
        <h4>Materi Pembelajaran</h4>
        <ul class="list-group">
            <?php while ($materi = $materi_list->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($materi['judul']); ?>
                    <a href="../uploads/materi/<?php echo $materi['file']; ?>" download class="btn btn-sm btn-primary">Download</a>
                </li>
            <?php endwhile; ?>
            <?php if ($materi_list->num_rows == 0): ?>
                <li class="list-group-item">Belum ada materi.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="col-md-7">
        <h4>Daftar Tugas</h4>
        <?php while ($tugas = $tugas_list->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($tugas['judul']); ?></h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($tugas['deskripsi'])); ?></p>
                    <p class="card-text"><small class="text-muted">Deadline: <?php echo date('d M Y, H:i', strtotime($tugas['deadline'])); ?></small></p>

                    <?php
                    // Cek status pengumpulan tugas ini
                    $stmt_status = $conn->prepare("SELECT file, nilai, komentar FROM pengumpulan WHERE tugas_id = ? AND siswa_id = ?");
                    $stmt_status->bind_param("ii", $tugas['id'], $siswa_id);
                    $stmt_status->execute();
                    $result_status = $stmt_status->get_result();

                    if ($result_status->num_rows > 0) {
                        // Jika sudah mengumpulkan
                        $status = $result_status->fetch_assoc();
                        echo '<div class="alert alert-success">';
                        echo '<strong>Anda sudah mengumpulkan.</strong><br>';
                        echo 'File: ' . htmlspecialchars($status['file']) . '<br>';
                        if ($status['nilai'] !== null) {
                            echo '<strong>Nilai: ' . $status['nilai'] . '</strong><br>';
                            echo 'Komentar Guru: ' . htmlspecialchars($status['komentar']);
                        } else {
                            echo '<i>Belum dinilai oleh guru.</i>';
                        }
                        echo '</div>';
                    } else {
                        // Jika belum mengumpulkan
                        if (new DateTime() > new DateTime($tugas['deadline'])) {
                            echo '<div class="alert alert-danger">Waktu pengumpulan sudah lewat.</div>';
                        } else {
                    ?>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="tugas_id" value="<?php echo $tugas['id']; ?>">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="file_jawaban" required>
                                    <button type="submit" name="kumpul_tugas" class="btn btn-primary">Kumpulkan</button>
                                </div>
                            </form>
                    <?php
                        }
                    }
                    $stmt_status->close();
                    ?>
                </div>
            </div>
        <?php endwhile; ?>
        <?php if ($tugas_list->num_rows == 0): ?>
            <div class="card">
                <div class="card-body">Belum ada tugas.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>