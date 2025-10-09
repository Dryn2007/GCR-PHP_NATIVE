<?php
require_once '../includes/header.php';

// Auth Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}

// [DIUBAH] Query untuk mengambil semua kelas, nama guru, nama mapel, dan jumlah siswa
// GANTI DENGAN QUERY YANG BENAR INI
$query_kelas = "
    SELECT k.id, k.nama_kelas, k.kode_kelas, COUNT(ks.siswa_id) AS jumlah_siswa
    FROM kelas k
    LEFT JOIN kelas_siswa ks ON k.id = ks.kelas_id
    GROUP BY k.id
    ORDER BY k.id DESC
";
$result = $conn->query($query_kelas);
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Admin</h2>

<div class="mb-6 flex space-x-2 border-b">
    <a href="dashboard.php" class="py-2 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-t-lg">Manajemen Pengguna</a>
    <a href="manage_classes.php" class="py-2 px-4 text-sm font-medium text-center text-white bg-blue-600 rounded-t-lg">Manajemen Kelas</a>
    <a href="manage_mapel.php" class="py-2 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-t-lg">Manajemen Mapel</a>
</div>

<div class="mb-6 flex justify-end">
    <a href="tambah_kelas.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">+ Tambah Kelas Baru</a>
</div>

<h3 class="text-xl font-semibold text-gray-700 mb-4">Daftar Semua Kelas</h3>
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kelas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Pelajaran</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Kelas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Siswa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($kelas = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($kelas['nama_mapel'] ?? '<em>Belum diatur</em>'); ?></td>
                        <td class="px-6 py-4 font-mono text-gray-700"><?php echo htmlspecialchars($kelas['kode_kelas']); ?></td>
                        <td class="px-6 py-4"><?php echo $kelas['jumlah_siswa']; ?></td>
                        <td class="px-6 py-4">
                            <a href="view_class.php?id=<?php echo $kelas['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">Lihat Detail</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada kelas yang dibuat.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>