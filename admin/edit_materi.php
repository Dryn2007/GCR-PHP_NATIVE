<?php
require_once '../includes/header.php';

// Auth Guard & Role Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}
if (!isset($_GET['id'])) {
    die("ID Materi tidak ditemukan.");
}
$materi_id = $_GET['id'];
$materi = $conn->query("SELECT * FROM materi WHERE id = $materi_id")->fetch_assoc();
if (!$materi) {
    die("Materi tidak ditemukan.");
}
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Edit Materi</h2>

<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <form action="../proses/admin_kelas_proses.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_materi">
        <input type="hidden" name="materi_id" value="<?php echo $materi['id']; ?>">
        <input type="hidden" name="kelas_id" value="<?php echo $materi['kelas_id']; ?>">
        <input type="hidden" name="file_lama" value="<?php echo $materi['file']; ?>">

        <div class="mb-4">
            <label for="judul" class="block text-gray-700 font-semibold mb-2">Judul Materi</label>
            <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($materi['judul']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="mb-6">
            <label for="file" class="block text-gray-700 font-semibold mb-2">Ganti File (Opsional)</label>
            <p class="text-sm text-gray-500 mb-2">File saat ini: <?php echo htmlspecialchars($materi['file']); ?></p>
            <input type="file" id="file" name="file" class="w-full text-gray-700">
            <small class="text-gray-500">Kosongkan jika tidak ingin mengganti file.</small>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">Simpan Perubahan</button>
            <a href="view_class.php?id=<?php echo $materi['kelas_id']; ?>" class="text-gray-600 hover:text-gray-800">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>