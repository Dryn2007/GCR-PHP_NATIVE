<?php require_once '../includes/header.php'; ?>

<?php
// Pastikan hanya guru yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'Guru') {
    header("Location: ../index.php");
    exit();
}

$guru_id = $_SESSION['user_id'];

// Logika untuk membuat kelas baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buat_kelas'])) {
    $nama_kelas = $_POST['nama_kelas'];
    // Generate kode kelas unik
    $kode_kelas = strtoupper(substr(md5(time()), 0, 6));

    $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, kode_kelas, guru_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nama_kelas, $kode_kelas, $guru_id);
    $stmt->execute();
    $stmt->close();
}

// Ambil daftar kelas yang dibuat oleh guru ini
$result = $conn->query("SELECT id, nama_kelas, kode_kelas FROM kelas WHERE guru_id = $guru_id");
?>

<h3>Dashboard Guru</h3>
<hr>

<div class="card mb-4">
    <div class="card-header">Buat Kelas Baru</div>
    <div class="card-body">
        <form method="POST">
            <div class="input-group">
                <input type="text" class="form-control" name="nama_kelas" placeholder="Contoh: Matematika Kelas X" required>
                <button class="btn btn-primary" type="submit" name="buat_kelas">Buat</button>
            </div>
        </form>
    </div>
</div>

<h4>Daftar Kelas Anda</h4>
<div class="list-group">
    <?php while ($row = $result->fetch_assoc()): ?>
        <a href="kelas.php?id=<?php echo $row['id']; ?>" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1"><?php echo htmlspecialchars($row['nama_kelas']); ?></h5>
            </div>
            <p class="mb-1">Kode Kelas: <strong><?php echo $row['kode_kelas']; ?></strong></p>
        </a>
    <?php endwhile; ?>
</div>

<?php require_once '../includes/footer.php'; ?>