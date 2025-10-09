<?php
require_once '../includes/header.php';

// Auth Guard: Pastikan hanya Admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}

$message = '';
$error = '';

// --- Logika Tambah & Edit Mapel ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Logika untuk Tambah Mapel Baru
    if (isset($_POST['tambah_mapel'])) {
        $nama_mapel = trim($_POST['nama_mapel']);
        if (!empty($nama_mapel)) {
            $stmt = $conn->prepare("INSERT INTO mata_pelajaran (nama_mapel) VALUES (?)");
            $stmt->bind_param("s", $nama_mapel);
            if ($stmt->execute()) {
                $message = "Mata pelajaran baru berhasil ditambahkan.";
            } else {
                $error = "Gagal menambahkan: Nama mata pelajaran mungkin sudah ada.";
            }
            $stmt->close();
        }
    }
    // Logika untuk Update Mapel
    if (isset($_POST['edit_mapel'])) {
        $mapel_id = $_POST['mapel_id'];
        $nama_mapel = trim($_POST['nama_mapel']);
        if (!empty($nama_mapel) && !empty($mapel_id)) {
            $stmt = $conn->prepare("UPDATE mata_pelajaran SET nama_mapel = ? WHERE id = ?");
            $stmt->bind_param("si", $nama_mapel, $mapel_id);
            if ($stmt->execute()) {
                $message = "Nama mata pelajaran berhasil diubah.";
            } else {
                $error = "Gagal mengubah: Nama mata pelajaran mungkin sudah ada.";
            }
            $stmt->close();
        }
    }
}

// --- Logika Hapus Mapel ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $mapel_id = $_GET['id'];
    // PENTING: Nantinya, tambahkan validasi di sini untuk memastikan mapel tidak sedang digunakan oleh kelas manapun sebelum dihapus.
    $stmt = $conn->prepare("DELETE FROM mata_pelajaran WHERE id = ?");
    $stmt->bind_param("i", $mapel_id);
    if ($stmt->execute()) {
        $message = "Mata pelajaran berhasil dihapus.";
    } else {
        $error = "Gagal menghapus mata pelajaran.";
    }
    $stmt->close();
}


// Ambil semua data mapel untuk ditampilkan
$result = $conn->query("SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC");
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Mata Pelajaran</h2>

<?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><?php echo $error; ?></div>
<?php endif; ?>


<div class="bg-white shadow-md rounded-lg p-6 mb-8">
    <h3 class="text-xl font-semibold text-gray-700 mb-4">Tambah Mata Pelajaran Baru</h3>
    <form method="POST" action="manage_mapel.php">
        <div class="flex">
            <input type="text" name="nama_mapel" placeholder="Contoh: Matematika Wajib" class="flex-grow w-full px-4 py-2 border rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <button type="submit" name="tambah_mapel" class="px-6 py-2 text-white font-semibold bg-blue-600 rounded-r-md hover:bg-blue-700">Tambah</button>
        </div>
    </form>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Mata Pelajaran</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($mapel = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="manage_mapel.php" class="flex items-center">
                                <input type="hidden" name="mapel_id" value="<?php echo $mapel['id']; ?>">
                                <input type="text" name="nama_mapel" value="<?php echo htmlspecialchars($mapel['nama_mapel']); ?>" class="w-full px-2 py-1 border rounded-md">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="submit" name="edit_mapel" class="text-indigo-600 hover:text-indigo-900">Simpan</button>
                            </form>
                            <a href="manage_mapel.php?action=delete&id=<?php echo $mapel['id']; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Anda yakin ingin menghapus mata pelajaran ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="px-6 py-4 text-center text-gray-500">Belum ada mata pelajaran.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php require_once '../includes/footer.php'; ?>