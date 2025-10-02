<?php
require_once '../includes/header.php';

// Pastikan hanya guru yang bisa mengakses
if ($_SESSION['role'] !== 'Guru') {
    header("Location: ../index.php");
    exit();
}

// Validasi ID tugas dari URL
if (!isset($_GET['tugas_id']) || !is_numeric($_GET['tugas_id'])) {
    die("ID Tugas tidak valid.");
}

$tugas_id = $_GET['tugas_id'];
$guru_id = $_SESSION['user_id'];

// --- LOGIKA PENILAIAN ---
if (isset($_POST['beri_nilai'])) {
    $pengumpulan_id = $_POST['pengumpulan_id'];
    $nilai = $_POST['nilai'];
    $komentar = $_POST['komentar'];

    $stmt = $conn->prepare("UPDATE pengumpulan SET nilai = ?, komentar = ? WHERE id = ?");
    $stmt->bind_param("isi", $nilai, $komentar, $pengumpulan_id);
    $stmt->execute();
    $stmt->close();
    echo "<div class='alert alert-success'>Nilai berhasil disimpan.</div>";
}

// Ambil detail tugas untuk verifikasi dan judul halaman
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

// Ambil daftar pengumpulan dari siswa
$query_pengumpulan = "
    SELECT p.id, p.file, p.nilai, p.komentar, u.nama as nama_siswa
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

<a href="kelas.php?id=<?php echo $kelas_id; ?>" class="btn btn-secondary mb-3">Kembali ke Kelas</a>
<h3>Penilaian Tugas: <?php echo htmlspecialchars($tugas['judul']); ?></h3>
<hr>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nama Siswa</th>
                <th>File Jawaban</th>
                <th>Nilai (0-100)</th>
                <th>Komentar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_pengumpulan->num_rows > 0): ?>
                <?php while ($row = $result_pengumpulan->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                        <td>
                            <a href="../uploads/pengumpulan/<?php echo $row['file']; ?>" target="_blank">
                                <?php echo htmlspecialchars($row['file']); ?>
                            </a>
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
                                <button type="submit" name="beri_nilai" class="btn btn-primary">Simpan</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Belum ada siswa yang mengumpulkan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php
$stmt_pengumpulan->close();
require_once '../includes/footer.php';
?>