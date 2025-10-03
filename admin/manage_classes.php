<?php
require_once '../includes/header.php';

// Auth Guard: Pastikan hanya Admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak. Anda bukan Admin.");
}

// Query untuk mengambil semua kelas, nama guru, dan jumlah siswa per kelas
$query_kelas = "
    SELECT
        k.id, k.nama_kelas, k.kode_kelas,
        u.nama AS nama_guru,
        COUNT(ks.siswa_id) AS jumlah_siswa
    FROM kelas k
    JOIN users u ON k.guru_id = u.id
    LEFT JOIN kelas_siswa ks ON k.id = ks.kelas_id
    GROUP BY k.id, k.nama_kelas, k.kode_kelas, u.nama
    ORDER BY k.id DESC
";
$result = $conn->query($query_kelas);
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Admin</h2>

<div class="mb-6 flex space-x-2 border-b">
    <a href="dashboard.php" class="py-2 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-t-lg">
        Manajemen Pengguna
    </a>
    <a href="manage_classes.php" class="py-2 px-4 text-sm font-medium text-center text-white bg-blue-600 rounded-t-lg">
        Manajemen Kelas
    </a>
</div>

<h3 class="text-xl font-semibold text-gray-700 mb-4">Daftar Semua Kelas</h3>
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guru Pengajar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Kelas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Siswa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php while ($kelas = $result->fetch_assoc()): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($kelas['nama_guru']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-700"><?php echo htmlspecialchars($kelas['kode_kelas']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $kelas['jumlah_siswa']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="view_class.php?id=<?php echo $kelas['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">Lihat Detail</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>