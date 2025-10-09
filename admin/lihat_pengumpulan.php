<?php
require_once '../includes/header.php';

// Auth Guard & Role Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}

// Validasi ID Tugas dari URL
if (!isset($_GET['tugas_id']) || !is_numeric($_GET['tugas_id'])) {
    die("ID Tugas tidak valid.");
}
$tugas_id = $_GET['tugas_id'];

// Ambil informasi tugas untuk judul halaman
$tugas_info_q = $conn->prepare("SELECT judul, kelas_id FROM tugas WHERE id = ?");
$tugas_info_q->bind_param("i", $tugas_id);
$tugas_info_q->execute();
$tugas_info = $tugas_info_q->get_result()->fetch_assoc();
if (!$tugas_info) {
    die("Tugas tidak ditemukan.");
}

// Ambil daftar pengumpulan tugas dari siswa
// Kita join dengan tabel users untuk mendapatkan nama siswa
$pengumpulan_q = $conn->prepare("
    SELECT u.nama, p.file, p.submitted_at
    FROM pengumpulan p
    JOIN users u ON p.siswa_id = u.id
    WHERE p.tugas_id = ?
    ORDER BY p.submitted_at DESC
");
$pengumpulan_q->bind_param("i", $tugas_id);
$pengumpulan_q->execute();
$pengumpulan_list = $pengumpulan_q->get_result();
?>

<div class="mb-6">
    <a href="view_class.php?id=<?php echo $tugas_info['kelas_id']; ?>" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded transition duration-200">&larr; Kembali ke Detail Kelas</a>
</div>

<h2 class="text-3xl font-bold text-gray-800 mb-2">Daftar Pengumpulan Tugas</h2>
<h3 class="text-xl font-semibold text-gray-700 mb-6">"<?php echo htmlspecialchars($tugas_info['judul']); ?>"</h3>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Mengumpulkan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if ($pengumpulan_list->num_rows > 0): ?>
                <?php while ($item = $pengumpulan_list->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($item['nama']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo date('d M Y, H:i', strtotime($item['submitted_at'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="../uploads/pengumpulan/<?php echo htmlspecialchars($item['file']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium">Lihat File</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada siswa yang mengumpulkan tugas ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>