<?php
require_once '../includes/header.php';

// Auth Guard & Role Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}
if (!isset($_GET['id'])) {
    die("ID Kelas tidak ditemukan.");
}

$kelas_id = $_GET['id'];
$kelas = $conn->query("SELECT nama_kelas FROM kelas WHERE id = $kelas_id")->fetch_assoc();
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Edit Nama Kelas</h2>

<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <form action="../proses/admin_kelas_proses.php" method="POST">
        <input type="hidden" name="action" value="edit_kelas">
        <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">

        <div class="mb-4">
            <label for="nama_kelas" class="block text-gray-700 font-semibold mb-2">Nama Kelas</label>
            <input type="text" id="nama_kelas" name="nama_kelas" value="<?php echo htmlspecialchars($kelas['nama_kelas']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">Simpan Perubahan</button>
            <a href="view_class.php?id=<?php echo $kelas_id; ?>" class="text-gray-600 hover:text-gray-800">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>