<?php
require_once '../includes/header.php';
// Auth Guard Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Buat Kelas Baru</h2>

<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <form action="../proses/admin_kelas_proses.php" method="POST">
        <input type="hidden" name="action" value="tambah_kelas">
        <div class="mb-4">
            <label for="nama_kelas" class="block text-gray-700 font-semibold mb-2">Nama Kelas</label>
            <input type="text" id="nama_kelas" name="nama_kelas" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Kelas 10A PPLG" required>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
            Simpan Kelas
        </button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>