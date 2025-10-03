<?php
require_once '../includes/header.php';

// Pastikan hanya guru yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Guru') {
    header("Location: ../index.php");
    exit();
}

// Validasi ID tugas dari URL, pastikan ada dan numerik
if (!isset($_GET['tugas_id']) || !is_numeric($_GET['tugas_id'])) {
    die("Error: ID Tugas tidak valid.");
}

$tugas_id = $_GET['tugas_id'];
$guru_id = $_SESSION['user_id'];

// --- LOGIKA UNTUK MENYIMPAN PENILAIAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['beri_nilai'])) {
    // Sanitasi input
    $pengumpulan_id = filter_input(INPUT_POST, 'pengumpulan_id', FILTER_VALIDATE_INT);
    $nilai = filter_input(INPUT_POST, 'nilai', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 100]]);
    $komentar = htmlspecialchars($_POST['komentar']);

    if ($pengumpulan_id && $nilai !== false) {
        // Update nilai dan komentar di database
        $stmt = $conn->prepare("UPDATE pengumpulan SET nilai = ?, komentar = ? WHERE id = ?");
        $stmt->bind_param("isi", $nilai, $komentar, $pengumpulan_id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Nilai berhasil disimpan.</div>";
        } else {
            echo "<div class='alert alert-danger'>Gagal menyimpan nilai.</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>Input tidak valid.</div>";
    }
}

// Ambil detail tugas untuk judul halaman dan verifikasi kepemilikan guru
$stmt_tugas = $conn->prepare("SELECT t.judul, t.kelas_id FROM tugas t JOIN kelas k ON t.kelas_id = k.id WHERE t.id = ? AND k.guru_id = ?");
$stmt_tugas->bind_param("ii", $tugas_id, $guru_id);
$stmt_tugas->execute();
$result_tugas = $stmt_tugas->get_result();
if ($result_tugas->num_rows === 0) {
    die("Anda tidak memiliki akses ke tugas ini atau tugas tidak ditemukan.");
}
$tugas = $result_tugas->fetch_assoc();
$kelas_id = $tugas['kelas_id'];
$stmt_tugas->close();

// Ambil daftar pengumpulan dari siswa (termasuk file dan jawaban teks)
$query_pengumpulan = "
    SELECT p.id, p.file, p.jawaban_teks, p.nilai, p.komentar, u.nama as nama_siswa
    FROM pengumpulan p
    JOIN users u ON p.siswa_id = u.id
    WHERE p.tugas_id = ?
    ORDER BY u.nama ASC
";
$stmt_pengumpulan = $conn->prepare($query_pengumpulan);
$stmt_pengumpulan->bind_param("i", $tugas_id);
$stmt_pengumpulan->execute();
$result_pengumpulan = $stmt_pengumpulan->get_result();

?>

<a href="kelas.php?id=<?php echo $kelas_id; ?>" class="btn btn-secondary mb-3">&larr; Kembali ke Detail Kelas</a>
<h3>Penilaian Tugas: <?php echo htmlspecialchars($tugas['judul']); ?></h3>
<hr>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th style="width: 15%;">Nama Siswa</th>
                <th style="width: 35%;">Jawaban Siswa</th>
                <th style="width: 10%;">Nilai (0-100)</th>
                <th style="width: 25%;">Komentar</th>
                <th style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_pengumpulan->num_rows > 0): ?>
                <?php while ($row = $result_pengumpulan->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                        <td>
                            <?php if (!empty($row['file'])): // Jika ada file 
                            ?>
                                <a href="../uploads/pengumpulan/<?php echo htmlspecialchars($row['file']); ?>" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-file-download"></i> Lihat File
                                </a>
                            <?php elseif (!empty($row['jawaban_teks'])): // Jika ada jawaban teks 
                            ?>
                                <div class="jawaban-teks-box">
                                    <?php echo nl2br(htmlspecialchars($row['jawaban_teks'])); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">-- Tidak ada jawaban --</span>
                            <?php endif; ?>
                        </td>
                        <form method="POST">
                            <input type="hidden" name="pengumpulan_id" value="<?php echo $row['id']; ?>">
                            <td>
                                <input type="number" name="nilai" class="form-control" value="<?php echo $row['nilai']; ?>" min="0" max="100">
                            </td>
                            <td>
                                <textarea name="komentar" class="form-control" rows="2"><?php echo htmlspecialchars($row['komentar']); ?></textarea>
                            </td>
                            <td>
                                <button type="submit" name="beri_nilai" class="btn btn-primary w-100">Simpan</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center p-4">Belum ada siswa yang mengumpulkan tugas ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    .jawaban-teks-box {
        max-height: 150px;
        overflow-y: auto;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 10px;
        border-radius: 5px;
        white-space: pre-wrap;
        /* Agar format teks tetap terjaga */
    }
</style>

<?php
$stmt_pengumpulan->close();
require_once '../includes/footer.php';
?>