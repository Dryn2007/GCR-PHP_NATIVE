<?php
require_once '../includes/header.php';

// Auth Guard: Pastikan hanya Admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak. Anda bukan Admin.");
}

// Query ini akan mengambil SEMUA pengguna dari tabel 'users'
// dan mengurutkannya berdasarkan ID terbaru.
$result = $conn->query("SELECT id, nama, email, role, status_guru FROM users ORDER BY id DESC");
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Admin</h2>

<div class="mb-6 flex space-x-2 border-b">
    <a href="dashboard.php" class="py-2 px-4 text-sm font-medium text-center text-white bg-blue-600 rounded-t-lg">
        Manajemen Pengguna
    </a>
    <a href="manage_classes.php" class="py-2 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-t-lg">
        Manajemen Kelas
    </a>
</div>

<h3 class="text-xl font-semibold text-gray-700 mb-4">Daftar Semua Pengguna</h3>
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Guru</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['nama']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['role']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($user['status_guru'] == 'pending'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Persetujuan</span>
                            <?php elseif ($user['status_guru'] == 'approved'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <?php if ($user['status_guru'] == 'pending'): ?>
                                <a href="../proses/persetujuan_guru_proses.php?action=approve&id=<?php echo $user['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Setujui</a>
                            <?php endif; ?>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="../proses/persetujuan_guru_proses.php?action=delete&id=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Anda yakin ingin menghapus pengguna ini?')">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada pengguna yang terdaftar di sistem.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>