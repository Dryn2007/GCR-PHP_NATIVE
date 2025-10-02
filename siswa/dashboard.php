<?php require_once '../includes/header.php'; ?>

<?php
// Pastikan hanya siswa yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'Siswa') {
    header("Location: ../index.php");
    exit();
}

$siswa_id = $_SESSION['user_id'];
$message = '';

// Logika untuk bergabung ke kelas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_kelas'])) {
    $kode_kelas = $_POST['kode_kelas'];

    // Cari kelas berdasarkan kode
    $stmt = $conn->prepare("SELECT id FROM kelas WHERE kode_kelas = ?");
    $stmt->bind_param("s", $kode_kelas);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $kelas = $result->fetch_assoc();
        $kelas_id = $kelas['id'];

        // Cek apakah siswa sudah terdaftar di kelas ini
        $stmt_check = $conn->prepare("SELECT id FROM kelas_siswa WHERE kelas_id = ? AND siswa_id = ?");
        $stmt_check->bind_param("ii", $kelas_id, $siswa_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            // Daftarkan siswa ke kelas
            $stmt_join = $conn->prepare("INSERT INTO kelas_siswa (kelas_id, siswa_id) VALUES (?, ?)");
            $stmt_join->bind_param("ii", $kelas_id, $siswa_id);
            $stmt_join->execute();
            $message = '<div class="alert alert-success">Berhasil bergabung ke kelas!</div>';
        } else {
            $message = '<div class="alert alert-warning">Anda sudah terdaftar di kelas ini.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Kode kelas tidak ditemukan.</div>';
    }
}

// Ambil daftar kelas yang diikuti oleh siswa ini
$stmt = $conn->prepare("SELECT k.id, k.nama_kelas, u.nama AS nama_guru FROM kelas k JOIN users u ON k.guru_id = u.id JOIN kelas_siswa ks ON k.id = ks.kelas_id WHERE ks.siswa_id = ?");
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$result_kelas_diikuti = $stmt->get_result();
?>

<h3>Dashboard Siswa</h3>
<hr>
<?php echo $message; ?>

<div class="card mb-4">
    <div class="card-header">Gabung Kelas Baru</div>
    <div class="card-body">
        <form method="POST">
            <div class="input-group">
                <input type="text" class="form-control" name="kode_kelas" placeholder="Masukkan Kode Kelas" required>
                <button class="btn btn-primary" type="submit" name="join_kelas">Gabung</button>
            </div>
        </form>
    </div>
</div>

<h4>Daftar Kelas Anda</h4>
<div class="list-group">
    <?php while ($row = $result_kelas_diikuti->fetch_assoc()): ?>
        <a href="kelas.php?id=<?php echo $row['id']; ?>" class="list-group-item list-group-item-action">
            <h5 class="mb-1"><?php echo htmlspecialchars($row['nama_kelas']); ?></h5>
            <small>Guru: <?php echo htmlspecialchars($row['nama_guru']); ?></small>
        </a>
    <?php endwhile; ?>
</div>

<?php require_once '../includes/footer.php'; ?>