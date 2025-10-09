<?php
require_once '../includes/header.php';

// Auth Guard & Role Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}

// Validasi dan ambil data tugas yang akan diedit
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID Tugas tidak valid.");
}
$tugas_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM tugas WHERE id = ?");
$stmt->bind_param("i", $tugas_id);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_assoc();

if (!$tugas) {
    die("Tugas tidak ditemukan.");
}
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Edit Tugas</h2>

<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <form action="../proses/admin_kelas_proses.php" method="POST">
        <input type="hidden" name="action" value="edit_tugas">
        <input type="hidden" name="tugas_id" value="<?php echo $tugas['id']; ?>">
        <input type="hidden" name="kelas_id" value="<?php echo $tugas['kelas_id']; ?>">

        <div class="mb-4">
            <label for="judul" class="block text-gray-700 font-semibold mb-2">Judul Tugas</label>
            <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($tugas['judul']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="mb-6">
            <label for="deadline" class="block text-gray-700 font-semibold mb-2">Tenggat Waktu (Deadline)</label>
            <input type="datetime-local" id="deadline" name="deadline" value="<?php echo date('Y-m-d\TH:i', strtotime($tugas['deadline'])); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">Simpan Perubahan</button>
            <a href="view_class.php?id=<?php echo $tugas['kelas_id']; ?>" class="text-gray-600 hover:text-gray-800">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>