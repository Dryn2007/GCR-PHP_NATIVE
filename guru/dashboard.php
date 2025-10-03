<?php require_once '../includes/header.php'; ?>

<?php
// Pastikan hanya guru yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Guru') {
    header("Location: ../index.php");
    exit();
}

$guru_id = $_SESSION['user_id'];
$message = '';

// Logika untuk membuat kelas baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buat_kelas'])) {
    $nama_kelas = htmlspecialchars(trim($_POST['nama_kelas']));
    if (!empty($nama_kelas)) {
        // Generate kode kelas unik
        $kode_kelas = strtoupper(substr(md5(time() . $nama_kelas), 0, 6));

        $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, kode_kelas, guru_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nama_kelas, $kode_kelas, $guru_id);
        if ($stmt->execute()) {
            $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6' role='alert'>Kelas baru berhasil dibuat!</div>";
        }
        $stmt->close();
    }
}

// Ambil daftar kelas yang dibuat oleh guru ini
$result = $conn->query("SELECT id, nama_kelas, kode_kelas FROM kelas WHERE guru_id = $guru_id ORDER BY id DESC");
?>

<div class="mb-6 pb-4 border-b border-gray-300">
    <h2 class="text-3xl font-bold text-gray-800">Dashboard Guru</h2>
</div>

<?php echo $message; // Menampilkan pesan sukses setelah membuat kelas 
?>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-700">Buat Kelas Baru</h3>
    </div>
    <div class="p-6">
        <form method="POST">
            <div class="flex">
                <input type="text" class="flex-grow w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" name="nama_kelas" placeholder="Contoh: Fisika Kelas XI" required>
                <button class="px-6 py-2 text-white font-semibold bg-blue-600 rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" type="submit" name="buat_kelas">Buat</button>
            </div>
        </form>
    </div>
</div>

<div>
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Daftar Kelas Anda</h3>
    <div class="space-y-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <a href="kelas.php?id=<?php echo $row['id']; ?>" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md hover:bg-gray-50 transition-all duration-200">
                    <h4 class="text-xl font-semibold text-blue-700"><?php echo htmlspecialchars($row['nama_kelas']); ?></h4>
                    <p class="text-sm text-gray-600 mt-1">Kode Kelas:
                        <strong class="font-mono bg-gray-200 text-gray-800 py-1 px-2 rounded-md"><?php echo $row['kode_kelas']; ?></strong>
                    </p>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md">
                <p>Anda belum membuat kelas. Silakan gunakan formulir di atas untuk membuat kelas pertama Anda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>